<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
        'default_shipping_address_id',
        'default_billing_address_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the addresses for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(\App\Domain\Users\Models\Address::class);
    }

    /**
     * Get the default shipping address for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function defaultShippingAddress()
    {
        return $this->belongsTo(\App\Domain\Users\Models\Address::class, 'default_shipping_address_id');
    }

    /**
     * Get the default billing address for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function defaultBillingAddress()
    {
        return $this->belongsTo(\App\Domain\Users\Models\Address::class, 'default_billing_address_id');
    }

    /**
     * Get the orders for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(\App\Domain\Orders\Models\Order::class);
    }

    // ==================== SCOPES DE OPTIMIZACIÓN ====================

    /**
     * Scope para cargar direcciones por defecto con eager loading.
     *
     * Optimiza queries al cargar usuarios con sus direcciones principales.
     * Evita N+1 al mostrar listados de usuarios con direcciones.
     *
     * Uso: User::withDefaultAddresses()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithDefaultAddresses($query)
    {
        return $query->with([
            'defaultShippingAddress:id,user_id,address,city,state,postal_code,country',
            'defaultBillingAddress:id,user_id,address,city,state,postal_code,country'
        ]);
    }

    /**
     * Scope para cargar todas las direcciones del usuario.
     *
     * Optimiza queries para perfil de usuario.
     *
     * Uso: User::withAllAddresses()->find($id)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllAddresses($query)
    {
        return $query->with([
            'addresses',
            'defaultShippingAddress',
            'defaultBillingAddress'
        ]);
    }

    /**
     * Scope para cargar ordenes recientes del usuario.
     *
     * Optimiza queries para dashboard del usuario.
     * Carga solo las últimas 5 ordenes con items.
     *
     * Uso: User::withRecentOrders()->find($id)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit Número de ordenes a cargar (default: 5)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRecentOrders($query, int $limit = 5)
    {
        return $query->with(['orders' => function ($query) use ($limit) {
            $query->latest()->limit($limit)
                ->with('items.product:id,name,slug');
        }]);
    }

    /**
     * Scope para usuarios activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope para buscar usuarios por nombre o email.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para usuarios con ordenes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasOrders($query)
    {
        return $query->whereHas('orders');
    }

    /**
     * Scope para ordenar por nombre.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction asc o desc (default: asc)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByName($query, string $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }

    /**
     * Scope para usuarios creados recientemente.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Días hacia atrás (default: 30)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== MÉTODOS HELPER ====================

    /**
     * Obtiene el nombre completo del usuario.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->name;
    }

    /**
     * Obtiene la URL del avatar o una imagen por defecto.
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /**
     * Verifica si el usuario tiene direcciones guardadas.
     *
     * @return bool
     */
    public function hasAddresses(): bool
    {
        return $this->addresses()->exists();
    }

    /**
     * Verifica si el usuario ha realizado ordenes.
     *
     * @return bool
     */
    public function hasOrdered(): bool
    {
        return $this->orders()->exists();
    }

    /**
     * Obtiene el total gastado por el usuario.
     *
     * @return float
     */
    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total');
    }
}
