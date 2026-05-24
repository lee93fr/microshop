<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'discount_amount', 'min_purchase',
        'max_uses', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'min_purchase'    => 'decimal:2',
            'starts_at'       => 'datetime',
            'expires_at'      => 'datetime',
            'is_active'       => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    public function isWithinSchedule(?\DateTimeInterface $now = null): bool
    {
        $now ??= now();
        if ($this->starts_at && $now < $this->starts_at) return false;
        if ($this->expires_at && $now > $this->expires_at) return false;
        return true;
    }

    public function isValidFor(float $subtotal, ?\DateTimeInterface $now = null): bool
    {
        return $this->is_active
            && $this->isWithinSchedule($now)
            && !$this->isExhausted()
            && $subtotal >= (float) $this->min_purchase;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active)        return '⏸️ Désactivé';
        if ($this->isExhausted())     return '🚫 Épuisé';
        if ($this->expires_at && now() > $this->expires_at) return '⌛ Expiré';
        if ($this->starts_at && now() < $this->starts_at)   return '🕒 À venir';
        return '✅ Actif';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
