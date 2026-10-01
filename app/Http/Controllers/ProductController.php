<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                                       ->orWhere('sku', 'like', "%{$s}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(5)          // 5 baris per halaman
            ->withQueryString();   // filter tetap terbawa saat pindah halaman

        return view('products.index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->pluck('name'),
            'statuses'   => Product::STATUSES,
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->pluck('name'),
            'nextSku'    => $this->nextSku(),
        ]);
    }

    public function store(Request $request)
    {
        // Stok TIDAK diisi di sini. Stok awal dicatat lewat menu Stok Masuk
        // supaya total stok dan stok per gudang selalu cocok.
        $data = $request->validate([
            'title'       => 'required|string|min:3',
            'category'    => ['required', Rule::exists('categories', 'name')],
            'status'      => 'required|in:' . implode(',', array_keys(Product::STATUSES)),
            'price'       => 'required|numeric|min:0',
        ]);

        $data['stock'] = 0;

        // SKU dibuat otomatis di server (bukan dari input form) dan dikunci
        // dalam transaksi supaya dua produk tidak mendapat nomor yang sama.
        DB::transaction(function () use ($data) {
            $data['sku'] = $this->nextSku(lock: true);
            Product::create($data);
        });

        return redirect()->route('products.index')->with('success', 'Data berhasil disimpan!');
    }

    public function show(string $id)
    {
        return view('products.show', [
            'product' => Product::with('locations')->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return view('products.edit', [
            'product'    => Product::findOrFail($id),
            'categories' => Category::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        // Stok tidak bisa diubah dari sini (hanya lewat Stok Masuk / Stok Keluar / Transfer).
        $data = $request->validate([
            'title'       => 'required|string|min:3',
            'category'    => ['required', Rule::exists('categories', 'name')],
            'status'      => 'required|in:' . implode(',', array_keys(Product::STATUSES)),
            'price'       => 'required|numeric|min:0',
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Data berhasil diubah!');
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Nomor SKU berikutnya dengan format PRD-001, PRD-002, dst.
     */
    private function nextSku(bool $lock = false): string
    {
        $query = Product::query()->where('sku', 'like', 'PRD-%');

        if ($lock) {
            $query->lockForUpdate();
        }

        $max = $query->pluck('sku')
            ->map(fn ($sku) => preg_match('/^PRD-(\d+)$/i', $sku, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return 'PRD-' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);
    }
}