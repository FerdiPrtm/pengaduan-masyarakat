<?php

namespace App\Policies;

use App\Models\Pengaduan;
use App\Models\User;

class PengaduanPolicy
{
    public function view(User $user, Pengaduan $pengaduan): bool
    {
        return $user->isPetugas() || $pengaduan->user_id === $user->id;
    }

    public function update(User $user, Pengaduan $pengaduan): bool
    {
        // pelapor hanya boleh edit saat butuh_info_tambahan (untuk resubmit)
        return $pengaduan->user_id === $user->id
            && $pengaduan->status === 'butuh_info_tambahan';
    }

    public function verify(User $user): bool
    {
        return $user->isPetugas();
    }
}
