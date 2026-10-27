{{--
    ============================================================================
    HALAMAN DATA USER  (khusus admin, route GET /admin/users)
    ============================================================================
    - Daftar user berhalaman (10 per halaman) dari UserController::index().
    - Dialog "Tambah user" mengirim POST ke 'admin.users.store'.
    - Dialog "Edit user" mengirim PUT ke 'admin.users.update';
      password kosong berarti password lama tidak diganti.
    - Tombol "Hapus" mengirim DELETE ke 'admin.users.destroy' + konfirmasi.
    - Jika validasi gagal, dialog yang sesuai dibuka kembali otomatis.

    PANDUAN CEPAT UNTUK WAWANCARA:
    - Judul halaman ............... cari "KEPALA HALAMAN"
    - Tombol "+ Tambah user" ...... bg-brand-600, hover:bg-brand-700
    - Modal tambah user ........... cari "DIALOG TAMBAH USER"
    - Daftar/baris user ........... cari "DAFTAR USER"
    - Modal edit per user ......... cari "DIALOG EDIT USER"
    - Pilihan role ................ select name="role" (customer / admin)
    - Tombol hapus + konfirmasi ... cari "TOMBOL HAPUS"
    - Pagination .................. {{ $users->links() }}
    - Mengubah aturan password ... UserController::store()/update() -> Password::min(8)
    - Menambah field user ........ input baru + validasi controller + kolom migration/$fillable
    - Mengubah pilihan role ...... <select name="role"> + Rule::in(...) di controller
    - Jumlah user per halaman .... UserController::index() -> paginate(10)
    - Lebar modal ................ max-w-lg pada <dialog>
    - Kenapa admin tidak bisa menghapus/menurunkan dirinya sendiri?
      Dicegah di UserController::update() dan UserController::destroy().

    ALUR DATA: UserController::index() mengirim $users (paginator, 10 per halaman).
--}}
@extends('layouts.dashboard')
@section('title', 'Data User')
@section('content')

