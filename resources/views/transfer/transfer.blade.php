@extends('layouts.app')

@section('title', 'Transfer')

@section('content')
@php
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $statusColor = fn ($s) => match ($s) {
        'selesai', 'completed' => 'bg-emerald-100 text-emerald-700',
        'batal', 'cancelled'   => 'bg-red-100 text-red-700',
        default                => 'bg-amber-100 text-amber-700',
    };
@endphp

<div class="mb-6 flex items-end justify-between">
    <div>
        <h3 class="text-5xl font-bold">Transfer</h3>
        <p class="text-sm text-gray-500 mt-1">Perpindahan stok antar gudang.</p>
    </div>
    <a href="{{ \Illuminate\Support\Facades\Route::has('transfers.create') ? route('transfers.create') : '#' }}"
        class="text-sm font-medium px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
        <i class="fa-solid fa-plus mr-1.5"></i>Buat Transfer
    </a>
</div>

<!-- Kartu utama -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Transfer</p><i class="fa-solid fa-right-left text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Qty Dipindah</p><i class="fa-solid fa-boxes-stacked text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total_qty']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Belum Selesai</p><i class="fa-solid fa-hourglass-half text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['pending']) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">

    <!-- Riwayat -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold"><i class="fa-solid fa-clock-rotate-left mr-2 text-gray-400"></i>Riwayat</h4>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full text-blue-600 bg-blue-50"><i class="fa-solid fa-right-left mr-1"></i>Transfer</span>
        </div>

        <div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 text-left">
                        <tr>
                            <th class="py-2 font-medium">Tanggal</th>
                            <th class="py-2 font-medium">Produk</th>
                            <th class="py-2 font-medium">Qty</th>
                            <th class="py-2 font-medium">Gudang Asal <i class="fa-solid fa-arrow-right text-xs mx-1"></i> Tujuan</th>
                            <th class="py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $r)
                            <tr class="border-t border-gray-100">
                                <td class="py-2.5 text-gray-700">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                                <td class="py-2.5 font-medium">{{ $r->product->title ?? '–' }}</td>
                                <td class="py-2.5 text-gray-700">{{ $n($r->qty) }}</td>
                                <td class="py-2.5 text-gray-700">
                                    {{ $r->fromWarehouse->name ?? '–' }} <i class="fa-solid fa-arrow-right text-xs mx-1"></i> {{ $r->toWarehouse->name ?? '–' }}
                                </td>
                                <td class="py-2.5">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColor($r->status) }}">
                                        {{ ucfirst($r->status ?? '–') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Belum ada data transfer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($records, 'links'))
                <div class="mt-4">{{ $records->links() }}</div>
            @endif
        </div>

    </div>

    <!-- Kolom kanan: arus per gudang + transfer belum selesai -->
    <div class="flex flex-col gap-4">

    <!-- Arus stok per gudang -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h4 class="font-semibold mb-4"><i class="fa-solid fa-warehouse mr-2 text-gray-400"></i>Arus per Gudang</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-left">
                    <tr>
                        <th class="py-2 font-medium">Gudang</th>
                        <th class="py-2 font-medium"><i class="fa-solid fa-arrow-up mr-1"></i>Keluar</th>
                        <th class="py-2 font-medium"><i class="fa-solid fa-arrow-down mr-1"></i>Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouseFlow as $w)
                        <tr class="border-t border-gray-100">
                            <td class="py-2.5 font-medium">{{ $w->name }}</td>
                            <td class="py-2.5 text-orange-600 font-semibold">-{{ $n($w->qty_out) }}</td>
                            <td class="py-2.5 text-emerald-600 font-semibold">+{{ $n($w->qty_in) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Belum ada data gudang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transfer yang belum selesai -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold"><i class="fa-solid fa-hourglass-half mr-2 text-gray-400"></i>Menunggu Selesai</h4>
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full {{ $pending->count() > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}">
                <i class="fa-solid fa-hourglass-half mr-1"></i>{{ $n($pending->count()) }} transfer
            </span>
        </div>
        @forelse ($pending as $r)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                <div class="min-w-0 pr-4">
                    <p class="text-sm truncate">{{ $r->product->title ?? '–' }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $r->fromWarehouse->name ?? '–' }} <i class="fa-solid fa-arrow-right text-xs mx-1"></i> {{ $r->toWarehouse->name ?? '–' }}
                    </p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $n($r->qty) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500"><i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i>Semua transfer sudah selesai.</p>
        @endforelse
    </div>

    </div>
</div>

@endsection