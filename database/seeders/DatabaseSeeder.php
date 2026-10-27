<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal aplikasi.
     * Perintah: php artisan db:seed
     */
    public function run(): void
    {
        // Akun admin awal untuk masuk ke dashboard administrator.
        // updateOrCreate menjaga agar menjalankan seeder berulang kali tidak membuat akun ganda.
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'admin123', // Otomatis di-hash oleh cast "hashed" pada model User.
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
