# Arquitectura - eCommerce Laravel 12

## Patrón: DDD Pragmático + Clean Architecture

Se elige esta arquitectura porque:

- **Separación clara de responsabilidades** por dominio de negocio
- **Escalabilidad**: cada dominio puede evolucionar o extraerse a microservicio
- **Testabilidad**: capas desacopladas permiten unit testing real
- **Mantenibilidad**: un dev nuevo entiende el dominio sin conocer todo el sistema
- **No over-engineering**: DDD pragmático sin CQRS/Event Sourcing innecesario

---

## Estructura de Carpetas

```
app/
├── Domain/                          # Capa de Dominio (Negocio puro)
│   ├── Catalog/
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   ├── Category.php
│   │   │   ├── Brand.php
│   │   │   └── ProductVariant.php
│   │   ├── DTOs/
│   │   │   ├── CreateProductDTO.php
│   │   │   └── UpdateProductDTO.php
│   │   ├── Services/
│   │   │   └── CatalogService.php
│   │   ├── Actions/
│   │   │   ├── CreateProductAction.php
│   │   │   ├── UpdateProductAction.php
│   │   │   └── DeleteProductAction.php
│   │   ├── Repositories/
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   └── EloquentProductRepository.php
│   │   ├── Events/
│   │   │   ├── ProductCreated.php
│   │   │   └── ProductStockUpdated.php
│   │   ├── Listeners/
│   │   │   ├── UpdateSearchIndex.php
│   │   │   └── NotifyLowStock.php
│   │   ├── Observers/
│   │   │   └── ProductObserver.php
│   │   ├── Policies/
│   │   │   └── ProductPolicy.php
│   │   ├── Enums/
│   │   │   └── ProductStatus.php
│   │   ├── ValueObjects/
│   │   │   ├── Money.php
│   │   │   ├── Sku.php
│   │   │   └── Slug.php
│   │   └── Exceptions/
│   │       ├── ProductNotFoundException.php
│   │       └── InsufficientStockException.php
│   │
│   ├── Cart/
│   │   ├── Models/
│   │   │   ├── Cart.php
│   │   │   └── CartItem.php
│   │   ├── DTOs/
│   │   │   └── AddToCartDTO.php
│   │   ├── Services/
│   │   │   └── CartService.php
│   │   ├── Actions/
│   │   │   ├── AddItemToCartAction.php
│   │   │   ├── RemoveItemFromCartAction.php
│   │   │   └── ClearCartAction.php
│   │   ├── Repositories/
│   │   │   ├── CartRepositoryInterface.php
│   │   │   └── EloquentCartRepository.php
│   │   ├── Events/
│   │   │   └── CartUpdated.php
│   │   └── Exceptions/
│   │       └── CartItemNotFoundException.php
│   │
│   ├── Orders/
│   │   ├── Models/
│   │   │   ├── Order.php
│   │   │   └── OrderItem.php
│   │   ├── DTOs/
│   │   │   ├── CreateOrderDTO.php
│   │   │   └── OrderFilterDTO.php
│   │   ├── Services/
│   │   │   └── OrderService.php
│   │   ├── Actions/
│   │   │   ├── PlaceOrderAction.php
│   │   │   ├── CancelOrderAction.php
│   │   │   └── RefundOrderAction.php
│   │   ├── Repositories/
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   └── EloquentOrderRepository.php
│   │   ├── Events/
│   │   │   ├── OrderPlaced.php
│   │   │   ├── OrderShipped.php
│   │   │   └── OrderCancelled.php
│   │   ├── Listeners/
│   │   │   ├── SendOrderConfirmation.php
│   │   │   ├── DeductStock.php
│   │   │   └── NotifyAdminNewOrder.php
│   │   ├── Jobs/
│   │   │   ├── ProcessOrderJob.php
│   │   │   └── GenerateInvoiceJob.php
│   │   ├── Observers/
│   │   │   └── OrderObserver.php
│   │   ├── Policies/
│   │   │   └── OrderPolicy.php
│   │   ├── Enums/
│   │   │   └── OrderStatus.php
│   │   └── Exceptions/
│   │       └── OrderCannotBeCancelledException.php
│   │
│   ├── Users/
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   └── Address.php
│   │   ├── DTOs/
│   │   │   ├── RegisterUserDTO.php
│   │   │   └── UpdateProfileDTO.php
│   │   ├── Services/
│   │   │   └── UserService.php
│   │   ├── Actions/
│   │   │   ├── RegisterUserAction.php
│   │   │   └── UpdateProfileAction.php
│   │   ├── Repositories/
│   │   │   ├── UserRepositoryInterface.php
│   │   │   └── EloquentUserRepository.php
│   │   ├── Events/
│   │   │   └── UserRegistered.php
│   │   ├── Listeners/
│   │   │   ├── SendWelcomeEmail.php
│   │   │   └── CreateDefaultWishlist.php
│   │   ├── Policies/
│   │   │   └── UserPolicy.php
│   │   ├── Enums/
│   │   │   └── UserRole.php
│   │   └── Exceptions/
│   │       └── EmailAlreadyExistsException.php
│   │
│   ├── Payments/
│   │   ├── Models/
│   │   │   ├── Payment.php
│   │   │   └── Refund.php
│   │   ├── DTOs/
│   │   │   └── ProcessPaymentDTO.php
│   │   ├── Services/
│   │   │   └── PaymentService.php
│   │   ├── Actions/
│   │   │   ├── ProcessPaymentAction.php
│   │   │   └── ProcessRefundAction.php
│   │   ├── Gateways/
│   │   │   ├── PaymentGatewayInterface.php
│   │   │   ├── StripeGateway.php
│   │   │   └── PayPalGateway.php
│   │   ├── Events/
│   │   │   ├── PaymentProcessed.php
│   │   │   └── PaymentFailed.php
│   │   ├── Listeners/
│   │   │   └── HandlePaymentWebhook.php
│   │   ├── Enums/
│   │   │   ├── PaymentStatus.php
│   │   │   └── PaymentMethod.php
│   │   └── Exceptions/
│   │       ├── PaymentFailedException.php
│   │       └── InvalidPaymentMethodException.php
│   │
│   ├── Shipping/
│   │   ├── Models/
│   │   │   └── Shipment.php
│   │   ├── DTOs/
│   │   │   └── ShippingRateDTO.php
│   │   ├── Services/
│   │   │   └── ShippingService.php
│   │   ├── Carriers/
│   │   │   ├── ShippingCarrierInterface.php
│   │   │   └── StandardCarrier.php
│   │   ├── Enums/
│   │   │   └── ShipmentStatus.php
│   │   └── Exceptions/
│   │       └── ShippingUnavailableException.php
│   │
│   ├── Promotions/
│   │   ├── Models/
│   │   │   ├── Coupon.php
│   │   │   └── Discount.php
│   │   ├── DTOs/
│   │   │   └── ApplyCouponDTO.php
│   │   ├── Services/
│   │   │   └── PromotionService.php
│   │   ├── Actions/
│   │   │   ├── ApplyCouponAction.php
│   │   │   └── ValidateCouponAction.php
│   │   ├── Enums/
│   │   │   └── DiscountType.php
│   │   └── Exceptions/
│   │       ├── CouponExpiredException.php
│   │       └── CouponAlreadyUsedException.php
│   │
│   └── Notifications/
│       ├── Services/
│       │   └── NotificationService.php
│       ├── Channels/
│       │   ├── EmailChannel.php
│       │   └── SmsChannel.php
│       └── Templates/
│           ├── OrderConfirmationMail.php
│           └── ShipmentTrackingMail.php
│
├── Http/                            # Capa de Presentación
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           ├── Catalog/
│   │           │   ├── ProductController.php
│   │           │   ├── CategoryController.php
│   │           │   └── BrandController.php
│   │           ├── Cart/
│   │           │   └── CartController.php
│   │           ├── Orders/
│   │           │   └── OrderController.php
│   │           ├── Users/
│   │           │   ├── AuthController.php
│   │           │   └── ProfileController.php
│   │           ├── Payments/
│   │           │   ├── PaymentController.php
│   │           │   └── WebhookController.php
│   │           └── Shipping/
│   │               └── ShippingController.php
│   ├── Requests/
│   │   ├── Catalog/
│   │   │   ├── StoreProductRequest.php
│   │   │   └── UpdateProductRequest.php
│   │   ├── Cart/
│   │   │   └── AddToCartRequest.php
│   │   ├── Orders/
│   │   │   └── PlaceOrderRequest.php
│   │   └── Users/
│   │       ├── RegisterRequest.php
│   │       └── LoginRequest.php
│   ├── Resources/
│   │   ├── Catalog/
│   │   │   ├── ProductResource.php
│   │   │   ├── ProductCollection.php
│   │   │   └── CategoryResource.php
│   │   ├── Cart/
│   │   │   └── CartResource.php
│   │   ├── Orders/
│   │   │   ├── OrderResource.php
│   │   │   └── OrderCollection.php
│   │   └── Users/
│   │       └── UserResource.php
│   └── Middleware/
│       ├── ForceJsonResponse.php
│       ├── ApiVersion.php
│       └── TrackApiUsage.php
│
├── Infrastructure/                  # Capa de Infraestructura
│   ├── Providers/
│   │   ├── CatalogServiceProvider.php
│   │   ├── OrderServiceProvider.php
│   │   ├── PaymentServiceProvider.php
│   │   └── RepositoryServiceProvider.php
│   ├── Cache/
│   │   └── CacheKeyGenerator.php
│   └── ExternalServices/
│       ├── StripeClient.php
│       └── MailService.php
│
├── Shared/                          # Componentes transversales
│   ├── Traits/
│   │   ├── HasUuid.php
│   │   ├── Auditable.php
│   │   └── Filterable.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Helpers/
│   │   └── MoneyHelper.php
│   └── Rules/
│       ├── PhoneNumber.php
│       └── ValidSku.php
│
├── Console/
│   └── Commands/
│       ├── PruneExpiredCarts.php
│       ├── GenerateSitemap.php
│       └── SyncInventory.php
│
config/
├── ecommerce.php                    # Config global del eCommerce
├── payments.php                     # Config de pasarelas de pago
└── shipping.php                     # Config de envíos

database/
├── migrations/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── CategorySeeder.php
│   ├── ProductSeeder.php
│   └── UserSeeder.php
└── factories/
    ├── ProductFactory.php
    ├── OrderFactory.php
    └── UserFactory.php

routes/
├── api/
│   └── v1/
│       ├── catalog.php
│       ├── cart.php
│       ├── orders.php
│       ├── users.php
│       ├── payments.php
│       └── shipping.php
└── api.php                          # Registra todos los archivos de rutas

tests/
├── Unit/
│   ├── Domain/
│   │   ├── Catalog/
│   │   │   ├── CatalogServiceTest.php
│   │   │   └── CreateProductActionTest.php
│   │   ├── Cart/
│   │   │   └── CartServiceTest.php
│   │   ├── Orders/
│   │   │   └── PlaceOrderActionTest.php
│   │   └── Payments/
│   │       └── PaymentServiceTest.php
│   └── Shared/
│       └── MoneyHelperTest.php
├── Feature/
│   ├── Api/
│   │   ├── Catalog/
│   │   │   └── ProductApiTest.php
│   │   ├── Cart/
│   │   │   └── CartApiTest.php
│   │   ├── Orders/
│   │   │   └── OrderApiTest.php
│   │   └── Users/
│   │       ├── AuthApiTest.php
│   │       └── ProfileApiTest.php
│   └── Webhooks/
│       └── StripeWebhookTest.php
└── TestCase.php
```

