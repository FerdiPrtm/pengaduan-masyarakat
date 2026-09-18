// Satu-satunya kamus label status. Semua halaman wajib pakai ini
// agar tidak ada lagi "Diproses" vs "diproses" vs "Diajukan".
export const STATUS_LABEL = {
    menunggu_verifikasi: 'Menunggu Verifikasi',
    butuh_info_tambahan: 'Butuh Info Tambahan',
    diverifikasi: 'Diverifikasi',
    ditolak: 'Ditolak',
    diproses: 'Diproses',
    selesai: 'Selesai',
};

export const STATUS_FLOW = ['menunggu_verifikasi', 'diverifikasi', 'diproses', 'selesai'];

export const label = (s) => STATUS_LABEL[s] ?? s;
