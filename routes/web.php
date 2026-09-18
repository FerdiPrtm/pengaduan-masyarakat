<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('pengaduan.index'),
            default => redirect()->route('pengaduan.index'),
        };
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
