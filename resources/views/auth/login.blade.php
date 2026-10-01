@php
    // Nama kolom login; harus sama dengan yang dipakai controller (email atau username).
    $loginField = 'email';
    $loginLabel = $loginField === 'email' ? 'Email' : 'Username';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">

        <title>Masuk — Sistem Inventaris</title>

        <!-- Font Montserrat -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <!-- Tailwind CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { theme: { extend: { fontFamily: { sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } };
        </script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="min-h-screen font-sans antialiased text-neutral-900 flex items-center justify-center p-4 sm:p-8
                 bg-gradient-to-r from-[#e2e4ea] to-[#c9d6f7]">

        <main class="w-full max-w-[900px] bg-white rounded-2xl shadow-[0_18px_45px_rgba(40,40,90,0.22)] overflow-hidden
                     flex flex-col md:flex-row md:min-h-[460px]">

            <!-- Kiri: form login -->
            <section class="w-full md:w-1/2 flex items-center justify-center px-8 py-12">
                <div class="w-full max-w-[300px]">
                    <h1 class="text-3xl font-bold text-center text-black">Masuk</h1>
                    <p class="text-xs text-neutral-500 text-center mt-2 mb-8">Gunakan akun Anda untuk mengelola inventaris.</p>

                    <form id="login-form" method="POST" action="{{ url('/login') }}" class="space-y-4" novalidate>
                        @csrf

                        <div>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
                                <input
                                    id="{{ $loginField }}"
                                    type="text"
                                    name="{{ $loginField }}"
                                    value="{{ old($loginField) }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="{{ $loginLabel }}"
                                    aria-label="{{ $loginLabel }}"
                                    class="w-full h-11 rounded-lg bg-[#eeeeee] pl-10 pr-4 text-sm text-neutral-900 placeholder:text-neutral-500
                                           border focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/30 focus:border-[#2563eb] transition-colors
                                           @error($loginField) border-red-400 @else border-transparent @enderror"
                                >
                            </div>
                            @error($loginField)
                                <p class="mt-1.5 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Kata sandi"
                                    aria-label="Kata sandi"
                                    class="w-full h-11 rounded-lg bg-[#eeeeee] pl-10 pr-11 text-sm text-neutral-900 placeholder:text-neutral-500
                                           border focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/30 focus:border-[#2563eb] transition-colors
                                           @error('password') border-red-400 @else border-transparent @enderror"
                                >
                                <button type="button" id="toggle-password"
                                    class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-md text-neutral-500 hover:text-[#2563eb]
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40 transition-colors"
                                    aria-label="Tampilkan kata sandi" aria-pressed="false">
                                    <i class="fa-solid fa-eye" id="toggle-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-3 flex justify-center">
                            <button
                                id="submit-btn"
                                type="submit"
                                class="h-11 px-12 rounded-lg bg-blue-600 text-white text-xs font-semibold tracking-wider uppercase
                                       hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-600
                                       disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                            >
                                Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Kanan: identitas sistem -->
            <section class="w-full md:w-1/2 flex items-center justify-center text-center text-white px-8 py-12
                            bg-gradient-to-r from-blue-600 to-blue-700
                            rounded-t-[32px] md:rounded-t-none md:rounded-tl-[72px] md:rounded-bl-[48px]">
                <div class="max-w-[300px]">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 text-2xl">
                        <i class="fa-solid fa-warehouse"></i>
                    </span>
                    <h2 class="text-3xl font-bold mt-5 leading-tight">Sistem Inventaris</h2>
                    <p class="text-sm leading-relaxed text-white/85 mt-4">
                        Pantau stok, kelola gudang, dan catat setiap barang yang masuk, keluar, atau berpindah dalam satu tempat.
                    </p>

                    
                </div>
            </section>
        </main>

        <script>
            const pw = document.getElementById('password');
            const userInput = document.getElementById(@json($loginField));

            // Tombol mata: tampilkan / sembunyikan kata sandi
            const toggleBtn = document.getElementById('toggle-password');
            const toggleIcon = document.getElementById('toggle-icon');
            toggleBtn.addEventListener('click', () => {
                const show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                toggleIcon.className = 'fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye');
                toggleBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggleBtn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                pw.focus();
            });

            // Validasi ringan di sisi klien + status loading saat submit
            const form = document.getElementById('login-form');
            const submitBtn = document.getElementById('submit-btn');
            form.addEventListener('submit', (e) => {
                if (!userInput.value.trim() || !pw.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        text: @json($loginLabel . ' dan kata sandi wajib diisi.'),
                        confirmButtonColor: '#2563eb',
                    });
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses…';
            });

            // Pesan error login dari server
            // Tampilkan popup HANYA pada kunjungan baru (bukan tombol back/forward)
            const navType = performance.getEntriesByType('navigation')[0]?.type;
            const isFreshVisit = navType === 'navigate';

            @if ($errors->has($loginField) && ! $errors->has('password'))
                if (isFreshVisit) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal masuk',
                        text: @json($errors->first($loginField)),
                        confirmButtonColor: '#2563eb',
                    });
                }
            @endif

            // Saat halaman dipulihkan dari cache browser (tombol back/forward)
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) {
                    Swal.close();
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Masuk';
                }
            });
        </script>
    </body>
</html>