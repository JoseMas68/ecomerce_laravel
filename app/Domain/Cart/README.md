# Cart Domain

## Descripción
Este módulo contiene toda la lógica relacionada con el carrito de compras de PawfectShop, implementando una arquitectura DDD (Domain-Driven Design).

## Estructura de Directorios

```
app/Domain/Cart/
├── Models/
│   ├── Cart.php          # Modelo principal del carrito
│   └── CartItem.php      # Modelo de items del carrito
├── Actions/
│   ├── AddItemToCart.php           # Acción para agregar items
│   ├── RemoveItemFromCart.php      # Acción para eliminar items
│   ├── UpdateCartItemQuantity.php  # Acción para actualizar cantidad
│   ├── MergeGuestCartToUser.php    # Acción para fusionar carritos
│   └── ClearCart.php                # Acción para limpiar carrito
├── Events/
│   ├── CartUpdated.php     # Evento cuando se actualiza el carrito
│   ├── ItemAdded.php       # Evento cuando se agrega un item
│   └── ItemRemoved.php     # Evento cuando se elimina un item
└── DTOs/
    ├── CartData.php        # DTO para operaciones de carrito
    └── CartItemData.php    # DTO para operaciones de items
```

## Modelos

### Cart
- **Campos**: id, user_id (nullable), session_id (nullable), created_at, updated_at
- **Relaciones**:
  - `user()`: belongsTo User
  - `items()`: hasMany CartItem
- **Métodos principales**:
  - `addItem(Product $product, int $quantity)`: Agrega un item al carrito
  - `removeItem(int $cartItemId)`: Elimina un item del carrito
  - `clear()`: Elimina todos los items del carrito
  - `merge(Cart $guestCart)`: Fusiona un carrito de invitado
  - `getTotalAttribute()`: Calcula el total del carrito
  - `getTotalItemsAttribute()`: Obtiene el número total de items

### CartItem
- **Campos**: id, cart_id, product_id, quantity, unit_price, subtotal, created_at, updated_at
- **Relaciones**:
  - `cart()`: belongsTo Cart
  - `product()`: belongsTo Product
- **Métodos principales**:
  - `calculateSubtotal()`: Calcula el subtotal del item
  - `updateQuantity(int $quantity)`: Actualiza la cantidad
  - `incrementQuantity(int $amount)`: Incrementa la cantidad
  - `decrementQuantity(int $amount)`: Decrementa la cantidad
  - `isInStock()`: Verifica si hay stock disponible

## Acciones

### AddItemToCart
Agrega un item al carrito con validaciones de stock y disponibilidad.

```php
$action = new AddItemToCart();
$cart = $action->execute($cart, $cartItemData);
```

### RemoveItemFromCart
Elimina un item del carrito y actualiza los totales.

```php
$action = new RemoveItemFromCart();
$cart = $action->execute($cart, $cartItemId);
```

### UpdateCartItemQuantity
Actualiza la cantidad de un item con validación de stock.

```php
$action = new UpdateCartItemQuantity();
$cart = $action->execute($cart, $cartItemId, $newQuantity);
```

### MergeGuestCartToUser
Fusiona un carrito de invitado con el carrito de un usuario autenticado.

```php
$action = new MergeGuestCartToUser();
$userCart = $action->execute($userCart, $guestCart);
```

### ClearCart
Elimina todos los items del carrito.

```php
$action = new ClearCart();
$cart = $action->execute($cart);
```

## Eventos

### CartUpdated
Se dispara cuando se modifica el carrito (actualizar cantidad, fusionar, limpiar).

```php
event(new CartUpdated($cart, $changes));
```

### ItemAdded
Se dispara cuando se agrega un item al carrito.

```php
event(new ItemAdded($cart, $item, $quantity));
```

### ItemRemoved
Se dispara cuando se elimina un item del carrito.

```php
event(new ItemRemoved($cart, $cartItemId, $productId, $quantity, $subtotal));
```

## DTOs

### CartData
Data Transfer Object para operaciones de carrito.

```php
$cartData = CartData::forUser($userId);
$cartData = CartData::forGuest($sessionId);
$cartData = CartData::fromRequest($request);
```

### CartItemData
Data Transfer Object para operaciones de items.

```php
$itemData = CartItemData::fromArray($data);
$itemData = CartItemData::fromRequest($request);
```

## Migraciones

### create_carts_table
- Tabla `carts`
- Índices en user_id, session_id
- Foreign key a users

### create_cart_items_table
- Tabla `cart_items`
- Índices en cart_id, product_id
- Unique constraint en (cart_id, product_id)
- Foreign keys a carts y products

## Ejemplo de Uso

```php
use App\Domain\Cart\Actions\AddItemToCart;
use App\Domain\Cart\DTOs\CartItemData;
use App\Domain\Cart\Models\Cart;

// Crear o obtener un carrito
$cart = Cart::firstOrCreate(
    ['user_id' => auth()->id()],
    ['session_id' => session()->getId()]
);

// Agregar un item
$itemData = CartItemData::fromArray([
    'product_id' => 1,
    'quantity' => 2
]);

$action = new AddItemToCart();
$cart = $action->execute($cart, $itemData);

// Obtener totales
$total = $cart->total;          // Total monetario
$itemsCount = $cart->total_items; // Total de items
```

## Consideraciones

1. **Carritos de Sesión**: El sistema soporta carritos para usuarios no autenticados usando `session_id`
2. **Stock**: Todas las acciones validan el stock disponible antes de modificar el carrito
3. **Transacciones**: Las acciones usan transacciones de base de datos para asegurar integridad
4. **Eventos**: Los eventos permiten escuchar y reaccionar a cambios en el carrito
5. **Precios**: Los precios se guardan en el momento de agregar al carrito para mantener historial
