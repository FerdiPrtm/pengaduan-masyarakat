<?php

namespace App\Observers;

use App\Models\Pengaduan;
use Illuminate\Support\Facades\Storage;

class PengaduanObserver
{
    public function created(Pengaduan $pengaduan): void
    {
        // Nomor tiket dari id: anti-race, tanpa counter harian
        $pengaduan->updateQuietly([
            'nomor_tiket' => 'PGD-'.$pengaduan->created_at->format('Ymd').'-'.str_pad((string) $pengaduan->id, 4, '0', STR_PAD_LEFT),
        ]);
        $pengaduan->logs()->create([
            'status_lama' => null,
            'status_baru' => 'menunggu_verifikasi',
            'updated_by' => $pengaduan->user_id,
        ]);
    }

    public function deleting(Pengaduan $pengaduan): void
    {
        if ($pengaduan->nomor_tiket) {
            Storage::disk('local')->deleteDirectory('pengaduan/'.$pengaduan->nomor_tiket);
        }
    }
}
