# Convenciones del Proyecto - eCommerce Laravel 12

---

## 1. Naming Conventions

| Elemento | Convención | Ejemplo |
|----------|-----------|---------|
| Model | PascalCase singular | `Product`, `OrderItem` |
| Controller | PascalCase + Controller | `ProductController` |
| Service | PascalCase + Service | `CartService` |
| Repository | PascalCase + Repository | `ProductRepository` |
| Interface | PascalCase + Interface | `ProductRepositoryInterface` |
| Action | Verbo + Sustantivo + Action | `CreateOrderAction` |
| DTO | PascalCase + DTO | `CreateProductDTO` |
| Event | PascalCase en pasado | `OrderPlaced` |
| Listener | PascalCase con verbo | `SendOrderConfirmation` |
| Job | PascalCase con verbo | `ProcessPayment` |
| Policy | PascalCase + Policy | `OrderPolicy` |
| Request | Verbo + Request | `StoreProductRequest` |
| Resource | PascalCase + Resource | `ProductResource` |
| Enum | PascalCase | `OrderStatus` |
| Migration | snake_case descriptivo | `create_products_table` |
| Tabla DB | snake_case plural | `order_items` |
| Columna DB | snake_case | `unit_price` |
| Ruta API | kebab-case plural | `/api/v1/order-items` |
| Config key | snake_case | `ecommerce.tax_rate` |
| Test method | snake_case descriptivo | `test_create_order_with_valid_data_returns_201` |

---

## 2. Estándar de Commits

### Formato
```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types
| Type | Uso |
|------|-----|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de bug |
| `refactor` | Refactorización sin cambio funcional |
| `docs` | Documentación |
| `test` | Tests |
| `chore` | Mantenimiento, config, CI |
| `perf` | Mejora de rendimiento |
| `security` | Parche de seguridad |

### Scopes válidos
`catalog`, `cart`, `orders`, `users`, `payments`, `shipping`, `promotions`, `notifications`, `infra`, `docker`, `ci`

### Ejemplos
```
feat(catalog): add product variant support
fix(cart): prevent negative quantities
refactor(orders): extract order total calculation to service
security(payments): sanitize webhook payload
test(orders): add PlaceOrderAction unit tests
```

---

## 3. Estrategia de Ramas

```
main ────────────────────────────────── Producción estable
  │
  └── develop ───────────────────────── Integración
        │
        ├── feature/catalog/variants ── Features nuevos
        ├── feature/payments/stripe
        ├── fix/cart/negative-qty ────── Bugs no urgentes
        │
        └── release/v1.2.0 ──────────── Pre-release
              │
main ◀────────┘
  │
  └── hotfix/payment-timeout ────────── Fixes urgentes en producción
```

| Rama | Origen | Merge a | Cuándo |
|------|--------|---------|--------|
| `feature/<domain>/<desc>` | develop | develop | Feature completo + tests |
| `fix/<domain>/<desc>` | develop | develop | Bug corregido + test |
| `release/v<semver>` | develop | main + develop | QA aprobado |
| `hotfix/<desc>` | main | main + develop | Bug crítico producción |

---

## 4. Patrón de Servicios

```php
<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Orders\DTOs\CreateOrderDTO;
use App\Domain\Orders\Actions\PlaceOrderAction;
use App\Domain\Orders\Repositories\OrderRepositoryInterface;
use App\Domain\Orders\Models\Order;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PlaceOrderAction $placeOrderAction,
    ) {}

    public function placeOrder(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            return $this->placeOrderAction->execute($dto);
        });
    }

    public function cancel(int $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);
        // lógica de negocio...
        return $order;
    }
}
```

**Reglas**:
- `final class` siempre
- Constructor injection con `readonly`
- Métodos públicos con return type
- Transacciones para operaciones multi-tabla
- `declare(strict_types=1)` obligatorio

---

## 5. Patrón de Repositorios

### Interface

```php
<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Product;
    public function findOrFail(int $id): Product;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Product;
    public function update(int $id, array $data): Product;
    public function delete(int $id): bool;
    public function findByCategory(int $categoryId): LengthAwarePaginator;
    public function search(string $query): LengthAwarePaginator;
}
```

### Implementación

```php
<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly Product $model,
    ) {}

    public function find(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['category', 'brand'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update($data);
        return $product->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function findByCategory(int $categoryId): LengthAwarePaginator
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->with(['brand'])
            ->paginate();
    }

    public function search(string $query): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->paginate();
    }
}
```

---

## 6. Patrón de DTOs

```php
<?php

declare(strict_types=1);

namespace App\Domain\Catalog\DTOs;

use App\Http\Requests\Catalog\StoreProductRequest;

