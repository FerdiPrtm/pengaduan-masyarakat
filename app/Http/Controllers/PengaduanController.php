<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengaduanRequest;
use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Services\PengaduanStatusService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PengaduanController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $user = $request->user();
        $q = Pengaduan::query()->with(['kategori', 'pelapor'])
            ->when(! $user->isPetugas(), fn ($w) => $w->where('user_id', $user->id))
            ->when($request->status, fn ($w, $s) => $w->where('status', $s))
            ->when($request->kategori_id, fn ($w, $k) => $w->where('kategori_id', $k))
            ->when($request->q, fn ($w, $s) => $w->where(fn ($x) => $x->where('judul', 'like', "%{$s}%")->orWhere('nomor_tiket', 'like', "%{$s}%")))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Pengaduan/Index', [
            'items' => $q,
            'filters' => $request->only(['status', 'kategori_id', 'q']),
            'kategoris' => KategoriPengaduan::orderBy('nama_kategori')->get(),
            'statuses' => Pengaduan::STATUS,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pengaduan/Create', [
            'kategoris' => KategoriPengaduan::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(StorePengaduanRequest $request): RedirectResponse
    {
        $pengaduan = Pengaduan::create([
            ...$request->safe()->except('foto'),
            'user_id' => $request->user()->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->storeFoto($request, $pengaduan);

        return redirect()->route('pengaduan.show', $pengaduan->nomor_tiket)
            ->with('success', 'Pengaduan terkirim: '.$pengaduan->nomor_tiket);
    }

    public function show(string $tiket): Response
    {
        $pengaduan = Pengaduan::where('nomor_tiket', $tiket)
            ->with(['kategori', 'pelapor', 'foto', 'logs.updater'])
            ->firstOrFail();

        $this->authorize('view', $pengaduan);

        return Inertia::render('Pengaduan/Show', ['item' => $pengaduan]);
    }

    public function update(StorePengaduanRequest $request, Pengaduan $pengaduan, PengaduanStatusService $service): RedirectResponse
    {
        $this->authorize('update', $pengaduan);

        $pengaduan->update($request->safe()->except('foto'));
        $this->storeFoto($request, $pengaduan);

        // resubmit: kembali ke antrean verifikasi
        $service->transition($pengaduan->refresh(), 'menunggu_verifikasi', $request->user(), 'Pelapor melengkapi info');

        return redirect()->route('pengaduan.show', $pengaduan->nomor_tiket)
            ->with('success', 'Perbaikan terkirim, kembali menunggu verifikasi.');
    }

    private function storeFoto(Request $request, Pengaduan $pengaduan): void
    {
        if (! $request->hasFile('foto')) {
            return;
        }

        abort_if($pengaduan->foto()->count() + count($request->file('foto')) > 5, 422, 'Maksimal 5 foto.');

        foreach ($request->file('foto') as $file) {
            $path = $file->store("pengaduan/{$pengaduan->nomor_tiket}", 'public');
            $pengaduan->foto()->create([
                'path_file' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
