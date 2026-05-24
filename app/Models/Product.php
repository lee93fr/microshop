<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'category_id',
        'purchase_price', 'sale_price', 'suggested_price', 'unit',
        'volume_ml', 'alcohol_degree', 'image', 'sku', 'is_active', 'is_new', 'in_stock',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price'  => 'decimal:2',
            'sale_price'      => 'decimal:2',
            'suggested_price' => 'decimal:2',
            'alcohol_degree' => 'decimal:2',
            'is_active'      => 'boolean',
            'is_new'         => 'boolean',
            'in_stock'       => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($p) => $p->slug ??= Str::slug($p->name));
    }

    public function getMarginAttribute(): float
    {
        if ($this->purchase_price <= 0) return 0;
        return round((($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/placeholder-bottle.png');
    }

    public function category()   { return $this->belongsTo(Category::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function cartItems()  { return $this->hasMany(Cart::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
