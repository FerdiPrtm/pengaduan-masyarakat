<?php

namespace Tests\Feature;

use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use App\Services\PengaduanStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PengaduanFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_flow_menunggu_hingga_selesai(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Infrastruktur']);

        $p = Pengaduan::create([
            'user_id' => $pelapor->id, 'kategori_id' => $kat->id,
            'judul' => 'Lampu jalan mati', 'deskripsi' => 'Sudah seminggu mati total.',
            'lokasi' => 'Jl. Mawar 1', 'status' => 'menunggu_verifikasi',
        ]);

        $this->assertMatchesRegularExpression('/^PGD-\d{8}-\d{4}$/', $p->refresh()->nomor_tiket);

        $svc = app(PengaduanStatusService::class);
        $svc->verifikasi($p, 'valid', $petugas);
        $this->assertSame('diverifikasi', $p->refresh()->status);

        $svc->transition($p, 'diproses', $petugas, 'Diteruskan ke DLH');
        $svc->transition($p->refresh(), 'selesai', $petugas, 'Sudah diperbaiki');

        $this->assertSame('selesai', $p->refresh()->status);
        $this->assertSame(4, $p->logs()->count()); // create + valid + diproses + selesai
    }

    public function test_transisi_ilegal_ditolak(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Lingkungan']);

        $p = Pengaduan::create([
            'user_id' => $pelapor->id, 'kategori_id' => $kat->id,
            'judul' => 'Sampah menumpuk', 'deskripsi' => 'Sudah berhari-hari menumpuk.',
            'lokasi' => 'Gang Buntu', 'status' => 'menunggu_verifikasi',
        ]);

        $this->expectException(ValidationException::class);
        app(PengaduanStatusService::class)->transition($p, 'selesai', $petugas);
    }

    public function test_butuh_info_bisa_resubmit(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Pelayanan']);

        $p = Pengaduan::create([
            'user_id' => $pelapor->id, 'kategori_id' => $kat->id,
            'judul' => 'Antrean lama', 'deskripsi' => 'Antrean sangat lama sekali.',
            'lokasi' => 'Kantor Camat', 'status' => 'menunggu_verifikasi',
        ]);

        $svc = app(PengaduanStatusService::class);
        $svc->verifikasi($p, 'butuh_info', $petugas, 'Foto kurang jelas');
        $this->assertSame('butuh_info_tambahan', $p->refresh()->status);

        $svc->transition($p->refresh(), 'menunggu_verifikasi', $pelapor, 'Sudah dilengkapi');
        $this->assertSame('menunggu_verifikasi', $p->refresh()->status);
    }
}
