<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLog extends Model
{
    protected $table = 'status_log';

    public $timestamps = false;

    protected $fillable = [
        'pengaduan_id',
        'status_lama',
        'status_baru',
        'hasil_verifikasi',
        'catatan',
        'updated_by',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
