@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')
@php
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
@endphp

<div class="mb-6 flex items-end justify-between">
    <div>
        <h3 class="text-5xl font-bold">Stok Masuk</h3>
        <p class="text-sm text-gray-500 mt-1">Catatan barang yang masuk ke gudang.</p>
    </div>
    <a href="{{ \Illuminate\Support\Facades\Route::has('stock-in.create') ? route('stock-in.create') : '#' }}"
        class="text-sm font-medium px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Stok Masuk
    </a>
</div>

<!-- Kartu utama -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Transaksi</p><i class="fa-solid fa-receipt text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Qty Masuk</p><i class="fa-solid fa-boxes-stacked text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total_qty']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Bulan Ini</p><i class="fa-solid fa-calendar-days text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['this_month']) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">

    <!-- Riwayat -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold"><i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i>Riwayat</h4>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full text-emerald-600 bg-emerald-50"><i class="fa-solid fa-download mr-1"></i>Stok Masuk</span>
        </div>

        <div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 text-left">
                        <tr>
                            <th class="py-2 font-medium">Tanggal</th>
                            <th class="py-2 font-medium">Produk</th>
                            <th class="py-2 font-medium">Qty</th>
                            <th class="py-2 font-medium">Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $r)
                            <tr class="border-t border-gray-100">
                                <td class="py-2.5 text-gray-700">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                                <td class="py-2.5 font-medium">{{ $r->product->title ?? '–' }}</td>
                                <td class="py-2.5 text-emerald-600 font-semibold">+{{ $n($r->qty) }}</td>
                                <td class="py-2.5 text-gray-700">{{ $r->supplier->name ?? '–' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Belum ada data stok masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($records, 'links'))
                <div class="mt-4">{{ $records->links() }}</div>
            @endif
        </div>

    </div>

    <!-- Hari ini -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold"><i class="fa-solid fa-calendar-day mr-2 text-gray-400"></i>Masuk Hari Ini</h4>
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full {{ $today->count() > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                <i class="fa-solid fa-receipt mr-1"></i>{{ $n($today->count()) }} transaksi
            </span>
        </div>
        @forelse ($today as $r)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                <span class="text-sm truncate pr-4">{{ $r->product->title ?? '–' }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">+{{ $n($r->qty) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500"><i class="fa-regular fa-calendar-xmark mr-1.5"></i>Belum ada stok masuk hari ini.</p>
        @endforelse
    </div>
</div>

@endsection