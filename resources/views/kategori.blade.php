@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $url = fn ($name, $p = []) => Route::has($name) ? route($name, $p) : '#';
    $palette = [
        ['bg-blue-50 text-blue-600',       'bg-blue-500'],
        ['bg-emerald-50 text-emerald-600', 'bg-emerald-500'],
        ['bg-orange-50 text-orange-600',   'bg-orange-500'],
        ['bg-violet-50 text-violet-600',   'bg-violet-500'],
        ['bg-rose-50 text-rose-600',       'bg-rose-500'],
        ['bg-amber-50 text-amber-600',     'bg-amber-500'],
    ];
    $maxStock = max(1, $categories->max('products_sum_stock') ?? 1);
@endphp

<div class="mb-6 flex items-end justify-between">
    <div>
        <h3 class="text-5xl font-bold">Kategori</h3>
        <p class="text-sm text-gray-500 mt-1">Kelompokkan produk agar lebih mudah dikelola.</p>
    </div>
    <button type="button" id="btn-add"
        class="text-sm font-medium px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 transition">
        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Kategori
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
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Kategori</p><i class="fa-solid fa-tags text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Produk Terkategori</p><i class="fa-solid fa-box text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['products']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Kategori Kosong</p><i class="fa-solid fa-folder-open text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['empty']) }}</p>
    </div>
</div>



<!-- Grid kategori -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @forelse ($categories as $c)
        @php [$soft, $bar] = $palette[$loop->index % count($palette)]; @endphp
        <div data-item="{{ strtolower($c->name) }}" class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col">
            <div class="flex items-start justify-between">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $soft }}">
                    <i class="fa-solid fa-tag"></i>
                </span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                    <i class="fa-solid fa-box mr-1"></i>{{ $n($c->products_count) }} produk
                </span>
            </div>
            <h4 class="font-semibold mt-3">{{ $c->name }}</h4>
            <p class="text-sm text-gray-500 mt-1 line-clamp-2 min-h-[2.5rem]">{{ $c->description ?: 'Tanpa deskripsi.' }}</p>

            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span><i class="fa-solid fa-cubes mr-1"></i>Total stok</span>
                    <span class="font-medium text-gray-700">{{ $n($c->products_sum_stock ?? 0) }}</span>
                </div>
                <div class="h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ round(($c->products_sum_stock ?? 0) / $maxStock * 100) }}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100 text-sm">
                <button type="button" class="btn-edit text-blue-600 hover:underline"
                    data-url="{{ $url('categories.update', $c) }}"
                    data-name="{{ $c->name }}" data-description="{{ $c->description }}"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</button>
                <form method="POST" action="{{ $url('categories.destroy', $c) }}"
                    onsubmit="return confirm('Hapus kategori {{ addslashes($c->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="text-red-600 hover:underline"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
            <i class="fa-solid fa-tags text-3xl text-gray-300 block mb-3"></i>
            Belum ada kategori. Klik "Tambah Kategori" untuk membuat yang pertama.
        </div>
    @endforelse
</div>

<!-- Modal tambah / edit -->
<div id="modal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <form id="modal-form" method="POST" action="{{ $url('categories.store') }}"
        class="bg-white rounded-2xl w-full max-w-md p-6">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST" disabled>
        <h4 class="font-semibold text-lg mb-4" id="modal-title">Tambah Kategori</h4>

        <label class="block text-sm text-gray-600 mb-1">Nama</label>
        <input name="name" id="f-name" required maxlength="100"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4">

        <label class="block text-sm text-gray-600 mb-1">Deskripsi</label>
        <textarea name="description" id="f-description" rows="3"
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

    function openModal(title, action, isEdit, name = '', description = '') {
        document.getElementById('modal-title').textContent = title;
        form.action = action;
        method.value = 'PUT';
        method.disabled = !isEdit;
        document.getElementById('f-name').value = name;
        document.getElementById('f-description').value = description;
        modal.classList.remove('hidden');
    }
    document.getElementById('btn-add').onclick = () => openModal('Tambah Kategori', storeUrl, false);
    document.querySelectorAll('.btn-edit').forEach(b => b.onclick = () =>
        openModal('Edit Kategori', b.dataset.url, true, b.dataset.name, b.dataset.description));
    document.getElementById('btn-cancel').onclick = () => modal.classList.add('hidden');
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    
</script>
@endsection