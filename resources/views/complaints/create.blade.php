{{--
    ============================================================================
    FORM BUAT PENGADUAN  (customer, route GET /pengaduan/buat)
    ============================================================================
    - Dikirim ke route 'complaints.store' (POST /pengaduan) dengan enctype multipart
      agar bisa mengunggah foto.
    - Isian: nama, nomor meja 1–30, email, no. HP, kategori, isi pengaduan, maks. 5 foto.
    - Validasi sebenarnya ada di ComplaintController::store();
      JavaScript di bawah hanya memberi petunjuk awal.

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Lebar form ................. max-w-3xl pada pembungkus (cari "PEMBUNGKUS FORM")
    - Judul halaman .............. cari "KEPALA HALAMAN"
    - Nama customer .............. input sender_name
    - Pilihan nomor meja ......... fieldset + grid-cols-5 / sm:grid-cols-10
    - Email & No. HP ............. grid sm:grid-cols-2 (dua kolom)
    - Kategori ................... select category_id (diisi dari $categories)
    - Isi pengaduan .............. textarea message
    - Upload foto ................ input type="file" multiple + pratinjau JavaScript
    - Tombol kirim ............... bg-brand-600, hover:bg-brand-700
    - Mengubah jumlah meja ....... @for($table = 1; $table <= 30) + validasi between:1,30
    - Mengubah batas jumlah foto . JavaScript "files.length > 5" + validasi array max:5
    - Mengubah batas ukuran foto . JavaScript "5 * 1024 * 1024" + validasi max:5120 (KB)
    - Mengubah batas karakter .... maxlength="5000" + validasi max:5000
    - Menambah field baru ........ tambah input + validasi di ComplaintController::store()
                                   + kolom di migration bila ingin disimpan
    - Mengubah lebar form ........ max-w-3xl pada pembungkus

    PENTING: enctype="multipart/form-data" WAJIB ada agar file foto terkirim.
    Tanpa itu, input file tidak akan sampai ke server.
--}}
@extends('layouts.dashboard')
@section('title', 'Buat Pengaduan')
@section('content')

