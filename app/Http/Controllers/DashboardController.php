<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Menyiapkan angka ringkasan dan tabel berhalaman untuk dashboard admin. */
    public function admin(): View
    {
        // Setiap penghitung dihitung langsung dari database agar selalu sesuai data terbaru.
        return view('dashboard.admin', [
            'totalUser' => User::count(),
            'totalCustomer' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'totalComplaints' => Complaint::count(),
            'pendingComplaints' => Complaint::where('status', 'Menunggu')->count(),
            'totalCategories' => Category::count(),
            'recentComplaints' => Complaint::with(['user', 'categoryRecord'])->latest()->paginate(10),
        ]);
    }

    /** Menyiapkan dashboard customer yang hanya menampilkan data milik pengguna yang sedang login. */
    public function customer(): View
    {
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $complaints Query pengaduan milik customer ini. */
        $complaints = auth()->user()->complaints();

        // clone dipakai agar filter pada satu penghitung tidak mengubah query untuk penghitung berikutnya.
        return view('dashboard.customer', [
            'totalComplaints' => (clone $complaints)->count(),
            'pendingComplaints' => (clone $complaints)->where('status', 'Menunggu')->count(),
            'resolvedComplaints' => (clone $complaints)->where('status', 'Selesai')->count(),
            'recentComplaints' => (clone $complaints)->with('categoryRecord')->latest()->take(6)->get(),
        ]);
    }
}
