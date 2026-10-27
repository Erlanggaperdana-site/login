<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Menambahkan kolom nomor meja yang dipakai customer restoran. */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            // Tipe bilangan kecil tanpa tanda sudah cukup untuk rentang 1–30.
            $table->unsignedTinyInteger('table_number')->nullable()->after('sender_phone');
        });
    }

    /** Menghapus kolom nomor meja saat migrasi dibatalkan. */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            $table->dropColumn('table_number');
        });
    }
};
