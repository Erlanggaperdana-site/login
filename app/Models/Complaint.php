<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    /** Label kategori bawaan yang dipertahankan untuk data lama dan tampilan. @var list<string> */
    public const CATEGORIES = ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Fasilitas Umum', 'Lainnya'];

    /** Kolom yang boleh diisi melalui create() dan update(). @var list<string> */
    protected $fillable = [
        'user_id', 'title', 'category', 'category_id', 'description', 'location', 'status',
        'sender_name', 'sender_email', 'sender_phone', 'table_number', 'message', 'attachments',
    ];

    /** Kolom lampiran disimpan sebagai JSON dan dibaca sebagai array PHP. */
    protected function casts(): array
    {
        return ['attachments' => 'array'];
    }

    /** Pemilik pengaduan; nilai null mungkin terjadi pada data lama yang dikirim tanpa akun. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi ke tabel categories; teks kategori tetap tersimpan walau kategori dihapus. */
    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Menyamarkan nama pelapor untuk kartu pengaduan yang tampil di ruang publik.
     * Contoh: "Budi Santoso" menjadi "B*** S***".
     */
    public function getMaskedSenderNameAttribute(): string
    {
        // Nama diambil dari isian form; bila kosong dipakai nama akun, bila tetap kosong diberi label umum.
        $name = trim((string) ($this->sender_name ?: $this->user?->name));

        if ($name === '') {
            return 'Customer';
        }

        // Setiap kata dipotong menjadi satu huruf awal ditambah "***".
        return collect(preg_split('/\s+/u', $name) ?: [])
            ->map(fn (string $part): string => mb_substr($part, 0, 1).'***')
            ->implode(' ');
    }
}
