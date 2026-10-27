<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /** Menampilkan daftar kategori admin beserta jumlah pengaduan yang memakai tiap kategori. */
    public function index(): View
    {
        // withCount('complaints') menambahkan kolom complaints_count tanpa query tambahan per baris.
        return view('categories.index', [
            'categories' => Category::withCount('complaints')->orderBy('name')->get(),
        ]);
    }

    /** Memvalidasi lalu menyimpan kategori baru. */
    public function store(Request $request): RedirectResponse
    {
        /** @var array{name:string,description?:?string} $data Kolom kategori yang sudah divalidasi. */
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        Category::create($data);

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    /** Memvalidasi lalu memperbarui kategori yang dipilih melalui ID pada route. */
    public function update(Request $request, Category $category): RedirectResponse
    {
        /** @var array{name:string,description?:?string} $data Nilai pengganti yang sudah divalidasi. */
        $data = $request->validate([
            // ID kategori ini dikecualikan dari aturan unik agar nama sendiri tidak dianggap duplikat.
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $category->update($data);

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    /** Menghapus kategori; pengaduan lama tetap menyimpan teks kategorinya sebagai arsip. */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil dihapus.');
    }
}
