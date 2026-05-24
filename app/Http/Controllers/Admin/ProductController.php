<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    private function indexUrl(): string
    {
        return session('products.index_url', route('admin.produits.index'));
    }

    public function index(Request $request)
    {
        session(['products.index_url' => $request->fullUrl()]);

        $allowedSorts = ['name', 'purchase_price', 'sale_price', 'volume_ml', 'is_active', 'created_at'];
        $sort      = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $products = Product::with('category')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('sku', 'ilike', "%{$request->search}%");
            }))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', (bool) $request->is_active))
            ->when($request->filled('volume_min'), fn ($q) => $q->where('volume_ml', '>=', (int) $request->volume_min))
            ->when($request->filled('price_min'),  fn ($q) => $q->where('purchase_price', '>=', (float) $request->price_min))
            ->when($request->filled('sale_min'),   fn ($q) => $q->where('sale_price', '>=', (float) $request->sale_min))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'sort', 'direction'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_new']    = $request->boolean('is_new');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_remote_url')) {
            if ($path = $this->downloadRemoteImage($request->image_remote_url)) {
                $data['image'] = $path;
            }
        }
        unset($data['image_remote_url']);

        Product::create($data);

        return redirect($this->indexUrl())
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.produits.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_new']    = $request->boolean('is_new');

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_remote_url')) {
            if ($path = $this->downloadRemoteImage($request->image_remote_url)) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $data['image'] = $path;
            }
        }
        unset($data['image_remote_url']);

        $product->update($data);

        return redirect($this->indexUrl())
            ->with('success', 'Produit mis à jour.');
    }

    private function downloadRemoteImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 LaTournee/1.0'])
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful()) return null;

        $contentType = strtolower($response->header('Content-Type', ''));
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        $ext = null;
        foreach ($allowed as $mime => $extension) {
            if (str_contains($contentType, $mime)) { $ext = $extension; break; }
        }
        if (!$ext) return null;

        $body = $response->body();
        if (strlen($body) === 0 || strlen($body) > 5 * 1024 * 1024) return null;

        $path = 'products/' . Str::random(40) . '.' . $ext;
        Storage::disk('public')->put($path, $body);
        return $path;
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();

        return redirect($this->indexUrl())
            ->with('success', 'Produit supprimé.');
    }

    public function quickUpdate(Request $request, Product $product)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $rules = [
            'name'            => 'required|string|max:255',
            'sku'             => "nullable|string|max:100|unique:products,sku,{$product->id}",
            'category_id'     => 'required|exists:categories,id',
            'purchase_price'  => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'suggested_price' => 'nullable|numeric|min:0',
            'volume_ml'       => 'nullable|integer|min:0',
            'alcohol_degree'  => 'nullable|numeric|min:0|max:100',
        ];

        if (!array_key_exists($field, $rules)) {
            return response()->json(['error' => 'Champ non autorisé'], 422);
        }

        $validated = validator([$field => $value], [$field => $rules[$field]])->validate();
        $product->update($validated);
        $product->load('category');

        $margin = $product->margin;

        return response()->json([
            'success'       => true,
            'field'         => $field,
            'value'         => $product->$field,
            'category_name' => $product->category->name ?? '',
            'margin'        => $margin,
            'margin_class'  => $margin >= 30 ? 'badge-green' : ($margin >= 15 ? 'badge-yellow' : 'badge-red'),
        ]);
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Statut modifié.');
    }

    public function toggleNew(Product $product)
    {
        $product->update(['is_new' => !$product->is_new]);
        return back()->with('success', 'Badge NEW modifié.');
    }

    public function importForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        Excel::import(new ProductsImport, $request->file('file'));

        return redirect()->route('admin.produits.index')
            ->with('success', 'Importation réussie.');
    }

    public function catalogPdf()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.products.catalog-pdf', [
            'products'    => $products,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'portrait');

        $filename = 'catalogue_admin_' . now()->format('Ymd') . '.pdf';
        return $pdf->stream($filename);
    }

    public function export()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $filename = 'produits_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($products) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, ['nom', 'prix_achat', 'prix_vente', 'prix_conseille', 'categorie', 'sku', 'description', 'volume_ml', 'degre', 'unite'], ';');
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->name,
                    $p->purchase_price,
                    $p->sale_price,
                    $p->suggested_price ?? '',
                    $p->category->name ?? '',
                    $p->sku ?? '',
                    $p->description ?? '',
                    $p->volume_ml ?? '',
                    $p->alcohol_degree ?? '',
                    $p->unit,
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
