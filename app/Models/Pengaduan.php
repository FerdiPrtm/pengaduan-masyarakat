<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';

    public const STATUS = [
        'menunggu_verifikasi',
        'butuh_info_tambahan',
        'diverifikasi',
        'ditolak',
        'diproses',
        'selesai',
    ];

    public const FINAL = ['ditolak', 'selesai'];

    protected $fillable = [
        'nomor_tiket',
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPengaduan::class, 'kategori_id');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(BuktiFoto::class, 'pengaduan_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(StatusLog::class, 'pengaduan_id')->latest('id');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL, true);
    }
}
