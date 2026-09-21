<?php

namespace Tests\Feature;

use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_admin_hanya_untuk_peran_admin(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($pelapor)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($petugas)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();
    }

    public function test_admin_bisa_mengubah_role_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $warga = User::factory()->create(['role' => 'pelapor', 'email' => 'warga@test.com']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $warga), ['role' => 'petugas'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $warga->id, 'role' => 'petugas']);
    }

    public function test_admin_tidak_bisa_mengubah_role_akun_sendiri(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'petugas'])
            ->assertRedirect()
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_tidak_bisa_menonaktifkan_admin_terakhir(): void
    {
        $adminA = User::factory()->create(['role' => 'admin']);
        $adminB = User::factory()->create(['role' => 'admin']);
        $warga = User::factory()->create(['role' => 'pelapor']);

        // turunkan B: masih ada A, jadi boleh
        $this->actingAs($adminA)->patch(route('admin.users.role', $adminB), ['role' => 'pelapor'])->assertSessionHas('success');

        // tinggal A sendiri -> tidak boleh turunkan diri
        $this->actingAs($adminA)
            ->patch(route('admin.users.role', $adminA), ['role' => 'pelapor'])
            ->assertRedirect()
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $adminA->id, 'role' => 'admin']);
    }

    public function test_kategori_terpakai_tidak_bisa_dihapus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Infrastruktur']);
        $owner = User::factory()->create(['role' => 'pelapor']);
        Pengaduan::create([
            'user_id' => $owner->id, 'kategori_id' => $kat->id,
            'judul' => 'Jalan rusak', 'deskripsi' => 'Perlu perbaikan segera oleh Dinas.', 'lokasi' => 'Jl. Melati',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($admin)->delete(route('admin.kategoris.destroy', $kat))->assertStatus(422);
        $this->assertDatabaseHas('kategori_pengaduan', ['id' => $kat->id]);
    }

    public function test_kategori_baru_bisa_manipulasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kat = KategoriPengaduan::create(['nama_kategori' => 'Sampah']);

        $this->actingAs($admin)
            ->post(route('admin.kategoris.store'), ['nama_kategori' => 'Jalan'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('admin.kategoris.store'), ['nama_kategori' => 'Sampah'])
            ->assertSessionHasErrors('nama_kategori');

        $this->actingAs($admin)->delete(route('admin.kategoris.destroy', $kat))->assertRedirect();
        $this->assertDatabaseMissing('kategori_pengaduan', ['id' => $kat->id]);
    }

    public function test_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
    }
}