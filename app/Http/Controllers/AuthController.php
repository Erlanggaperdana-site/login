<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** Menampilkan halaman login. Halaman ini hanya boleh dibuka oleh tamu (belum masuk). */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Memeriksa email dan password, membuat sesi login, lalu mengarahkan pengguna ke dashboard sesuai perannya.
     *
     * @param Request $request Berisi email, password, dan pilihan "ingat saya" yang bersifat opsional.
     * @return RedirectResponse Kembali ke form bila data salah, atau ke dashboard admin/customer bila berhasil.
     */
    public function login(Request $request): RedirectResponse
    {
        /** @var array{email:string,password:string} $credentials Data login yang sudah divalidasi. */
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Auth::attempt mencocokkan data dengan tabel users; bila gagal, pengguna dikembalikan ke form.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau password salah.']);
        }

        // Ganti ID sesi setelah login agar sesi lama tidak dapat dipakai ulang (mencegah session fixation).
        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->isAdmin() ? 'dashboard.admin' : 'dashboard.customer'));
    }

    /** Menampilkan halaman pendaftaran akun customer. */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** Membuat akun customer baru, langsung memasukkan pengguna ke sesi, lalu memperbarui ID sesi. */
    public function register(Request $request): RedirectResponse
    {
        /** @var array{name:string,email:string,password:string} $data Data pendaftaran yang sudah divalidasi. */
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        // Pendaftaran mandiri selalu menghasilkan role customer; role admin hanya dapat dibuat oleh admin.
        $user = User::create([...$data, 'role' => User::ROLE_CUSTOMER]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.customer');
    }

    /** Mengakhiri sesi login, menghapus data sesi, dan memperbarui token CSRF sebelum kembali ke halaman login. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
