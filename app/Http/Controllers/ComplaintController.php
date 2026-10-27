<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Menampilkan kartu pengaduan dan menerapkan filter kategori atau status bila dipilih pengguna.
     *
     * @param Request $request Permintaan HTTP yang membawa filter kategori/status dari URL.
     * @return View Daftar pengaduan berhalaman beserta pilihan kategori untuk filter.
     */
    public function index(Request $request): View
    {
        // Memuat relasi user dan kategori di awal agar query tidak berulang untuk tiap kartu.
        $query = Complaint::with(['user', 'categoryRecord'])->latest();

        // Filter hanya dijalankan bila parameter benar-benar diisi, sehingga tampilan default menampilkan semua data.
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // withQueryString menjaga parameter filter tetap ada saat pengguna pindah halaman.
        return view('complaints.index', [
            'complaints' => $query->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** Menampilkan form pengiriman pengaduan beserta daftar kategori yang aktif. */
    public function create(): View
    {
        return view('complaints.create', ['categories' => Category::orderBy('name')->get()]);
    }

    /**
     * Memvalidasi isian customer, menyimpan maksimal lima foto, lalu membuat data pengaduan.
     *
     * @param Request $request Isian form dan berkas foto yang diunggah.
     * @return RedirectResponse Kembali ke daftar pengaduan setelah data tersimpan.
     */
    public function store(Request $request): RedirectResponse
    {
        // Aturan validasi: meja 1–30, maksimal 5 foto, tiap foto berupa gambar maksimal 5 MB.
        $data = $request->validate([
            'sender_name' => ['required', 'string', 'max:160'],
            'table_number' => ['required', 'integer', 'between:1,30'],
            'sender_email' => ['required', 'email', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'category_id' => ['required', 'exists:categories,id'],
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        /** @var Category $category Kategori yang dipilih pada form. */
        $category = Category::findOrFail($data['category_id']);

        // Setiap foto disimpan ke folder storage/app/public/complaints, lalu metadata-nya dicatat.
        /** @var list<array{path:string,name:string}> $attachmentData Metadata foto yang sudah tersimpan. */
        $attachmentData = collect($request->file('attachments', []))
            ->map(fn ($photo): array => [
                'path' => $photo->store('complaints', 'public'),
                'name' => $photo->getClientOriginalName(),
            ])
            ->all();

        // Judul singkat diambil dari awal isi pengaduan agar mudah ditampilkan pada tabel/kartu.
        /** @var string $title Judul singkat turunan dari pesan customer. */
        $title = Str::limit(Str::squish($data['message']), 155);

        // Data disimpan melalui relasi milik user yang sedang login, sehingga user_id terisi otomatis.
        $request->user()->complaints()->create([
            'title' => $title,
            'category' => $category->name,
            'category_id' => $category->id,
            'description' => $data['message'],
            'message' => $data['message'],
            'sender_name' => $data['sender_name'],
            'sender_email' => $data['sender_email'],
            'sender_phone' => $data['sender_phone'],
            'table_number' => $data['table_number'],
            'attachments' => $attachmentData,
            'status' => 'Menunggu',
        ]);

        return redirect()->route('complaints.index')->with('status', 'Pengaduan berhasil dikirim.');
    }

    /** Memperbarui status pengaduan; hanya dapat diakses admin karena dibatasi middleware role:admin. */
    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        // Status dibatasi pada tiga nilai yang dikenal sistem agar data tetap konsisten.
        $data = $request->validate([
            'status' => ['required', 'string', 'in:Menunggu,Diproses,Selesai'],
        ]);

        $complaint->update(['status' => $data['status']]);

        return redirect()->route('complaints.index')->with('status', 'Status pengaduan berhasil diperbarui.');
    }
}
