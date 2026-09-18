<?php

namespace Database\Seeders;

use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        User::firstOrCreate(['email' => 'petugas@example.com'], [
            'name' => 'Petugas Satu', 'password' => Hash::make('password'), 'role' => 'petugas',
        ]);
        User::firstOrCreate(['email' => 'petugas2@example.com'], [
            'name' => 'Petugas Dua', 'password' => Hash::make('password'), 'role' => 'petugas',
        ]);
        $pelapor = User::firstOrCreate(['email' => 'warga@example.com'], [
            'name' => 'Warga Contoh', 'password' => Hash::make('password'), 'role' => 'pelapor',
            'nik' => '3201010101010001', 'no_hp' => '081234567890',
        ]);

        foreach ([
            ['Infrastruktur', 'Dinas PU'],
            ['Pelayanan', 'Disdukcapil'],
            ['Lingkungan', 'DLH'],
            ['Keamanan', 'Satpol PP'],
            ['Lainnya', null],
        ] as [$nama, $unit]) {
            KategoriPengaduan::firstOrCreate(['nama_kategori' => $nama], ['unit_penanggung_jawab' => $unit]);
        }

        // 3 contoh laporan (tanpa foto) untuk demo timeline
        if (Pengaduan::count() === 0) {
            $kat = KategoriPengaduan::first();
            Pengaduan::create([
                'user_id' => $pelapor->id, 'kategori_id' => $kat->id,
                'judul' => 'Jalan berlubang di Jl. Merdeka',
                'deskripsi' => 'Lubang cukup besar membahayakan pengendara motor, mohon segera diperbaiki.',
                'lokasi' => 'Jl. Merdeka No. 10', 'status' => 'menunggu_verifikasi',
            ]);
        }
    }
}