---

## Flujo de una Request Típica

```
HTTP Request
  → Middleware (auth, rate limit, force JSON)
    → Controller (thin, solo orquesta)
      → Form Request (validación)
        → DTO (datos tipados)
          → Service (lógica de negocio)
            → Action (operación atómica)
              → Repository (acceso a datos)
            → Event dispatch
          → API Resource (transformación response)
        → JSON Response
```

---

## Dominios y Responsabilidades

| Dominio | Responsabilidad |
|---------|----------------|
| **Catalog** | Productos, categorías, marcas, variantes, stock, búsqueda |
| **Cart** | Carrito de compra, items, cálculos de precio, sesión/usuario |
| **Orders** | Pedidos, estados, historial, facturación |
| **Users** | Registro, autenticación, perfil, direcciones, roles |
| **Payments** | Procesamiento de pagos, reembolsos, webhooks, pasarelas |
| **Shipping** | Envíos, tracking, carriers, cálculo de tarifas |
| **Promotions** | Cupones, descuentos, reglas de promoción |
| **Notifications** | Emails transaccionales, SMS, notificaciones push |

---

## Registro de Bindings por Dominio

Cada dominio tiene su propio `ServiceProvider` que registra:

```php
// app/Infrastructure/Providers/CatalogServiceProvider.php
public function register(): void
{
    $this->app->bind(
        ProductRepositoryInterface::class,
        EloquentProductRepository::class
    );
}
```

Todos los providers de dominio se registran en `config/app.php`.
