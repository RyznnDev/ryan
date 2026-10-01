<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->withSum('products', 'stock')
            ->orderBy('name')->get();

        $stats = [
            'total'    => $categories->count(),
            'products' => Product::whereNotNull('category_id')->count(),
            'empty'    => $categories->where('products_count', 0)->count(),
        ];

        return view('kategori', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        Category::create($request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['name' => 'Kategori masih dipakai oleh produk.']);
        }

        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}