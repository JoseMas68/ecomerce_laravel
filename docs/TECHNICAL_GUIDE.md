# Guía Técnica Avanzada - eCommerce Laravel 12

---

## 1. Caching (Redis)

### Estrategia por Dominio

```php
// Catálogo: Cache por tags, invalidar por producto/categoría
Cache::tags(['catalog', 'products'])->remember(
    key: "product:{$id}",
    ttl: now()->addHours(6),
    callback: fn () => $this->productRepository->findWithRelations($id),
);

// Invalidar al actualizar
Cache::tags(['catalog', 'products'])->flush();

// Carrito: Cache por usuario, TTL corto
Cache::remember("cart:user:{$userId}", now()->addMinutes(30), fn () => ...);

// Categorías (cambian poco): Cache largo
Cache::tags(['catalog', 'categories'])->remember(
    'categories:tree', now()->addDay(), fn () => ...
);
```

### Política de Cache

| Recurso | TTL | Invalidación |
|---------|-----|-------------|
| Producto detalle | 6h | Al editar producto |
| Lista productos | 1h | Al crear/editar/eliminar |
| Categorías | 24h | Al modificar árbol |
| Carrito | 30min | Al modificar carrito |
| User profile | 2h | Al editar perfil |
| Config global | 24h | Al modificar config |

### Config Redis

```php
// config/cache.php
'redis' => [
    'driver'     => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
    'prefix'     => 'ecommerce_cache',
],
```

---

## 2. Indexación en MariaDB

### Índices Recomendados

```sql
-- Products: búsqueda, filtro por categoría, ordenamiento
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_brand ON products(brand_id);
CREATE INDEX idx_products_price ON products(price_in_cents);
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_slug ON products(slug);
CREATE FULLTEXT INDEX idx_products_search ON products(name, description);

-- Orders: consultas por usuario y estado
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_created ON orders(created_at);
CREATE INDEX idx_orders_number ON orders(order_number);

-- Order Items: consulta por producto
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);

-- Coupons: validación rápida
CREATE INDEX idx_coupons_code ON coupons(code);
CREATE INDEX idx_coupons_valid ON coupons(code, expires_at, is_active);
```

### Reglas de Indexación

- **Índice compuesto**: columnas que se filtran juntas (`user_id + status`)
- **Cardinalidad**: indexar columnas con muchos valores distintos
- **Nunca indexar**: columnas tipo TEXT largas (usar FULLTEXT si necesitas buscar)
- **Monitorear**: `EXPLAIN ANALYZE` en queries >50ms
- **Revisar**: `SHOW INDEX FROM table` periódicamente

### Configuración MariaDB Optimizada

```ini
# docker/mariadb/conf.d/custom.cnf
[mysqld]
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
innodb_flush_method = O_DIRECT
innodb_flush_log_at_trx_commit = 2
query_cache_type = OFF
max_connections = 200
join_buffer_size = 4M
sort_buffer_size = 4M
tmp_table_size = 64M
max_heap_table_size = 64M
```

---

## 3. Seguridad

### CSRF Protection

```php
// Automático en Laravel para rutas web
// Para API stateless con Sanctum: token-based, no necesita CSRF
// Para SPA: configurar Sanctum stateful domains
```

### XSS Prevention

```php
// Blade escapa automáticamente con {{ }}
// Para API: sanitizar input en Form Requests
'name' => ['required', 'string', 'max:255'],
// strip_tags() para contenido rich-text si es necesario

// Middleware en Kernel
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SanitizeInput::class);
})
```

### SQL Injection

```php
// SIEMPRE usar Eloquent o Query Builder (parameterized queries)
// NUNCA:
DB::raw("SELECT * FROM products WHERE name = '$name'");
// SIEMPRE:
Product::where('name', $name)->get();
DB::select('SELECT * FROM products WHERE name = ?', [$name]);
```

### Headers de Seguridad (Nginx)

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### Almacenamiento Seguro

```php
// Passwords: bcrypt (default Laravel)
Hash::make($password);

// Datos sensibles: encrypt/decrypt
Crypt::encryptString($cardToken);
Crypt::decryptString($encryptedToken);

// API keys en .env, NUNCA en código
config('services.stripe.secret');
```

---

## 4. Rate Limiting

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi('60,1'); // 60 req/min por defecto
})

// routes/api.php - Limits específicos
Route::middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// AppServiceProvider
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('checkout', function (Request $request) {
    return Limit::perMinute(3)->by($request->user()->id);
});

RateLimiter::for('search', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});
```

---

## 5. Autenticación (Laravel Sanctum)

### Setup

```php
// API Token authentication para mobile/external clients
// SPA authentication para frontend propio

// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:8080')),
'expiration' => 60 * 24 * 7, // 7 días
```

### Endpoints de Auth

```php
// routes/api/v1/users.php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/me', [ProfileController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/tokens', [AuthController::class, 'revokeAll']);
});
```

### Token con Abilities

```php
// Crear token con permisos específicos
$token = $user->createToken('api-token', [
    'orders:read',
    'orders:create',
    'profile:update',
]);

// Verificar en middleware o controller
if ($request->user()->tokenCan('orders:create')) {
    // ...
}
```

---

## 6. Escalabilidad Futura

### Fase 1: Monolito Modular (Actual)

```
[Nginx LB] → [PHP App 1] → [MariaDB Primary]
             [PHP App 2]    [MariaDB Replica]
                           [Redis Cluster]
```

### Fase 2: Servicios Separados

```
[API Gateway]
  ├── [Catalog Service]  → [DB Catalog]
  ├── [Order Service]    → [DB Orders]
  ├── [Payment Service]  → [DB Payments]
  └── [User Service]     → [DB Users]
  
[Message Queue (Redis/RabbitMQ)]
[Shared Cache (Redis Cluster)]
```

### Preparación desde el Monolito

1. **Dominios independientes**: cada dominio puede extraerse sin romper otros
2. **Interfaces en boundaries**: comunicación entre dominios vía interfaces, no modelos directos
3. **Events para comunicación cross-domain**: `OrderPlaced` → `DeductStock` (Catalog escucha)
4. **IDs como referencia**: entre dominios usar IDs, no relaciones Eloquent directas
5. **Config separada**: `config/catalog.php`, `config/payments.php` por dominio

---

## 7. Queue System

### Configuración

```php
// config/queue.php
'redis' => [
    'driver'      => 'redis',
    'connection'  => 'default',
    'queue'       => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for'   => null,
],
```

### Queues por Prioridad

```php
// Colas separadas por tipo de tarea
// docker-compose.yml ya incluye queue worker

// Alta prioridad: pagos, stock
ProcessPaymentJob::dispatch($order)->onQueue('payments');
DeductStockJob::dispatch($items)->onQueue('inventory');

// Media prioridad: emails
SendOrderConfirmationJob::dispatch($order)->onQueue('notifications');

// Baja prioridad: reportes, cleanup
GenerateMonthlyReportJob::dispatch()->onQueue('reports');
PruneExpiredCartsJob::dispatch()->onQueue('maintenance');
```

### Worker Config para Producción

```bash
# Alta prioridad primero
php artisan queue:work redis --queue=payments,inventory,notifications,reports,maintenance --tries=3 --max-time=3600

# Workers dedicados por cola crítica
php artisan queue:work redis --queue=payments --tries=5 --backoff=30
php artisan queue:work redis --queue=notifications --tries=3 --backoff=10
```

### Job Pattern

```php
<?php

declare(strict_types=1);

namespace App\Domain\Orders\Jobs;

use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;

final class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly Order $order,
    ) {
        $this->onQueue('payments');
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->order->id),
        ];
    }

    public function handle(PaymentService $paymentService): void
    {
        $paymentService->processForOrder($this->order);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('payments')->error('Payment processing failed', [
            'order_id'  => $this->order->id,
            'exception' => $exception->getMessage(),
            'attempts'  => $this->attempts(),
        ]);
    }
}
```

---

## 8. Microservicios - Estrategia de Migración

### Cuándo Migrar

| Señal | Acción |
|-------|--------|
| Un dominio tiene >50 endpoints | Extraer a microservicio |
| Team >8 devs trabajando en monolito | Dividir por dominio |
| Un dominio necesita escalar independientemente | Separar servicio |
| Deploy del monolito tarda >10 min | Dividir pipeline |

### Pasos de Extracción

1. **Asegurar que el dominio no tiene acoplamiento directo** con otros dominios
2. **Reemplazar llamadas internas** por eventos o HTTP calls internos
3. **Duplicar la base de datos** del dominio a su propio servidor
4. **Crear API Gateway** que rutea al microservicio
5. **Implementar service discovery** (Consul, Kubernetes DNS)
6. **Agregar circuit breaker** para fallos de comunicación

### Comunicación entre Servicios

```
Síncrona: HTTP REST (para queries, datos en tiempo real)
Asíncrona: Events via Redis/RabbitMQ (para comandos, notificaciones)

Checkout Flow:
  1. [Cart Service] --HTTP--> [Order Service]: crear pedido
  2. [Order Service] --Event--> [Payment Service]: procesar pago
  3. [Payment Service] --Event--> [Order Service]: confirmar pago
  4. [Order Service] --Event--> [Inventory Service]: descontar stock
  5. [Order Service] --Event--> [Notification Service]: enviar email
```
