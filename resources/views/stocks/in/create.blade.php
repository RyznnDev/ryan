@extends('layouts.app')

@section('title', 'Tambah Stok Masuk')

@section('content')
@php
    // peta stok: [product_id => [location_id => stok]]
    $stockMap = $products->mapWithKeys(fn ($p) => [$p->id => $p->locations->pluck('pivot.stock', 'id')]);
@endphp

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h3 class="text-5xl font-bold">Tambah Stok Masuk</h3>
        <p class="text-sm text-gray-500 mt-1">Catat barang yang masuk dan pilih gudang tujuannya.</p>
    </div>
    <a href="{{ route('stock-in.index') }}"
       class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200">
    <div class="p-6">
        <form action="{{ route('stock-in.store') }}" method="POST" class="space-y-5">
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

                {{-- Lokasi tujuan --}}
                <div>
                    <label for="location_id" class="block text-sm font-semibold text-gray-700 mb-2">Masuk ke gudang</label>
                    <select id="location_id" name="location_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="">Pilih gudang tujuan</option>
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

            {{-- Info stok di gudang terpilih --}}
            <div id="stock-info" class="hidden rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Supplier --}}
                <div>
                    <label for="supplier_id" class="block text-sm font-semibold text-gray-700 mb-2">Supplier</label>
                    <select id="supplier_id" name="supplier_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="">Tanpa supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal masuk</label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('date')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Qty --}}
                <div>
                    <label for="qty" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                    <input id="qty" type="number" min="1" name="qty" value="{{ old('qty') }}" placeholder="Contoh: 50"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('qty')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="3" placeholder="Contoh: pengiriman dari PO-0012"
                          class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">{{ old('note') }}</textarea>
                @error('note')
                    <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan stok masuk
                </button>
                <a href="{{ route('stock-in.index') }}"
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
    const icon = cls => { const i = document.createElement('i'); i.className = 'fa-solid ' + cls + ' mr-1.5'; return i; };

    function updateInfo() {
        const p = productEl.value, l = locationEl.value;
        if (!p || !l) { infoEl.classList.add('hidden'); return; }

        const current = Number((stockMap[p] || {})[l] || 0);
        const add = Number(qtyEl.value || 0);
        const locName = locationEl.options[locationEl.selectedIndex].text.trim();

        infoEl.textContent = 'Stok saat ini di ' + locName + ': ' + current +
            (add > 0 ? ' → setelah ditambah: ' + (current + add) : '');
        infoEl.prepend(icon('fa-circle-info'));
        infoEl.classList.remove('hidden');
    }

    [productEl, locationEl].forEach(el => el.addEventListener('change', updateInfo));
    qtyEl.addEventListener('input', updateInfo);
    updateInfo();
</script>
@endsection