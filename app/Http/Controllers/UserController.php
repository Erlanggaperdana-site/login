<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Menampilkan daftar user berurutan berdasarkan nama, 10 user per halaman. */
    public function index(): View
    {
        return view('dashboard.users.index', ['users' => User::orderBy('name')->paginate(10)]);
    }

    /** Memvalidasi lalu membuat user baru; password otomatis di-hash oleh model User. */
    public function store(Request $request): RedirectResponse
    {
        /** @var array{name:string,email:string,password:string,role:string} $data Kolom user baru yang sudah divalidasi. */
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
        ]);
        User::create($data);

        return back()->with('status', 'User berhasil ditambahkan.');
    }

    /** Memperbarui profil, password (opsional), dan role user yang dipilih berdasarkan ID. */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            // Email milik user ini dikecualikan agar tidak dianggap duplikat saat disimpan ulang.
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
        ]);
        // Bila validasi gagal, modal edit untuk user terkait dibuka kembali oleh halaman view.
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('edit_user', $user->id);
        }
        $data = $validator->validated();
        // Pengaman: admin tidak boleh menurunkan role akunnya sendiri agar tidak terkunci dari halaman admin.
        if ($user->is($request->user()) && $data['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }
        // Password kosong berarti password lama dipertahankan, bukan diganti dengan nilai kosong.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return back()->with('status', 'User berhasil diperbarui.');
    }

    /** Menghapus user lain, sekaligus mencegah admin menghapus akunnya sendiri. */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        $user->delete();

        return back()->with('status', 'User berhasil dihapus.');
    }
}
