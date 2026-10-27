{{--
    ============================================================================
    DAFTAR PENGADUAN / MASUKAN CUSTOMER  (admin & customer)
    route GET /pengaduan
    ============================================================================
    - Form filter (kategori/status) mengirim GET ke route 'complaints.index'.
    - $complaints->links() menampilkan tombol pindah halaman.
    - Nama pelapor disamarkan lewat accessor masked_sender_name pada model Complaint.
    - Form ubah status hanya tampil untuk admin (route 'complaints.status').
    - Dialog foto dipakai untuk memperbesar lampiran gambar.

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Judul halaman ............... cari "KEPALA HALAMAN"
    - Tombol "+ Buat pengaduan" ... bg-brand-600, hover:bg-brand-700
    - Panel filter (buka/tutup) ... cari "PANEL FILTER" + fungsi toggleFilters()
    - Jumlah kartu per baris ...... grid md:grid-cols-2 (2 kartu per baris di desktop)
    - Warna kartu ................. bg-white + border-slate-200 + hover:border-brand-200
    - Avatar inisial nama ......... bg-brand-100 text-brand-700
    - Ukuran foto lampiran ........ variabel $thumbnailSize (baris lampiran)
    - Form ubah status (admin) .... cari "FORM UBAH STATUS"
    - Popup foto besar ............ cari "DIALOG FOTO" + fungsi openPhoto()
    - Jumlah data per halaman ..... ComplaintController::index() -> paginate(10)
    - Menambah pilihan filter ..... tambah <select>/<input> + tangani di ComplaintController::index()
    - Mengubah pilihan status ..... 2 tempat: filter status & select admin + validasi di controller
    - Mengubah ukuran foto ........ array $thumbnailSize = [1=>100, 2=>64, ...]
    - Mengubah nama disamarkan .... app/Models/Complaint.php -> getMaskedSenderNameAttribute()
    - Menyembunyikan tombol buat .. hapus <a href="{{ route('complaints.create') }}">

    ALUR DATA:
    ComplaintController::index() mengirim $complaints (paginator) dan $categories.
    Setiap kartu berasal dari @foreach($complaints as $complaint).
--}}
@extends('layouts.dashboard')
@section('title', 'Pengaduan')
@section('content')

{{-- KEPALA HALAMAN: label "Ruang publik", judul, deskripsi, dan tombol buat pengaduan. --}}
<div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="mb-2 text-sm font-medium text-brand-600">Ruang publik</p><h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Masukan customer</h2><p class="mt-1 text-sm text-slate-500">Masukan terbaru dari customer, dengan identitas yang disamarkan.</p></div><a href="{{ route('complaints.create') }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Buat pengaduan</a></div>

