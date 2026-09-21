<?php

namespace Tests\Feature;

use App\Models\BuktiFoto;
use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use App\Services\PengaduanStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaduanAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $owner): Pengaduan
    {
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Infrastruktur']);

        return Pengaduan::create([
            'user_id' => $owner->id, 'kategori_id' => $kat->id,
            'judul' => 'Jalan berlubang', 'deskripsi' => 'Jalan rusak parah, perlu diperbaiki segera.',
            'lokasi' => 'Pasar Baru', 'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_user_lain_tidak_bisa_melihat_laporan_pelapor(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $p = $this->makeReport($owner);
        $bystander = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($bystander)->get(route('pengaduan.show', $p->nomor_tiket))->assertForbidden();
    }

    public function test_foto_privat_hanya_bisa_diakses_pemilik_dan_petugas(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $bystander = User::factory()->create(['role' => 'pelapor']);
        $p = $this->makeReport($owner);

        $ft = $p->foto()->create([
            'path_file' => 'pengaduan/'.$p->nomor_tiket.'/bukti.jpg',
            'original_name' => 'bukti.jpg', 'mime' => 'image/jpeg', 'size' => 100,
        ]);
        Storage::disk('local')->put($ft->path_file, 'data');

        $route = fn () => route('pengaduan.foto', [$p->id, $ft->id]);

        $this->actingAs($bystander)->get($route())->assertForbidden();
        $this->actingAs($petugas)->get($route())->assertOk();
        $this->actingAs($owner)->get($route())->assertOk();
    }

    public function test_foto_hanya_bisa_dihapus_pemilik_saat_butuh_info(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $bystander = User::factory()->create(['role' => 'pelapor']);
        $p = $this->makeReport($owner);

        $ft = $p->foto()->create([
            'path_file' => 'pengaduan/'.$p->nomor_tiket.'/bukti.jpg',
            'original_name' => 'bukti.jpg', 'mime' => 'image/jpeg', 'size' => 100,
        ]);
        Storage::disk('local')->put($ft->path_file, 'data');

        $svc = app(PengaduanStatusService::class);
        $svc->verifikasi($p->refresh(), 'butuh_info', $petugas, 'Foto kurang jelas');
        $this->assertSame('butuh_info_tambahan', $p->refresh()->status);

        $route = fn () => route('pengaduan.foto.destroy', [$p->id, $ft->id]);

        $this->actingAs($bystander)->delete($route())->assertForbidden();
        $this->actingAs($owner)->delete($route())->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('bukti_foto', ['id' => $ft->id]);
    }

    public function test_pelapor_tidak_bisa_mengubah_laporan_milik_orang_lain(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $p = $this->makeReport($owner);
        $bystander = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($bystander)
            ->put(route('pengaduan.update', $p->id), ['judul' => 'Rusak', 'deskripsi' => 'Deskripsi panjang.', 'lokasi' => 'X', 'kategori_id' => $p->kategori_id])
            ->assertForbidden();
    }

    public function test_pelapor_tidak_bisa_mengakses_halaman_verifikasi(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $p = $this->makeReport($owner);

        $this->actingAs($petugas)->get(route('pengaduan.verify', $p->id))->assertOk();
        $this->actingAs($owner)->get(route('pengaduan.verify', $p->id))->assertForbidden();
    }

    public function test_upload_foto_asli_tersimpan_di_disk_privat(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Lingkungan']);

        $resp = $this->actingAs($owner)->post(route('pengaduan.store'), [
            'kategori_id' => $kat->id,
            'judul' => 'Sampah menumpuk',
            'deskripsi' => 'Sampah sudah menumpuk berhari-hari di tepi jalan.',
            'lokasi' => 'Gang Mawar',
            'foto' => [UploadedFile::fake()->image('bukti.jpg', 100, 100)],
        ]);

        $resp->assertRedirect();
        $p = Pengaduan::first();
        $this->assertNotNull($p);
        $this->assertSame(1, $p->foto()->count());
        Storage::disk('local')->assertExists($p->foto()->first()->path_file);
        Storage::disk('public')->assertMissing($p->foto()->first()->path_file);
    }
}