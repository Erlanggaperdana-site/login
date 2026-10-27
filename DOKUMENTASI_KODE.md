# Dokumentasi Kode — Sistem Pengaduan

Dokumen ini menjelaskan **setiap fungsi** pada controller, model, middleware, seeder, migration, view, `routes/web.php`, dan `.env`, ditulis dengan bahasa yang mudah dipakai saat menjelaskan ke Kaprodi.

Aplikasi ini adalah sistem pengaduan dengan dua peran:

| Peran | Bisa melakukan |
|---|---|
| **Customer** | Daftar, login, membuat pengaduan, melihat daftar pengaduan, melihat dashboard pribadi |
| **Admin** | Semua yang bisa dilakukan customer, ditambah mengelola kategori, mengelola user, dan mengubah status pengaduan |

---

## 1. Alur Singkat Aplikasi

1. Pengunjung membuka `/` → diarahkan ke halaman **login** (belum masuk) atau **dashboard** sesuai peran (sudah masuk).
2. Customer mendaftar → akun tersimpan dengan role `customer` → langsung masuk dashboard customer.
3. Customer mengirim pengaduan → status awal `Menunggu`.
4. Admin melihat pengaduan → mengubah status menjadi `Diproses` lalu `Selesai`.
5. Admin dapat menambah, mengubah, dan menghapus **kategori** serta **user**.

---

## 2. Controller

### 2.1 `app/Http/Controllers/AuthController.php`
Menangani seluruh proses masuk, daftar, dan keluar.

| Fungsi | Penjelasan | Route yang memanggil |
|---|---|---|
| `showLogin()` | Menampilkan halaman login. | `GET /login` |
| `login()` | Memvalidasi email & password, membuat sesi, mengganti ID sesi, lalu mengarahkan ke dashboard admin/customer. Bila gagal, kembali ke form dengan pesan "Email atau password salah." | `POST /login` |
| `showRegister()` | Menampilkan halaman pendaftaran. | `GET /register` |
| `register()` | Memvalidasi nama, email (harus belum terpakai), dan password minimal 8 karakter. Akun baru **selalu** berperan customer, lalu langsung login. | `POST /register` |
| `logout()` | Mengakhiri sesi, menghapus data sesi, memperbarui token CSRF, kembali ke halaman login. | `POST /logout` |

**Poin yang perlu dijelaskan:** `session()->regenerate()` mencegah *session fixation*, dan `regenerateToken()` mencegah penyalahgunaan token CSRF.

---

### 2.2 `app/Http/Controllers/DashboardController.php`
Menyiapkan angka ringkasan pada dashboard.

| Fungsi | Penjelasan | Route |
|---|---|---|
| `admin()` | Menghitung total user, total customer, total pengaduan, pengaduan berstatus Menunggu, total kategori, serta menampilkan 10 pengaduan terbaru. | `GET /dashboard/admin` |
| `customer()` | Menghitung total pengaduan, Menunggu, dan Selesai **milik akun yang login saja**, serta menampilkan 6 pengaduan terbaru miliknya. | `GET /dashboard/customer` |

**Poin yang perlu dijelaskan:** `clone` dipakai agar filter pada satu penghitung tidak mengubah query untuk penghitung berikutnya.

---

### 2.3 `app/Http/Controllers/ComplaintController.php`
Inti fitur pengaduan.

| Fungsi | Penjelasan | Route |
|---|---|---|
| `index()` | Menampilkan kartu pengaduan dengan filter kategori dan status, 10 data per halaman. `with(['user','categoryRecord'])` mencegah query berulang (N+1). | `GET /pengaduan` |
| `create()` | Menampilkan form pengaduan beserta daftar kategori. | `GET /pengaduan/buat` |
| `store()` | Memvalidasi isian (nama, meja 1–30, email, no. HP, kategori, isi maks. 5000 karakter, maks. 5 foto berukuran maks. 5 MB), menyimpan foto ke `storage/app/public/complaints`, membuat judul otomatis dari isi pesan, lalu menyimpan data dengan status `Menunggu`. | `POST /pengaduan` |
| `updateStatus()` | Mengubah status pengaduan; hanya menerima nilai `Menunggu`, `Diproses`, atau `Selesai`. Hanya admin (dibatasi middleware). | `PUT /pengaduan/{id}/status` |

