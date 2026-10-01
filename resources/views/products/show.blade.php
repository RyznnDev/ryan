@extends('layouts.app')

@section('title', $product->title)

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h3 class="text-5xl font-bold">Detail Produk</h3>
        <p class="text-sm text-gray-500 mt-1">Informasi lengkap produk dan stoknya.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('products.edit', $product->id) }}"
           class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition">
            <i class="fa-solid fa-pen-to-square mr-1.5"></i>Edit
        </a>
        <a href="{{ route('products.index') }}"
           class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
        </a>
    </div>
</div>

<div>
    {{-- Info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h4 class="text-2xl font-bold text-gray-900">{{ $product->title }}</h4>
            <p class="mt-1 text-xl font-semibold text-gray-700">
                {{ 'Rp ' . number_format($product->price, 0, ',', '.') }}
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 font-mono"><i class="fa-solid fa-barcode mr-1"></i>SKU: {{ $product->sku }}</span>
                <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold"><i class="fa-solid fa-tag mr-1"></i>{{ $product->category }}</span>
                @if ($product->status === 'active')
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>Aktif</span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-semibold"><i class="fa-solid fa-circle-xmark mr-1"></i>Nonaktif</span>
                @endif
            </div>

            <hr class="my-5 border-gray-200">

            <p class="text-sm text-gray-700">
                <i class="fa-solid fa-cubes mr-1.5 text-gray-400"></i>Total stok:
                <span class="font-semibold {{ $product->stock <= 5 ? 'text-red-600' : 'text-gray-900' }}">{{ $product->stock }}</span>
            </p>
        </div>

        {{-- Stok per gudang --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4"><i class="fa-solid fa-warehouse mr-2 text-gray-400"></i>Stok per gudang</h4>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Gudang</th>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($product->locations as $location)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $location->name }}</td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $location->code ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $location->pivot->stock <= 5 ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ $location->pivot->stock }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-3xl text-gray-300 block mb-3"></i>
                                    Belum ada stok di gudang mana pun. Catat lewat menu Stok Masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection