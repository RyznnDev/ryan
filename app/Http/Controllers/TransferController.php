<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        $records = Transfer::with(['product', 'fromWarehouse', 'toWarehouse'])
            ->latest('date')->latest('id')->paginate(10);

        $stats = [
            'total'     => Transfer::count(),
            'total_qty' => (int) Transfer::sum('qty'),
            'pending'   => Transfer::where('status', '!=', 'selesai')->count(),
        ];

        $topProducts = Transfer::join('products', 'products.id', '=', 'transfers.product_id')
            ->selectRaw('products.title, SUM(transfers.qty) as total_qty')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $pending = Transfer::with(['product', 'fromWarehouse', 'toWarehouse'])
            ->where('status', '!=', 'selesai')->latest('date')->limit(8)->get();

        $warehouseFlow = Location::withSum('outgoingTransfers as qty_out', 'qty')
            ->withSum('incomingTransfers as qty_in', 'qty')
            ->orderBy('name')->get();

        return view('transfer.transfer', compact('records', 'stats', 'topProducts', 'pending', 'warehouseFlow'));
    }

    public function create()
    {
        return view('transfer.create', [
            'products'  => Product::with('locations')->orderBy('title')->get(), // tambah with('locations')
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:locations,id',
            'to_warehouse_id'   => 'required|exists:locations,id|different:from_warehouse_id',
            'date'              => 'required|date',
            'qty'               => 'required|integer|min:1',
        ]);
        $data['status'] = 'selesai';

        DB::transaction(function () use ($data) {
            Transfer::create($data);

            // stok total produk tidak berubah, hanya pindah antar gudang
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $product->adjustLocationStock($data['from_warehouse_id'], -$data['qty']);
            $product->adjustLocationStock($data['to_warehouse_id'], $data['qty']);
        });

        return redirect()->route('transfers.index')->with('success', 'Transfer antar gudang berhasil.');
    }
}