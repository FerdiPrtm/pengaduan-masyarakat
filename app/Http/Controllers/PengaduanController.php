<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengaduanRequest;
use App\Models\BuktiFoto;
use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Services\PengaduanStatusService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function create(Request $request): Response
    {
        abort_unless($request->user()->role === 'pelapor', 403);

        return Inertia::render('Pengaduan/Create', [
            'kategoris' => KategoriPengaduan::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(StorePengaduanRequest $request): RedirectResponse
    {
        $pengaduan = null;

        try {
            $pengaduan = DB::transaction(function () use ($request) {
                $p = Pengaduan::create([
                    ...$request->safe()->except('foto'),
                    'user_id' => $request->user()->id,
                    'status' => 'menunggu_verifikasi',
                ]);

                $this->storeFoto($request, $p);

                return $p;
            });
        } catch (\Throwable $e) {
            if ($pengaduan !== null && $pengaduan->nomor_tiket) {
                Storage::disk('local')->deleteDirectory("pengaduan/{$pengaduan->nomor_tiket}");
            }

            throw $e;
        }

        return redirect()->route('pengaduan.show', $pengaduan->nomor_tiket)
            ->with('success', 'Pengaduan terkirim: '.$pengaduan->nomor_tiket);
    }

    public function show(Request $request, string $tiket): Response
    {
        $pengaduan = Pengaduan::where('nomor_tiket', $tiket)
            ->with(['kategori', 'pelapor', 'foto', 'logs.updater'])
            ->firstOrFail();

        $this->authorize('view', $pengaduan);

        $request->user()->notifikasi()
            ->where('pengaduan_id', $pengaduan->id)
            ->whereNull('dibaca_at')
            ->update(['dibaca_at' => now()]);

        return Inertia::render('Pengaduan/Show', ['item' => $pengaduan]);
    }

    public function update(StorePengaduanRequest $request, Pengaduan $pengaduan, PengaduanStatusService $service): RedirectResponse
    {
        $this->authorize('update', $pengaduan);

        DB::transaction(function () use ($request, $pengaduan, $service) {
            $pengaduan->update($request->safe()->except('foto'));
            $this->storeFoto($request, $pengaduan);

            // resubmit: kembali ke antrean verifikasi
            $service->transition($pengaduan->refresh(), 'menunggu_verifikasi', $request->user(), 'Pelapor melengkapi info');
        });

        return redirect()->route('pengaduan.show', $pengaduan->nomor_tiket)
            ->with('success', 'Perbaikan terkirim, kembali menunggu verifikasi.');
    }

    public function foto(Pengaduan $pengaduan, BuktiFoto $foto)
    {
        abort_if($foto->pengaduan_id !== $pengaduan->id, 404);

        $this->authorize('view', $pengaduan);

        return Storage::disk('local')->response($foto->path_file);
    }

    public function destroyFoto(Pengaduan $pengaduan, BuktiFoto $foto)
    {
        abort_if($foto->pengaduan_id !== $pengaduan->id, 404);

        $this->authorize('update', $pengaduan);

        Storage::disk('local')->delete($foto->path_file);
        $foto->delete();

        return back()->with('success', 'Foto dihapus.');
    }

    private function storeFoto(Request $request, Pengaduan $pengaduan): void
    {
        if (! $request->hasFile('foto')) {
            return;
        }

        abort_if($pengaduan->foto()->count() + count($request->file('foto')) > 5, 422, 'Maksimal 5 foto.');

        foreach ($request->file('foto') as $file) {
            $path = $file->store("pengaduan/{$pengaduan->nomor_tiket}", 'local');
            $pengaduan->foto()->create([
                'path_file' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
