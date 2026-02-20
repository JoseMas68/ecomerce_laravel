# Configuración de Laravel Sanctum para Autenticación de API

Este documento describe la configuración completa de Laravel Sanctum para autenticación de API en el proyecto eCommerce con arquitectura DDD.

## Configuración Realizada

### 1. Archivos Creados/Modificados

#### Archivos de Configuración
- **`config/sanctum.php`**: Configuración de Laravel Sanctum con stateful domains, guards y middleware.

#### Service Providers
- **`bootstrap/providers.php`**: Agregado `Laravel\Sanctum\SanctumServiceProvider` a la lista de providers.

#### Modelo
- **`app/Models/User.php`**: Agregado trait `HasApiTokens` para habilitar tokens de autenticación.

#### Form Requests (Validación)
- **`app/Http/Requests/RegisterRequest.php`**: Validación para registro de usuarios.
  - `name`: requerido, string, max:255
  - `email`: requerido, email, único en tabla users
  - `password`: requerido, min:8, confirmed

- **`app/Http/Requests/LoginRequest.php`**: Validación para inicio de sesión.
  - `email`: requerido, email
  - `password`: requerido, string

#### API Resources
- **`app/Http/Resources/UserResource.php`**: Transforma los datos del usuario para respuestas JSON.

#### Controladores
- **`app/Http/Controllers/Api/V1/Users/AuthController.php`**: Implementado con métodos:
  - `register()`: Crea usuario y devuelve token
  - `login()`: Valida credenciales y devuelve token
  - `logout()`: Revoca token actual del usuario
  - `revokeAll()`: Revoca todos los tokens del usuario
  - `tokens()`: Lista todos los tokens activos del usuario

#### Seeders
- **`database/seeders/UserSeeder.php`**: Crea usuarios de prueba:
  - Admin: `admin@ecommerce.com` / `password123`
  - Cliente 1: `cliente1@ecommerce.com` / `password123`
  - Cliente 2: `cliente2@ecommerce.com` / `password123`

- **`database/seeders/DatabaseSeeder.php`**: Actualizado para llamar a UserSeeder.

#### Migraciones
- **`database/migrations/2026_02_16_223500_create_personal_access_tokens_table.php`**: Crea tabla para almacenar tokens de Sanctum.

## Rutas de Autenticación

Las rutas están registradas en `routes/api/v1/users.php`:

### Rutas Públicas (Sin Autenticación)
```
POST /api/v1/register    - Registro de nuevo usuario
POST /api/v1/login       - Inicio de sesión
```

### Rutas Protegidas (Requieren Token)
```
POST   /api/v1/logout    - Cierre de sesión (revoca token actual)
DELETE /api/v1/tokens    - Revocar todos los tokens del usuario
GET    /api/v1/tokens    - Listar tokens activos del usuario
```

## Formato de Respuestas

### Registro Exitoso
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "created_at": "2026-02-16T22:35:00.000000Z",
      "updated_at": "2026-02-16T22:35:00.000000Z"
    },
    "token": "1|aabbccddeeffgghhiijjkkllmmnnooppqqrrsstt",
    "token_type": "Bearer"
  }
}
```

### Inicio de Sesión Exitoso
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "created_at": "2026-02-16T22:35:00.000000Z",
      "updated_at": "2026-02-16T22:35:00.000000Z"
    },
    "token": "2|aabbccddeeffgghhiijjkkllmmnnooppqqrrsstt",
    "token_type": "Bearer"
  }
}
```

### Cierre de Sesión Exitoso
```json
{
  "success": true,
  "message": "Cierre de sesión exitoso"
}
```

### Error de Validación
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "email": ["El campo email es obligatorio."],
    "password": ["La contraseña debe tener al menos 8 caracteres."]
  }
}
```

### Error de Credenciales
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "email": ["Las credenciales proporcionadas son incorrectas."]
  }
}
```

