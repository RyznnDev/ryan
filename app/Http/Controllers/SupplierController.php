<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('stockIns')->orderBy('name')->get();

        // Tambahkan variabel $stats untuk kartu ringkasan
        $stats = [
            'total'      => $suppliers->count(),
            'total_qty'  => (int) StockIn::sum('qty'), // Ganti 'qty' ke 'quantity' jika kolom di database bernama quantity
            'this_month' => StockIn::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count(),
        ];

        // Pastikan 'stats' ikut dimasukkan ke dalam compact()
        // Sesuaikan nama view dengan file blade kamu ('supplier' atau 'suplier')
        return view('supplier', compact('suppliers', 'stats'));
        
    }

    public function store(Request $request)
    {
        Supplier::create($request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
        ]));

        return back()->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
        ]));

        return back()->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->stockIns()->exists()) {
            return back()->withErrors(['name' => 'Supplier tidak bisa dihapus karena memiliki riwayat stok masuk.']);
        }

        $supplier->delete();

        return back()->with('success', 'Supplier berhasil dihapus.');
    }
}