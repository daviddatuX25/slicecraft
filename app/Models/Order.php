<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // NOTE (change NOT in the worksheet instructions): Orders store the cart
    // records needed by the midterm Manage Cart requirement.

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const CART_STATUS = 'cart';

    /** @var list<string> */
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'total_amount_cents',
        'notes',
        'scheduled_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'total_amount_cents' => 'integer',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Limit the query to editable draft carts.
     */
    public function scopeCart(Builder $query): void
    {
        $query->where('status', self::CART_STATUS);
    }

    /**
     * Limit the query to placed order history records.
     */
    public function scopePlaced(Builder $query): void
    {
        $query->where('status', '!=', self::CART_STATUS);
    }

    public function isCart(): bool
    {
        return $this->status === self::CART_STATUS;
    }

    public function isPlaced(): bool
    {
        return ! $this->isCart();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->items();
    }
}