**Poin yang perlu dijelaskan:** data disimpan lewat `$request->user()->complaints()->create(...)` sehingga `user_id` terisi otomatis dan customer tidak bisa memalsukan pemilik data.

---

### 2.4 `app/Http/Controllers/CategoryController.php`
Pengelolaan kategori (khusus admin).

| Fungsi | Penjelasan | Route |
|---|---|---|
| `index()` | Menampilkan daftar kategori beserta jumlah pengaduan tiap kategori (`withCount`). | `GET /kategori` |
| `store()` | Memvalidasi lalu menyimpan kategori baru; nama harus unik. | `POST /kategori` |
| `update()` | Mengubah nama/deskripsi; pengecekan unik mengecualikan ID kategori itu sendiri. | `PUT /kategori/{id}` |
| `destroy()` | Menghapus kategori; pengaduan lama tetap menyimpan teks kategorinya. | `DELETE /kategori/{id}` |

---

### 2.5 `app/Http/Controllers/UserController.php`
Pengelolaan akun (khusus admin).

| Fungsi | Penjelasan | Route |
|---|---|---|
| `index()` | Daftar user berurutan nama, 10 per halaman. | `GET /admin/users` |
| `store()` | Membuat user baru (nama, email unik, password + konfirmasi minimal 8 karakter, role admin/customer). | `POST /admin/users` |
| `update()` | Memperbarui profil, role, dan password (opsional). Menolak admin menurunkan role akunnya sendiri. Password kosong berarti tidak diganti. | `PUT /admin/users/{id}` |
| `destroy()` | Menghapus user lain; admin tidak dapat menghapus akunnya sendiri. | `DELETE /admin/users/{id}` |

---

## 3. Model

### 3.1 `app/Models/User.php`
| Bagian | Penjelasan |
|---|---|
| `ROLE_ADMIN`, `ROLE_CUSTOMER` | Konstanta nilai role, dipakai di seluruh aplikasi agar tidak salah tulis. |
| `$fillable` | Kolom yang boleh diisi massal: `name`, `email`, `password`, `role`. |
| `$hidden` | `password` dan `remember_token` tidak ikut tampil saat data diubah ke JSON. |
| `casts()` | `password` otomatis di-hash saat diisi; `email_verified_at` dibaca sebagai tanggal. |
| `isAdmin()` | `true` bila role = `admin`. |
| `isCustomer()` | `true` bila role = `customer`. |
| `complaints()` | Relasi **one-to-many**: satu user punya banyak pengaduan. |

### 3.2 `app/Models/Complaint.php`
| Bagian | Penjelasan |
|---|---|
| `CATEGORIES` | Daftar label kategori bawaan (untuk data lama). |
| `$fillable` | Kolom yang boleh diisi massal. |
| `casts()` | `attachments` disimpan sebagai JSON dan dibaca sebagai array PHP. |
| `user()` | Relasi **belongsTo** ke tabel users (boleh null untuk data lama). |
| `categoryRecord()` | Relasi **belongsTo** ke tabel categories melalui `category_id`. |
| `getMaskedSenderNameAttribute()` | Accessor yang menyamarkan nama pelapor, mis. `Budi Santoso` → `B*** S***`, agar identitas tidak tampil penuh di ruang publik. Dipakai di view sebagai `$complaint->masked_sender_name`. |

### 3.3 `app/Models/Category.php`
| Bagian | Penjelasan |
|---|---|
| `$fillable` | Hanya `name` dan `description` yang boleh diisi massal. |
| `complaints()` | Relasi **one-to-many**: satu kategori dipakai banyak pengaduan. |

