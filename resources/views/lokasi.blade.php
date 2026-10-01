@extends('layouts.app')

@section('title', 'Lokasi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $url = fn ($name, $p = []) => Route::has($name) ? route($name, $p) : '#';
    $totalStock = max(1, $stats['total_stock'] ?? 1);
@endphp

<div class="mb-6 flex items-end justify-between">
    <div>
        <h3 class="text-5xl font-bold">Lokasi</h3>
        <p class="text-sm text-gray-500 mt-1">Daftar gudang tempat stok disimpan.</p>
    </div>
    <button type="button" id="btn-add"
        class="text-sm font-medium px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 transition">
        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Gudang
    </button>
</div>

@if (session('success'))
    <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3"><i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $errors->first() }}</div>
@endif

<!-- Kartu ringkasan -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Gudang</p><i class="fa-solid fa-warehouse text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Jenis Produk Tersimpan</p><i class="fa-solid fa-boxes-stacked text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total_products']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Stok</p><i class="fa-solid fa-cubes text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total_stock']) }}</p>
    </div>
</div>



<!-- Kartu gudang -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    @forelse ($locations as $l)
        @php $share = round(($l->stock_total ?? 0) / $totalStock * 100); @endphp
        <div data-item="{{ strtolower($l->name.' '.$l->code.' '.$l->address) }}"
            class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-location-dot text-lg"></i>
                    </span>
                    <div class="min-w-0">
                        <h4 class="font-semibold truncate">{{ $l->name }}</h4>
                        @if ($l->code)
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $l->code }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-sm whitespace-nowrap">
                    <button type="button" class="btn-edit text-blue-600 hover:underline mr-3"
                        data-url="{{ $url('locations.update', $l) }}"
                        data-name="{{ $l->name }}" data-code="{{ $l->code }}" data-address="{{ $l->address }}"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</button>
                    <form method="POST" action="{{ $url('locations.destroy', $l) }}" class="inline"
                        onsubmit="return confirm('Hapus gudang {{ addslashes($l->name) }}?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                    </form>
                </div>
            </div>

            <p class="text-sm text-gray-500 mt-3 min-h-[1.25rem]">{{ $l->address ?: 'Alamat belum diisi.' }}</p>

            <div class="grid grid-cols-2 divide-x divide-gray-100 mt-4">
                <div class="pr-4">
                    <p class="text-xs text-gray-500">Jenis produk</p>
                    <p class="text-2xl font-bold mt-1">{{ $n($l->products_count ?? 0) }}</p>
                </div>
                <div class="pl-4">
                    <p class="text-xs text-gray-500">Total stok</p>
                    <p class="text-2xl font-bold mt-1">{{ $n($l->stock_total ?? 0) }}</p>
                </div>
            </div>

            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Porsi dari seluruh stok</span>
                    <span class="font-medium text-gray-700">{{ $share }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-500" style="width: {{ $share }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
            <i class="fa-solid fa-warehouse text-3xl text-gray-300 block mb-3"></i>
            Belum ada gudang. Klik "Tambah Gudang" untuk membuat yang pertama.
        </div>
    @endforelse
</div>

<!-- Modal tambah / edit -->
<div id="modal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <form id="modal-form" method="POST" action="{{ $url('locations.store') }}"
        class="bg-white rounded-2xl w-full max-w-md p-6">
        @csrf
        <input type="hidden" name="_method" id="method" value="PUT" disabled>
        <h4 class="font-semibold text-lg mb-4" id="modal-title">Tambah Gudang</h4>

        <label class="block text-sm text-gray-600 mb-1">Nama gudang</label>
        <input name="name" id="f-name" required maxlength="100"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4">

        <label class="block text-sm text-gray-600 mb-1">Kode (opsional)</label>
        <input name="code" id="f-code" maxlength="20"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4">

        <label class="block text-sm text-gray-600 mb-1">Alamat</label>
        <textarea name="address" id="f-address" rows="3"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-5"></textarea>

        <div class="flex justify-end gap-2">
            <button type="button" id="btn-cancel" class="text-sm px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50"><i class="fa-solid fa-xmark mr-1.5"></i>Batal</button>
            <button class="text-sm font-medium px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700"><i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan</button>
        </div>
    </form>
</div>

<script>
    const modal = document.getElementById('modal');
    const form = document.getElementById('modal-form');
    const method = document.getElementById('method');
    const storeUrl = form.action;
    const fields = ['name', 'code', 'address'];

    function openModal(title, action, isEdit, data = {}) {
        document.getElementById('modal-title').textContent = title;
        form.action = action;
        method.disabled = !isEdit;
        fields.forEach(f => document.getElementById('f-' + f).value = data[f] || '');
        modal.classList.remove('hidden');
    }
    document.getElementById('btn-add').onclick = () => openModal('Tambah Gudang', storeUrl, false);
    document.querySelectorAll('.btn-edit').forEach(b => b.onclick = () =>
        openModal('Edit Gudang', b.dataset.url, true, b.dataset));
    document.getElementById('btn-cancel').onclick = () => modal.classList.add('hidden');
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    
</script>
@endsection