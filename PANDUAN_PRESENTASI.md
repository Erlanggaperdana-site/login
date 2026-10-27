# Panduan presentasi aplikasi Sistem Pengaduan

## Gambaran alur

Pengguna membuka `/login`. Setelah autentikasi, middleware membaca role dari record user. Role `admin` masuk ke dashboard admin yang memuat ringkasan dan tabel semua pengaduan. Role `customer` masuk ke dashboard customer yang dibatasi pada data pengaduannya sendiri. Pengaduan dibuat di halaman Pengaduan; daftar pengaduan menampilkan kartu dengan nama customer disamarkan. Admin mengelola kategori dan data user.

## Halaman dan route

| Method dan URL | Route name | Akses | Kegunaan |
|---|---|---|---|
| `GET /login`, `POST /login` | `login` | Guest | Form dan proses autentikasi |
| `GET /register`, `POST /register` | `register` | Guest | Pendaftaran customer |
| `GET /dashboard/admin` | `dashboard.admin` | Admin | Ringkasan dan tabel pengaduan |
| `GET /dashboard/customer` | `dashboard.customer` | Customer | Ringkasan pengaduan milik sendiri |
| `GET /pengaduan` | `complaints.index` | User login | Kartu pengaduan dan filter |
| `GET /pengaduan/buat`, `POST /pengaduan` | `complaints.create`, `complaints.store` | User login | Form dan penyimpanan pengaduan |
| `/kategori` | `categories.*` | Admin | CRUD kategori |
| `/admin/users` | `admin.users.*` | Admin | CRUD user; tambah/edit memakai modal |
| `POST /logout` | `logout` | User login | Mengakhiri sesi |

Sumber route: `routes/web.php`. Group `guest` hanya untuk login/daftar. Group `auth` melindungi dashboard dan data. Middleware `role:admin` membatasi administrasi; middleware `role:customer` membatasi dashboard customer. Route `/` mengarahkan guest ke `/login` dan user login ke dashboard berdasarkan role.

## Form pengaduan dan tipe data

| Input | Nama field | Tipe yang divalidasi | Aturan |
|---|---|---|---|
| Nama customer | `sender_name` | string | Wajib, maksimal 160 karakter |
| Nomor meja | `table_number` | integer | Wajib, rentang 1–30; UI berupa tombol pilihan |
| Email | `sender_email` | string/email | Wajib, maksimal 255 karakter |
| No. HP | `sender_phone` | string | Wajib, maksimal 30 karakter |
| Kategori | `category_id` | integer/foreign key | Wajib dan harus ada di tabel `categories` |
| Isi pengaduan | `message` | string/text | Wajib, maksimal 5000 karakter |
| Lampiran | `attachments` | array file | Opsional, maksimal 5 file; tiap file gambar jpg/jpeg/png/webp maksimal 5120 KB |

Validasi server berada di `ComplaintController::store()`. Pemeriksaan JavaScript di view memberi umpan balik langsung, tetapi validasi Laravel tetap menjadi aturan utama. File disimpan di disk `public`; database menyimpan array JSON nama/path lampiran. Status awal berisi string `Menunggu`. Nama pada kartu menggunakan accessor `masked_sender_name` di model `Complaint`.

## File kode penting

