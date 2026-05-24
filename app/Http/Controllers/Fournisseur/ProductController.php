<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Imports\FournisseurProductsImport;
use App\Models\Category;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sortable  = ['name', 'purchase_price', 'category_id'];
        $sort      = in_array($request->sort, $sortable) ? $request->sort : 'name';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        $products = Product::with('category')
            ->when($request->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($request->search) . '%']))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('in_stock'), fn ($q) => $q->where('in_stock', (bool) $request->in_stock))
            ->orderBy($sort, $direction)
            ->paginate(30)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('fournisseur.products.index', compact('products', 'categories', 'sort', 'direction'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('fournisseur.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'sku'            => 'nullable|string|max:100|unique:products,sku',
            'volume_ml'      => 'nullable|integer|min:1',
            'alcohol_degree' => 'nullable|numeric|min:0|max:100',
            'unit'           => 'nullable|string|max:50',
            'description'    => 'nullable|string|max:2000',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = false;
        $data['in_stock']  = true;
        $data['unit']      = $data['unit'] ?? 'bouteille';
        $data['sale_price'] = $data['sale_price'] ?? 0;

        // Ensure slug uniqueness
        $base = $data['slug'];
        $n = 1;
        while (Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $base . '-' . $n++;
        }

        Product::create($data);

        return redirect()->route('fournisseur.products.index')
            ->with('success', "Produit « {$data['name']} » soumis. Il sera visible après validation par un administrateur.");
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('fournisseur.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'purchase_price' => 'required|numeric|min:0',
            'sku'            => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'volume_ml'      => 'nullable|integer|min:1',
            'alcohol_degree' => 'nullable|numeric|min:0|max:100',
            'description'    => 'nullable|string|max:2000',
            'is_new'         => 'boolean',
        ]);

        $data['is_new'] = $request->boolean('is_new');
        $product->update($data);

        return redirect()->route('fournisseur.products.index')
            ->with('success', "Produit « {$product->name} » mis à jour.");
    }

    public function toggleStock(Product $product)
    {
        $product->update(['in_stock' => !$product->in_stock]);

        $status = $product->in_stock ? 'en stock' : 'en rupture de stock';
        return back()->with('success', "« {$product->name} » marqué {$status}.");
    }

    public function quickUpdate(Request $request, Product $product)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $rules = [
            'purchase_price' => 'required|numeric|min:0',
            'sku'            => "nullable|string|max:100|unique:products,sku,{$product->id}",
            'volume_ml'      => 'nullable|integer|min:0',
            'alcohol_degree' => 'nullable|numeric|min:0|max:100',
        ];

        if (!array_key_exists($field, $rules)) {
            return response()->json(['error' => 'Champ non autorisé'], 422);
        }

        $validated = validator([$field => $value], [$field => $rules[$field]])->validate();
        $product->update($validated);

        return response()->json([
            'success' => true,
            'field'   => $field,
            'value'   => $product->$field,
        ]);
    }

    public function toggleNew(Product $product)
    {
        $product->update(['is_new' => !$product->is_new]);

        $status = $product->is_new ? 'marqué comme nouveauté' : 'badge NEW retiré';
        return back()->with('success', "« {$product->name} » {$status}.");
    }

    public function importForm()
    {
        return view('fournisseur.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        Excel::import(new FournisseurProductsImport, $request->file('file'));

        return redirect()->route('fournisseur.products.index')
            ->with('success', 'Importation réussie. Les produits seront visibles après validation par un administrateur.');
    }

    public function export()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $filename = 'produits_fournisseur_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($products) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['nom', 'prix_achat', 'prix_vente', 'categorie', 'sku', 'description', 'volume_ml', 'degre', 'unite'], ';');
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->name,
                    $p->purchase_price,
                    $p->sale_price,
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

    public function catalogPdf()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('fournisseur.products.catalog-pdf', [
            'products'    => $products,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('A4', 'portrait');

        $filename = 'catalogue_fournisseur_' . now()->format('Ymd') . '.pdf';
        return $pdf->stream($filename);
    }
}