---

## 4. Middleware

### `app/Http/Middleware/RoleMiddleware.php`
| Bagian | Penjelasan |
|---|---|
| `handle()` | Memeriksa apakah user yang login memiliki role yang diminta route. Bila belum login atau role tidak cocok, permintaan dihentikan dengan **HTTP 403** dan halaman `errors/403.blade.php` ditampilkan. Pemakaian: `->middleware('role:admin')`. |

Pendaftaran alias `role` dilakukan di `bootstrap/app.php`.

---

## 5. Seeder

### `database/seeders/DatabaseSeeder.php`
| Bagian | Penjelasan |
|---|---|
| `run()` | Mengisi akun admin awal: `admin@gmail.com` / `admin123`, role `admin`. Memakai `updateOrCreate` sehingga aman dijalankan berulang tanpa membuat akun ganda. |

### `database/factories/UserFactory.php`
| Bagian | Penjelasan |
|---|---|
| `definition()` | Nilai bawaan user palsu untuk pengujian: nama acak, email unik, role customer. |
| `admin()` | Membuat user hasil factory dengan role admin. |
| `unverified()` | Membuat user yang email-nya belum diverifikasi. |

---

## 6. Migration

Migration dijalankan berurutan sesuai nama berkas (timestamp).

| Berkas | Fungsi `up()` | Fungsi `down()` |
|---|---|---|
| `0001_01_01_000000_create_users_table.php` | Membuat tabel `users` (akun), `password_reset_tokens`, dan `sessions` (penyimpan sesi login). | Menghapus ketiga tabel tersebut. |
| `2026_10_19_000000_add_role_to_users_table.php` | Menambah kolom `role` pada `users` dengan nilai awal `customer`. | Menghapus kolom `role`. |
| `2026_10_22_000000_create_complaints_table.php` | Membuat tabel `complaints` dasar: pemilik, judul, kategori, isi, lokasi, status. | Menghapus tabel `complaints`. |
| `2026_10_22_000001_create_categories_and_extend_complaints.php` | Membuat tabel `categories` + mengisi 6 kategori awal, lalu menambah kolom pengirim (`sender_*`), `message`, `attachments` (JSON), dan `category_id` pada `complaints`. | Menghapus kolom tambahan dan tabel `categories`. |
| `2026_10_23_000000_add_table_number_to_complaints.php` | Menambah kolom `table_number` (nomor meja 1–30). | Menghapus kolom `table_number`. |

**Poin yang perlu dijelaskan:** setiap migration punya pasangan `up()` (menjalankan perubahan) dan `down()` (membatalkannya), sehingga struktur database dapat dibangun dan dikembalikan secara konsisten.

---

## 7. View (Blade)

| Berkas | Penjelasan |
|---|---|
| `layouts/app.blade.php` | Kerangka halaman publik (login & daftar). Menyediakan `@yield('title')` dan `@yield('content')`. |
| `layouts/dashboard.blade.php` | Kerangka halaman setelah login: sidebar (menu Data User & Kategori hanya tampil untuk admin), header, area isi, footer, dan tombol keluar. |
| `auth/login.blade.php` | Form login (email, password, "ingat saya"), pesan error, tombol lihat password. |
| `auth/register.blade.php` | Form pendaftaran customer (nama, email, password). |
| `dashboard/admin.blade.php` | Dashboard admin: 4 kartu ringkasan dan tabel 10 pengaduan terbaru. |
| `dashboard/customer.blade.php` | Dashboard customer: 3 kartu ringkasan dan 6 pengaduan terbaru miliknya. |
| `complaints/index.blade.php` | Daftar pengaduan berbentuk kartu: filter kategori/status, pagination, foto lampiran yang bisa diperbesar, dan form ubah status khusus admin. |
| `complaints/create.blade.php` | Form pengaduan: nama, pemilih nomor meja 1–30, email, no. HP, kategori, isi, dan unggah maks. 5 foto dengan pratinjau. |
| `categories/index.blade.php` | Daftar kategori dengan dialog tambah/ubah dan tombol hapus. |
| `dashboard/users/index.blade.php` | Daftar user dengan dialog tambah/edit dan tombol hapus. |
| `errors/403.blade.php` | Halaman "Akses Ditolak" saat middleware role menolak akses. |
| `welcome.blade.php` | Halaman sambutan statis. |

