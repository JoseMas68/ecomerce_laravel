<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Order con scopes de optimización de queries.
 *
 * Los scopes optimizan las consultas para evitar el problema N+1
 * cuando se cargan ordenes con sus items, pagos y relaciones.
 *
 * Uso recomendado:
 * - Order::withItemsAndPayments()->get() para listados
 * - Order::withUser()->find($id) para detalle individual
 * - Order::withAllRelations()->paginate(20) para dashboard admin
 */
class Order extends Model
{
    use HasFactory;

    /**
     * Estados posibles de una orden.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'currency',
        'shipping_address',
        'billing_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * Atributos que deben ser casteados.
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario (N:1).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los items de la orden (1:N).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relación con los pagos (1:N).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relación con el historial de estado (1:N).
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Scope para cargar items y pagos con eager loading.
     *
     * Optimiza queries para listados de ordenes.
     * Evita N+1 al cargar items de cada orden.
     *
     * Uso: Order::withItemsAndPayments()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithItemsAndPayments($query)
    {
        return $query->with(['items.product:id,name,slug', 'payments']);
    }

    /**
     * Scope para cargar usuario con eager loading.
     *
     * Optimiza queries para detalle de orden.
     *
     * Uso: Order::withUser()->find($id)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithUser($query)
    {
        return $query->with('user:id,name,email,avatar');
    }

    /**
     * Scope para cargar todas las relaciones comunes.
     *
     * Combina las relaciones más usadas en dashboard y detalles.
     * Carga anticipada: user, items.product, payments
     *
     * Uso: Order::withAllRelations()->paginate(20)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'user:id,name,email,avatar',
            'items.product:id,name,slug,price',
            'payments'
        ]);
    }

    /**
     * Scope para ordenes de un usuario.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para ordenes con un estado específico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para ordenes pendientes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para ordenes en proceso.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope para ordenes completadas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_SHIPPED
        ]);
    }

    /**
     * Scope para ordenes recientes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Días hacia atrás (default: 30)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para ordenes por fecha de creación descendente.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope para ordenes por monto total descendente.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighestTotal($query)
    {
        return $query->orderBy('total', 'desc');
    }

    /**
     * Verifica si la orden puede ser cancelada.
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    /**
     * Verifica si la orden está pagada.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payments()
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Obtiene el total de items en la orden.
     *
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Obtiene el estado formateado para mostrar.
     *
     * @return string
     */
    public function getStatusFormattedAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Boot del modelo para eventos.
     */
    protected static function booted()
    {
        // Registrar cambio de estado
        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'notes' => 'Status changed from ' . $order->getOriginal('status'),
                ]);
            }
        });
    }
}
