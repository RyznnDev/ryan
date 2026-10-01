<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Transfer;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function inventory(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');
        $loc  = $request->input('location_id');

        // Filter tanggal yang dipakai ulang di semua laporan
        $period = fn ($q) => $q
            ->when($from, fn ($w) => $w->whereDate('date', '>=', $from))
            ->when($to,   fn ($w) => $w->whereDate('date', '<=', $to));

        $stockIns = StockIn::with(['product', 'location'])
            ->tap($period)
            ->when($loc, fn ($q) => $q->where('location_id', $loc))
            ->latest('date')
            ->get();

        $stockOuts = StockOut::with(['product', 'location'])
            ->tap($period)
            ->when($loc, fn ($q) => $q->where('location_id', $loc))
            ->latest('date')
            ->get();

        $transfers = Transfer::with(['product', 'fromWarehouse', 'toWarehouse'])
            ->tap($period)
            ->when($loc, fn ($q) => $q->where(fn ($w) => $w
                ->where('from_warehouse_id', $loc)
                ->orWhere('to_warehouse_id', $loc)))
            ->latest('date')
            ->get();

        $supplierReceipts = StockIn::with(['supplier', 'product', 'location'])
            ->whereNotNull('supplier_id')
            ->tap($period)
            ->when($loc, fn ($q) => $q->where('location_id', $loc))
            ->latest('date')
            ->get();

        $stats = [
            'stock_in_qty'  => $stockIns->sum('qty'),
            'stock_out_qty' => $stockOuts->sum('qty'),
            'transfer_qty'  => $transfers->sum('qty'),
            'supplier_qty'  => $supplierReceipts->sum('qty'),
        ];

        return view('report.laporan', [
            'stockIns'         => $stockIns,
            'stockOuts'        => $stockOuts,
            'transfers'        => $transfers,
            'supplierReceipts' => $supplierReceipts,
            'stats'            => $stats,
            'locations'        => Location::orderBy('name')->get(),
        ]);
    }
}