# User Model Documentation

## Ubicación del Modelo
El modelo User principal se encuentra en: `app/Models/User.php`

## Métodos a Agregar al Modelo User

Para mantener la funcionalidad del dominio Users, agrega los siguientes métodos al modelo User existente:

### Relaciones

```php
/**
 * Get all addresses for the user.
 */
public function addresses()
{
    return $this->hasMany(\App\Domain\Users\Models\Address::class);
}

/**
 * Get the default shipping address.
 */
public function defaultShippingAddress()
{
    return $this->hasOne(\App\Domain\Users\Models\Address::class)
        ->where('is_default_shipping', true);
}

/**
 * Get the default billing address.
 */
public function defaultBillingAddress()
{
    return $this->hasOne(\App\Domain\Users\Models\Address::class)
        ->where('is_default_billing', true);
}
```

### Accessors y Mutators

```php
/**
 * Get the user's full name.
 */
protected function getFullNameAttribute(): string
{
    return $this->name;
}

/**
 * Get the user's initials.
 */
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

## Notas Importantes

- El modelo User ya utiliza Laravel Sanctum (`HasApiTokens`)
- No elimines ni modifiques la configuración existente de Sanctum
- Estos métodos complementan la funcionalidad base del modelo User
- Las direcciones se gestionan a través del modelo Address en el dominio Users

## Uso

```php
// Obtener todas las direcciones de un usuario
$user->addresses;

// Obtener dirección de envío por defecto
$user->defaultShippingAddress;

// Obtener dirección de facturación por defecto
$user->defaultBillingAddress;

// Obtener nombre completo
$user->full_name;

// Obtener iniciales
$user->initials;
```
