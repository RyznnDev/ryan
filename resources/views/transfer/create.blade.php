@extends('layouts.app')

@section('title', 'Transfer Antar Gudang')

@section('content')
@php
    // peta stok: [product_id => [location_id => stok]]
    $stockMap = $products->mapWithKeys(fn ($p) => [$p->id => $p->locations->pluck('pivot.stock', 'id')]);
    $locList  = $locations->map(fn ($l) => ['id' => $l->id, 'name' => $l->name . ($l->code ? " ({$l->code})" : '')])->values();
@endphp

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h3 class="text-5xl font-bold">Transfer Antar Gudang</h3>
        <p class="text-sm text-gray-500 mt-1">Pindahkan stok produk dari satu gudang ke gudang lain.</p>
    </div>
    <a href="{{ route('transfers.index') }}"
       class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
    </a>
</div>

@if ($locations->count() < 2)
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
        <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Transfer butuh minimal dua gudang. Tambahkan gudang dulu di menu
        <a href="{{ route('locations.index') }}" class="underline font-semibold">Lokasi</a>.
    </div>
@endif

<div class="bg-white rounded-2xl border border-gray-200">
    <div class="p-6">
        <form action="{{ route('transfers.store') }}" method="POST" class="space-y-5">
            @csrf

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

            {{-- Gudang asal & tujuan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="from_warehouse_id" class="block text-sm font-semibold text-gray-700 mb-2">Dari gudang</label>
                    <select id="from_warehouse_id" name="from_warehouse_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200"></select>
                    @error('from_warehouse_id')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="to_warehouse_id" class="block text-sm font-semibold text-gray-700 mb-2">Ke gudang</label>
                    <select id="to_warehouse_id" name="to_warehouse_id"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200"></select>
                    @error('to_warehouse_id')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Info stok --}}
            <div id="stock-info" class="hidden rounded-lg border text-sm px-4 py-3"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal transfer</label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('date')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="qty" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                    <input id="qty" type="number" min="1" name="qty" value="{{ old('qty') }}" placeholder="Contoh: 10"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                    @error('qty')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="submit-btn" type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-right-left mr-1.5"></i>Transfer stok
                </button>
                <a href="{{ route('transfers.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const stockMap  = @json($stockMap);
    const locations = @json($locList);
    const oldFrom   = @json(old('from_warehouse_id'));
    const oldTo     = @json(old('to_warehouse_id'));

    const productEl = document.getElementById('product_id');
    const fromEl    = document.getElementById('from_warehouse_id');
    const toEl      = document.getElementById('to_warehouse_id');
    const qtyEl     = document.getElementById('qty');
    const infoEl    = document.getElementById('stock-info');
    const submitEl  = document.getElementById('submit-btn');
    const icon = cls => { const i = document.createElement('i'); i.className = 'fa-solid ' + cls + ' mr-1.5'; return i; };

    function setOptions(select, placeholder, items, selected) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        items.forEach(i => select.add(new Option(i.label, i.id)));
        if (selected && items.some(i => String(i.id) === String(selected))) select.value = selected;
    }

    // Gudang asal: hanya gudang yang punya stok produk terpilih
    function renderFrom(selected) {
        const p = productEl.value;
        if (!p) { setOptions(fromEl, 'Pilih produk dulu', [], null); return; }

        const stocks = stockMap[p] || {};
        const items = locations
            .filter(l => Number(stocks[l.id] || 0) > 0)
            .map(l => ({ id: l.id, label: l.name + ' — stok ' + stocks[l.id] }));

        setOptions(fromEl, items.length ? 'Pilih gudang asal' : 'Produk ini belum punya stok di gudang mana pun', items, selected);
    }

    // Gudang tujuan: semua gudang kecuali yang dipilih sebagai asal
    function renderTo(selected) {
        const items = locations
            .filter(l => String(l.id) !== String(fromEl.value))
            .map(l => ({ id: l.id, label: l.name }));
        setOptions(toEl, 'Pilih gudang tujuan', items, selected);
    }

    function updateInfo() {
        const p = productEl.value, from = fromEl.value;
        qtyEl.removeAttribute('max');
        submitEl.disabled = false;

        if (!p || !from) { infoEl.classList.add('hidden'); return; }

        const available = Number((stockMap[p] || {})[from] || 0);
        const qty = Number(qtyEl.value || 0);
        const tooMany = qty > available;

        infoEl.className = 'rounded-lg border text-sm px-4 py-3 ' +
            (tooMany ? 'bg-red-50 border-red-200 text-red-700' : 'bg-blue-50 border-blue-200 text-blue-800');
        infoEl.textContent = tooMany
            ? 'Jumlah melebihi stok di gudang asal (tersedia ' + available + ').'
            : 'Stok tersedia di gudang asal: ' + available + (qty > 0 ? ' → sisa setelah transfer: ' + (available - qty) : '');
        infoEl.prepend(icon(tooMany ? 'fa-triangle-exclamation' : 'fa-circle-info'));

        qtyEl.max = available;
        submitEl.disabled = tooMany;
    }

    productEl.addEventListener('change', () => { renderFrom(null); renderTo(null); updateInfo(); });
    fromEl.addEventListener('change',   () => { renderTo(toEl.value); updateInfo(); });
    qtyEl.addEventListener('input', updateInfo);

    renderFrom(oldFrom);
    renderTo(oldTo);
    updateInfo();
</script>
@endsection