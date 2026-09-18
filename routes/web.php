<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerifikasiController;
use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'stats' => [
            'total' => Pengaduan::count(),
            'selesai' => Pengaduan::where('status', 'selesai')->count(),
            'ditindaklanjuti' => Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count(),
            'kategori' => KategoriPengaduan::withCount('pengaduan')->orderByDesc('pengaduan_count')->get(),
        ],
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Dashboard Petugas: fokus antrean verifikasi (PRD §10)
        if ($user->isPetugas()) {
            return Inertia::render('Dashboard', [
                'role' => 'petugas',
                'stats' => [
                    'antre' => Pengaduan::where('status', 'menunggu_verifikasi')->count(),
                    'diproses' => Pengaduan::whereIn('status', ['diverifikasi', 'diproses'])->count(),
                    'selesai' => Pengaduan::where('status', 'selesai')->count(),
                ],
                'antrean' => Pengaduan::with(['kategori', 'pelapor'])
                    ->where('status', 'menunggu_verifikasi')
                    ->latest()->take(8)->get(),
            ]);
        }

        // Dashboard Pelapor: ringkasan + laporan terbaru (PRD §10)
        $mine = Pengaduan::where('user_id', $user->id);

        return Inertia::render('Dashboard', [
            'role' => 'pelapor',
            'stats' => [
                'total' => (clone $mine)->count(),
                'menunggu' => (clone $mine)->whereIn('status', ['menunggu_verifikasi', 'butuh_info_tambahan'])->count(),
                'aktif' => (clone $mine)->whereIn('status', ['diverifikasi', 'diproses'])->count(),
                'selesai' => (clone $mine)->where('status', 'selesai')->count(),
            ],
            'recent' => (clone $mine)->with('kategori')->latest()->take(5)->get(),
            'perhatian' => (clone $mine)->where('status', 'butuh_info_tambahan')->latest()->take(3)->get(['id', 'nomor_tiket', 'judul']),
        ]);
    })->name('dashboard');

    // Pelapor + petugas: daftar & buat (throttle anti-spam)
    Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('pengaduan.index');
    Route::get('/pengaduan/buat', [PengaduanController::class, 'create'])->name('pengaduan.create');
    Route::post('/pengaduan', [PengaduanController::class, 'store'])
        ->middleware('throttle:5,60')->name('pengaduan.store');
    Route::get('/pengaduan/{tiket}', [PengaduanController::class, 'show'])->name('pengaduan.show');
    Route::put('/pengaduan/{pengaduan}', [PengaduanController::class, 'update'])->name('pengaduan.update');

    // Petugas: verifikasi + update status
    Route::middleware('role:petugas,admin')->group(function () {
        Route::get('/pengaduan/{pengaduan}/verifikasi', [VerifikasiController::class, 'show'])->name('pengaduan.verify');
        Route::post('/pengaduan/{pengaduan}/verifikasi', [VerifikasiController::class, 'store'])->name('pengaduan.verifikasi');
        Route::post('/pengaduan/{pengaduan}/status', [VerifikasiController::class, 'status'])->name('pengaduan.status');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/export/csv', [AdminController::class, 'export'])->name('export');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::get('/kategori', [AdminController::class, 'kategoris'])->name('kategoris');
        Route::post('/kategori', [AdminController::class, 'storeKategori'])->name('kategoris.store');
        Route::delete('/kategori/{kategori}', [AdminController::class, 'destroyKategori'])->name('kategoris.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
