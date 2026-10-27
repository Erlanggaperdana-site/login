{{--
    ============================================================================
    HALAMAN DAFTAR AKUN  (route GET /register)
    ============================================================================

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Warna latar halaman ......... bg-[#f7f7fb] pada wadah paling luar
    - Ukuran kotak daftar ......... max-w-md; w-full membuatnya responsif
    - Warna tombol "Daftar" ....... bg-[#7654d6], hover:bg-[#6342c3]
    - Warna fokus input ........... focus:border-[#7654d6] focus:ring-[#ede9fe]
    - Logo & nama aplikasi ........ cari "LOGO & NAMA APLIKASI"
    - Tampilkan/sembunyikan sandi . fungsi togglePassword() di bagian bawah
    - Tautan ke halaman login ..... cari "TAUTAN MASUK"
    - Ganti judul "Buat akun" ...... cari "JUDUL & SUBJUDUL"
    - Menambah input baru ......... salin blok <div> input, lalu tambah validasinya di AuthController::register
    - Mengganti pesan error ....... otomatis dari validasi AuthController::register
    - Mengganti aturan password ... AuthController::register (Password::min(8))
    - Mengubah role hasil daftar .. AuthController::register (User::ROLE_CUSTOMER)

    Catatan: semua akun yang mendaftar di sini otomatis berperan "customer"
    (diatur di AuthController::register), bukan admin.
    Halaman ini memakai layout resources/views/layouts/app.blade.php.
--}}
@extends('layouts.app')
@section('title', 'Daftar')
@section('content')

{{-- WADAH HALAMAN: memusatkan kotak daftar secara horizontal & vertikal. --}}
<div class="flex min-h-screen items-center justify-center bg-[#f7f7fb] px-4 py-10">

    {{-- KARTU DAFTAR: w-full = responsif; max-w-md = lebar maksimum; bg-white = warna kartu. --}}
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

        {{-- LOGO & NAMA APLIKASI (gambar: public/icon/logo.png). --}}
        <div class="mb-6 flex items-center gap-3">
            <img src="{{ asset('icon/logo.png') }}" alt="Logo Sistem Pengaduan" class="h-10 w-10 rounded-xl object-contain">
            <span class="text-lg font-bold text-slate-900">Sistem Pengaduan</span>
        </div>

        {{-- JUDUL & SUBJUDUL: ubah teks "Buat akun" dan "Daftar untuk menyampaikan pengaduan." di sini. --}}
        <h1 class="mb-1 text-2xl font-bold text-slate-900">Buat akun</h1>
        <p class="mb-6 text-sm text-slate-500">Daftar untuk menyampaikan pengaduan.</p>

        {{-- PESAN ERROR VALIDASI (email sudah dipakai, sandi terlalu pendek, dll). --}}
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{--
            FORM DAFTAR
            - action="{{ route('register') }}" : dikirim ke POST /register.
            - @csrf : token keamanan Laravel.

            MENAMBAH INPUT BARU:
            1. Salin salah satu <div> input di bawah.
            2. Ganti name="..." dan label-nya.
            3. Tambahkan aturan validasi + kolom database bila perlu
               (AuthController::register untuk validasi, migration untuk kolom).
            4. Jika ingin konfirmasi password, tambahkan input name="password_confirmation"
               dan aturan 'confirmed' di AuthController::register.
        --}}
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            {{-- INPUT NAMA: name="name" dibaca AuthController saat membuat user. --}}
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    autocomplete="name"
                    required
                    autofocus
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#7654d6] focus:ring-2 focus:ring-[#ede9fe]"
                >
            </div>

            {{-- INPUT EMAIL: harus unik (tidak boleh sama dengan akun lain). --}}
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-[#7654d6] focus:ring-2 focus:ring-[#ede9fe]"
                >
            </div>

            {{--
                INPUT PASSWORD + TOMBOL MATA
                - pr-12 : memberi ruang untuk tombol mata di kanan.
                - autocomplete="new-password" : browser menawarkan sandi baru, bukan mengisi sandi lama.
                - onclick="togglePassword('password', this)" : memanggil script di bawah.
            --}}
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
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

            {{-- TOMBOL DAFTAR: warna ungu utama, berubah lebih tua saat di-hover.
                 focus-visible:ring memberi tanda fokus saat navigasi keyboard (Tab). --}}
            <button
                type="submit"
                class="w-full rounded-xl bg-[#7654d6] px-4 py-3 text-sm font-semibold text-white hover:bg-[#6342c3] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7654d6] focus-visible:ring-offset-2"
            >
                Daftar
            </button>
        </form>

        {{-- TAUTAN MASUK: kembali ke halaman login. --}}
        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-[#6342c3] hover:underline">Masuk</a>
        </p>
    </div>
</div>

{{-- SCRIPT MATA PASSWORD: menukar type input password <-> text dan ikon mata. --}}
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