final readonly class CreateProductDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public int $priceInCents,
        public int $categoryId,
        public ?int $brandId,
        public int $stock,
        public string $sku,
    ) {}

    public static function fromRequest(StoreProductRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            priceInCents: (int) ($request->validated('price') * 100),
            categoryId: $request->validated('category_id'),
            brandId: $request->validated('brand_id'),
            stock: $request->validated('stock'),
            sku: $request->validated('sku'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
```

**Reglas**:
- `final readonly class`
- Propiedades inmutables y tipadas
- Factories `fromRequest()` y `fromArray()`
- Sin lógica de negocio
- Precios en centavos (int) para evitar problemas de flotantes

---

## 7. Validaciones (Form Requests)

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'    => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'stock'       => ['required', 'integer', 'min:0'],
            'sku'         => ['required', 'string', 'max:100', Rule::unique('products', 'sku')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre del producto es obligatorio.',
            'price.min'         => 'El precio debe ser al menos 0.01.',
            'sku.unique'        => 'Este SKU ya está en uso.',
            'category_id.exists'=> 'La categoría seleccionada no existe.',
        ];
    }
}
```

**Reglas de validación**:
- Toda validación en Form Requests, nunca en controllers
- `authorize()` siempre implementado (revisa permisos)
- Mensajes en español para el usuario final
- Rules como arrays, no strings con pipes
- Usar `Rule::exists()`, `Rule::unique()` para validaciones DB

---

## 8. Manejo de Excepciones

### Excepciones de Dominio

```php
<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use Exception;

final class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Stock insuficiente para '{$productName}': solicitado {$requested}, disponible {$available}."
        );
    }
}
```

### Handler Global (API)

```php
// bootstrap/app.php → withExceptions()
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (InsufficientStockException $e) {
        return response()->json([
            'error' => [
                'code'    => 'INSUFFICIENT_STOCK',
                'message' => $e->getMessage(),
                'details' => [
                    'product'   => $e->productName,
                    'requested' => $e->requested,
                    'available' => $e->available,
                ],
            ],
        ], 422);
    });
})
```

**Reglas**:
- Cada dominio tiene sus propias excepciones
- Nunca `catch (Exception $e)` vacío
- Códigos de error como constantes string (`INSUFFICIENT_STOCK`)
- HTTP codes correctos: 404 not found, 422 validation, 409 conflict, 402 payment

---

## 9. Logging

```php
// Uso en servicios
Log::channel('orders')->info('Order placed', [
    'order_id'   => $order->id,
    'user_id'    => $order->user_id,
    'total'      => $order->total,
    'items_count'=> $order->items->count(),
]);

Log::channel('payments')->warning('Payment retry', [
    'order_id' => $orderId,
    'attempt'  => $attempt,
    'gateway'  => 'stripe',
]);

Log::channel('security')->critical('Unauthorized access attempt', [
    'ip'       => $request->ip(),
    'endpoint' => $request->path(),
    'user_id'  => $request->user()?->id,
]);
```

**Canales de log** (configurar en `config/logging.php`):
- `orders` — Lifecycle de pedidos
- `payments` — Transacciones de pago
- `security` — Eventos de seguridad y acceso
- `performance` — Queries lentas, timeouts
- `default` — Todo lo demás

**Reglas**:
- Siempre pasar contexto como array asociativo
- Nunca loggear datos sensibles (passwords, tarjetas, tokens)
- Usar niveles correctos: info, warning, error, critical
- Log channel por dominio para facilitar debug

---

## 10. Gestión de Errores API

### Formato de Respuesta Estándar

**Éxito**:
```json
{
  "data": { ... },
  "message": "Product created successfully",
  "meta": {
    "timestamp": "2025-01-15T10:30:00Z"
  }
}
```

**Éxito con paginación**:
```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  },
  "links": {
    "first": "/api/v1/products?page=1",
    "last": "/api/v1/products?page=10",
    "next": "/api/v1/products?page=2",
    "prev": null
  }
}
```

**Error**:
```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Stock insuficiente para 'Laptop Pro'",
    "details": {
      "product": "Laptop Pro",
      "requested": 5,
      "available": 2
    }
  }
}
```

**Error de validación**:
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos proporcionados no son válidos",
    "details": {
      "name": ["El nombre del producto es obligatorio."],
      "price": ["El precio debe ser al menos 0.01."]
    }
  }
}
```

### HTTP Status Codes

| Code | Uso |
|------|-----|
| `200` | OK - GET, PUT exitoso |
| `201` | Created - POST exitoso |
| `204` | No Content - DELETE exitoso |
| `400` | Bad Request - Input mal formado |
| `401` | Unauthorized - Sin autenticación |
| `403` | Forbidden - Sin permisos |
| `404` | Not Found - Recurso no existe |
| `409` | Conflict - Operación conflictiva |
| `422` | Unprocessable - Validación fallida |
| `429` | Too Many Requests - Rate limited |
| `500` | Server Error - Error interno |
