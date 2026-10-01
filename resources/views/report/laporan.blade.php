@extends('layouts.app')

@section('title', 'Persediaan')

@section('content')
@php
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $d = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '–';
    $statusColor = fn ($s) => match ($s) {
        'selesai', 'completed' => 'bg-emerald-100 text-emerald-700',
        'batal', 'cancelled'   => 'bg-red-100 text-red-700',
        default                => 'bg-amber-100 text-amber-700',
    };
    $tabs = [
        ['stock-in',  'Stok Masuk',          $stats['stock_in_qty'],  'text-emerald-600 bg-emerald-50', 'fa-download'],
        ['stock-out', 'Stok Keluar',         $stats['stock_out_qty'], 'text-orange-600 bg-orange-50', 'fa-upload'],
        ['transfer',  'Transfer Antar Gudang', $stats['transfer_qty'], 'text-blue-600 bg-blue-50', 'fa-right-left'],
        ['supplier',  'Barang dari Supplier', $stats['supplier_qty'], 'text-violet-600 bg-violet-50', 'fa-truck'],
    ];
@endphp

<div class="mb-6 flex items-end justify-between print:hidden">
    <div>
        <h3 class="text-5xl font-bold">Persediaan</h3>
        <p class="text-sm text-gray-500 mt-1">Laporan pergerakan stok: stok masuk, stok keluar, transfer antar gudang, dan barang dari supplier.</p>
    </div>
    <button type="button" onclick="window.print()"
        class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
        <i class="fa-solid fa-print mr-1.5"></i>Cetak Laporan
    </button>
</div>

<!-- Filter periode -->
<form method="GET" class="bg-white rounded-2xl border border-gray-200 p-5 mb-4 flex flex-wrap items-end gap-4 print:hidden">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Dari tanggal</label>
        <input type="date" name="from" value="{{ request('from') }}"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Sampai tanggal</label>
        <input type="date" name="to" value="{{ request('to') }}"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Gudang</label>
        <select name="location_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm min-w-[180px]">
            <option value="">Semua gudang</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>
    <button class="text-sm font-medium px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 transition"><i class="fa-solid fa-filter mr-1.5"></i>Terapkan</button>
    <a href="{{ url()->current() }}" class="text-sm text-gray-500 hover:underline py-2"><i class="fa-solid fa-rotate-left mr-1"></i>Reset</a>
</form>