{{-- PESAN SUKSES: tampil setelah pengaduan berhasil dikirim / status diperbarui. --}}
@if(session('status'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif

{{--
    PANEL FILTER
    - Tombol "☷ Filter" memanggil toggleFilters() untuk membuka/menutup #filter-panel.
    - Form mengirim GET sehingga hasil filter terlihat di URL (?category=..&status=..).
    - select kategori diisi dari $categories; select status berisi 3 pilihan tetap.
--}}
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white"><div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4"><div><h3 class="font-semibold text-slate-900">Semua pengaduan</h3><p class="mt-0.5 text-xs text-slate-500">{{ $complaints->total() }} kiriman</p></div><button type="button" onclick="toggleFilters()" aria-expanded="false" aria-controls="filter-panel" id="filter-toggle" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">☷ Filter</button></div>
<div id="filter-panel" class="hidden border-t border-slate-100 bg-slate-50/70 px-5 py-4"><form method="GET" action="{{ route('complaints.index') }}" class="flex flex-wrap items-end gap-3"><div><label for="category-filter" class="mb-1 block text-xs font-semibold text-slate-500">Kategori</label><select id="category-filter" name="category" class="min-w-44 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></div><div><label for="status-filter" class="mb-1 block text-xs font-semibold text-slate-500">Status</label><select id="status-filter" name="status" class="min-w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><option value="">Semua status</option>@foreach(['Menunggu','Diproses','Selesai'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Terapkan</button><a href="{{ route('complaints.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:bg-white">Reset</a></form></div></section>

{{-- Keadaan kosong: belum ada pengaduan yang cocok / masuk. --}}
@if($complaints->isEmpty())
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center"><div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-xl text-brand-700">❝</div><p class="font-semibold text-slate-800">Belum ada pengaduan</p><p class="mt-1 text-sm text-slate-500">Kiriman yang masuk akan tampil di sini.</p></div>
@else
    {{--
        GRID KARTU: "md:grid-cols-2" = 2 kartu per baris pada layar sedang ke atas; 1 kartu di HP.
        Ingin 3 kartu per baris? Ganti "md:grid-cols-2" menjadi "md:grid-cols-2 xl:grid-cols-3".
        Jarak antar kartu diatur oleh "gap-4".
    --}}
    <div class="grid gap-4 md:grid-cols-2">@foreach($complaints as $complaint)

        {{--
            KARTU PENGADUAN
            - bg-white / border-slate-200 : warna dasar kartu.
            - hover:border-brand-200 + hover:shadow-sm : efek saat kursor di atas kartu.
            - rounded-2xl / p-5 : sudut membulat dan jarak dalam kartu.
        --}}
        <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-brand-200 hover:shadow-sm">

            {{-- BARIS ATAS: avatar inisial + nama disamarkan + meja/tanggal + badge kategori. --}}
            <div class="flex items-start justify-between gap-3"><div class="flex min-w-0 items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ mb_strtoupper(mb_substr($complaint->masked_sender_name, 0, 1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ $complaint->masked_sender_name }}</p><p class="text-xs text-slate-500">Meja {{ $complaint->table_number ?: '—' }} · {{ $complaint->created_at->format('d M Y') }}</p></div></div><span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand-700">{{ $complaint->categoryRecord?->name ?? $complaint->category }}</span></div>

            {{-- ISI PENGADUAN: whitespace-pre-line menjaga baris baru dari textarea tetap tampil. --}}
            <div class="mt-4 flex-1"><p class="whitespace-pre-line text-sm leading-6 text-slate-700">{{ $complaint->message ?: $complaint->description }}</p></div>

            {{--
                LAMPIRAN FOTO (thumbnail)
                - $photoCount  : jumlah foto, dipakai untuk menentukan ukuran thumbnail.
                - $thumbnailSize : makin banyak foto, thumbnail makin kecil agar tetap satu baris.
                  Ubah angkanya di sini, contoh [1 => 120, 2 => 80, 3 => 64, 4 => 56, 5 => 48].
                - Klik foto memanggil openPhoto(url) untuk membuka dialog foto besar.
                - url('storage/'.$photoPath) : file disimpan di storage/app/public.
                  Jika foto tidak tampil, jalankan: php artisan storage:link
            --}}
            @if($complaint->attachments)@php($photoCount = count($complaint->attachments))@php($thumbnailSize = [1 => 100, 2 => 64, 3 => 56, 4 => 48, 5 => 44][$photoCount] ?? 44)<div class="mt-4 flex flex-wrap gap-1">@foreach($complaint->attachments as $attachment)@php($photoPath = is_array($attachment) ? $attachment['path'] : $attachment)@php($photoUrl = url('storage/'.$photoPath))<button type="button" onclick="openPhoto('{{ $photoUrl }}')" aria-label="Perbesar foto lampiran" class="overflow-hidden rounded-lg bg-slate-100" style="width: {{ $thumbnailSize }}px; height: {{ $thumbnailSize }}px"><img src="{{ $photoUrl }}" alt="Lampiran pengaduan" loading="lazy" class="h-full w-full object-cover transition hover:scale-105"></button>@endforeach</div>@endif

            {{--
                BAGIAN BAWAH KARTU
                - Teks status selalu tampil.
                - FORM UBAH STATUS hanya untuk admin (auth()->user()->isAdmin()).
                  Select berisi Menunggu / Diproses / Selesai, dikirim PUT ke complaints.status.
                - Customer hanya melihat lokasi (jika ada), tanpa form ubah status.

                MENGUBAH PILIHAN STATUS:
                Ubah <option> di sini DAN aturan validasi di ComplaintController::updateStatus()
                (saat ini 'in:Menunggu,Diproses,Selesai'). Jika tidak disamakan, server menolak.
            --}}
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3"><span class="text-xs text-slate-500">Status: {{ $complaint->status === 'Menunggu' ? 'Menunggu ditinjau' : $complaint->status }}</span>@if(auth()->user()->isAdmin())<form action="{{ route('complaints.status', $complaint) }}" method="POST" class="flex items-center gap-2">@csrf @method('PUT')<label for="complaint-status-{{ $complaint->id }}" class="sr-only">Ubah status</label><select id="complaint-status-{{ $complaint->id }}" name="status" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700"><option value="Menunggu" @selected($complaint->status === 'Menunggu')>Menunggu</option><option value="Diproses" @selected($complaint->status === 'Diproses')>Diproses</option><option value="Selesai" @selected($complaint->status === 'Selesai')>Selesai</option></select><button class="rounded-lg bg-brand-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">Simpan</button></form>@elseif($complaint->location)<span class="truncate text-xs text-slate-400">{{ $complaint->location }}</span>@endif</div>
        </article>
    @endforeach</div>

    {{-- PAGINASI: tombol halaman sebelumnya/berikutnya. --}}
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-5 py-4">{{ $complaints->links() }}</div>
@endif

{{--
    DIALOG FOTO (popup pratinjau ukuran besar)
    - id="photo-viewer" dibuka lewat openPhoto().
    - Tombol × menutup dialog (close()).
    - Gambar memakai object-contain agar tidak terpotong.
--}}
<dialog id="photo-viewer" class="m-auto max-h-[92vh] max-w-[92vw] overflow-visible rounded-2xl border-0 bg-transparent p-0 shadow-none"><div class="relative"><button type="button" onclick="document.getElementById('photo-viewer').close()" aria-label="Tutup foto" class="absolute -right-3 -top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-xl text-slate-700 shadow">×</button><img id="photo-viewer-image" src="" alt="Foto lampiran ukuran besar" class="max-h-[88vh] max-w-[90vw] rounded-xl bg-white object-contain shadow-2xl"></div></dialog>

<script>
// TOGGLE FILTER: membuka/menyembunyikan panel filter, sekaligus memperbarui aria-expanded.
function toggleFilters() {
    const panel = document.getElementById('filter-panel');
    const button = document.getElementById('filter-toggle');
    panel.classList.toggle('hidden');
    button.setAttribute('aria-expanded', String(!panel.classList.contains('hidden')));
}

// OPEN PHOTO: mengisi src gambar di dialog lalu menampilkannya (showModal()).
function openPhoto(url) {
    document.getElementById('photo-viewer-image').src = url;
    document.getElementById('photo-viewer').showModal();
}
</script>
@endsection
