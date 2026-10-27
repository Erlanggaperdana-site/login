{{--
    ============================================================================
    DASHBOARD ADMIN  (route GET /dashboard/admin)
    ============================================================================
    Data dari DashboardController::admin():
    - $totalComplaints, $pendingComplaints, $totalUser, $totalCategories : angka ringkasan.
    - $recentComplaints : daftar pengaduan terbaru (10 per halaman).

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Warna kartu ringkasan ....... array $tone pada baris kartu statistik
    - Jumlah kartu per baris ..... grid xl:grid-cols-4 / sm:grid-cols-2
    - Warna judul/kicker .......... text-brand-600
    - Tabel pengaduan ............ cari "TABEL PENGADUAN TERBARU"
    - Tombol "Lihat pengaduan" ... cari "TOMBOL AKSI"
    - Warna status ................ bg-amber-50 text-amber-700
    - Menambah kartu statistik ... tambah satu item array pada @foreach kartu
    - Mengubah angka statistik ... DashboardController::admin() (User::count(), Complaint::count(), dll)
    - Mengubah jumlah baris tabel  DashboardController::admin() -> paginate(10)
    - Mengubah kolom tabel ....... cari "KOLOM TABEL"
    - Warna badge per status ..... cari "CATATAN WARNA STATUS"

    CARA KERJA DATA:
    Controller mengirim variabel ($totalComplaints, $pendingComplaints, $totalUser,
    $totalCategories, $recentComplaints) lalu view menampilkannya dengan {{ $variabel }}.
--}}
@extends('layouts.dashboard')
@section('title','Dashboard')
@section('content')

{{-- KEPALA HALAMAN: label kecil "Ringkasan", judul, deskripsi, dan tombol ke daftar pengaduan. --}}
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="mb-2 text-sm font-medium text-brand-600">Ringkasan</p>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard</h2>
        <p class="mt-1 text-sm text-slate-500">Pantau pengaduan terbaru yang masuk.</p>
    </div>

    {{-- TOMBOL AKSI: tampilan tombol putih bergaris. Ganti bg-white / border-slate-200 untuk warna lain. --}}
    <a href="{{ route('complaints.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat pengaduan</a>
</div>

{{--
    KARTU STATISTIK (4 kotak ringkasan)
    Setiap item array berisi: [label, nilai, class warna badge].
    - "grid gap-4 sm:grid-cols-2 xl:grid-cols-4":
        * HP          : 1 kolom
        * tablet (sm) : 2 kolom
        * desktop(xl) : 4 kolom
      Ganti angka 4 -> 3 bila ingin 3 kartu per baris.
    - Warna badge diatur di elemen ketiga:
        bg-brand-50 text-brand-700   (ungu)
        bg-amber-50 text-amber-700   (kuning)
        bg-sky-50 text-sky-700       (biru)
        bg-emerald-50 text-emerald-700 (hijau)

    MENAMBAH KARTU BARU:
    Tambahkan satu pasang ['Label', $nilai, 'bg-warna text-warna'] di dalam array @foreach.
    Contoh: ['Selesai', $selesai, 'bg-emerald-50 text-emerald-700'].
    Jangan lupa kirim $selesai dari DashboardController::admin().

    MENGUBAH URUTAN KARTU:
    Cukup tukar posisi item di dalam array @foreach.
--}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['Total pengaduan',$totalComplaints,'bg-brand-50 text-brand-700'],['Menunggu',$pendingComplaints,'bg-amber-50 text-amber-700'],['Total pengguna',$totalUser,'bg-sky-50 text-sky-700'],['Kategori',$totalCategories,'bg-emerald-50 text-emerald-700']] as [$label,$value,$tone])
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $tone }}">●</span>
        </div>
        <p class="mt-4 text-3xl font-bold text-slate-900">{{ $value }}</p>
    </div>
    @endforeach
</div>

{{-- PANEL DAFTAR PENGADUAN TERBARU: judul panel + tautan "Semua pengaduan". --}}
<section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
        <div>
            <h3 class="font-semibold text-slate-900">Pengaduan terbaru</h3>
            <p class="mt-0.5 text-xs text-slate-500">Hasil pengaduan yang dikirim masuk ke sini.</p>
        </div>
        <a href="{{ route('complaints.index') }}" class="text-sm font-semibold text-brand-700">Semua pengaduan</a>
    </div>

{{-- Keadaan kosong: tampil bila belum ada pengaduan sama sekali. --}}
@if($recentComplaints->isEmpty())<div class="px-6 py-14 text-center text-sm text-slate-500">Belum ada pengaduan yang masuk.</div>

{{--
    TABEL PENGADUAN TERBARU
    KOLOM TABEL (urutan <th> menentukan urutan tampilan):
    1. Pengaduan  -> $complaint->title
    2. Pelapor    -> $complaint->user?->name ?? $complaint->sender_name
    3. Kategori   -> $complaint->categoryRecord?->name ?? $complaint->category
    4. Status     -> $complaint->status
    5. Tanggal    -> $complaint->created_at->format('d M Y')

    MENAMBAH KOLOM: tambah <th> di <thead> dan <td> yang sesuai di <tbody>.
    MENGHAPUS KOLOM : hapus pasangan <th> dan <td>-nya.
    MENGUBAH LEBAR  : tambah class w-* pada <th>, atau biarkan otomatis.

    CATATAN WARNA STATUS:
    Badge status di bawah saat ini SELALU kuning (bg-amber-50 text-amber-700).
    Jika ingin warna berbeda per status, ganti span menjadi kondisi, contoh:
    @if($complaint->status === 'Selesai') text-emerald-700 @elseif($complaint->status === 'Diproses') text-sky-700 @endif
--}}
@else<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-6 py-3 font-semibold">Pengaduan</th>
                <th class="px-6 py-3 font-semibold">Pelapor</th>
                <th class="px-6 py-3 font-semibold">Kategori</th>
                <th class="px-6 py-3 font-semibold">Status</th>
                <th class="px-6 py-3 font-semibold">Tanggal</th>
            </tr>
        </thead>
            <tbody class="divide-y divide-slate-100">@foreach($recentComplaints as $complaint)
                <tr>
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $complaint->title }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $complaint->user?->name ?? $complaint->sender_name }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $complaint->categoryRecord?->name ?? $complaint->category }}</td>
                    <td class="px-6 py-4"><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $complaint->status === 'Menunggu' ? 'Menunggu ditinjau' : $complaint->status }}</span></td><td class="px-6 py-4 text-slate-500">{{ $complaint->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- PAGINASI: tombol pindah halaman (bawaan Laravel, $recentComplaints->links()). --}}
<div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $recentComplaints->links() }}</div>@endif
</section>
@endsection