<!-- Kartu ringkasan = juga tombol tab -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6 print:hidden">
    @foreach ($tabs as [$key, $label, $value, $color, $icon])
        <button type="button" data-tab-btn="{{ $key }}"
            class="tab-card text-left bg-white rounded-2xl border p-5 transition hover:border-gray-400 {{ $loop->first ? 'border-gray-900' : 'border-gray-200' }}">
            <span class="inline-block text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $color }}"><i class="fa-solid {{ $icon }} mr-1"></i>{{ $label }}</span>
            <p class="text-3xl font-bold mt-3">{{ $n($value) }}</p>
            <p class="text-xs text-gray-400 mt-1">total qty pada periode</p>
        </button>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <!-- Panel: Stok Masuk -->
    <div data-tab-panel="stock-in">
        <h4 class="font-semibold mb-4"><i class="fa-solid fa-download mr-2 text-gray-400"></i>Laporan Stok Masuk</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-left">
                    <tr>
                        <th class="py-2 font-medium">Tanggal</th>
                        <th class="py-2 font-medium">Produk</th>
                        <th class="py-2 font-medium">Gudang</th>
                        <th class="py-2 font-medium text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockIns as $r)
                        <tr class="border-t border-gray-100">
                            <td class="py-2.5 text-gray-700">{{ $d($r->date) }}</td>
                            <td class="py-2.5 font-medium">{{ $r->product->title ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->location->name ?? '–' }}</td>
                            <td class="py-2.5 text-right text-emerald-600 font-semibold">+{{ $n($r->qty) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Tidak ada stok masuk pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($stockIns->count())
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold">
                            <td colspan="3" class="py-2.5">Total</td>
                            <td class="py-2.5 text-right">{{ $n($stats['stock_in_qty']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Panel: Stok Keluar -->
    <div data-tab-panel="stock-out" class="hidden print:block print:mt-8">
        <h4 class="font-semibold mb-4"><i class="fa-solid fa-upload mr-2 text-gray-400"></i>Laporan Stok Keluar</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-left">
                    <tr>
                        <th class="py-2 font-medium">Tanggal</th>
                        <th class="py-2 font-medium">Produk</th>
                        <th class="py-2 font-medium">Gudang</th>
                        <th class="py-2 font-medium">Keterangan</th>
                        <th class="py-2 font-medium text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockOuts as $r)
                        <tr class="border-t border-gray-100">
                            <td class="py-2.5 text-gray-700">{{ $d($r->date) }}</td>
                            <td class="py-2.5 font-medium">{{ $r->product->title ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->location->name ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->note ?: '–' }}</td>
                            <td class="py-2.5 text-right text-orange-600 font-semibold">-{{ $n($r->qty) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Tidak ada stok keluar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($stockOuts->count())
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold">
                            <td colspan="4" class="py-2.5">Total</td>
                            <td class="py-2.5 text-right">{{ $n($stats['stock_out_qty']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Panel: Transfer Antar Gudang -->
    <div data-tab-panel="transfer" class="hidden print:block print:mt-8">
        <h4 class="font-semibold mb-4"><i class="fa-solid fa-right-left mr-2 text-gray-400"></i>Laporan Transfer Antar Gudang</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-left">
                    <tr>
                        <th class="py-2 font-medium">Tanggal</th>
                        <th class="py-2 font-medium">Produk</th>
                        <th class="py-2 font-medium">Gudang Asal <i class="fa-solid fa-arrow-right text-xs mx-1"></i> Tujuan</th>
                        <th class="py-2 font-medium">Status</th>
                        <th class="py-2 font-medium text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $r)
                        <tr class="border-t border-gray-100">
                            <td class="py-2.5 text-gray-700">{{ $d($r->date) }}</td>
                            <td class="py-2.5 font-medium">{{ $r->product->title ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->fromWarehouse->name ?? '–' }} <i class="fa-solid fa-arrow-right text-xs mx-1"></i> {{ $r->toWarehouse->name ?? '–' }}</td>
                            <td class="py-2.5">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColor($r->status) }}">{{ ucfirst($r->status ?? '–') }}</span>
                            </td>
                            <td class="py-2.5 text-right font-semibold">{{ $n($r->qty) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Tidak ada transfer pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($transfers->count())
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold">
                            <td colspan="4" class="py-2.5">Total</td>
                            <td class="py-2.5 text-right">{{ $n($stats['transfer_qty']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Panel: Barang dari Supplier -->
    <div data-tab-panel="supplier" class="hidden print:block print:mt-8">
        <h4 class="font-semibold mb-4"><i class="fa-solid fa-truck mr-2 text-gray-400"></i>Laporan Barang Masuk dari Supplier</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-left">
                    <tr>
                        <th class="py-2 font-medium">Tanggal</th>
                        <th class="py-2 font-medium">Supplier</th>
                        <th class="py-2 font-medium">Produk</th>
                        <th class="py-2 font-medium">Gudang</th>
                        <th class="py-2 font-medium text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplierReceipts as $r)
                        <tr class="border-t border-gray-100">
                            <td class="py-2.5 text-gray-700">{{ $d($r->date) }}</td>
                            <td class="py-2.5 font-medium">{{ $r->supplier->name ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->product->title ?? '–' }}</td>
                            <td class="py-2.5 text-gray-700">{{ $r->location->name ?? '–' }}</td>
                            <td class="py-2.5 text-right text-violet-600 font-semibold">+{{ $n($r->qty) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-gray-500"><i class="fa-solid fa-inbox mr-1.5"></i>Tidak ada barang dari supplier pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if ($supplierReceipts->count())
                    <tfoot>
                        <tr class="border-t border-gray-200 font-semibold">
                            <td colspan="4" class="py-2.5">Total</td>
                            <td class="py-2.5 text-right">{{ $n($stats['supplier_qty']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-card').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tabBtn;
            document.querySelectorAll('.tab-card').forEach(b => {
                b.classList.toggle('border-gray-900', b === btn);
                b.classList.toggle('border-gray-200', b !== btn);
            });
            document.querySelectorAll('[data-tab-panel]').forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.tabPanel !== target);
            });
        });
    });
</script>
@endsection