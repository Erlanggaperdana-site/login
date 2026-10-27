<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel categories serta menambah kolom pengirim, pesan, dan foto pada complaints. */
    public function up(): void
    {
        // Tabel kategori yang dapat dikelola admin melalui menu Kategori.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Enam kategori awal dimasukkan langsung agar form pengaduan bisa langsung dipakai.
        $now = now();
        DB::table('categories')->insert(array_map(fn (string $name) => [
            'name' => $name,
            'description' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Fasilitas Umum', 'Lainnya']));

        Schema::table('complaints', function (Blueprint $table) {
            // user_id dibuat opsional untuk mendukung data lama yang dikirim tanpa akun.
            $table->foreignId('user_id')->nullable()->change();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->json('attachments')->nullable(); // Daftar foto disimpan dalam format JSON.
            // Bila kategori dihapus, kolom ini menjadi null dan teks kategori lama tetap ada.
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }

    /** Membatalkan perluasan skema ini beserta tabel categories. */
    public function down(): void
    {
        // Data tanpa pemilik dihapus lebih dulu karena kolom user_id akan dikembalikan menjadi wajib.
        DB::table('complaints')->whereNull('user_id')->delete();
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['sender_name', 'sender_email', 'sender_phone', 'message', 'attachments', 'category_id']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
        Schema::dropIfExists('categories');
    }
};
