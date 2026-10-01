<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount(['products' => fn ($q) => $q->where('product_location.stock', '>', 0)])
            ->withSum('products as stock_total', 'product_location.stock')
            ->orderBy('name')->get();

        $stats = [
            'total'          => $locations->count(),
            'total_products' => DB::table('product_location')->where('stock', '>', 0)->distinct()->count('product_id'),
            'total_stock'    => (int) $locations->sum('stock_total'),
        ];

        return view('lokasi', compact('locations', 'stats'));
    }

    public function store(Request $request)
    {
        Location::create($request->validate([
            'name'    => 'required|string|max:100',
            'code'    => 'nullable|string|max:20|unique:locations,code',
            'address' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Gudang ditambahkan.');
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->validate([
            'name'    => 'required|string|max:100',
            'code'    => 'nullable|string|max:20|unique:locations,code,' . $location->id,
            'address' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Gudang diperbarui.');
    }

    public function destroy(Location $location)
    {
        if ($location->products()->wherePivot('stock', '>', 0)->exists()) {
            return back()->withErrors(['name' => 'Gudang masih menyimpan stok, pindahkan dulu lewat transfer.']);
        }

        $location->delete();

        return back()->with('success', 'Gudang dihapus.');
    }
}