**Poin yang perlu dijelaskan:** `@extends` memakai layout induk, `@section`/`@yield` mengisi bagian layout, `@csrf` menyisipkan token keamanan, dan `@if(auth()->user()->isAdmin())` menyembunyikan menu khusus admin.

---

## 8. `routes/web.php`

| Route | Method | Fungsi | Akses |
|---|---|---|---|
| `/` | GET | Mengarahkan ke login atau dashboard sesuai status & peran. | Semua |
| `/login` | GET | Menampilkan form login. | Tamu |
| `/login` | POST | Memproses login. | Tamu |
| `/register` | GET | Menampilkan form daftar. | Tamu |
| `/register` | POST | Memproses pendaftaran. | Tamu |
| `/logout` | POST | Keluar dari sesi. | Login |
| `/dashboard/admin` | GET | Dashboard admin. | Login + role admin |
| `/dashboard/customer` | GET | Dashboard customer. | Login + role customer |
| `/pengaduan` | GET | Daftar pengaduan. | Login |
| `/pengaduan/buat` | GET | Form buat pengaduan. | Login |
| `/pengaduan` | POST | Simpan pengaduan. | Login |
| `/pengaduan/{id}/status` | PUT | Ubah status pengaduan. | Login + role admin |
| `/kategori` | GET | Daftar kategori. | Login + role admin |
| `/kategori` | POST | Tambah kategori. | Login + role admin |
| `/kategori/{id}` | PUT | Ubah kategori. | Login + role admin |
| `/kategori/{id}` | DELETE | Hapus kategori. | Login + role admin |
| `/admin/users` | GET | Daftar user. | Login + role admin |
| `/admin/users` | POST | Tambah user. | Login + role admin |
| `/admin/users/{id}` | PUT | Ubah user. | Login + role admin |
| `/admin/users/{id}` | DELETE | Hapus user. | Login + role admin |

**Poin yang perlu dijelaskan:** route dikelompokkan dengan `middleware('guest')` (hanya tamu), `middleware('auth')` (harus login), dan `middleware('role:admin')` (hanya admin). `->name(...)` memberi nama route agar bisa dipanggil dengan `route('nama')` di view/controller.

---

## 9. `.env`

| Variabel | Nilai | Penjelasan |
|---|---|---|
| `APP_NAME` | `Laravel` | Nama aplikasi. |
| `APP_ENV` | `local` | Mode pengembangan di komputer sendiri. |
| `APP_KEY` | `base64:...` | Kunci enkripsi aplikasi; wajib ada dan rahasia. |
| `APP_DEBUG` | `true` | Menampilkan detail error; ubah ke `false` saat online. |
| `APP_URL` | `http://localhost` | Alamat dasar aplikasi. |
| `APP_LOCALE` | `en` | Bahasa bawaan pesan sistem. |
| `DB_CONNECTION` | `mysql` | Jenis database. |
| `DB_HOST` / `DB_PORT` | `127.0.0.1` / `3306` | Alamat dan port database lokal. |
| `DB_DATABASE` | `test_skenario` | Nama database aplikasi. |
| `DB_USERNAME` / `DB_PASSWORD` | `root` / *(kosong)* | Akun database lokal (XAMPP/Laragon). |
| `SESSION_DRIVER` | `database` | Sesi login disimpan di tabel `sessions`. |
| `SESSION_LIFETIME` | `120` | Sesi habis setelah 120 menit tanpa aktivitas. |
| `FILESYSTEM_DISK` | `local` | Penyimpanan bawaan; foto pengaduan memakai disk `public`. |
| `CACHE_STORE` | `database` | Cache disimpan di database. |
| `QUEUE_CONNECTION` | `database` | Antrean pekerjaan disimpan di database. |
| `MAIL_MAILER` | `log` | Email hanya ditulis ke log, tidak dikirim. |
| `LOG_LEVEL` | `debug` | Tingkat detail log untuk pengembangan. |

