# Service Providers - eCommerce Laravel 12

## Qué son los ServiceProviders

Los ServiceProviders en Laravel son el lugar central donde **registras servicios, configuras bindings, y bootstraspeas características** de tu aplicación.

En nuestra arquitectura DDD, hemos creado **ServiceProvider por dominio** para mantener la separación de responsabilidades.

---

## Service Providers Creados

### 1. RepositoryServiceProvider
**Ubicación**: `app/Infrastructure/Providers/RepositoryServiceProvider.php`

**Propósito**: Registra TODOS los bindings de repositorios de todos los dominios.

**Contenido**:
- Interfaces y implementaciones de repositorios
- Bindings de Gateways de pago
- Centraliza todas las dependencias de acceso a datos

### 2. CatalogServiceProvider
**Ubicación**: `app/Infrastructure/Providers/CatalogServiceProvider.php`

**Propósito**: Configuración específica del dominio de Catálogo.

**Contenido**:
- Bindings de repositorios de catálogo (Products, Categories, Brands)
- Migrations del dominio
- Views específicas del dominio (si aplica)

### 3. OrderServiceProvider
**Ubicación**: `app/Infrastructure/Providers/OrderServiceProvider.php`

**Propósito**: Configuración específica del dominio de Pedidos.

**Contenido**:
- Bindings de repositorios de pedidos
- Migrations del dominio
- Events y Listeners del dominio

---

## Cómo Registrar los Providers

### Paso 1: Actualizar `config/app.php`

Agrega los providers al array `providers`:

```php
// config/app.php

'providers' => [
    // Laravel Framework Service Providers...
    Illuminate\Auth\AuthServiceProvider::class,
    // ... otros providers de Laravel

    // ──────────────────────────────────────────────
    // Application Service Providers (DDD)
    // ──────────────────────────────────────────────
    App\Providers\AppServiceProvider::class,

    // Dominios
    App\Infrastructure\Providers\RepositoryServiceProvider::class,
    App\Infrastructure\Providers\CatalogServiceProvider::class,
    App\Infrastructure\Providers\OrderServiceProvider::class,
    // Agregar aquí más providers por dominio según se creen
],
```

### Paso 2: Limpiar caché de configuración

```bash
php artisan config:clear
php artisan optimize:clear
```

### Paso 3: Verificar que los providers se cargan

```bash
php artisan tinker
>>> app()->getBindings()
```

---

## Orden de Carga Importante

Los ServiceProviders se cargan en este orden:

1. **RepositoryServiceProvider** - PRIMERO
   - Registra todos los bindings de repositorios
   - Otros providers pueden depender de estos bindings

2. **Domain ServiceProviders** - SEGUNDO
   - CatalogServiceProvider
   - OrderServiceProvider
   - CartServiceProvider (cuando se cree)
   - etc.

3. **Feature-based ServiceProviders** - TERCERO
   - PaymentServiceProvider
   - ShippingServiceProvider
   - etc.

---

## Ejemplo de Provider Completo

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Catalog\Repositories\EloquentProductRepository;
use App\Domain\Catalog\Repositories\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);

        // Singleton para servicios sin estado
        $this->app->singleton(\App\Domain\Catalog\Services\CatalogService::class);

        // Scoped bindings para instancias por request
        $this->app->scoped(\App\Domain\Catalog\DTOs\CreateProductDTO::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations del dominio
        $this->loadMigrationsFrom(database_path('migrations/domain/catalog'));

        // Events y Listeners del dominio
        Event::listen(
            \App\Domain\Catalog\Events\ProductCreated::class,
            \App\Domain\Catalog\Listeners\UpdateSearchIndex::class
        );

        // Routes del dominio (si no se usan archivos separados)
        // $this->loadRoutesFrom(base_path('routes/api/v1/catalog.php'));

        // Views del dominio
        // $this->loadViewsFrom(resource_path('views/domains/catalog'), 'catalog');

        // Commands del dominio
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Domain\Catalog\Commands\SyncCatalog::class,
            ]);
        }

        // Policies del dominio
        // Gate::policy(Product::class, ProductPolicy::class);
    }
}
```

---

## Providers por Crear

### Pendientes (según ROADMAP)

- [ ] **CartServiceProvider** - Dominio del carrito
- [ ] **UserServiceProvider** - Dominio de usuarios
- [ ] **PaymentServiceProvider** - Dominio de pagos
- [ ] **ShippingServiceProvider** - Dominio de envíos
- [ ] **PromotionServiceProvider** - Dominio de promociones
- [ ] **NotificationServiceProvider** - Dominio de notificaciones

---

## Best Practices

### ✅ Hacer
1. **Usar `declare(strict_types=1)`** - Type safety
2. **Interfaces siempre primero** - Dependency Inversion Principle
3. **Separar register() y boot()** - register para bindings, boot para eventos
4. **Usar typed properties** - PHP 8.4
5. **Lazy loading** - Solo cargar lo necesario

### ❌ No Hacer
1. No poner lógica de negocio en providers
2. No hacer llamadas a BD en `register()`
3. No sobrecargar un solo provider (máximo 5-7 bindings)
4. No olvidar limpiar cache después de modificar providers

---

## Troubleshooting

### Error: "Target class [X] does not exist"

**Causa**: Provider no registrado o clase no existe.

**Solución**:
```bash
php artisan config:clear
composer dump-autoload
```

### Error: "Interface not bound"

**Causa**: RepositoryServiceProvider no está registrado antes del domain provider.

**Solución**: Verificar orden en `config/app.php`

### Error: "Circular dependency"

**Causa**: Un Service Provider depende de otro que aún no se ha registrado.

**Solución**: Reordenar providers o usar lazy loading con `defer: true`

---

## Referencias

- [Laravel Service Providers Documentation](https://laravel.com/docs/providers)
- [Container Bindings](https://laravel.com/docs/container)
- [ARCHITECTURE.md](ARCHITECTURE.md) - Arquitectura general del proyecto
- [CONVENTIONS.md](CONVENTIONS.md) - Convenciones de código

---

**Última actualización**: 16 de Febrero de 2026