{{-- PEMBUNGKUS FORM: max-w-3xl membuat form tidak terlalu lebar di layar besar; mx-auto menengahkan. --}}
<div class="mx-auto max-w-3xl">

    {{-- KEPALA HALAMAN: label, judul, dan petunjuk singkat. --}}
    <div class="mb-6"><p class="mb-2 text-sm font-medium text-brand-600">Pengaduan baru</p><h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Sampaikan pengaduan</h2><p class="mt-1 text-sm text-slate-500">Lengkapi informasi berikut agar dapat kami tindak lanjuti.</p></div>

    {{-- DAFTAR ERROR VALIDASI: semua pesan error ditampilkan dalam bentuk poin. --}}
    @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    {{--
        FORM PENGADUAN
        - action route('complaints.store') : POST /pengaduan.
        - enctype="multipart/form-data"    : WAJIB agar file foto terkirim.
        - class "rounded-2xl border ... p-5 sm:p-7" : kartu putih pembungkus form.
        - @csrf : token keamanan.
    --}}
    <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        @csrf

        {{-- NAMA CUSTOMER: otomatis terisi nama akun jika login sebagai customer. --}}
        <div><label for="sender_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama customer</label><input id="sender_name" name="sender_name" value="{{ old('sender_name', auth()->user()->isCustomer() ? auth()->user()->name : '') }}" required maxlength="160" autocomplete="name" placeholder="Nama lengkap" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></div>

        {{--
            PILIHAN NOMOR MEJA (1–30)
            - @for($table = 1; $table <= 30; $table++) : membuat 30 tombol radio.
            - input memakai class "peer sr-only" (tersembunyi), lalu <span> menjadi
              tampilan tombolnya.
            - "peer-checked:bg-brand-600 peer-checked:text-white" : saat dipilih,
              tombol berubah ungu dengan teks putih.
            - grid-cols-5 (HP) / sm:grid-cols-10 (layar besar) : 5 atau 10 kolom.

            MENGUBAH JUMLAH MEJA:
            - Ubah angka 30 pada @for(...) di sini.
            - Ubah juga aturan validasi 'between:1,30' di ComplaintController::store().
            - Jika meja lebih dari 30, sesuaikan juga migration table_number.
        --}}
        <fieldset><legend class="mb-2 block text-sm font-semibold text-slate-700">No. meja</legend><div class="grid grid-cols-5 gap-2 sm:grid-cols-10">@for($table = 1; $table <= 30; $table++)<label class="cursor-pointer"><input class="peer sr-only" type="radio" name="table_number" value="{{ $table }}" required @checked((string) old('table_number') === (string) $table)><span class="flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-600 transition peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white hover:border-brand-300">{{ $table }}</span></label>@endfor</div></fieldset>

        {{-- EMAIL & NO. HP: dibagi dua kolom pada layar sm ke atas ("grid gap-5 sm:grid-cols-2"). --}}
        <div class="grid gap-5 sm:grid-cols-2"><div><label for="sender_email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label><input id="sender_email" name="sender_email" type="email" value="{{ old('sender_email', auth()->user()->isCustomer() ? auth()->user()->email : '') }}" required maxlength="255" autocomplete="email" placeholder="nama@email.com" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></div><div><label for="sender_phone" class="mb-1.5 block text-sm font-semibold text-slate-700">No. HP</label><input id="sender_phone" name="sender_phone" type="tel" value="{{ old('sender_phone') }}" required maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></div></div>

        {{-- KATEGORI: pilihan diambil dari tabel categories ($categories dari controller). --}}
        <div><label for="category_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori</label><select id="category_id" name="category_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>

        {{--
            ISI PENGADUAN: textarea 6 baris, maksimal 5000 karakter.
            - rows="6"       : tinggi awal textarea.
            - maxlength="5000": batas di browser (validasi server juga max:5000).
            - old('message') : mengembalikan isi bila validasi gagal.
        --}}
        <div><label for="message" class="mb-1.5 block text-sm font-semibold text-slate-700">Isi pengaduan</label><textarea id="message" name="message" rows="6" required maxlength="5000" placeholder="Jelaskan masukan atau pengaduan Anda..." class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('message') }}</textarea></div>

        {{--
            LAMPIRAN FOTO
            - multiple              : bisa memilih beberapa foto sekaligus.
            - accept="image/..."    : membatasi pilihan ke JPG, PNG, WEBP.
            - file:bg-brand-50 ...  : warna tombol "Choose File" bawaan browser.
            - #file-count            : tempat petunjuk jumlah foto (diisi JavaScript).
            - #attachment-preview    : tempat pratinjau foto yang dipilih.

            BATAS FOTO ADA DI DUA TEMPAT (harus sama):
            - Tampilan/JS : files.length > 5 dan file.size > 5 * 1024 * 1024 (5 MB).
            - Server      : ComplaintController::store() -> 'max:5' dan 'max:5120' (KB per file).
            Jika hanya mengubah salah satu, pengguna bisa melihat pesan berbeda dari server.
        --}}
        <div><label for="attachments" class="mb-1.5 block text-sm font-semibold text-slate-700">Lampiran foto <span class="font-normal text-slate-400">(maks. 5 foto, 5 MB/foto)</span></label><input id="attachments" name="attachments[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-brand-50 file:px-4 file:py-3 file:font-semibold file:text-brand-700 hover:file:bg-brand-100"><p id="file-count" class="mt-1.5 text-xs text-slate-400">Format JPG, PNG, atau WEBP.</p><div id="attachment-preview" class="mt-3 flex flex-wrap gap-1"></div></div>

        {{-- TOMBOL KIRIM: warna ungu utama. --}}
        <div class="flex justify-end border-t border-slate-100 pt-5"><button type="submit" class="rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700">Kirim pengaduan</button></div>
    </form>
</div>

<script>
// PRATINJAU FOTO: menampilkan jumlah file, peringatan ukuran, dan thumbnail.
// Batas sebenarnya tetap diperiksa di server (ComplaintController::store).
document.getElementById('attachments').addEventListener('change', function () {
    const files = Array.from(this.files);
    const hint = document.getElementById('file-count');
    const preview = document.getElementById('attachment-preview');
    preview.replaceChildren();

    // Peringatan bila lebih dari 5 foto atau ada file lebih dari 5 MB.
    const oversized = files.some((file) => file.size > 5 * 1024 * 1024);
    hint.textContent = files.length > 5
        ? 'Pilih maksimal 5 foto.'
        : oversized
            ? 'Setiap foto maksimal 5 MB.'
            : files.length
                ? `${files.length} foto dipilih.`
                : 'Format JPG, PNG, atau WEBP.';
    hint.classList.toggle('text-red-600', files.length > 5 || oversized);
    if (files.length > 5 || oversized) return;

    // Ukuran thumbnail menyesuaikan jumlah foto agar tetap rapi.
    const size = [0, 100, 64, 56, 48, 44][files.length] || 44;
    files.forEach((file) => {
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = `Pratinjau ${file.name}`;
        image.title = file.name;
        image.className = 'rounded-lg border border-slate-200 object-cover';
        image.style.width = `${size}px`;
        image.style.height = `${size}px`;
        preview.append(image);
    });
});
</script>
@endsection