{{-- KEPALA HALAMAN: label "Administrasi", judul, dan tombol tambah user. --}}
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div><p class="mb-2 text-sm font-medium text-brand-600">Administrasi</p><h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Data user</h2></div>

    {{-- TOMBOL TAMBAH USER: membuka dialog #create-user-modal lewat showModal(). --}}
    <button type="button" onclick="document.getElementById('create-user-modal').showModal()" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Tambah user</button>
</div>

{{-- PESAN SUKSES: setelah tambah/edit/hapus user berhasil. --}}
@if(session('status'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif

{{-- PESAN ERROR KHUSUS dari controller (misal email sudah dipakai). --}}
@if(session('error'))<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

{{-- PESAN ERROR VALIDASI form. --}}
@if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

{{--
    DIALOG TAMBAH USER
    - id="create-user-modal" : dibuka oleh tombol "+ Tambah user".
    - action route('admin.users.store') : POST /admin/users.
    - p-6 / space-y-4 : jarak antar field di dalam modal.
    - Input password bertipe "password" agar karakter disamarkan.
    - "password_confirmation" wajib sama dengan "password" (aturan confirmed).

    MENGUBAH ATURAN PASSWORD:
    - Panjang minimal diatur di UserController::store()/update() -> Password::min(8).
    - Menambah angka/huruf: Password::min(8)->numbers()->letters().
    - Input confirmation HARUS tetap ada jika aturan 'confirmed' dipakai.
--}}
<dialog id="create-user-modal" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-0 shadow-xl">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 p-6">
        @csrf
        <div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-slate-900">Tambah user</h3><p class="text-sm text-slate-500">Isi data akun dan pilih peran.</p></div><button type="button" onclick="this.closest('dialog').close()" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100" aria-label="Tutup">✕</button></div>
        <div><label for="new-name" class="mb-1 block text-sm font-medium text-slate-700">Nama</label><input id="new-name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none"></div>
        <div><label for="new-email" class="mb-1 block text-sm font-medium text-slate-700">Email</label><input id="new-email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none"></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label for="new-password" class="mb-1 block text-sm font-medium text-slate-700">Password</label><input id="new-password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none"></div><div><label for="new-password-confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi</label><input id="new-password-confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none"></div></div>

        {{-- PILIHAN ROLE: customer (pengguna biasa) atau admin (bisa kelola data). --}}
        <div><label for="new-role" class="mb-1 block text-sm font-medium text-slate-700">Role</label><select id="new-role" name="role" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="customer">Customer</option><option value="admin">Admin</option></select></div>

        {{-- TOMBOL BAWAH MODAL: Batal menutup dialog, Simpan mengirim form. --}}
        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="this.closest('dialog').close()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan user</button></div>
    </form>
</dialog>

{{-- PANEL DAFTAR USER + jumlah total user. --}}
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="border-b border-slate-100 px-5 py-4"><h3 class="font-semibold text-slate-900">Daftar user</h3><p class="mt-0.5 text-xs text-slate-500">{{ $users->total() }} user</p></div>

    {{-- DAFTAR USER: setiap baris menampilkan nama, email, role, serta tombol Edit/Hapus. --}}
    <div class="divide-y divide-slate-100">
        @forelse($users as $user)
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <div><p class="font-medium text-slate-900">{{ $user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $user->email }} · <span class="capitalize">{{ $user->role }}</span></p></div>

                {{-- TOMBOL AKSI: Edit membuka modal sesuai id user; Hapus mengirim DELETE. --}}
                <div class="flex gap-2"><button type="button" onclick="document.getElementById('edit-user-{{ $user->id }}').showModal()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Edit</button>

                    {{-- TOMBOL HAPUS: warna teks merah + konfirmasi sebelum menghapus. --}}
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')<button title="Hapus user" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50">Hapus</button></form>
                </div>
            </div>

            {{--
                DIALOG EDIT USER (satu per user)
                - id unik memakai {{ $user->id }} agar tombol Edit membuka user yang benar.
                - @method('PUT') : mengirim PUT ke route admin.users.update.
                - Password boleh dikosongkan: jika kosong, password lama tidak diubah.
            --}}
            <dialog id="edit-user-{{ $user->id }}" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-0 shadow-xl">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 p-6">@csrf @method('PUT')
                    <div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-slate-900">Edit user</h3><p class="text-sm text-slate-500">Perbarui profil atau role.</p></div><button type="button" onclick="this.closest('dialog').close()" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100" aria-label="Tutup">✕</button></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Nama</label><input name="name" value="{{ $user->name }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Email</label><input name="email" type="email" value="{{ $user->email }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>

                    {{-- Password baru (opsional) + konfirmasi. --}}
                    <div class="grid gap-4 sm:grid-cols-2"><input name="password" type="password" placeholder="Password baru (opsional)" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"><input name="password_confirmation" type="password" placeholder="Konfirmasi password" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></div>

                    {{-- Role: @selected menandai pilihan yang sedang aktif. --}}
                    <select name="role" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="customer" @selected($user->role === 'customer')>Customer</option><option value="admin" @selected($user->role === 'admin')>Admin</option></select>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="this.closest('dialog').close()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Batal</button><button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button></div>
                </form>
            </dialog>
        @empty
            {{-- Keadaan kosong: belum ada user. --}}
            <p class="px-5 py-12 text-center text-sm text-slate-500">Belum ada user.</p>
        @endforelse
    </div>

    {{-- PAGINASI: 10 user per halaman (diatur di UserController::index). --}}
    <div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>
</section>

{{-- Jika validasi gagal, buka kembali modal edit (jika ada session edit_user) atau modal tambah. --}}
@if($errors->any())<script>document.getElementById(@json(session('edit_user') ? 'edit-user-'.session('edit_user') : 'create-user-modal')).showModal()</script>@endif
@endsection
