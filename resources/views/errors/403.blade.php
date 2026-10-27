{{--
    ============================================================================
    HALAMAN 403 — AKSES DITOLAK
    ============================================================================
    Tampil saat RoleMiddleware menolak akses karena role pengguna tidak sesuai
    (contoh: customer membuka /admin/users).
    Pesan dari middleware ditampilkan melalui $exception->getMessage().

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Warna latar halaman ..... bg-slate-100 pada <body>
    - Warna kartu ............. bg-white + shadow-lg
    - Angka "403" ............. p.text-5xl
    - Tombol "Kembali" ........ bg-slate-900, hover:bg-slate-700
    - Teks pesan error ........ {{ $exception->getMessage() }}
    - Mengubah tujuan tombol .. href="{{ url()->previous() }}" (bisa diganti route('dashboard...'))
    - Mengubah judul halaman .. tag <title>403 - Akses Ditolak</title>
    - Mengubah ukuran kartu ... max-w-md pada <div> pembungkus

    KENAPA HALAMAN INI MUNCUL?
    RoleMiddleware memanggil abort(403, '...') ketika role user tidak cocok.
    Laravel otomatis memakai file resources/views/errors/403.blade.php.
    File error lain bisa ditambah dengan nama 404.blade.php, 500.blade.php, dst.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>

    {{-- Tailwind CSS via CDN (halaman ini berdiri sendiri, tidak memakai layout). --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

{{-- LATAR HALAMAN: bg-slate-100; flex + items-center + justify-center = kartu di tengah layar. --}}
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 text-slate-800">
    {{-- KARTU PESAN: max-w-md membatasi lebar, rounded-2xl sudut membulat. --}}
    <div class="max-w-md rounded-2xl bg-white p-8 text-center shadow-lg shadow-slate-200">
        {{-- KODE STATUS 403. --}}
        <p class="text-5xl font-bold text-slate-900">403</p>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">Akses Ditolak</h1>

        {{-- PESAN DARI MIDDLEWARE; jika kosong memakai pesan bawaan. --}}
        <p class="mt-2 text-sm text-slate-500">{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>

        {{--
            TOMBOL KEMBALI: kembali ke halaman sebelumnya (url()->previous()).
            Ingin selalu ke dashboard? Ganti dengan:
            href="{{ route('dashboard.admin') }}" atau route('dashboard.customer').
            Warna tombol: bg-slate-900 (latar), hover:bg-slate-700 (saat kursor di atas).
        --}}
        <a href="{{ url()->previous() }}"
            class="mt-6 inline-block rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
            Kembali
        </a>
    </div>
</body>
</html>
