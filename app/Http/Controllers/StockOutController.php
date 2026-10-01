<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index()
    {
        // 1. Data Riwayat Transaksi
        $records = StockOut::with('product')
            ->latest('date')
            ->latest('id')
            ->paginate(10);

        // 2. Data Kartu Statistik Utama
        $stats = [
            'total'      => StockOut::count(),
            'total_qty'  => (int) StockOut::sum('qty'),
            'this_month' => (int) StockOut::whereMonth('date', now()->month)
                                        ->whereYear('date', now()->year)
                                        ->sum('qty'),
        ];

        // 3. Data Produk Terbanyak Keluar
        $topProducts = StockOut::join('products', 'products.id', '=', 'stock_outs.product_id')
            ->selectRaw('products.title, SUM(stock_outs.qty) as total_qty')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 4. Data Stok Keluar Hari Ini
        $today = StockOut::with('product')
            ->whereDate('date', today())
            ->get();

        return view('products.outProduct', compact('records', 'stats', 'topProducts', 'today'));
    }

    public function create()
    {
        return view('stocks.out.create', [
            'products'  => Product::with('locations')->orderBy('title')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'date'        => 'required|date',
            'qty'         => 'required|integer|min:1',
            'note'        => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            // Dicek dulu: kalau stok gudang kurang, ValidationException (field "qty") dan semuanya dibatalkan
            $product->adjustLocationStock($data['location_id'], -$data['qty']);
            $product->decrement('stock', $data['qty']);

            StockOut::create($data);
        });

        return redirect()->route('stock-out.index')->with('success', 'Stok keluar berhasil dicatat.');
    }
}