---

## 10. Perintah yang Biasa Dipakai

```bash
# ---- Pemasangan awal ----
composer install                      # Memasang dependensi PHP
cp .env.example .env                  # (Windows) copy .env.example .env
php artisan key:generate              # Membuat APP_KEY

# ---- Database ----
php artisan migrate                   # Membuat semua tabel dari folder database/migrations
php artisan migrate:fresh             # Menghapus semua tabel lalu membuat ulang dari awal
php artisan migrate:rollback          # Membatalkan migration terakhir (menjalankan down())
php artisan migrate:status            # Melihat status setiap migration
php artisan db:seed                   # Mengisi akun admin awal (DatabaseSeeder)
php artisan migrate:fresh --seed      # Membuat ulang database + langsung mengisi data awal

# ---- Penyimpanan foto ----
php artisan storage:link              # Membuat tautan public/storage ke storage/app/public

# ---- Menjalankan aplikasi ----
php artisan serve                     # Menjalankan server di http://127.0.0.1:8000
php artisan route:list                # Melihat daftar seluruh route beserta middleware-nya
php artisan about                     # Ringkasan konfigurasi aplikasi

# ---- Membersihkan cache ----
php artisan optimize:clear            # Membersihkan seluruh cache konfigurasi/route/view
```

**Memeriksa hasil di database (MySQL):**

```sql
SELECT id, name, email, role FROM users;                 -- daftar akun + peran
SELECT id, name FROM categories;                         -- daftar kategori
SELECT id, sender_name, table_number, status FROM complaints;  -- daftar pengaduan
```

---

## 11. Akun Bawaan

| Email | Password | Peran |
|---|---|---|
| `admin@gmail.com` | `admin123` | Admin |

Dibuat oleh `DatabaseSeeder`. Akun customer dapat dibuat sendiri lewat halaman **Daftar**.

---

## 12. Pertanyaan yang Sering Muncul Saat Presentasi

**Kenapa ada dua kolom kategori (`category` dan `category_id`)?**
`category_id` adalah relasi resmi ke tabel `categories`. Kolom `category` menyimpan teks nama kategori sebagai cadangan, sehingga pengaduan lama tidak kehilangan informasi bila kategorinya dihapus.

**Bagaimana aplikasi mencegah customer membuka halaman admin?**
Tiga lapis: (1) route admin dibungkus `middleware('role:admin')`, (2) `RoleMiddleware` menolak dengan HTTP 403, (3) menu admin disembunyikan di sidebar oleh `@if(auth()->user()->isAdmin())`.

**Bagaimana keamanan password?**
Password di-hash otomatis oleh cast `'password' => 'hashed'` pada model User (algoritma bcrypt, `BCRYPT_ROUNDS=12`). Password asli tidak pernah disimpan dan tidak ikut tampil saat data diubah ke JSON (`$hidden`).

**Bagaimana keamanan form?**
Setiap form memakai `@csrf`. Untuk update dan hapus, view mengirim `@method('PUT')` / `@method('DELETE')` karena HTML hanya mendukung GET dan POST.

**Kenapa identitas pelapor disamarkan?**
Daftar pengaduan bersifat publik bagi pengguna yang login. Accessor `masked_sender_name` mengubah nama menjadi bentuk seperti `B*** S***` untuk menjaga privasi.