- `app/Http/Controllers/AuthController.php`: menampilkan login/daftar, memvalidasi kredensial, membuat user customer, memulai dan mengakhiri session. Request berisi input HTTP; hasil validasi berupa array; respons berupa `View` atau `RedirectResponse`.
- `app/Http/Controllers/DashboardController.php`: menyiapkan angka hitungan integer dan koleksi/paginator model untuk view admin/customer. Query dashboard customer dimulai dari relasi user yang sedang login.
- `app/Http/Controllers/ComplaintController.php`: `index()` mengambil paginator pengaduan + kategori; `create()` menyediakan kategori; `store()` memvalidasi, menyimpan berkas, dan membuat record pengaduan.
- `app/Http/Controllers/CategoryController.php` dan `UserController.php`: operasi kategori dan user; validasi menjaga format dan keunikan nilai.
- `app/Http/Middleware/RoleMiddleware.php`: parameter variadik `string ...$roles` adalah daftar role yang diizinkan; `in_array(..., true)` membandingkan secara ketat; akses yang tidak cocok menghasilkan HTTP 403.
- `bootstrap/app.php`: mendaftarkan alias middleware `role` dan tujuan redirect guest ke halaman login.
- `routes/web.php`: URL, HTTP method, nama route, dan middleware yang menjaga akses.
- `app/Models/User.php`: model tabel `users`, konstanta role bertipe string, autentikasi Laravel, serta relasi `hasMany` ke pengaduan.
- `app/Models/Complaint.php`: model tabel `complaints`; `$fillable` adalah daftar kolom yang boleh diisi mass assignment; `$casts['attachments'] = 'array'` mengubah JSON menjadi array PHP; relasi `belongsTo` menuju user dan kategori.
- `app/Models/Category.php`: model kategori dan relasi `hasMany` ke pengaduan.
- `database/migrations/`: skema database yang dapat dilacak versinya. Migration nomor meja menggunakan unsigned tiny integer karena nilainya 1–30.
- `resources/views/layouts/dashboard.blade.php`: kerangka tampilan, sidebar, urutan navigasi, dan area konten.
- `resources/views/dashboard/admin.blade.php`: kartu ringkasan dan tabel pengaduan terbaru.
- `resources/views/dashboard/customer.blade.php`: dashboard terbatas pada pengaduan customer yang sedang masuk.
- `resources/views/complaints/create.blade.php`: form pengaduan, pilihan meja, multi upload, dan petunjuk validasi.
- `resources/views/complaints/index.blade.php`: kartu pengaduan, sensor nama, lampiran, dan tombol buka/tutup filter.
- `resources/views/dashboard/users/index.blade.php`: daftar user dan dialog modal untuk tambah/edit.
- `.env.example`: contoh nama environment variable; `.env` lokal menyimpan nilai rahasia dan tidak untuk dipresentasikan/dibagikan.

## Konfigurasi environment

`APP_NAME` nama aplikasi, `APP_ENV` lingkungan (`local` saat pengembangan), `APP_DEBUG` menampilkan detail error saat lokal (matikan di production), dan `APP_URL` alamat aplikasi. `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, serta `DB_PASSWORD` menunjuk database. `SESSION_DRIVER` mengatur penyimpanan sesi login. `FILESYSTEM_DISK=public` memilih disk untuk upload yang perlu ditampilkan. Nilai tiap mesin disimpan dalam `.env`; `.env.example` berisi placeholder. Jangan menampilkan password, app key, atau kredensial asli saat presentasi.

## Perintah yang berguna

```bash
php artisan migrate --seed       # Jalankan migration dan data awal
php artisan storage:link          # Buat public/storage menuju storage/app/public
php artisan route:list            # Lihat route dan middleware
php artisan migrate:status        # Lihat status migration
php artisan optimize:clear         # Bersihkan cache konfigurasi/route/view
php artisan serve                  # Jalankan server pengembangan
```

## Catatan keamanan dan demonstrasi

Seed admin untuk demo lokal: `admin@gmail.com` / `admin123`; ganti kredensial tersebut sebelum deployment. Form user serta form tambah/ubah kategori berada di modal supaya halaman daftar tetap ringkas. Upload menerima hanya gambar yang divalidasi backend; batas `max:5120` Laravel dihitung dalam kilobyte per file. Nama customer pada kartu disamarkan, sementara admin dapat melihat detail melalui halaman administrasi pengaduan yang sesuai.

Pratinjau lampiran pada form muncul setelah foto dipilih. Pada kartu pengaduan, ukuran thumbnail mengikuti jumlah foto: 1 foto 100×100 px, 2 foto 64×64 px, 3 foto 56×56 px, 4 foto 48×48 px, dan 5 foto 44×44 px. Jarak antar-thumbnail 4 px. Klik foto untuk melihatnya dalam ukuran besar.
