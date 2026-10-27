<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Halaman awal mengarahkan tamu ke /login dan pengguna masuk ke dashboard sesuai peran.
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isAdmin() ? 'dashboard.admin' : 'dashboard.customer');
});

// Route tamu: hanya boleh dibuka sebelum login, agar pengguna yang sudah masuk tidak membuka form lagi.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Semua dashboard dan route pengiriman pengaduan memerlukan sesi masuk yang sah.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Middleware role:admin / role:customer membatasi dashboard berdasarkan peran akun.
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('dashboard.admin');
    Route::get('/dashboard/customer', [DashboardController::class, 'customer'])->middleware('role:customer')->name('dashboard.customer');

    // Pengaduan: daftar, form pembuatan, dan proses simpan dapat diakses admin maupun customer.
    Route::get('/pengaduan', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/pengaduan/buat', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/pengaduan', [ComplaintController::class, 'store'])->name('complaints.store');

    // Pengelolaan kategori dan data user hanya dapat dibuka oleh admin.
    Route::middleware('role:admin')->group(function () {
        Route::put('/pengaduan/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.status');

        Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });
});
