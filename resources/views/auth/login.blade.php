{{--
    ============================================================================
    HALAMAN LOGIN  (route GET /login)
    ============================================================================

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Warna latar halaman ......... bg-[#f7f7fb] pada wadah paling luar
    - Ukuran kotak login .......... max-w-md (bisa max-w-sm / max-w-lg); w-full membuatnya responsif
    - Sudut kotak ................. rounded-2xl
    - Warna tombol "Masuk" ........ bg-[#7654d6], hover:bg-[#6342c3]
    - Warna fokus input ........... focus:border-[#7654d6] focus:ring-[#ede9fe]
    - Logo & nama aplikasi ........ cari "LOGO & NAMA APLIKASI"
    - Tampilkan/sembunyikan sandi . fungsi togglePassword() di bagian bawah
    - Teks & tautan daftar ........ cari "TAUTAN DAFTAR"
    - Ganti judul "Selamat datang"  cari "JUDUL & SUBJUDUL"
    - Menambah input baru ......... salin satu blok <div> input, lalu tambah validasinya di AuthController::login
    - Mengganti pesan error ....... AuthController::login (withErrors)
    - Mengganti tujuan setelah login  AuthController::login (redirect()->intended(...))
    - Mengganti link "Lupa password"  belum ada fiturnya, perlu route + controller baru
      (Laravel menyediakan Password::sendResetLink() bawaan)

    Catatan: @extends('layouts.app') berarti halaman ini memakai kerangka
    resources/views/layouts/app.blade.php (bagian <head>, <body>, dll).
    Artinya warna latar & <title> juga bisa diubah dari layout tersebut.
--}}
@extends('layouts.app')
@section('title', 'Masuk')
@section('content')

{{-- WADAH HALAMAN: min-h-screen + items-center membuat kotak login berada di tengah layar. --}}
<div class="flex min-h-screen items-center justify-center bg-[#f7f7fb] px-4 py-10">

    {{--
        KARTU LOGIN
        - w-full       : mengisi lebar layar kecil (responsif).
        - max-w-md     : lebar maksimum kartu (28rem / 448px).
        - bg-white     : warna latar kartu.
        - rounded-2xl  : sudut membulat.
        - shadow-sm    : bayangan tipis.
        - p-8          : jarak dalam kartu.
    --}}
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

        {{-- LOGO & NAMA APLIKASI: gambar dari public/icon/logo.png; teks bisa diganti di sini. --}}
        <div class="mb-6 flex items-center gap-3">
            <img src="{{ asset('icon/logo.png') }}" alt="Logo Sistem Pengaduan" class="h-10 w-10 rounded-xl object-contain">
            <span class="text-lg font-bold text-slate-900">Sistem Pengaduan</span>
        </div>

        {{-- JUDUL & SUBJUDUL: ubah teks "Selamat datang" dan "Masuk ke akun Anda." di sini.
             Ukuran huruf: text-2xl (judul), text-sm (subjudul). Warna: text-slate-900 / text-slate-500. --}}
        <h1 class="mb-1 text-2xl font-bold text-slate-900">Selamat datang</h1>
        <p class="mb-6 text-sm text-slate-500">Masuk ke akun Anda.</p>

        {{-- PESAN SUKSES (contoh: setelah pendaftaran berhasil). Warna hijau. --}}
        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- PESAN ERROR VALIDASI: menampilkan pesan error pertama, latar merah.
             Sengaja hanya satu pesan agar tampilan tetap ringkas. --}}
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{--
            FORM LOGIN
            - action="{{ route('login') }}" : dikirim ke POST /login (AuthController::login).
            - method="POST"                 : form login wajib POST.
            - @csrf                         : token keamanan Laravel (wajib untuk form POST).

            MENAMBAH INPUT BARU:
            1. Salin salah satu <div> input di bawah (misal blok email).
            2. Ganti name="..." dengan nama field baru.
            3. Tambahkan aturan validasinya di AuthController::login.
            4. Jika data ingin disimpan, sesuaikan juga AuthController::register.
        --}}
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            {{--
                INPUT EMAIL
                - name="email"  : nama field yang dibaca controller.
                - old('email')  : mengisi kembali email bila validasi gagal.
                - autocomplete  : membantu password manager browser.
                - focus:border-[#7654d6] : border berubah ungu saat diklik.
                - focus:ring-2 focus:ring-[#ede9fe] : efek cincin ungu muda saat fokus.
            --}}
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#7654d6] focus:ring-2 focus:ring-[#ede9fe]"
                >
            </div>

            {{--
                INPUT PASSWORD + TOMBOL MATA
                - type="password"        : karakter disamarkan.
                - pr-12                  : ruang kanan agar teks tidak menimpa tombol mata.
                - tombol absolute right-3: menempel di kanan dalam kolom input.
                - onclick="togglePassword('password', this)" : memanggil script di bawah.
                - .icon-eye / .icon-eye-off : dua ikon, salah satu disembunyikan class "hidden".
            --}}
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 pr-12 text-sm outline-none focus:border-[#7654d6] focus:ring-2 focus:ring-[#ede9fe]"
                    >
                    <button
                        type="button"
                        onclick="togglePassword('password', this)"
                        class="absolute inset-y-0 right-3 flex items-center text-[#6342c3]"
                        aria-label="Tampilkan password"
                    >
                        {{-- Ikon mata (sandi tersembunyi) --}}
                        <svg class="icon-eye h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        {{-- Ikon mata tercoret (sandi terlihat) --}}
                        <svg class="icon-eye-off hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                            <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a15.8 15.8 0 0 1-3.1 3.9M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 3.2-.5"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- INGAT SAYA: checkbox "remember" agar sesi login tidak cepat berakhir. --}}
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#7654d6] focus:ring-[#7654d6]">
                Ingat saya
            </label>

            {{-- TOMBOL MASUK: bg-[#7654d6] = ungu utama; hover:bg-[#6342c3] = ungu lebih tua saat kursor di atasnya.
                 focus-visible:ring memberi tanda fokus saat navigasi keyboard (Tab). --}}
            <button
                type="submit"
                class="w-full rounded-xl bg-[#7654d6] px-4 py-3 text-sm font-semibold text-white hover:bg-[#6342c3] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7654d6] focus-visible:ring-offset-2"
            >
                Masuk
            </button>
        </form>

        {{-- TAUTAN DAFTAR: mengarah ke route 'register' (GET /register). --}}
        <p class="mt-6 text-center text-sm text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-[#6342c3] hover:underline">Daftar</a>
        </p>
    </div>
</div>

{{--
    SCRIPT MATA PASSWORD
    1. Mengambil input berdasarkan id.
    2. Jika masih type="password" -> ubah ke "text" (sandi terlihat).
    3. Ikon mata dan mata-tercoret dipertukarkan (class hidden).
    4. aria-label ikut diubah agar ramah pembaca layar.
--}}
<script>
    function togglePassword(id, button) {
        const field = document.getElementById(id);
        const terlihat = field.type === 'password';

        field.type = terlihat ? 'text' : 'password';
        button.querySelector('.icon-eye').classList.toggle('hidden', terlihat);
        button.querySelector('.icon-eye-off').classList.toggle('hidden', !terlihat);
        button.setAttribute('aria-label', terlihat ? 'Sembunyikan password' : 'Tampilkan password');
    }
</script>
@endsection 