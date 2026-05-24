<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'reference',
        'amount', 'used_amount', 'reason', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'used_amount' => 'decimal:2',
            'expires_at'  => 'datetime',
        ];
    }

    public function getRemainingAttribute(): float
    {
        return round(max(0, $this->amount - $this->used_amount), 2);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeAvailableFor($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->whereRaw('used_amount < amount')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public static function generateReference(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'AV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function user()  { return $this->belongsTo(User::class)->withTrashed(); }
    public function order() { return $this->belongsTo(Order::class); }
}
