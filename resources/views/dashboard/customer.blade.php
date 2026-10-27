{{--
    ============================================================================
    DASHBOARD CUSTOMER  (route GET /dashboard/customer)
    ============================================================================
    Data dari DashboardController::customer() — hanya milik akun yang login:
    - $totalComplaints, $pendingComplaints, $resolvedComplaints : angka ringkasan.
    - $recentComplaints : enam pengaduan terbaru milik customer tersebut.

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Sapaan nama pengguna ........ {{ auth()->user()->name }}
    - Tombol "+ Buat pengaduan" ... cari "TOMBOL BUAT PENGADUAN" (warna bg-brand-600)
    - Jumlah kartu ringkasan ...... grid sm:grid-cols-3
    - Warna kartu ringkasan ....... array $tone (brand/amber/emerald)
    - Daftar pengaduan terbaru .... cari "DAFTAR PENGADUAN TERBARU"
    - Mengubah sapaan "Halo, ..." . cari "SAPAAAN CUSTOMER"
    - Menambah kartu ringkasan .... tambah item array pada @foreach kartu
    - Mengubah jumlah pengaduan .. DashboardController::customer() -> take(6)
    - Mengubah status badge ....... cari "CATATAN WARNA STATUS"

    PENTING: data di halaman ini hanya milik customer yang login, karena query
    dimulai dari auth()->user()->complaints() di DashboardController::customer().
--}}
@extends('layouts.dashboard')
@section('title','Dashboard')
@section('content')

{{--
    KEPALA HALAMAN: sapaan customer + tombol membuat pengaduan baru.
    SAPAAAN CUSTOMER: teks "Halo, {{ auth()->user()->name }}" bisa diganti di sini.
    Tombol "+ Buat pengaduan" mengarah ke route complaints.create.
--}}
<div class="mb-7 flex flex-wrap items-end justify-between gap-4"><div><p class="mb-2 text-sm font-medium text-brand-600">Ringkasan</p><h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Halo, {{ auth()->user()->name }}</h2><p class="mt-1 text-sm text-slate-500">Pantau perkembangan pengaduan Anda.</p></div>

    {{-- TOMBOL BUAT PENGADUAN: bg-brand-600 (ungu) -> hover:bg-brand-700 (ungu tua). --}}
    <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Buat pengaduan</a>
</div>

{{--
    KARTU STATISTIK CUSTOMER (3 kotak)
    - "grid gap-4 sm:grid-cols-3" = 1 kolom di HP, 3 kolom di tablet/desktop.
      Jika menambah kartu ke-4, ganti sm:grid-cols-3 menjadi sm:grid-cols-4.
    - Warna badge: ungu (brand), kuning (amber), hijau (emerald).
    - Setiap item array berisi [label, nilai, class warna badge].
    - Angka berasal dari DashboardController::customer().
--}}
<div class="grid gap-4 sm:grid-cols-3">@foreach([['Total pengaduan',$totalComplaints,'bg-brand-50 text-brand-700'],['Menunggu',$pendingComplaints,'bg-amber-50 text-amber-700'],['Selesai',$resolvedComplaints,'bg-emerald-50 text-emerald-700']] as [$label,$value,$tone])<div class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><span class="rounded-lg px-2.5 py-1 text-xs {{ $tone }}">●</span></div><p class="mt-4 text-3xl font-bold text-slate-900">{{ $value }}</p></div>@endforeach</div>

{{-- PANEL DAFTAR PENGADUAN TERBARU milik customer yang login. --}}
<section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white"><div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6"><h3 class="font-semibold text-slate-900">Pengaduan terbaru</h3><a href="{{ route('complaints.index') }}" class="text-sm font-semibold text-brand-700">Lihat semua</a></div>

{{-- Keadaan kosong: belum ada pengaduan yang dikirim customer ini. --}}
@if($recentComplaints->isEmpty())<div class="px-6 py-14 text-center text-sm text-slate-500">Belum ada pengaduan yang dikirim.</div>

{{--
    DAFTAR PENGADUAN TERBARU: baris berisi judul, kategori + tanggal, dan badge status.
    CATATAN WARNA STATUS: badge saat ini selalu kuning (bg-amber-50 text-amber-700).
    Ingin beda warna per status? Ganti span dengan kondisi:
    @if($complaint->status === 'Selesai') bg-emerald-50 text-emerald-700
    @elseif($complaint->status === 'Diproses') bg-sky-50 text-sky-700
    @else bg-amber-50 text-amber-700 @endif
    Ubah juga jumlah data yang tampil di DashboardController::customer() (take(6)).
--}}
@else<div class="divide-y divide-slate-100">@foreach($recentComplaints as $complaint)<div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6"><div><p class="font-medium text-slate-900">{{ $complaint->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $complaint->categoryRecord?->name ?? $complaint->category }} · {{ $complaint->created_at->format('d M Y') }}</p></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $complaint->status === 'Menunggu' ? 'Menunggu ditinjau' : $complaint->status }}</span></div>@endforeach</div>@endif</section>
@endsection
