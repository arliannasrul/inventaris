<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'destination_city_id',
        'destination_city_name',
        'weight_grams',
        'courier',
        'shipping_service',
        'shipping_cost',
        'status',
        'waybill',
        'kiriminaja_order_id',
    ];

    /**
     * Relasi ke item order (pivot).
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi langsung ke data inventory Item.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