## Cómo Usar la API

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Ejecutar Seeders (Opcional - para crear usuarios de prueba)
```bash
php artisan db:seed --class=UserSeeder
```

O ejecutar todos los seeders:
```bash
php artisan db:seed
```

### 3. Registrar un Nuevo Usuario
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 4. Iniciar Sesión
```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "password123"
  }'
```

### 5. Acceder a Rutas Protegidas
Usa el token recibido en las respuestas anteriores:

```bash
curl -X GET http://localhost/api/v1/me \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 2|aabbccddeeffgghhiijjkkllmmnnooppqqrrsstt"
```

### 6. Cerrar Sesión
```bash
curl -X POST http://localhost/api/v1/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 2|aabbccddeeffgghhiijjkkllmmnnooppqqrrsstt"
```

## Características de Seguridad Implementadas

1. **Tokens con Abilities**: Todos los tokens se crean con abilities `['*']` (acceso completo).
2. **Revocación de Tokens**: Al iniciar sesión, se revocan los tokens anteriores (una sola sesión activa).
3. **Logout Completo**: El método `logout()` revoca el token actual del usuario.
4. **Revocación Masiva**: El método `revokeAll()` permite revocar todos los tokens del usuario.
5. **Validación de Contraseñas**: Mínimo 8 caracteres con confirmación obligatoria.
6. **Email Único**: Validación automática de email único en el sistema.
7. **Hash de Contraseñas**: Todas las contraseñas se hashean usando `Hash::make()`.

## Características de la Implementación

1. **Type Safety**: Uso de `declare(strict_types=1)` en todos los archivos.
2. **DDD Architecture**: Sigue la arquitectura Domain-Driven Design del proyecto.
3. **Mensajes en Español**: Todos los mensajes de error y validación están en español.
4. **Respuestas JSON Consistentes**: Todas las respuestas siguen el formato `{success, message, data}`.
5. **Excepciones HTTP**: Las validaciones fallidas lanzan `HttpResponseException` con formato JSON.
6. **Type Hints**: Uso completo de type hints para parámetros y valores de retorno.

## Próximos Pasos Recomendados

1. **Implementar refresh de tokens**: Agregar endpoint para renovar tokens antes de que expiren.
2. **Implementar forgot/reset password**: Completar los métodos `forgotPassword()` y `resetPassword()`.
3. **Rate Limiting**: Configurar rate limiting para prevenir ataques de fuerza bruta.
4. **Email Verification**: Implementar verificación de email después del registro.
5. **2FA (Two-Factor Authentication)**: Agregar autenticación de dos factores para mayor seguridad.
6. **OAuth2 Integration**: Si se requiere integración con proveedores externos (Google, Facebook, etc.).
7. **Role-Based Access Control**: Implementar roles y permisos usando gates o policies.

## Usuarios de Prueba (Después de Ejecutar Seeders)

| Rol | Email | Password |
|-----|-------|----------|
| Administrador | admin@ecommerce.com | password123 |
| Cliente 1 | cliente1@ecommerce.com | password123 |
| Cliente 2 | cliente2@ecommerce.com | password123 |

## Solución de Problemas

### Error "Class 'Laravel\Sanctum\SanctumServiceProvider' not found"
**Solución**: Ejecutar `composer require laravel/sanctum` para instalar Sanctum.

### Error "Table 'personal_access_tokens' doesn't exist"
**Solución**: Ejecutar `php artisan migrate` para crear la tabla de tokens.

### Error 401 Unauthorized al acceder a rutas protegidas
**Solución**: Verificar que el header `Authorization: Bearer {token}` esté presente y que el token sea válido.

### Las rutas retornan 404 Not Found
**Solución**: Verificar que las rutas estén correctamente registradas en `routes/api.php` y `routes/api/v1/users.php`.

## Recursos Adicionales

- [Documentación Oficial de Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Arquitectura DDD del Proyecto](./ARCHITECTURE.md)
