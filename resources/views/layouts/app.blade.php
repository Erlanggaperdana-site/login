{{--
    ============================================================================
    LAYOUT DASAR HALAMAN PUBLIK (login & daftar)
    ============================================================================

    PANDUAN CEPAT:
    - @yield('title')   : judul tab browser, diisi oleh view turunan.
    - @yield('content') : isi utama halaman, diisi oleh view turunan.
    - Mengubah warna latar halaman : class "bg-slate-100" pada <body> di bawah.
    - Mengubah jenis huruf         : tambahkan class font seperti "font-sans" / "font-serif".
    - Mengubah bahasa halaman      : atribut lang="id" pada <html>.
    - Mengubah favicon             : ganti file public/favicon.ico.
    - Tailwind dimuat lewat CDN, jadi tidak perlu proses build (npm run dev).

    PERTANYAAN YANG MUNGKIN DITANYA:
    - "Kenapa login dan daftar tampilannya mirip?" Karena keduanya memakai layout ini.
    - "Di mana mengubah judul tab?" Di tag <title> yang berisi @yield('title').
    - "Di mana mengubah latar halaman?" Di <body class="... bg-slate-100 ...">.
    - "Apakah perlu npm run dev?" Tidak, Tailwind dimuat lewat CDN di <head>.
    - "Di mana isi halaman disisipkan?" Di @yield('content') dalam <body>.

    File ini hanya kerangka. Tampilan login diatur di auth/login.blade.php
    dan tampilan daftar di auth/register.blade.php.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Judul tab browser: diisi @section('title', '...') dari halaman login/daftar. --}}
    <title>@yield('title')</title>

    {{-- Tailwind CSS via CDN: semua warna & tata letak ditulis sebagai class di HTML. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

{{-- LATAR HALAMAN PUBLIK: bg-slate-100 (abu muda). Ganti misal menjadi bg-white atau bg-[#f7f7fb]. --}}
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    {{-- Isi halaman login/daftar disisipkan di sini. --}}
    @yield('content')
</body>
</html>
