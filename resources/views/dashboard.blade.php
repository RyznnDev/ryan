@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    // Format angka; null (tabel belum ada) tampil sebagai "–"
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $maxStock = max(1, $topStock->max('stock') ?? 1);

    $shortcuts = [
        ['Stok Masuk',  $stats['stock_in'],   'stock-in.index',  'text-emerald-600 bg-emerald-50', 'fa-arrow-down'],
        ['Stok Keluar', $stats['stock_out'],  'stock-out.index', 'text-orange-600 bg-orange-50', 'fa-arrow-up'],
        ['Transfer',    $stats['transfers'],  'transfers.index', 'text-blue-600 bg-blue-50', 'fa-right-left'],
    ];
@endphp

{{-- Di layar lebar tinggi halaman dikunci setinggi layar, jadi tidak ada scroll halaman --}}
<div class="flex flex-col lg:h-[calc(100vh-4rem)]">

    <div class="mb-4">
        <h3 class="text-5xl font-bold">Dashboard</h3>
        <p class="text-sm text-gray-500 mt-1">Halo Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </div>

    <!-- Kartu utama: hanya angka ringkasan inti -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Produk</p><i class="fa-solid fa-box text-gray-400"></i></div>
            <p class="text-2xl font-bold mt-1">{{ $n($stats['total_products']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Stok</p><i class="fa-solid fa-cubes text-gray-400"></i></div>
            <p class="text-2xl font-bold mt-1">{{ $n($stats['total_stock']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Nilai Persediaan</p><i class="fa-solid fa-wallet text-gray-400"></i></div>
            <p class="text-2xl font-bold mt-1">Rp {{ $n($stats['stock_value']) }}</p>
        </div>
    </div>

    <!-- Aktivitas stok: gabungan Stok Masuk / Stok Keluar / Transfer dalam satu kartu -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <h4 class="font-semibold mb-3"><i class="fa-solid fa-chart-line mr-2 text-gray-400"></i>Aktivitas Stok</h4>
        <div class="grid grid-cols-3 divide-x divide-gray-100">
            @foreach ($shortcuts as [$label, $value, $route, $color, $icon])
                <a href="{{ \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#' }}"
                    class="px-4 first:pl-0 last:pr-0 hover:opacity-70 transition">
                    <span class="inline-block text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $color }}"><i class="fa-solid {{ $icon }} mr-1"></i>{{ $label }}</span>
                    <p class="text-2xl font-bold mt-2">{{ $n($value) }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Dua kartu sejajar setengah-setengah dengan celah di tengah: Produk (kiri), Perlu Restock (kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:flex-1 lg:min-h-0">

        <!-- Kiri: tab "Terbaru" dan "Stok Terbanyak" -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col min-h-0">
            <div class="flex items-center justify-between mb-3 shrink-0">
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <button type="button" data-tab-btn="latest"
                        class="tab-btn px-3 py-1.5 text-sm font-medium rounded-md bg-white shadow-sm">
                        <i class="fa-solid fa-clock mr-1.5"></i>Produk Terbaru
                    </button>
                    <button type="button" data-tab-btn="top-stock"
                        class="tab-btn px-3 py-1.5 text-sm font-medium rounded-md text-gray-500">
                        <i class="fa-solid fa-ranking-star mr-1.5"></i>Stok Terbanyak
                    </button>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('products.index') ? route('products.index') : '#' }}"
                    class="text-sm text-blue-600 hover:underline">Lihat semua <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar">
                <!-- Panel: Produk Terbaru -->
                <div data-tab-panel="latest">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-gray-500 text-left">
                                <tr>
                                    <th class="py-2 font-medium">Produk</th>
                                    <th class="py-2 font-medium">Harga</th>
                                    <th class="py-2 font-medium">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latest as $p)
                                    <tr class="border-t border-gray-100">
                                        <td class="py-2"><span class="font-medium">{{ $p->title }}</span></td>
                                        <td class="py-2 text-gray-700">Rp {{ number_format($p->price, 2, ',', '.') }}</td>
                                        <td class="py-2 text-gray-700">{{ $p->stock }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-gray-500">Data Products belum ada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel: Stok Terbanyak -->
                <div data-tab-panel="top-stock" class="hidden">
                    @forelse ($topStock as $p)
                        <div class="mb-3 last:mb-0">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="truncate pr-4">{{ $p->title }}</span>
                                <span class="font-medium">{{ $n($p->stock) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ round($p->stock / $maxStock * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada data produk.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kanan: stok menipis -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col min-h-0">
            <div class="flex items-center justify-between mb-3 shrink-0">
                <h4 class="font-semibold"><i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i>Perlu Restock <span class="text-xs font-normal text-gray-400">(≤ {{ $lowLimit }})</span></h4>
                <span class="text-sm font-semibold px-2.5 py-1 rounded-full {{ $stats['low_stock'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }}">
                    <i class="fa-solid fa-box mr-1"></i>{{ $n($stats['low_stock']) }} produk
                </span>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar">
                @forelse ($lowStock as $p)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm truncate pr-4">{{ $p->title }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $p->stock == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            <i class="fa-solid {{ $p->stock == 0 ? 'fa-circle-xmark' : 'fa-circle-exclamation' }} mr-1"></i>{{ $p->stock == 0 ? 'Habis' : 'Sisa '.$p->stock }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500"><i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i>Semua stok aman.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tabBtn;

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.toggle('bg-white', b === btn);
                b.classList.toggle('shadow-sm', b === btn);
                b.classList.toggle('text-gray-500', b !== btn);
            });

            document.querySelectorAll('[data-tab-panel]').forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.tabPanel !== target);
            });
        });
    });
</script>
@endsection