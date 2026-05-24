<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::orderByDesc('created_at')->paginate(20);
        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        PromoCode::create($data);
        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Code promo créé.');
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $data = $this->validateData($request, $promoCode->id);
        $promoCode->update($data);
        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Code promo mis à jour.');
    }

    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);
        return back()->with('success', $promoCode->is_active ? 'Code activé.' : 'Code désactivé.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return back()->with('success', 'Code promo supprimé.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code'            => ['required', 'string', 'max:50', Rule::unique('promo_codes', 'code')->ignore($ignoreId)],
            'discount_amount' => 'required|numeric|min:0.01',
            'min_purchase'    => 'required|numeric|min:0',
            'max_uses'        => 'nullable|integer|min:1',
            'starts_at'       => 'nullable|date',
            'expires_at'      => 'nullable|date|after_or_equal:starts_at',
            'is_active'       => 'sometimes|boolean',
        ]);
        $data['code']      = strtoupper(trim($data['code']));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        return $data;
    }
}
