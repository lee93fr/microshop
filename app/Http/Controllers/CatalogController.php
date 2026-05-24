<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->with('category')
            ->when($request->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->category)))
            ->when($request->search, function ($q) use ($request) {
                $like = '%' . mb_strtolower($request->search) . '%';
                $q->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                      ->orWhereHas('category', fn ($c) => $c->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $categories = Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $cartItems = Auth::check()
            ? Cart::where('user_id', Auth::id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('client.catalog.index', compact('products', 'categories', 'cartItems'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('category');

        $cartQty = Auth::check()
            ? (int) Cart::where('user_id', Auth::id())->where('product_id', $product->id)->value('quantity')
            : 0;

        return view('client.catalog.show', compact('product', 'cartQty'));
    }
}
