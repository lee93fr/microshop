<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'user_id', 'created_by', 'status',
        'delivery_mode', 'delivery_fee',
        'delivery_address', 'delivery_city', 'delivery_postal_code', 'delivery_country',
        'notes', 'supplier_notes',
        'payment_method', 'payment_status', 'payment_link',
        'stripe_session_id', 'stripe_payment_intent',
        'subtotal', 'discount', 'credit_used', 'total',
        'promo_code_id', 'promo_code_label',
    ];

    public const DELIVERY_MODE_LABELS = [
        'pickup' => '🏪 Retrait sur place',
        'home'   => '🚚 Livraison à domicile',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'     => 'decimal:2',
            'discount'     => 'decimal:2',
            'credit_used'  => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total'        => 'decimal:2',
        ];
    }

    public const STATUS_LABELS = [
        'pending'            => '⏳ En attente',
        'processing'         => '🔄 En traitement',
        'supplier_preparing' => '📦 Fournisseur prépare',
        'ready_at_supplier'  => '✅ Prêt chez fournisseur',
        'picked_up'          => '🚗 Récupéré',
        'delivered'          => '🏠 Livré',
        'cancelled'          => '❌ Annulé',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid'  => '❌ Non payé',
        'partial' => '⚠️ Partiellement payé',
        'paid'    => '✅ Payé',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function user()            { return $this->belongsTo(User::class)->withTrashed(); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by')->withTrashed(); }
    public function items()           { return $this->hasMany(OrderItem::class); }
    public function statusHistories() { return $this->hasMany(OrderStatusHistory::class)->orderByDesc('changed_at'); }
    public function credits()         { return $this->hasMany(Credit::class); }
    public function promoCode()       { return $this->belongsTo(PromoCode::class); }

    public function scopeForClient($query, int $userId) { return $query->where('user_id', $userId); }
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
}
