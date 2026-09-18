<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiFoto extends Model
{
    protected $table = 'bukti_foto';

    protected $fillable = [
        'pengaduan_id',
        'path_file',
        'original_name',
        'mime',
        'size',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }
}
