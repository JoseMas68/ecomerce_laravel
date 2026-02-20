# GUÍA PASO A PASO: Instalar Inertia.js para Laravel

Sigue estos pasos en orden para instalar Inertia.js correctamente.

---

## 📋 REQUISITOS PREVIOS

Asegúrate de tener instalado:
- ✅ PHP 8.4 (ya debería estar instalado)
- ✅ Composer (ya debería estar instalado)
- ✅ Node.js y NPM (ya instalados)

---

## PASO 1: Abrir Terminal en tu Carpeta del Proyecto

### Opción A: Usar la carpeta del proyecto
1. Abre tu carpeta del proyecto en el explorador de archivos:
   `c:\Users\Jose\Desktop\Proyectos\Proyecto  codeia\Ecomercelaravel`

2. En la barra de direcciones, escribe: `powershell` y presiona Enter

3. Se abrirá una PowerShell ya en la carpeta correcta

### Opción B: Usar VS Code
1. En VS Code, presiona: `Ctrl + ñ` (o `Ctrl + Shift + ñ` en español)
2. Se abrirá una terminal integrada en la carpeta del proyecto

---

## PASO 2: Instalar Inertia Laravel

En la terminal que abriste, ejecuta este comando:

```powershell
composer require inertiajs/inertia-laravel
```

**¿Qué hace este comando?**
- Descarga el paquete de Inertia.js para Laravel
- Lo añade a tu archivo `composer.json`
- Instala todas las dependencias necesarias

**Tiempo estimado**: 1-2 minutos

---

## PASO 3: Publicar el Middleware

Después de que termine el comando anterior, ejecuta:

```powershell
php artisan inertia:middleware
```

**¿Qué hace este comando?**
- Crea el archivo `app/Http/Middleware/HandleInertiaRequests.php`
- Este middleware maneja la comunicación entre Laravel y Inertia.js

---

## PASO 4: Registrar el Middleware

Ahora necesitas registrar el middleware en Laravel.

### Abre el archivo: `bootstrap/app.php`

Busca esta sección:

```php
->withMiddleware(function (Middleware $middleware) {
    //
})
```

Y cámbiala por:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

### Antes:
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Después:
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

---

## PASO 5: Limpiar Caché

Ejecuta estos comandos para limpiar la caché:

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## PASO 6: Compilar Assets de Vite

Ahora compila los assets de JavaScript/CSS:

```powershell
npm run build
```

Esto tomará unos segundos y creará la carpeta `public/build/`.

---

## PASO 7: Iniciar Servidor de Desarrollo

Tienes dos opciones:

### Opción A: Servidor de desarrollo de Vite (recomendado)

Abre **OTRA terminal** y ejecuta:

```powershell
npm run dev
```

Déjala corriendo. Esta terminal compilará los cambios automáticamente.

### Opción B: Servidor de Laravel

Abre **OTRA terminal** y ejecuta:

```powershell
php artisan serve
```

Ahora tu aplicación estará en: `http://localhost:8000`

---

## PASO 8: Probar la Aplicación

1. Abre tu navegador
2. Ve a: `http://localhost:8000` (o `http://localhost:8080` si usas Docker)

Deberías ver:
- ✅ La página Home con el hero section
- ✅ Header con navegación
- ✅ Footer
- ✅ Sin errores de JavaScript en la consola (F12)

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Error: "Class 'Inertia\Inertia' not found"

**Solución**: Ejecuta `composer require inertiajs/inertia-laravel` nuevamente.

### Error: "Target class [HandleInertiaRequests] does not exist"

**Solución**: Ejecuta `php artisan inertia:middleware` nuevamente.

### Error: Las páginas se ven en blanco

**Solución**:
1. Limpia caché: `php artisan config:clear`
2. Rebuild: `npm run build`
3. Verifica que `bootstrap/app.php` tenga el middleware registrado

### Error: "Cannot find module '@/Components/...'"

**Solución**: Verifica que en `vite.config.js` tengas:
```javascript
resolve: {
    alias: {
        '@': './resources/js',
    },
},
```

### Las rutas no funcionan (404)

**Solución**: Ejecuta `php artisan route:list` para verificar las rutas.

---

## ✅ VERIFICACIÓN

Para verificar que todo funciona, ejecuta:

```powershell
# Verificar que Inertia está instalado
composer show inertiajs/inertia-laravel

# Verificar rutas
php artisan route:list --path=home

# Verificar configuración
php artisan config:cache
```

---

## 📝 PRÓXIMOS PASOS DESPUÉS DE INSTALAR

Una vez que Inertia esté funcionando:

1. **Probar navegación** - Haz clic en los links del menú
2. **Verificar API** - Abre Network tab (F12) y verifica que se llamen los endpoints
3. **Completar páginas restantes**:
   - Catalog/Show.vue (detalle de producto)
   - Checkout/Index.vue (proceso de pago)
   - Auth/Login.vue y Auth/Register.vue

---

## 🆘 AYUDA

Si tienes algún error:

1. **Copia el error completo**
2. **Revisa este archivo** para ver si hay una solución
3. **Revisa** `INERTIA_INSTALLATION.md` para más detalles
4. **Ejecuta**: `php artisan config:clear` y `npm run build`

---

**Tiempo total estimado**: 5-10 minutos

**Buena suerte! 🚀**
