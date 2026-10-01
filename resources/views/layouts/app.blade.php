<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Data Products') - SantriKoding.com</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>

        body { -webkit-user-select: none; user-select: none; }
        input, textarea, select { -webkit-user-select: text; user-select: text; }
        /* Panah dropdown yang berputar saat daftar opsi terbuka */
        select.select-anim { -webkit-appearance: none; appearance: none; padding-right: 2.5rem; cursor: pointer; }
        .select-wrap { position: relative; }
        .select-caret {
            position: absolute; top: 0; bottom: 0; right: 0.9rem;
            display: flex; align-items: center;
            font-size: 0.7rem; color: #6b7280; pointer-events: none;
            transition: transform 0.25s ease, color 0.25s ease;
        }
        .select-wrap.is-open .select-caret { transform: rotate(180deg); color: #111827; }
        @media (prefers-reduced-motion: reduce) { .select-caret { transition: none; } }

        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

@php
    $user = auth()->user();
    $go = fn ($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';

    $icons = [
        'home'     => 'fa-house',
        'cube'     => 'fa-box',
        'transfer' => 'fa-right-left',
        'in'       => 'fa-download',
        'out'      => 'fa-upload',
        'report'   => 'fa-file-lines',
        'tag'      => 'fa-tag',
        'truck'    => 'fa-truck',
        'pin'      => 'fa-location-dot',
        'logout'   => 'fa-right-from-bracket',
    ];

    $menu = [
        'Utama' => [
            ['Dashboard',   'dashboard',       'dashboard',   'home'],
            ['Produk',      'products.index',  'products.*',  'cube'],
        ],
        'Transaksi' => [
            ['Transfer',    'transfers.index', 'transfers.*', 'transfer'],
            ['Stok Masuk',  'stock-in.index',  'stock-in.*',  'in'],
            ['Stok Keluar', 'stock-out.index', 'stock-out.*', 'out'],
        ],
        'Laporan' => [
            ['Persediaan',  'reports.inventory', 'reports.*', 'report'],
        ],
        'Administrasi' => [
            ['Kategori',    'categories.index','categories.*','tag'],
            ['Supplier',    'suppliers.index', 'suppliers.*', 'truck'],
            ['Lokasi',      'locations.index', 'locations.*', 'pin'],
        ],
    ];
@endphp

<body class="bg-gray-100 text-gray-900">

    <div id="overlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col
               -translate-x-full lg:translate-x-0 transition-transform duration-200">

        <div class="flex items-center gap-3 px-5 py-5">
            @if (!empty($user->photo))
                <img src="{{ asset('storage/'.$user->photo) }}" alt="{{ $user->name }}"
                    class="w-11 h-11 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-11 h-11 rounded-full bg-blue-500 text-white flex items-center justify-center font-semibold text-lg shrink-0">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ $user->role }}</p>
            </div>
        </div>

        <hr class="border-gray-200">

        <nav class="flex-1 overflow-y-auto no-scrollbar px-3 py-4 space-y-5">
            @foreach ($menu as $section => $items)
                <div>
                    <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $section }}</p>
                    <ul class="space-y-1">
                        @foreach ($items as [$label, $route, $pattern, $icon])
                            @php $active = request()->routeIs($pattern); @endphp
                            <li>
                                <a href="{{ $go($route) }}"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                                           {{ $active ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    <i class="fa-solid {{ $icons[$icon] }} w-5 text-center shrink-0"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <hr class="border-gray-200">

            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
                    <i class="fa-solid {{ $icons['logout'] }} w-5 text-center shrink-0"></i>
                    Log out
                </button>
            </form>
        </nav>
    </aside>

    <!-- KONTEN -->
    <div class="lg:pl-64 min-h-screen">
        <div class="lg:hidden flex items-center gap-3 bg-white border-b border-gray-200 px-4 py-3">
            <button id="open-sidebar" type="button" class="p-1.5 rounded-lg hover:bg-gray-100" aria-label="Buka menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <span class="font-semibold">Data Products</span>
        </div>

        <main class="p-4 sm:p-8">
            @yield('content')
        </main>
    </div>

    <script>

        // Bungkus semua <select> dengan panah yang berputar saat dibuka
        (function () {
            const layoutRe = /^((sm|md|lg|xl|2xl):)?(w-|min-w-|max-w-|flex-|grow|shrink|basis-|self-)/;

            document.querySelectorAll('select:not([multiple]):not([data-no-caret])').forEach(sel => {
                const wrap = document.createElement('div');
                const moved = [];
                sel.classList.forEach(c => { if (layoutRe.test(c)) moved.push(c); });
                moved.forEach(c => sel.classList.remove(c));
                wrap.className = 'select-wrap ' + moved.join(' ');
                sel.classList.add('select-anim', 'w-full');

                const caret = document.createElement('span');
                caret.className = 'select-caret';
                caret.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';

                sel.parentNode.insertBefore(wrap, sel);
                wrap.appendChild(sel);
                wrap.appendChild(caret);

                const open  = () => wrap.classList.add('is-open');
                const close = () => wrap.classList.remove('is-open');

                sel.addEventListener('mousedown', () => wrap.classList.toggle('is-open'));
                sel.addEventListener('change', close);
                sel.addEventListener('blur', close);
                sel.addEventListener('keydown', e => {
                    if (e.key === 'Escape') close();
                    else if (e.key === ' ' || e.key === 'Enter' || (e.altKey && e.key === 'ArrowDown')) open();
                });
            });

            // Klik di luar select menutup panah kembali
            document.addEventListener('mousedown', e => {
                document.querySelectorAll('.select-wrap.is-open').forEach(w => {
                    if (!w.contains(e.target)) w.classList.remove('is-open');
                });
            });
        })();

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleSidebar = (open) => {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
        };
        document.getElementById('open-sidebar').addEventListener('click', () => toggleSidebar(true));
        overlay.addEventListener('click', () => toggleSidebar(false));

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((r) => { if (r.isConfirmed) form.submit(); });
            });
        });

        document.getElementById('logout-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Keluar dari akun?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((r) => { if (r.isConfirmed) form.submit(); });
        });

        @if (session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
        @endif
    </script>
    @stack('scripts')
</body>

</html>