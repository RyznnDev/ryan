@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="mb-6">
    <h3 class="text-5xl font-bold">Produk</h3>
    <p class="text-sm text-gray-500 mt-1">Kelola data produk, stok, dan harga.</p>
</div>

<div class="bg-white rounded-2xl border border-gray-200">
        <div class="p-6">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-gray-900"><i class="fa-solid fa-boxes-stacked mr-2 text-gray-400"></i>Data Product</h4>
                <a href="{{ route('products.create') }}"
                   class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus mr-1.5"></i>TAMBAH PRODUCT
                </a>
            </div>

            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- Filter --}}
            <form action="{{ route('products.index') }}" method="GET"
                  class="flex flex-col lg:flex-row gap-3 mb-6">

                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari SKU / nama produk..."
                           class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                </div>

                <select name="category"
                        class="border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>

                <select name="status"
                        class="border border-gray-200 rounded-lg px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <option value="">Semua Status Produk</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-filter mr-1.5"></i>TERAPKAN
                    </button>
                    <a href="{{ route('products.index') }}"
                       class="px-4 py-2 rounded-lg bg-gray-500 text-white text-sm font-semibold hover:bg-gray-600 transition">
                        <i class="fa-solid fa-rotate-left mr-1.5"></i>RESET
                    </a>
                </div>
            </form>

            {{-- Tabel --}}
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">SKU</th>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-700">{{ $product->sku }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $product->id) }}"
                                       class="flex items-center gap-3 group">
                                        <span class="font-semibold text-gray-900 group-hover:text-blue-600">
                                            {{ $product->title }}
                                        </span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $product->category }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold {{ $product->stock <= 5 ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    {{ 'Rp ' . number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($product->status === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i>Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700"><i class="fa-solid fa-circle-xmark mr-1"></i>Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Edit --}}
                                        <a href="{{ route('products.edit', $product->id) }}" title="Edit"
                                           class="p-2 rounded-lg bg-amber-500 text-white hover:bg-amber-600 transition">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        {{-- Hapus --}}
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                              class="form-delete" data-title="{{ $product->title }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="p-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-3xl text-gray-300 block mb-3"></i>
                                    Data produk tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination (5 data per halaman) --}}
            <div class="mt-6">
                {{ $products->links('vendor.pagination.blue') }}
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Konfirmasi sebelum menghapus
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus produk?',
                text: '"' + this.dataset.title + '" akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) this.submit();
            });
        });
    });
</script>
@endsection