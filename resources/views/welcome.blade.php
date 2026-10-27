{{--
    ============================================================================
    HALAMAN SAMBUTAN STATIS
    ============================================================================
    Halaman ini tidak dipakai sebagai halaman utama (route "/" langsung
    mengarahkan ke login atau dashboard di routes/web.php). Bisa dipakai
    sebagai contoh tampilan kartu di tengah layar.

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Warna latar halaman .... bg-[#f7f7fb] pada <body>
    - Ukuran & warna kartu ... max-w-lg, bg-white, rounded-3xl
    - Logo ................... public/icon/logo.png (h-14 w-14)
    - Titik ungu di judul .... <span class="text-[#7654d6]">.</span>
    - Tombol "Buka dashboard"  bg-[#7654d6], hover:bg-[#6342c3]
    - Mengubah tujuan tombol . href="/" (nanti diarahkan oleh route "/" di routes/web.php)
    - Mengubah judul ......... <h1> "Sistem Pengaduan."
    - Mengubah deskripsi ..... <p> "Sampaikan aspirasi ..."

    CARA MENJADIKAN HALAMAN INI SEBAGAI BERANDA:
    Di routes/web.php, ganti isi Route::get('/', ...) menjadi:
    return view('welcome');
    (Saat ini route "/" langsung redirect ke login/dashboard.)
--}}
<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sistem Pengaduan</title><script src="https://cdn.tailwindcss.com"></script></head>

{{-- LATAR HALAMAN + posisi konten di tengah layar. --}}
<body class="flex min-h-screen items-center justify-center bg-[#f7f7fb] px-5 text-slate-800">
    {{-- KARTU SAMBUTAN: lebar maksimum max-w-lg, sudut rounded-3xl. --}}
    <main class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12">

        {{-- LOGO APLIKASI (ukuran diatur h-14 w-14). --}}
        <img src="{{ asset('icon/logo.png') }}" alt="Logo Sistem Pengaduan" class="mx-auto mb-5 h-14 w-14 rounded-2xl object-contain">

        {{-- JUDUL: titik ungu di akhir kalimat memakai warna hex langsung. --}}
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Sistem Pengaduan<span class="text-[#7654d6]">.</span></h1>

        {{-- DESKRIPSI SINGKAT. --}}
        <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500">Sampaikan aspirasi dan pantau tindak lanjutnya dengan mudah.</p>

        {{-- TOMBOL MENUJU ALAMAT UTAMA "/" (kemudian diarahkan oleh route). --}}
        <div class="mt-8 flex justify-center"><a href="/" class="rounded-xl bg-[#7654d6] px-5 py-3 text-sm font-semibold text-white hover:bg-[#6342c3]">Buka dashboard</a></div>
    </main>
</body>
</html>
