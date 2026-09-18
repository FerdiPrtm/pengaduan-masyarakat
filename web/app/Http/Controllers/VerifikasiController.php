<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifikasiRequest;
use App\Models\Pengaduan;
use App\Services\PengaduanStatusService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerifikasiController extends Controller
{
    use AuthorizesRequests;
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
