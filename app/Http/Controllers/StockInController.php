<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index()
    {
        $records = StockIn::with(['product', 'supplier'])->latest('date')->latest('id')->paginate(10);

        $stats = [
            'total'      => StockIn::count(),
            'total_qty'  => (int) StockIn::sum('qty'),
            'this_month' => StockIn::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ];

        $topProducts = StockIn::join('products', 'products.id', '=', 'stock_ins.product_id')
            ->selectRaw('products.title, SUM(stock_ins.qty) as total_qty')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $today = StockIn::with('product')->whereDate('date', today())->get();

        return view('products.inProduct', compact('records', 'stats', 'topProducts', 'today'));
    }

    public function create()
    {
        return view('stocks.in.create', [
            'products'  => Product::with('locations')->orderBy('title')->get(),
            'locations' => Location::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date'        => 'required|date',
            'qty'         => 'required|integer|min:1',
            'note'        => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            StockIn::create($data);

            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $product->increment('stock', $data['qty']);
            $product->adjustLocationStock($data['location_id'], $data['qty']);
        });

        return redirect()->route('stock-in.index')->with('success', 'Stok masuk berhasil dicatat.');
    }
}