# Sistem Pengaduan

Aplikasi Laravel untuk login admin/customer dan pengelolaan pengaduan, menggunakan tampilan light mode dengan aksen ungu. Panduan alur dan kode untuk presentasi tersedia di [PANDUAN_PRESENTASI.md](PANDUAN_PRESENTASI.md).

## Halaman

| Halaman | URL | Akses |
|---|---|---|
| Masuk | `/login` | Guest |
| Daftar customer | `/register` | Guest |
| Dashboard admin | `/dashboard/admin` | Admin |
| Dashboard customer | `/dashboard/customer` | Customer |
| Pengaduan | `/pengaduan` | Admin dan customer |
| Buat pengaduan | `/pengaduan/buat` | Admin dan customer |
| Kategori | `/kategori` | Admin |
| Data user | `/admin/users` | Admin |

Pengaduan diisi pada halaman Pengaduan, sedangkan dashboard admin menampilkan ringkasan dan tabel hasil pengaduan terbaru. Customer hanya melihat ringkasan dan pengaduan miliknya pada dashboard. Daftar pengaduan memakai kartu dengan nama yang disamarkan; tombol **Filter** membuka/menutup panel filter.

Form pengaduan meminta nama customer, nomor meja 1–30, email, nomor HP, kategori, isi pengaduan, dan maksimal 5 foto (jpg/jpeg/png/webp) berukuran maksimal 5 MB per foto. File disimpan di disk `public` dalam `storage/app/public/complaints` dan diakses lewat `public/storage` setelah menjalankan `php artisan storage:link`.

## Menjalankan

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000/login. Akun admin seed: `admin@gmail.com` / `admin123`.

Konfigurasi lokal disimpan pada `.env`; jangan membagikan file itu karena dapat berisi kredensial. Gunakan `.env.example` sebagai template aman, lalu sesuaikan koneksi database dan jalankan `php artisan key:generate` bila menyiapkan instalasi baru.
