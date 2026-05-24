<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function items(): Collection
    {
        return Cart::where('user_id', Auth::id())
            ->with('product.category')
            ->get();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = Cart::firstOrNew([
            'user_id'    => Auth::id(),
            'product_id' => $product->id,
        ]);
        $cart->quantity = ($cart->quantity ?? 0) + $quantity;
        $cart->save();
    }

    public function update(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        }
        Cart::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);
    }

    public function remove(int $productId): void
    {
        Cart::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();
    }

    public function clear(): void
    {
        Cart::where('user_id', Auth::id())->delete();
    }

    public function total(): float
    {
        return $this->items()->sum(fn ($item) => $item->product->sale_price * $item->quantity);
    }

    public function count(): int
    {
        return (int) Cart::where('user_id', Auth::id())->sum('quantity');
    }

    public function quantityFor(int $productId): int
    {
        return (int) Cart::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->value('quantity');
    }
}
