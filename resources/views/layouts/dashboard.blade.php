{{--
    ============================================================================
    LAYOUT DASHBOARD — kerangka semua halaman setelah login
    (dipakai oleh dashboard admin/customer, pengaduan, kategori, data user)
    ============================================================================

    PANDUAN CEPAT UNTUK WAWANCARA — cari kata kunci di komentar bawah:
    01. Ganti warna utama (ungu/brand) ...... <head> — cari "WARNA TEMA"
    02. Ganti warna latar halaman ........... <body> — cari "LATAR HALAMAN"
    03. Pindah sidebar ke kanan ............. <aside> — cari "POSISI SIDEBAR"
    04. Ubah lebar sidebar .................. class w-64 (sidebar) + lg:pl-64 (pembungkus konten)
    05. Sembunyikan/tampilkan sidebar ....... fungsi toggleSidebar() di paling bawah
    06. Ubah logo & nama aplikasi ........... cari "LOGO & NAMA APLIKASI"
    07. Tambah/ubah menu sidebar ............ cari "URUTAN MENU"
    08. Ubah header atas .................... cari "HEADER ATAS"
    09. Ubah lebar isi halaman .............. class max-w-7xl pada <main> — cari "AREA KONTEN"
    10. Ubah footer ......................... cari "FOOTER"
    11. Ubah menu jadi hanya untuk admin .... cari "MENU KHUSUS ADMIN"
    12. Urutan menu sidebar ................. cari "URUTAN MENU"
    13. Menambah menu baru .................. cari "CARA MENAMBAH MENU"
    14. Ganti ikon menu (emblem) ............ cari "IKON MENU"
    15. Ganti teks label "Menu" ............. cari "LABEL MENU"
    16. Ganti judul tab browser ............. tag <title> di <head>
    17. Ganti format tanggal header ......... now()->translatedFormat(...)
    18. Atur tampilan HP/tablet/desktop ..... prefix sm: md: lg: xl:
    19. Ganti warna menu aktif .............. variabel $navActive
    20. Pindahkan profil ke atas sidebar .... cari "PROFIL PENGGUNA"

    Semua warna & tata letak memakai Tailwind CSS (dimuat lewat CDN),
    jadi cukup mengganti nama class: bg-white, text-slate-600, w-64, dll.

    PETA FILE VIEW (halaman mana diatur di file mana):
    - layouts/app.blade.php ............. kerangka halaman login & daftar
    - layouts/dashboard.blade.php ....... kerangka semua halaman setelah login (file ini)
    - auth/login.blade.php .............. halaman masuk
    - auth/register.blade.php ........... halaman daftar akun
    - dashboard/admin.blade.php ......... dashboard admin
    - dashboard/customer.blade.php ...... dashboard customer
    - complaints/index.blade.php ........ daftar pengaduan + filter + ubah status
    - complaints/create.blade.php ....... form buat pengaduan + upload foto
    - categories/index.blade.php ........ kelola kategori (admin)
    - dashboard/users/index.blade.php ... kelola user (admin)
    - errors/403.blade.php .............. halaman akses ditolak
    - welcome.blade.php ................. halaman sambutan statis (tidak dipakai route)

    CARA MENJAWAB PERTANYAAN "DI MANA MENGUBAH ...?":
    - Warna  : cari class Tailwind (bg-*, text-*, border-*) pada elemen terkait.
    - Posisi  : cari class posisi (left-0, right-0, fixed, sticky, order-*).
    - Ukuran  : cari class w-*, h-*, max-w-*, p-*, m-*, gap-*.
    - Teks    : langsung ubah teks di antara tag HTML.
    - Data    : berasal dari controller lewat variabel {{ $namaVariabel }}.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- JUDUL TAB BROWSER: "@yield('title')" diisi oleh @section('title') tiap halaman.
         Teks setelah titik tengah (· Sistem Pengaduan) bisa diganti di sini. --}}
    <title>@yield('title') · Sistem Pengaduan</title>

    {{-- Tailwind CSS dimuat dari CDN: tidak perlu npm/build, cukup tulis class di HTML. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{--
        WARNA TEMA (BRAND)
        Ini sumber warna ungu aplikasi. Ubah kode hex di sini, maka seluruh
        class brand-* ikut berubah (tombol, menu aktif, badge, dsb).
        Contoh: brand-500 '#7654d6' -> '#0ea5e9' (biru), '#16a34a' (hijau).
        - 50  : warna latar sangat muda (bg-brand-50)
        - 100 : warna latar muda (bg-brand-100)
        - 500 : warna utama (focus:border-brand-500)
        - 600 : tombol utama (bg-brand-600)
        - 700 : teks/hover (text-brand-700, hover:bg-brand-700)
    --}}
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#f5f3ff',100:'#ede9fe',500:'#7654d6',600:'#6342c3',700:'#5133a8'}}}}}</script>

    {{-- Gaya tambahan kecil: latar gelap di belakang dialog/modal dan menghilangkan panah <summary>. --}}
    <style>dialog::backdrop{background:rgb(15 23 42/.42)}summary::-webkit-details-marker{display:none}</style>
</head>

{{--
    LATAR HALAMAN
    bg-[#f7f7fb] = warna abu sangat muda sebagai latar seluruh dashboard.
    Ganti dengan bg-slate-100, bg-white, atau kode hex lain sesuai kebutuhan.
    text-slate-800 = warna teks default.
--}}
<body class="bg-[#f7f7fb] text-slate-800 antialiased">

{{--
    OVERLAY SIDEBAR
    Lapisan gelap yang muncul di belakang sidebar ketika dibuka pada layar kecil.
    Klik area ini akan menutup sidebar (onclick="toggleSidebar()").
    z-30 = lapisan di atas konten, di bawah sidebar (sidebar z-40).
--}}
<div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/30 lg:hidden" onclick="toggleSidebar()"></div>

{{--
    ============================================================================
    SIDEBAR (MENU KIRI)  id="sidebar"
    ============================================================================
    POSISI SIDEBAR:
      • Kiri (sekarang) : class "left-0"
      • Kanan           : ganti "left-0" -> "right-0",
                          ganti "-translate-x-full" -> "translate-x-full",
                          lalu pada pembungkus konten <div class="lg:pl-64">
                          ganti "lg:pl-64" -> "lg:pr-64".

    LEBAR SIDEBAR:
      • "w-64" = 16rem / 256px. Jika diubah, samakan "lg:pl-64" pada konten
        (contoh w-72 -> lg:pl-72) agar isi tidak tertutup sidebar.

    WARNA SIDEBAR:
      • "bg-white"     = warna latar sidebar.
      • "border-slate-200" = garis pemisah di sisi kanan.

    TAMPIL / SEMBUNYI:
      • "-translate-x-full" = tersembunyi (di luar layar) pada HP/tablet.
      • "lg:translate-x-0"  = selalu tampil pada layar lebar (desktop).
      • z-40 = lapisan paling atas agar tidak tertutup konten.

    FIXED: "fixed inset-y-0" membuat sidebar menempel penuh dari atas ke bawah.
--}}
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0">

    {{-- LOGO & NAMA APLIKASI: ganti gambar di public/icon/logo.png dan teks "Sistem Pengaduan". --}}
    <a href="{{ auth()->user()->isAdmin() ? route('dashboard.admin') : route('dashboard.customer') }}" class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><img src="{{ asset('icon/logo.png') }}" alt="Logo Sistem Pengaduan" class="h-10 w-10 rounded-xl object-contain"><span class="text-base font-bold tracking-tight text-slate-900">Sistem Pengaduan</span></a>

    {{--
        URUTAN MENU
        Urutan menu di sidebar = urutan tag <a> dari atas ke bawah di dalam <nav> ini.
        Untuk memindah urutan menu, cukup potong baris <a ...>...</a> lalu tempel di posisi baru.

        CARA MENAMBAH MENU
        1. Salin salah satu baris <a ...>...</a> di bawah.
        2. Ganti href menjadi route tujuan, misal href="{{ route('complaints.index') }}".
        3. Ganti teks & ikonnya, misal: <span class="text-base">★</span>Laporan.
        4. Menu otomatis mendapat gaya aktif/non-aktif dari $navItem + $navActive/$navIdle.
        5. Jika menu hanya untuk admin, letakkan di dalam blok @if(auth()->user()->isAdmin()).

        IKON MENU
        Ikon sekarang memakai karakter/emoji di dalam <span class="text-base">...</span>.
        Bisa diganti dengan emoji lain (misal 📊, 👥, 📝, 🗂) atau SVG.

        JARAK ANTAR MENU
        - "space-y-1" pada <nav> = jarak vertikal antar item.
        - "px-4 py-6" = jarak kiri/kanan dan atas/bawah area menu.
    --}}
    <nav class="flex-1 space-y-1 px-4 py-6">
        {{-- LABEL MENU: teks kecil "Menu" di atas daftar menu. Ganti teksnya di sini. --}}
        <p class="px-3 pb-3 text-[11px] font-semibold uppercase tracking-[.14em] text-slate-400">Menu</p>

        {{--
            GAYA MENU (dipakai semua item di bawah):
            - $navItem   : bentuk dasar menu (jarak, sudut, ukuran huruf).
            - $navActive : tampilan menu yang sedang dibuka (latar ungu muda + teks ungu).
            - $navIdle   : tampilan menu biasa (teks abu, latar berubah saat di-hover).
            Ubah warna menu aktif di $navActive, misal bg-brand-50 -> bg-amber-50.
        --}}
        @php $navItem='flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '; $navActive='bg-brand-50 text-brand-700'; $navIdle='text-slate-600 hover:bg-slate-50'; @endphp

        {{--
            URUTAN 1: MENU DASHBOARD
            - href memakai ternary: admin ke dashboard.admin, customer ke dashboard.customer.
            - request()->routeIs('dashboard.*') membuat menu berwarna aktif saat halaman dashboard dibuka.
            - Ikon ⌂ bisa diganti emoji lain di dalam <span class="text-lg">.
        --}}
        <a href="{{ auth()->user()->isAdmin() ? route('dashboard.admin') : route('dashboard.customer') }}" class="{{ $navItem }}{{ request()->routeIs('dashboard.*') ? $navActive : $navIdle }}"><span class="text-lg">⌂</span>Dashboard</a>

        {{--
            URUTAN 2: MENU DATA USER (KHUSUS ADMIN)
            @if(auth()->user()->isAdmin()) membuat menu ini HANYA dirender untuk admin.
            Customer tidak melihat menu ini sama sekali.
        --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="{{ $navItem }}{{ request()->routeIs('admin.users.*') ? $navActive : $navIdle }}"><span class="text-base">♙</span>Data User</a>
        @endif

        {{--
            URUTAN 3: MENU PENGADUAN
            Bisa dibuka admin maupun customer, jadi tidak dibungkus @if role.
        --}}
        <a href="{{ route('complaints.index') }}" class="{{ $navItem }}{{ request()->routeIs('complaints.*') ? $navActive : $navIdle }}"><span class="text-base">▤</span>Pengaduan</a>

        {{--
            URUTAN 4: MENU KATEGORI (KHUSUS ADMIN)
            Sama seperti Data User, hanya tampil bila role = admin.
        --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('categories.index') }}" class="{{ $navItem }}{{ request()->routeIs('categories.*') ? $navActive : $navIdle }}"><span class="text-base">▦</span>Kategori</a>
        @endif
    </nav>

    {{--
        PROFIL PENGGUNA + TOMBOL KELUAR (bagian bawah sidebar)
        - bg-brand-100 / text-brand-700 : warna lingkaran inisial nama.
        - strtoupper(substr(...))       : mengambil huruf pertama nama.
        - Form logout memakai POST + @csrf sesuai route 'logout'.
        - MEMINDAHKAN PROFIL KE ATAS: potong seluruh <div class="border-t ..."> ini,
          lalu tempel di atas <nav> (di bawah logo). Agar tetap di bawah, biarkan di sini.
        - MENYEMBUNYIKAN TOMBOL KELUAR: hapus blok <form action="{{ route('logout') }}">.
        - MENGGANTI TEKS "Keluar": ubah tulisan Keluar di dalam <button>.
    --}}
    <div class="border-t border-slate-100 p-4"><div class="mb-3 flex items-center gap-3 px-2"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p><p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p></div></div><form action="{{ route('logout') }}" method="POST">@csrf<button class="w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800">Keluar</button></form></div>
</aside>

{{--
    PEMBUNGKUS KONTEN
    "lg:pl-64" memberi ruang kiri sebesar lebar sidebar (w-64) agar isi tidak
    tertutup. Jika sidebar dipindah ke kanan, ubah menjadi "lg:pr-64".
--}}
<div class="lg:pl-64">

    {{--
        HEADER ATAS
        - "sticky top-0" : header menempel di atas saat halaman di-scroll.
        - "h-16"         : tinggi header (4rem / 64px).
        - "bg-white"     : warna latar header.
        - Tombol ☰ (kiri) hanya tampil di layar kecil ("lg:hidden") untuk membuka sidebar.
        - Tanggal di kanan disembunyikan di HP ("hidden sm:block").
        - @yield('title') diisi oleh @section('title') pada view turunan.

        MENGUBAH TANGGAL HEADER:
        - now()->translatedFormat('l, d F Y') menghasilkan "Senin, 27 Oktober 2026".
        - Ganti formatnya, misal:
            'd/m/Y'            -> 27/10/2026
            'd M Y'            -> 27 Oct 2026
            'l, d F Y H:i'     -> tambah jam
        - Bahasa tanggal mengikuti APP_LOCALE di file .env (id untuk Indonesia).

        MENYEMBUNYIKAN TANGGAL:
        - Hapus <div class="hidden text-sm text-slate-500 sm:block">...</div>.
        - Atau ganti "hidden sm:block" menjadi "hidden" agar tidak pernah tampil.
    --}}
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white px-4 sm:px-8"><div class="flex items-center gap-3"><button type="button" onclick="toggleSidebar()" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">☰</button><h1 class="text-sm font-semibold text-slate-900 sm:text-base">@yield('title')</h1></div><div class="hidden text-sm text-slate-500 sm:block">{{ now()->translatedFormat('l, d F Y') }}</div></header>

    {{--
        AREA KONTEN — setiap halaman turunan menaruh isinya di sini (@yield('content')).
        - "max-w-7xl"  : lebar maksimum isi halaman. Ganti max-w-5xl (lebih sempit)
                         atau max-w-full (selebar layar).
        - "p-4 sm:p-8" : jarak tepi konten (HP : 1rem, layar besar : 2rem).
        - "mx-auto"    : isi halaman tetap di tengah.
    --}}
    <main class="mx-auto min-h-[calc(100vh-112px)] max-w-7xl p-4 sm:p-8">@yield('content')</main>

    {{--
        FOOTER
        - border-t = garis atas, py-4 = jarak atas/bawah, text-xs = huruf kecil.
        - Ganti teks "Sistem Pengaduan · Layanan aspirasi masyarakat" di sini.
        - Menghilangkan footer: hapus baris <footer> ini.
        - Menaruh footer di kiri: ganti "text-center" menjadi "text-left".
    --}}
    <footer class="border-t border-slate-200/80 px-6 py-4 text-center text-xs text-slate-400">Sistem Pengaduan · Layanan aspirasi masyarakat</footer>
</div>

{{--
    SCRIPT BUKA/TUTUP SIDEBAR (layar HP/tablet)
    - class "-translate-x-full" = sidebar keluar layar (tersembunyi).
    - class "hidden"            = overlay disembunyikan.
    Fungsi ini menambah/melepas kedua class tersebut saat tombol ☰ atau overlay diklik.
    Jika sidebar dipindah ke kanan, ganti "-translate-x-full" di sini menjadi "translate-x-full".
--}}
<script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('-translate-x-full');document.getElementById('sidebar-overlay').classList.toggle('hidden')}</script>
</body></html>
