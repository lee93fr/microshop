<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 'purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'     => 'decimal:2',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function getLineTotalAttribute(): float
    {
        return round($this->quantity * $this->unit_price, 2);
    }

    public function getMarginAttribute(): float
    {
        return round(($this->unit_price - $this->purchase_price) * $this->quantity, 2);
    }

    public function order()   { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class)->withTrashed(); }
}
