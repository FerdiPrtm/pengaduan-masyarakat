<?php

namespace App\Http\Controllers;

use App\Models\KategoriPengaduan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'perStatus' => Pengaduan::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'perKategori' => Pengaduan::join('kategori_pengaduan', 'kategori_pengaduan.id', '=', 'pengaduan.kategori_id')
                ->select('kategori_pengaduan.nama_kategori', DB::raw('count(*) as total'))
                ->groupBy('kategori_pengaduan.nama_kategori')->pluck('total', 'nama_kategori'),
            'total' => Pengaduan::count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = Pengaduan::query()->with(['kategori', 'pelapor'])
            ->when($request->status, fn ($w, $s) => $w->where('status', $s))
            ->when($request->kategori_id, fn ($w, $k) => $w->where('kategori_id', $k))
            ->when($request->from, fn ($w, $d) => $w->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($w, $d) => $w->whereDate('created_at', '<=', $d))
            ->latest()->get();

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['nomor_tiket', 'tanggal', 'pelapor', 'kategori', 'judul', 'status']);
            foreach ($data as $p) {
                fputcsv($out, [$p->nomor_tiket, $p->created_at->format('Y-m-d H:i'), $p->pelapor->name, $p->kategori->nama_kategori, $p->judul, $p->status]);
            }
            fclose($out);
        }, 'pengaduan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function users()
    {
        return Inertia::render('Admin/Users', [
            'items' => User::orderBy('name')->paginate(15),
        ]);
    }

    public function updateUserRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:pelapor,petugas,admin']]);
        $user->update($data);

        return back()->with('success', 'Role diperbarui.');
    }

    public function kategoris()
    {
        return Inertia::render('Admin/Kategoris', [
            'items' => KategoriPengaduan::withCount('pengaduan')->orderBy('nama_kategori')->get(),
        ]);
    }

    public function storeKategori(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori_pengaduan,nama_kategori'],
            'unit_penanggung_jawab' => ['nullable', 'string', 'max:100'],
        ]);
        KategoriPengaduan::create($data);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function destroyKategori(KategoriPengaduan $kategori)
    {
        abort_if($kategori->pengaduan()->exists(), 422, 'Kategori sudah dipakai laporan.');
        $kategori->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
