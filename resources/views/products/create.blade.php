@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h3 class="text-5xl font-bold">Tambah Produk</h3>
        <p class="text-sm text-gray-500 mt-1">Isi data produk baru.</p>
    </div>
    <a href="{{ route('products.index') }}"
       class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200">
    <div class="p-6">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama produk</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Masukkan nama produk"
                       class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                @error('title')
                    <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">SKU</label>
                    <input type="text" value="{{ $nextSku }}" readonly
                           class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm font-mono bg-gray-50 text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Dibuat otomatis, tidak bisa diubah.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <select name="category"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @if ($categories->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Belum ada kategori. <a href="{{ route('categories.index') }}" class="underline">Tambahkan dulu di menu Kategori</a>.
                        </p>
                    @endif
                    @error('category')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                        @foreach (\App\Models\Product::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price') }}" placeholder="Contoh: 150000"
                       class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                <p class="mt-1 text-xs text-gray-500">Stok awal diisi lewat menu Stok Masuk setelah produk disimpan.</p>
                @error('price')
                    <div class="mt-2 text-sm text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan produk
                </button>
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection