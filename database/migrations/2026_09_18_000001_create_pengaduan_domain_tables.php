<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori')->unique();
            $table->string('unit_penanggung_jawab')->nullable();
            $table->timestamps();
        });

        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket')->unique()->nullable(); // diisi setelah create dari id
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_pengaduan')->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lokasi');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('menunggu_verifikasi');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('kategori_id');
        });

        Schema::create('bukti_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduan')->cascadeOnDelete();
            $table->string('path_file');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->timestamps();
        });

        Schema::create('status_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduan')->cascadeOnDelete();
            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->string('hasil_verifikasi')->nullable(); // valid|tidak_valid|butuh_info
            $table->text('catatan')->nullable();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_log');
        Schema::dropIfExists('bukti_foto');
        Schema::dropIfExists('pengaduan');
        Schema::dropIfExists('kategori_pengaduan');
    }
};
