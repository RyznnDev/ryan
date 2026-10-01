@extends('layouts.app')

@section('title', 'Tambah Stok Keluar')

@section('content')
@php
    // peta stok: [product_id => [location_id => stok]]
    $stockMap = $products->mapWithKeys(fn ($p) => [$p->id => $p->locations->pluck('pivot.stock', 'id')]);
@endphp

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h3 class="text-5xl font-bold">Tambah Stok Keluar</h3>
        <p class="text-sm text-gray-500 mt-1">Catat barang yang keluar dan pilih gudang asalnya.</p>
    </div>
    <a href="{{ route('stock-out.index') }}"
       class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200">
    <div class="p-6">
        <form action="{{ route('stock-out.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Produk --}}
                <div>
                    <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">Produk</label>
                    <select id="product_id" name="product_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->sku }} - {{ $product->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Lokasi asal --}}
                <div>
                    <label for="location_id" class="block text-sm font-semibold text-gray-700 mb-2">Keluar dari gudang</label>
                    <select id="location_id" name="location_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="">Pilih gudang asal</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>
                                {{ $location->name }}@if ($location->code) ({{ $location->code }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Info stok tersedia --}}
            <div id="stock-info" class="hidden rounded-lg border text-sm px-4 py-3"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Tanggal --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal keluar</label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('date')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Qty --}}
                <div>
                    <label for="qty" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                    <input id="qty" type="number" min="1" name="qty" value="{{ old('qty') }}" placeholder="Contoh: 10"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('qty')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="3" placeholder="Contoh: penjualan ke toko cabang"
                          class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">{{ old('note') }}</textarea>
                @error('note')
                    <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <button id="submit-btn" type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan stok keluar
                </button>
                <a href="{{ route('stock-out.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const stockMap = @json($stockMap);
    const productEl  = document.getElementById('product_id');
    const locationEl = document.getElementById('location_id');
    const qtyEl      = document.getElementById('qty');
    const infoEl     = document.getElementById('stock-info');
    const submitEl   = document.getElementById('submit-btn');
    const icon = cls => { const i = document.createElement('i'); i.className = 'fa-solid ' + cls + ' mr-1.5'; return i; };

    function updateInfo() {
        const p = productEl.value, l = locationEl.value;
        if (!p || !l) {
            infoEl.classList.add('hidden');
            qtyEl.removeAttribute('max');
            submitEl.disabled = false;
            return;
        }

        const available = Number((stockMap[p] || {})[l] || 0);
        const out = Number(qtyEl.value || 0);
        const locName = locationEl.options[locationEl.selectedIndex].text.trim();
        const tooMany = out > available;

        infoEl.className = 'rounded-lg border text-sm px-4 py-3 ' +
            (tooMany || available === 0
                ? 'bg-red-50 border-red-200 text-red-700'
                : 'bg-amber-50 border-amber-200 text-amber-800');

        infoEl.textContent = available === 0
            ? 'Stok produk ini di ' + locName + ' kosong.'
            : 'Stok tersedia di ' + locName + ': ' + available +
              (tooMany ? ' — jumlah melebihi stok.' : (out > 0 ? ' → sisa setelah keluar: ' + (available - out) : ''));

        infoEl.prepend(icon(tooMany || available === 0 ? 'fa-triangle-exclamation' : 'fa-circle-info'));

        qtyEl.max = available;
        submitEl.disabled = tooMany || available === 0;
    }

    [productEl, locationEl].forEach(el => el.addEventListener('change', updateInfo));
    qtyEl.addEventListener('input', updateInfo);
    updateInfo();
</script>
@endsection