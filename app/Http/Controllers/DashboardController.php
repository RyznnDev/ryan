<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $lowLimit = 10;

        $stats = [
            'total_products' => Product::count(),
            'total_stock'    => Product::sum('stock'),
            'low_stock'      => Product::where('stock', '<=', $lowLimit)->count(),
            'stock_value'    => Product::sum(DB::raw('stock * price')),

            // Aktivitas stok: jumlah transaksi yang sudah tercatat
            'stock_in'       => StockIn::count(),
            'stock_out'      => StockOut::count(),
            'transfers'      => Transfer::count(),

            'categories'     => Category::count(),
            'suppliers'      => Supplier::count(),
            'locations'      => Location::count(),
        ];

        // Produk dengan stok terbanyak
        $topStock = Product::orderBy('stock', 'desc')->take(5)->get();

        // Produk yang perlu restock
        $lowStock = Product::where('stock', '<=', $lowLimit)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // Produk terbaru
        $latest = Product::latest()->take(5)->get();

        return view('dashboard', compact(
            'stats',
            'topStock',
            'lowStock',
            'lowLimit',
            'latest'
        ));
    }
}