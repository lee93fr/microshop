<?php
// app/Http/Controllers/Client/CartController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PromoCodeService $promoCodeService,
    ) {}

    public function index()
    {
        $items    = $this->cartService->items();
        $total    = $this->cartService->total();
        $promo    = $this->promoCodeService->currentValidFor($total);
        $discount = $this->promoCodeService->discountFor($total, $promo);
        return view('client.cart.index', compact('items', 'total', 'promo', 'discount'));
    }

    public function applyPromo(Request $request)
    {
        $data = $request->validate(['promo_code' => 'required|string|max:50']);
        $subtotal = $this->cartService->total();
        $promo = $this->promoCodeService->validateForSubtotal($data['promo_code'], $subtotal);
        $this->promoCodeService->store($promo);
        return back()->with('success', "Code promo « {$promo->code} » appliqué : −" . number_format((float) $promo->discount_amount, 2, ',', ' ') . ' €');
    }

    public function removePromo()
    {
        $this->promoCodeService->forget();
        return back()->with('success', 'Code promo retiré.');
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $request->validate(['quantity' => 'integer|min:1|max:999']);
        $this->cartService->add($product, $request->quantity ?? 1);

        if ($request->wantsJson()) {
            return response()->json([
                'message'    => "{$product->name} ajouté au panier.",
                'count'      => $this->cartService->count(),
                'total'      => $this->cartService->total(),
                'productId'  => $product->id,
                'productQty' => $this->cartService->quantityFor($product->id),
            ]);
        }

        return back()->with('success', "{$product->name} ajouté au panier.");
    }

    public function update(Request $request, int $productId)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);
        $this->cartService->update($productId, $request->quantity);

        if ($request->wantsJson()) {
            return response()->json([
                'message'    => 'Panier mis à jour.',
                'count'      => $this->cartService->count(),
                'total'      => $this->cartService->total(),
                'productId'  => $productId,
                'productQty' => $this->cartService->quantityFor($productId),
            ]);
        }

        return back()->with('success', 'Panier mis à jour.');
    }

    public function remove(Request $request, int $productId)
    {
        $this->cartService->remove($productId);

        if ($request->wantsJson()) {
            return response()->json([
                'message'    => 'Produit retiré du panier.',
                'count'      => $this->cartService->count(),
                'total'      => $this->cartService->total(),
                'productId'  => $productId,
                'productQty' => 0,
            ]);
        }

        return back()->with('success', 'Produit retiré.');
    }

    public function clear()
    {
        $this->cartService->clear();
        return back()->with('success', 'Panier vidé.');
    }
}
