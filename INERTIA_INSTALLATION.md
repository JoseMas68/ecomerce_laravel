# Instalación de Inertia.js para Laravel

Inertia.js necesita el paquete de Laravel para funcionar correctamente. Sigue estos pasos:

## Paso 1: Instalar el paquete de Laravel

Ejecuta este comando en tu terminal:

```bash
composer require inertiajs/inertia-laravel
```

## Paso 2: Publicar el middleware

```bash
php artisan inertia:middleware
```

## Paso 3: Registrar el middleware

En `bootstrap/app.php`, añade el middleware de Inertia:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Añade estas líneas
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Paso 4: Limpiar caché

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Paso 5: Compilar assets

```bash
npm run build
```

## Paso 6: Probar

Ejecuta el servidor de desarrollo:

```bash
npm run dev
```

Y abre tu navegador en `http://localhost:8080`

---

## Resumen de Dependencias Instaladas

### NPM (JavaScript):
- ✅ @inertiajs/vue3 (v1.0.0)
- ✅ @inertiajs/core
- ✅ vue@^3.4.0
- ✅ @vitejs/plugin-vue

### Composer (PHP):
- ⚠️ inertiajs/inertia-laravel (PENDIENTE)

## Archivos Creados Hasta Ahora

### Componentes Vue:
- ✅ resources/js/Components/Layout/AuthenticatedLayout.vue
- ✅ resources/js/Components/Layout/Header.vue
- ✅ resources/js/Components/Layout/Footer.vue
- ✅ resources/js/Components/Catalog/ProductCard.vue

### Composables:
- ✅ resources/js/Composables/useAuth.js
- ✅ resources/js/Composables/useCart.js

### Páginas:
- ✅ resources/js/Pages/Home.vue
- ⏳ resources/js/Pages/Catalog/Index.vue (PENDIENTE)
- ⏳ resources/js/Pages/Catalog/Show.vue (PENDIENTE)
- ⏳ resources/js/Pages/Cart/Index.vue (PENDIENTE)
- ⏳ resources/js/Pages/Checkout/Index.vue (PENDIENTE)
- ⏳ resources/js/Pages/Auth/Login.vue (PENDIENTE)
- ⏳ resources/js/Pages/Auth/Register.vue (PENDIENTE)

### Configuración:
- ✅ vite.config.js (Vue plugin añadido)
- ✅ resources/js/app.js (Inertia.js configurado)
- ✅ routes/web.php (Rutas de Inertia añadidas)

---

## Próximos Pasos

Una vez instalado Inertia.js para Laravel:

1. **Testing de Home page** - Verificar que la página principal funciona
2. **Completar páginas restantes**:
   - Catalog/Index.vue
   - Catalog/Show.vue
   - Cart/Index.vue
   - Checkout/Index.vue
3. **Actualizar seeders** con datos de mascotas en español
4. **Crear archivo de traducciones** es.json
5. **Testing end-to-end** del flujo completo de compra
