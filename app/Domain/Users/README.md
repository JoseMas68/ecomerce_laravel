# Dominio Users - PawfectShop

## Estructura del Dominio

```
app/Domain/Users/
├── Models/
│   ├── Address.php              # Modelo de direcciones de usuario
│   └── UserDocumentation.md     # Documentación del modelo User
├── Actions/
│   ├── RegisterUser.php         # Registrar nuevo usuario
│   ├── UpdateUserProfile.php    # Actualizar perfil de usuario
│   ├── ChangePassword.php       # Cambiar contraseña
│   ├── CreateAddress.php        # Crear dirección
│   ├── UpdateAddress.php        # Actualizar dirección
│   ├── DeleteAddress.php        # Eliminar dirección
│   └── SetDefaultAddress.php    # Establecer dirección por defecto
├── Events/
│   ├── UserRegistered.php       # Evento: Usuario registrado
│   ├── UserUpdated.php          # Evento: Usuario actualizado
│   ├── AddressCreated.php       # Evento: Dirección creada
│   ├── AddressUpdated.php       # Evento: Dirección actualizada
│   ├── AddressDeleted.php       # Evento: Dirección eliminada
│   └── PasswordChanged.php      # Evento: Contraseña cambiada
├── DTOs/
│   ├── UserData.php             # DTO para datos de usuario
│   ├── AddressData.php          # DTO para datos de dirección
│   └── UpdateProfileData.php    # DTO para actualizar perfil
└── Policies/
    ├── UserPolicy.php           # Política de autorización de usuarios
    └── AddressPolicy.php        # Política de autorización de direcciones
```

## Modelo Address

### Campos
- `id` - Identificador único
- `user_id` - ID del usuario (FK a users)
- `label` - Etiqueta de la dirección ("Casa", "Trabajo", "Otros")
- `first_name` - Nombre
- `last_name` - Apellido
- `company` - Empresa (nullable)
- `address_line_1` - Primera línea de dirección
- `address_line_2` - Segunda línea de dirección (nullable)
- `city` - Ciudad
- `postal_code` - Código postal
- `province` - Provincia
- `country` - País (default: España)
- `phone` - Teléfono
- `is_default_shipping` - Dirección de envío por defecto (boolean)
- `is_default_billing` - Dirección de facturación por defecto (boolean)
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización

### Métodos del Modelo Address

```php
// Establecer como dirección de envío por defecto
$address->setAsDefaultShipping();

// Establecer como dirección de facturación por defecto
$address->setAsDefaultBilling();

// Obtener dirección completa
$fullAddress = $address->full_address;

// Scopes
Address::defaultShipping()->get();
Address::defaultBilling()->get();
Address::byUser($userId)->get();
```

## Actions (Casos de Uso)

### RegisterUser
Registra un nuevo usuario en el sistema.

```php
$action = new RegisterUser();
$userData = UserData::fromArray([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'phone' => '+34 123 456 789'
]);
$user = $action->execute($userData);
```

### UpdateUserProfile
Actualiza el perfil de un usuario existente.

```php
$action = new UpdateUserProfile();
$profileData = UpdateProfileData::fromArray([
    'name' => 'John Updated',
    'phone' => '+34 987 654 321'
]);
$user = $action->execute($user, $profileData);
```

### ChangePassword
Cambia la contraseña de un usuario.

```php
$action = new ChangePassword();
$success = $action->execute($user, 'current_password', 'new_password');
```

### CreateAddress
Crea una nueva dirección para un usuario.

```php
$action = new CreateAddress();
$addressData = AddressData::fromArray([
    'user_id' => $user->id,
    'label' => 'Casa',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'address_line_1' => 'Calle Principal 123',
    'city' => 'Madrid',
    'postal_code' => '28001',
    'province' => 'Madrid',
    'phone' => '+34 123 456 789',
    'is_default_shipping' => true,
    'is_default_billing' => true,
]);
$address = $action->execute($user, $addressData);
```

### UpdateAddress
Actualiza una dirección existente.

```php
$action = new UpdateAddress();
$addressData = AddressData::fromArray([...]);
$address = $action->execute($address, $addressData);
```

### DeleteAddress
Elimina una dirección.

```php
$action = new DeleteAddress();
$action->execute($address);
```

### SetDefaultAddress
Establece una dirección como por defecto (envío, facturación o ambos).

```php
$action = new SetDefaultAddress();
$address = $action->execute($address, 'shipping'); // 'shipping', 'billing', o 'both'
```

## Events

Todos los eventos se disparan automáticamente al ejecutar las Actions correspondientes:

- **UserRegistered**: Nuevo usuario registrado
- **UserUpdated**: Perfil de usuario actualizado
- **AddressCreated**: Nueva dirección creada
- **AddressUpdated**: Dirección actualizada
- **AddressDeleted**: Dirección eliminada
- **PasswordChanged**: Contraseña cambiada

## Policies

### UserPolicy
- `viewAny`: Solo admin puede ver todos los usuarios
- `view`: Usuario puede ver su propio perfil
- `update`: Usuario puede actualizar su propio perfil
- `delete`: Usuario puede eliminar su propia cuenta (excepto admin)

### AddressPolicy
- `viewAny`: Todos pueden ver
- `view`: Usuario puede ver sus propias direcciones
- `create`: Todos pueden crear
- `update`: Usuario puede actualizar sus direcciones
- `delete`: Usuario puede eliminar sus direcciones

## Integración con Modelo User

Para integrar el dominio Users con el modelo User existente, agrega los siguientes métodos a `app/Models/User.php`:

```php
// Relaciones
public function addresses()
{
    return $this->hasMany(\App\Domain\Users\Models\Address::class);
}

public function defaultShippingAddress()
{
    return $this->hasOne(\App\Domain\Users\Models\Address::class)
        ->where('is_default_shipping', true);
}

public function defaultBillingAddress()
{
    return $this->hasOne(\App\Domain\Users\Models\Address::class)
        ->where('is_default_billing', true);
}

// Accessors
protected function getFullNameAttribute(): string
{
    return $this->name;
}

protected function getInitialsAttribute(): string
{
    $words = explode(' ', $this->name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(mb_substr($word, 0, 1));
    }
    return $initials;
}
```

## Migración

La migración `create_addresses_table` crea la tabla de direcciones con:
- Foreign key a `users` con `ON DELETE CASCADE`
- Índices en `user_id`, `is_default_shipping`, `is_default_billing`

## Factory

El `AddressFactory` permite generar direcciones de prueba:

```php
use App\Domain\Users\Models\Address;

// Dirección básica
$address = Address::factory()->create();

// Dirección de envío por defecto
$address = Address::factory()->defaultShipping()->create();

// Dirección para un usuario específico
$address = Address::factory()->forUser($user)->create();
```

## Notas Importantes

1. El modelo User en `app/Models/User.php` NO debe ser modificado o eliminado
2. El modelo User ya tiene Laravel Sanctum configurado
3. Todas las operaciones de direcciones se manejan a través de Actions
4. Los eventos se disparan automáticamente para permitir listeners y observadores
5. Las políticas aseguran que los usuarios solo puedan modificar sus propios recursos
