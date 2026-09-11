<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'channel',
        'customer_name',
        'customer_phone',
        'customer_notes',
        'total_cost',
        'total_price',
        'profit',
        'payment_method',
        'payment_status',
        'amount_paid',
        'change_amount',
        'order_status',
        'pickup_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_cost' => 'integer',
            'total_price' => 'integer',
            'profit' => 'integer',
            'amount_paid' => 'integer',
            'change_amount' => 'integer',
            'pickup_date' => 'date',
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeOts($query)
    {
        return $query->where('channel', 'ots');
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopePo($query)
    {
        return $query->where('channel', 'po');
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
