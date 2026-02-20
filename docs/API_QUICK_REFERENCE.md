# API Quick Reference - Autenticación Sanctum

## Base URL
```
http://localhost:8080/api/v1
```

## Endpoints

### Autenticación Pública

| Método | Endpoint | Descripción | Body |
|--------|----------|-------------|------|
| POST | `/register` | Registrar nuevo usuario | `name`, `email`, `password`, `password_confirmation` |
| POST | `/login` | Iniciar sesión | `email`, `password` |

### Autenticación Requerida (Header: `Authorization: Bearer {token}`)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/me` | Obtener perfil del usuario |
| POST | `/logout` | Cerrar sesión (revoca token actual) |
| DELETE | `/tokens` | Revocar todos los tokens del usuario |
| GET | `/tokens` | Listar tokens activos del usuario |

## Códigos de Estado HTTP

| Código | Significado |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 401 | Unauthorized - Token inválido o no proporcionado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

## Formato de Request

### Registro
```json
POST /api/v1/register
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login
```json
POST /api/v1/login
{
  "email": "juan@example.com",
  "password": "password123"
}
```

## Formato de Response Exitoso

```json
{
  "success": true,
  "message": "Mensaje de éxito",
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

## Formato de Response de Error

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

## Usuarios de Prueba (Después de ejecutar seeders)

| Email | Password | Rol |
|-------|----------|-----|
| admin@ecommerce.com | password123 | Administrador |
| cliente1@ecommerce.com | password123 | Cliente |
| cliente2@ecommerce.com | password123 | Cliente |

## Ejemplos cURL

### Registrar Usuario
```bash
curl -X POST http://localhost:8080/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Juan Pérez","email":"juan@example.com","password":"password123","password_confirmation":"password123"}'
```

### Login
```bash
curl -X POST http://localhost:8080/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@ecommerce.com","password":"password123"}'
```

### Acceder a Ruta Protegida
```bash
curl -X GET http://localhost:8080/api/v1/me \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

### Logout
```bash
curl -X POST http://localhost:8080/api/v1/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

## Reglas de Validación

### Register
- `name`: requerido, string, max 255 caracteres
- `email`: requerido, email válido, único en base de datos
- `password`: requerido, string, mínimo 8 caracteres, debe coincidir con password_confirmation

### Login
- `email`: requerido, email válido
- `password`: requerido, string

## Características de Seguridad

- Tokens con abilities `['*']` (acceso completo)
- Revocación de tokens anteriores al hacer login (una sola sesión activa)
- Hash automático de contraseñas con bcrypt
- Validación de email único
- Tokens revocables individualmente o en masa
- Middleware `auth:sanctum` en rutas protegidas

## Herramientas de Testing

- **REST Client**: Archivo en `.vscode/rest-client.http`
- **Postman**: Colección en `docs/postman_collection.json`
- **Documentación completa**: `docs/SANCTUM_AUTH_SETUP.md`
