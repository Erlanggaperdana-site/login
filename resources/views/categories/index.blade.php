{{--
    ============================================================================
    HALAMAN KATEGORI  (khusus admin, route GET /kategori)
    ============================================================================
    - Daftar kategori + jumlah pengaduan (complaints_count) dari CategoryController::index().
    - Tombol "+ Tambah kategori" membuka dialog form tambah.
    - Tombol "Ubah" membuka dialog edit; "Hapus" mengirim DELETE.
    - Jika validasi gagal, dialog tambah dibuka otomatis (script paling bawah).

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Tombol tambah (warna) ....... cari "TOMBOL TAMBAH KATEGORI" (bg-brand-600)
    - Daftar kategori ............. cari "DAFTAR KATEGORI"
    - Modal/dialog tambah ......... cari "DIALOG TAMBAH KATEGORI"
    - Modal/dialog edit ........... cari "DIALOG EDIT KATEGORI"
    - Tombol hapus + konfirmasi ... cari "TOMBOL HAPUS"
    - Pesan sukses/error .......... blok session('status') & $errors
    - Menambah field kategori .... input baru + validasi CategoryController + kolom migration
    - Mengubah lebar modal ....... class max-w-lg pada <dialog>
    - Mengubah teks konfirmasi ... onsubmit="return confirm('...')"
    - Mengubah teks kosong ....... "Belum ada kategori." / "Belum ada deskripsi"
    - Kenapa pengaduan tidak ikut terhapus? Karena category_id memakai nullOnDelete
      dan teks kategori lama tetap disimpan di kolom complaints.category.

    ALUR DATA: CategoryController::index() mengirim $categories (dengan complaints_count).
--}}
@extends('layouts.dashboard')
@section('title', 'Kategori')
@section('content')

{{-- KEPALA HALAMAN: judul "Kategori" + tombol membuka modal tambah. --}}
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="mb-2 text-sm font-medium text-brand-600">Pengaturan</p>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kategori</h2>
        <p class="mt-1 text-sm text-slate-500">Atur kategori yang tersedia pada form pengaduan.</p>
    </div>

    {{--
        TOMBOL TAMBAH KATEGORI
        onclick="document.getElementById('create-category-modal').showModal()"
        = membuka <dialog id="create-category-modal"> yang ada di bawah.
        Warna tombol: bg-brand-600 (ungu), hover:bg-brand-700.
    --}}
    <button type="button" onclick="document.getElementById('create-category-modal').showModal()" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Tambah kategori</button>
</div>

{{-- PESAN SUKSES: tampil setelah tambah/ubah/hapus kategori berhasil. --}}
@if(session('status'))
<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif

{{-- PESAN ERROR VALIDASI (nama kosong, terlalu panjang, dll). --}}
@if($errors->any())
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
@endif

{{-- PANEL DAFTAR KATEGORI. --}}
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="border-b border-slate-100 px-5 py-4">
        <h3 class="font-semibold text-slate-900">Daftar kategori</h3>
        <p class="mt-0.5 text-xs text-slate-500">{{ $categories->count() }} kategori</p>
    </div>

{{-- DAFTAR KATEGORI: setiap baris menampilkan nama, deskripsi, jumlah masukan, dan tombol aksi. --}}
@forelse($categories as $category)
<div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-0">
    <div class="min-w-0">
        <p class="font-medium text-slate-900">{{ $category->name }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $category->description ?: 'Belum ada deskripsi' }} · {{ $category->complaints_count }} masukan</p></div>

        {{-- TOMBOL AKSI per kategori: "Ubah" membuka modal edit, "Hapus" mengirim form DELETE. --}}
        <div class="flex items-center gap-2"><button type="button" onclick="document.getElementById('edit-category-{{ $category->id }}').showModal()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Ubah</button>

            {{--
                TOMBOL HAPUS
                - method="POST" + @method('DELETE') : Laravel hanya mendukung DELETE lewat method spoofing.
                - onsubmit="return confirm(...)"   : konfirmasi sebelum menghapus.
                - Warna teks merah: text-red-600, hover:bg-red-50.
            --}}
            <form action="{{ route('categories.destroy',$category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini? Masukan lama tetap tersimpan.')">@csrf @method('DELETE')<button title="Hapus kategori" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button></form>
        </div>
    </div>

    {{--
        DIALOG EDIT KATEGORI (satu dialog untuk setiap kategori)
        - id="edit-category-{{ $category->id }}" : id unik agar tombol "Ubah" membuka dialog yang benar.
        - <dialog> + showModal() : modal bawaan browser (latar gelap diatur di layout).
        - @method('PUT') : mengirim permintaan PUT ke route categories.update.
        - class max-w-lg : lebar maksimum dialog (ganti max-w-xl / max-w-2xl bila lebih lebar).

        MENAMBAH FIELD: tambah <div> berisi label + input di sini, lalu:
        1. Tambah aturan validasi di CategoryController::update().
        2. Tambah kolom di migration + $fillable pada model Category.
        3. Tampilkan nilainya dengan {{ $category->nama_kolom }}.
    --}}
    <dialog id="edit-category-{{ $category->id }}" class="w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-0 shadow-xl"><form action="{{ route('categories.update',$category) }}" method="POST" class="space-y-4 p-6">@csrf @method('PUT')<div class="flex items-center justify-between"><h3 class="text-lg font-semibold text-slate-900">Ubah kategori</h3><button type="button" onclick="this.closest('dialog').close()" aria-label="Tutup" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100">✕</button></div><div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama kategori</label><input name="name" value="{{ $category->name }}" required maxlength="100" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-500 focus:outline-none"></div><div><label class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label><textarea name="description" rows="3" maxlength="500" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-500 focus:outline-none">{{ $category->description }}</textarea></div><div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="this.closest('dialog').close()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button></div></form></dialog>

{{-- Keadaan kosong: belum ada kategori sama sekali. --}}
@empty<div class="px-6 py-12 text-center text-sm text-slate-500">Belum ada kategori.</div>@endforelse
</section>

{{--
    DIALOG TAMBAH KATEGORI
    - id="create-category-modal" : dipanggil oleh tombol "+ Tambah kategori" di atas.
    - action route('categories.store') : POST /kategori.
    - input memakai old('...') agar isian tidak hilang saat validasi gagal.
--}}
<dialog id="create-category-modal" class="w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-0 shadow-xl"><form action="{{ route('categories.store') }}" method="POST" class="space-y-4 p-6">@csrf<div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-slate-900">Tambah kategori</h3><p class="text-sm text-slate-500">Masukkan nama dan keterangan kategori.</p></div><button type="button" onclick="this.closest('dialog').close()" aria-label="Tutup" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100">✕</button></div><div><label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama kategori</label><input id="name" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Contoh: Kebersihan" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-500 focus:outline-none"></div><div><label for="description" class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label><textarea id="description" name="description" rows="3" maxlength="500" placeholder="Keterangan singkat kategori" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm focus:border-brand-500 focus:outline-none">{{ old('description') }}</textarea></div><div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="this.closest('dialog').close()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan kategori</button></div></form></dialog>

{{-- Jika validasi gagal, buka kembali dialog tambah agar pesan error terlihat. --}}
@if($errors->any())<script>document.getElementById('create-category-modal').showModal();</script>@endif
@endsection
