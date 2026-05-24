<?php

namespace App\Services;

use App\Models\PromoCode;
use Illuminate\Validation\ValidationException;

class PromoCodeService
{
    public const SESSION_KEY = 'promo_code_id';

    public function findByCode(string $code): ?PromoCode
    {
        return PromoCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();
    }

    public function validateForSubtotal(string $code, float $subtotal): PromoCode
    {
        $promo = $this->findByCode($code);

        if (!$promo) {
            throw ValidationException::withMessages(['promo_code' => 'Code promo inconnu.']);
        }
        if (!$promo->is_active) {
            throw ValidationException::withMessages(['promo_code' => 'Ce code promo est désactivé.']);
        }
        if (!$promo->isWithinSchedule()) {
            throw ValidationException::withMessages(['promo_code' => 'Ce code promo n\'est plus valable.']);
        }
        if ($promo->isExhausted()) {
            throw ValidationException::withMessages(['promo_code' => 'Ce code promo a atteint son nombre maximum d\'utilisations.']);
        }
        if ($subtotal < (float) $promo->min_purchase) {
            throw ValidationException::withMessages([
                'promo_code' => sprintf(
                    'Ce code nécessite un minimum de %s € d\'achat.',
                    number_format((float) $promo->min_purchase, 2, ',', ' ')
                ),
            ]);
        }
        return $promo;
    }

    public function store(PromoCode $promo): void
    {
        session([self::SESSION_KEY => $promo->id]);
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function current(): ?PromoCode
    {
        $id = session(self::SESSION_KEY);
        return $id ? PromoCode::find($id) : null;
    }

    public function currentValidFor(float $subtotal): ?PromoCode
    {
        $promo = $this->current();
        if (!$promo) return null;
        if (!$promo->isValidFor($subtotal)) {
            $this->forget();
            return null;
        }
        return $promo;
    }

    public function discountFor(float $subtotal, ?PromoCode $promo): float
    {
        if (!$promo) return 0.0;
        return (float) min($promo->discount_amount, $subtotal);
    }

    public function increment(PromoCode $promo): void
    {
        $promo->increment('used_count');
    }
}
