<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products as products_count' => fn ($q) => $q->withTrashed()])
            ->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);
        Category::create($data);
        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => "required|string|max:100|unique:categories,name,{$category->id}",
            'description' => 'nullable|string',
        ]);
        $category->update($data);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->withTrashed()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une catégorie contenant des produits.');
        }
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    public function create()  { return redirect()->route('admin.categories.index'); }
    public function edit(Category $category) { return redirect()->route('admin.categories.index'); }
    public function show(Category $category) { return redirect()->route('admin.categories.index'); }
}
