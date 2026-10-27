<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel complaints dasar dan menghubungkan setiap data ke pemiliknya. */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id(); // Kunci utama BIGINT yang bertambah otomatis.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Kunci asing ke users.id.
            $table->string('title'); // Ringkasan singkat untuk tabel daftar.
            $table->string('category'); // Teks kategori, dipertahankan untuk kompatibilitas data lama.
            $table->text('description'); // Isi lengkap pengaduan.
            $table->string('location')->nullable(); // Lokasi kejadian (opsional).
            $table->string('status')->default('Menunggu'); // Status alur penanganan.
            $table->timestamps(); // Kolom created_at dan updated_at.
        });
    }

    /** Menghapus tabel complaints saat migrasi dibatalkan. */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
