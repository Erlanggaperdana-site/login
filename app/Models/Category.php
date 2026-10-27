<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** Hanya nama dan deskripsi yang boleh diisi melalui create() dan update(). @var list<string> */
    protected $fillable = ['name', 'description'];

    /** Relasi satu-ke-banyak: satu kategori dipakai oleh banyak pengaduan. */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
