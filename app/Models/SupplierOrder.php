<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    protected $fillable = [
        'reference', 'order_ids', 'status', 'sent_at', 'sent_via', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_ids' => 'array',
            'sent_at'   => 'datetime',
        ];
    }

    public const STATUS_LABELS = [
        'draft'     => '✏️ Brouillon',
        'sent'      => '📨 Envoyé',
        'confirmed' => '✅ Confirmé',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function orders()
    {
        return Order::whereIn('id', $this->order_ids ?? [])->get();
    }
}
