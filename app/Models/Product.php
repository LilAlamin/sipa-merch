<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'category',
        'cost_price',
        'selling_price',
        'stock',
        'has_variants',
        'variants',
        'badge_text',
        'icon',
        'image',
        'description',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_variants' => 'boolean',
            'variants' => 'array',
            'is_active' => 'boolean',
            'cost_price' => 'integer',
            'selling_price' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope for active products
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
