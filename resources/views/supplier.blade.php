@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $n = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $d = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '–';
    $url = fn ($name, $p = []) => Route::has($name) ? route($name, $p) : '#';
@endphp

<div class="mb-6 flex items-end justify-between">
    <div>
        <h3 class="text-5xl font-bold">Supplier</h3>
        <p class="text-sm text-gray-500 mt-1">Daftar pemasok barang beserta kontaknya.</p>
    </div>
    <button type="button" id="btn-add"
        class="text-sm font-medium px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 transition">
        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Supplier
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
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Supplier</p><i class="fa-solid fa-truck text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Total Barang Diterima</p><i class="fa-solid fa-box-open text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['total_qty']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between"><p class="text-sm text-gray-500">Kirim Bulan Ini</p><i class="fa-solid fa-calendar-check text-gray-400"></i></div>
        <p class="text-3xl font-bold mt-2">{{ $n($stats['this_month']) }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <h4 class="font-semibold"><i class="fa-solid fa-list mr-2 text-gray-400"></i>Daftar Supplier</h4>
        <div class="relative w-full sm:w-64"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i><input type="search" id="search" placeholder="Cari nama / kontak…" class="w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm"></div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 text-left">
                <tr>
                    <th class="py-2 font-medium">Supplier</th>
                    <th class="py-2 font-medium">Kontak</th>
                    <th class="py-2 font-medium">Alamat</th>
                    <th class="py-2 font-medium text-right">Barang Diterima</th>
                    <th class="py-2 font-medium">Terakhir Kirim</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $s)
                    <tr class="border-t border-gray-100"
                        data-item="{{ strtolower($s->name.' '.$s->contact_person.' '.$s->phone.' '.$s->email) }}">
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-violet-50 text-violet-600 text-sm">
                                    <i class="fa-solid fa-truck-field"></i>
                                </span>
                                <span class="font-medium">{{ $s->name }}</span>
                            </div>
                        </td>
                        <td class="py-3 text-gray-700">
                            <p><i class="fa-solid fa-user mr-1.5 text-gray-400"></i>{{ $s->contact_person ?: '–' }}</p>
                            <p class="text-xs text-gray-400">
                                @if ($s->phone)<span><i class="fa-solid fa-phone mr-1"></i>{{ $s->phone }}</span>@endif
                                @if ($s->phone && $s->email)<span class="mx-1">·</span>@endif
                                @if ($s->email)<span><i class="fa-solid fa-envelope mr-1"></i>{{ $s->email }}</span>@endif
                            </p>
                        </td>
                        <td class="py-3 text-gray-700 max-w-[220px] truncate"><i class="fa-solid fa-location-dot mr-1.5 text-gray-400"></i>{{ $s->address ?: '–' }}</td>
                        <td class="py-3 text-right">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-violet-600">
                                {{ $n($s->receipts_sum_qty ?? 0) }}
                            </span>
                        </td>
                        <td class="py-3 text-gray-700">{{ $d($s->last_receipt_date ?? null) }}</td>
                        <td class="py-3 text-right whitespace-nowrap">
                            <button type="button" class="btn-edit text-blue-600 hover:underline mr-3"
                                data-url="{{ $url('suppliers.update', $s) }}"
                                data-name="{{ $s->name }}"
                                data-contact_person="{{ $s->contact_person }}"
                                data-phone="{{ $s->phone }}"
                                data-email="{{ $s->email }}"
                                data-address="{{ $s->address }}"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</button>
                            <form method="POST" action="{{ $url('suppliers.destroy', $s) }}" class="inline"
                                onsubmit="return confirm('Hapus supplier {{ addslashes($s->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-gray-500">Belum ada supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($suppliers, 'links'))
        <div class="mt-4">{{ $suppliers->links() }}</div>
    @endif
</div>

<!-- Modal tambah / edit -->
<div id="modal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <form id="modal-form" method="POST" action="{{ $url('suppliers.store') }}"
        class="bg-white rounded-2xl w-full max-w-lg p-6">
        @csrf
        <input type="hidden" name="_method" id="method" value="PUT" disabled>
        <h4 class="font-semibold text-lg mb-4" id="modal-title">Tambah Supplier</h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Nama supplier</label>
                <input name="name" id="f-name" required maxlength="150" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama kontak</label>
                <input name="contact_person" id="f-contact_person" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Telepon</label>
                <input name="phone" id="f-phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" name="email" id="f-email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Alamat / asal</label>
                <textarea name="address" id="f-address" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
        </div>

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
    const fields = ['name', 'contact_person', 'phone', 'email', 'address'];

    function openModal(title, action, isEdit, data = {}) {
        document.getElementById('modal-title').textContent = title;
        form.action = action;
        method.disabled = !isEdit;
        fields.forEach(f => document.getElementById('f-' + f).value = data[f] || '');
        modal.classList.remove('hidden');
    }
    document.getElementById('btn-add').onclick = () => openModal('Tambah Supplier', storeUrl, false);
    document.querySelectorAll('.btn-edit').forEach(b => b.onclick = () =>
        openModal('Edit Supplier', b.dataset.url, true, b.dataset));
    document.getElementById('btn-cancel').onclick = () => modal.classList.add('hidden');
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    document.getElementById('search').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('[data-item]').forEach(el =>
            el.classList.toggle('hidden', !el.dataset.item.includes(q)));
    });
</script>
@endsection