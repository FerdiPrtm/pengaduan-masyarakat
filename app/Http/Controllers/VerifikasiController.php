<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifikasiRequest;
use App\Models\Pengaduan;
use App\Services\PengaduanStatusService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerifikasiController extends Controller
{
    use AuthorizesRequests;

    // Halaman Verifikasi khusus petugas (PRD §10): detail + foto + form aksi
    public function show(Pengaduan $pengaduan): Response
    {
        $this->authorize('verify', Pengaduan::class);

        $pengaduan->load(['kategori', 'pelapor', 'foto', 'logs.updater']);

        return Inertia::render('Pengaduan/Verify', ['item' => $pengaduan]);
    }
    public function store(VerifikasiRequest $request, Pengaduan $pengaduan, PengaduanStatusService $service): RedirectResponse
    {
        $this->authorize('verify', Pengaduan::class);

        $service->verifikasi($pengaduan, $request->validated('hasil'), $request->user(), $request->validated('catatan'));

        return back()->with('success', 'Verifikasi tersimpan.');
    }

    public function status(Request $request, Pengaduan $pengaduan, PengaduanStatusService $service): RedirectResponse
    {
        $this->authorize('verify', Pengaduan::class);

        $data = $request->validate([
            'status_baru' => ['required', 'in:diproses,selesai'],
            'catatan' => ['nullable', 'string'],
        ]);

        $service->transition($pengaduan, $data['status_baru'], $request->user(), $data['catatan'] ?? null);

        return back()->with('success', 'Status → '.$data['status_baru']);
    }
}
