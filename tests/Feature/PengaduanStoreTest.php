<?php

namespace Tests\Feature;

use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PengaduanStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_pengaduan_berhasil_dengan_log_awal(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Infrastruktur']);

        $this->actingAs($pelapor)->post(route('pengaduan.store'), [
            'kategori_id' => $kat->id,
            'judul' => 'Lampu mati',
            'deskripsi' => 'Lampu jalan mati sejak seminggu lalu di seluruh gang.',
            'lokasi' => 'Gang Anggrek',
        ])->assertRedirect();

        $p = Pengaduan::first();
        $this->assertNotNull($p);
        $this->assertSame('menunggu_verifikasi', $p->status);
        $this->assertMatchesRegularExpression('/^PGD-\d{8}-\d{4}$/', $p->nomor_tiket);
        $this->assertSame(1, $p->logs()->count());
    }

    public function test_petugas_dan_admin_tidak_bisa_membuat_pengaduan(): void
    {
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Lingkungan']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([$petugas, $admin] as $u) {
            $this->actingAs($u)->post(route('pengaduan.store'), [
                'kategori_id' => $kat->id,
                'judul' => 'Abaikan',
                'deskripsi' => 'Tidak boleh terkirim dari akun non-pelapor.',
                'lokasi' => 'X',
            ])->assertForbidden();
        }
    }

    public function test_validasi_bahasa_indonesia_berjalan(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $resp = $this->actingAs($pelapor)->from('/pengaduan/buat')->post(route('pengaduan.store'), [
            'deskripsi' => 'pendek',
        ]);

        $resp->assertSessionHasErrors(['kategori_id', 'judul', 'deskripsi', 'lokasi']);
        $this->assertStringContainsString('Pilih kategori pengaduan', session('errors')->first('kategori_id'));
        $this->assertStringContainsString('minimal 10 karakter', session('errors')->first('deskripsi'));
    }

    public function test_foto_maksimal_5(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Pelayanan']);

        $resp = $this->actingAs($pelapor)->post(route('pengaduan.store'), [
            'kategori_id' => $kat->id,
            'judul' => 'Antrean panjang',
            'deskripsi' => 'Antrean sangat panjang dan memakan waktu lama.',
            'lokasi' => 'Kantor Desa',
            'foto' => array_map(fn () => UploadedFile::fake()->image('b.jpg', 50, 50), range(1, 6)),
        ]);

        $resp->assertSessionHasErrors('foto');
        $this->assertSame(0, Pengaduan::count());
        $this->assertSame(0, \App\Models\BuktiFoto::count());
    }
}