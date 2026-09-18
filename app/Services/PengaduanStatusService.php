<?php

namespace App\Services;

use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// ponytail: single state machine; tambah transisi hanya di $transitions
class PengaduanStatusService
{
    /** @var array<string, string[]> */
    private const TRANSITIONS = [
        'menunggu_verifikasi' => ['diverifikasi', 'ditolak', 'butuh_info_tambahan'],
        'butuh_info_tambahan' => ['menunggu_verifikasi'],
        'diverifikasi' => ['diproses'],
        'diproses' => ['selesai'],
    ];

    public function transition(Pengaduan $pengaduan, string $baru, User $by, ?string $catatan = null, ?string $hasilVerifikasi = null): Pengaduan
    {
        $lama = $pengaduan->status;

        if (! in_array($baru, self::TRANSITIONS[$lama] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi {$lama} → {$baru} tidak diizinkan.",
            ]);
        }

        return DB::transaction(function () use ($pengaduan, $lama, $baru, $by, $catatan, $hasilVerifikasi) {
            $pengaduan->update(['status' => $baru]);
            $pengaduan->logs()->create([
                'status_lama' => $lama,
                'status_baru' => $baru,
                'hasil_verifikasi' => $hasilVerifikasi,
                'catatan' => $catatan,
                'updated_by' => $by->id,
            ]);

            return $pengaduan->refresh();
        });
    }

    public function verifikasi(Pengaduan $pengaduan, string $hasil, User $petugas, ?string $catatan = null): Pengaduan
    {
        $map = ['valid' => 'diverifikasi', 'tidak_valid' => 'ditolak', 'butuh_info' => 'butuh_info_tambahan'];

        if (! isset($map[$hasil])) {
            throw ValidationException::withMessages(['hasil' => 'Hasil verifikasi tidak valid.']);
        }

        if (in_array($hasil, ['tidak_valid', 'butuh_info'], true) && blank($catatan)) {
            throw ValidationException::withMessages(['catatan' => 'Catatan wajib untuk hasil ini.']);
        }

        return $this->transition($pengaduan, $map[$hasil], $petugas, $catatan, $hasil);
    }
}
