<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const menuOpen = ref(false);

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    stats: Object,
});

const steps = [
    { n: '01', t: 'Buat laporan', d: 'Isi judul, kategori, lokasi, dan lampirkan foto bukti. Dapat nomor tiket otomatis.' },
    { n: '02', t: 'Diverifikasi petugas', d: 'Petugas memeriksa kelengkapan dan kevalidan laporanmu.' },
    { n: '03', t: 'Ditindaklanjuti', d: 'Laporan valid diteruskan ke unit terkait dan mulai diproses.' },
    { n: '04', t: 'Selesai & terpantau', d: 'Pantau setiap perubahan status lewat timeline sampai selesai.' },
];

const faqs = [
    { q: 'Apakah melapor harus daftar akun?', a: 'Ya, cukup daftar sekali dengan nama dan email. Akun dipakai agar kamu bisa memantau status laporanmu dan petugas bisa meminta info tambahan bila diperlukan.' },
    { q: 'Bukti apa yang perlu dilampirkan?', a: 'Foto kondisi di lapangan (1–5 foto, format JPG/PNG, maksimal 2 MB per file). Foto yang jelas membuat verifikasi jauh lebih cepat.' },
    { q: 'Berapa lama laporan diproses?', a: 'Setelah diverifikasi valid, laporan diteruskan ke unit terkait. Lama penanganan tergantung jenis masalahnya — semua progres bisa kamu pantau di halaman detail laporan.' },
    { q: 'Bagaimana jika laporan saya ditolak?', a: 'Penolakan selalu disertai alasan dari petugas. Kamu bisa membuat laporan baru yang lebih lengkap sesuai catatan tersebut.' },
];

const avatarBg = ['bg-orange-100 text-orange-700', 'bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-purple-100 text-purple-700', 'bg-rose-100 text-rose-700'];
</script>

<template>
    <Head title="LaporWarga — Sampaikan Pengaduanmu" />

    <div class="min-h-screen bg-white font-sans text-gray-900 antialiased">
        <!-- NAV -->
        <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-orange-600 text-white shadow-sm">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </span>
                    <span class="text-lg font-extrabold tracking-tight">Lapor<span class="text-orange-600">Warga</span></span>
                </Link>
                <nav class="hidden items-center gap-7 text-sm font-medium text-gray-600 md:flex">
                    <a href="#kategori" class="transition hover:text-orange-600">Kategori</a>
                    <a href="#cara" class="transition hover:text-orange-600">Cara Melapor</a>
                    <a href="#faq" class="transition hover:text-orange-600">FAQ</a>
                </nav>
                <div class="flex items-center gap-2.5">
                    <template v-if="canLogin">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Dasbor Saya</Link>
                        <template v-else>
                            <Link :href="route('login')" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 transition hover:text-orange-600 sm:block">Masuk</Link>
                            <Link v-if="canRegister" :href="route('register')" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700">Buat Laporan</Link>
                        </template>
                    </template>
                    <button @click="menuOpen = !menuOpen" aria-label="Menu navigasi" class="rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 md:hidden">
                        <svg v-if="!menuOpen" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                        <svg v-else class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
            </div>
            <div v-if="menuOpen" class="border-t border-gray-100 bg-white px-4 py-3 md:hidden">
                <nav class="grid gap-1 text-sm font-semibold text-gray-700" @click="menuOpen = false">
                    <a href="#kategori" class="rounded-lg px-3 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Kategori</a>
                    <a href="#cara" class="rounded-lg px-3 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Cara Melapor</a>
                    <a href="#faq" class="rounded-lg px-3 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">FAQ</a>
                    <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')" class="rounded-lg px-3 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Masuk</Link>
                </nav>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(60rem_30rem_at_80%_-10%,#ffedd5,transparent)]"></div>
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 md:pt-20 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">
                        <span class="size-1.5 rounded-full bg-orange-500"></span>
                        Layanan pengaduan masyarakat online
                    </p>
                    <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-5xl">
                        Lihat masalah di lingkunganmu? <span class="text-orange-600">Laporkan.</span>
                    </h1>
                    <p class="mt-5 max-w-lg text-lg text-gray-600">
                        Jalan rusak, lampu mati, sampah menumpuk, pelayanan lambat — sampaikan dalam hitungan menit, lampirkan foto, dan pantau penanganannya sampai selesai.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link v-if="canRegister" :href="route('register')" class="rounded-xl bg-orange-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-600/25 transition hover:-translate-y-0.5 hover:bg-orange-700">Buat Laporan Sekarang</Link>
                        <a href="#cara" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-800 ring-1 ring-inset ring-gray-200 transition hover:ring-orange-300">Lihat Cara Kerja</a>
                    </div>
                    <dl class="mt-10 grid max-w-md grid-cols-3 gap-6 border-t border-gray-100 pt-6">
                        <div><dt class="text-2xl font-extrabold">{{ stats.total }}</dt><dd class="mt-1 text-xs font-medium text-gray-500">Laporan masuk</dd></div>
                        <div><dt class="text-2xl font-extrabold">{{ stats.ditindaklanjuti }}</dt><dd class="mt-1 text-xs font-medium text-gray-500">Ditindaklanjuti</dd></div>
                        <div><dt class="text-2xl font-extrabold text-green-600">{{ stats.selesai }}</dt><dd class="mt-1 text-xs font-medium text-gray-500">Selesai</dd></div>
                    </dl>
                </div>

                <!-- Visual: mockup kartu tiket -->
                <div class="relative mx-auto w-full max-w-md">
                    <p class="absolute -top-3 left-6 z-10 rounded-full bg-gray-900 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow">Contoh tampilan</p>
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl shadow-orange-900/10">
                        <div class="flex items-center justify-between">
                            <p class="font-mono text-xs font-bold text-gray-400">PGD-20260918-0001</p>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800"><span class="size-1.5 rounded-full bg-current"></span>Selesai</span>
                        </div>
                        <p class="mt-3 font-bold">Jalan berlubang di Jl. Merdeka</p>
                        <p class="mt-1 text-sm text-gray-500">Infrastruktur · Jl. Merdeka No. 10</p>
                        <div class="mt-4 space-y-0">
                            <div v-for="(s, i) in ['Diajukan', 'Diverifikasi', 'Diproses', 'Selesai']" :key="s" class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="flex size-5 items-center justify-center rounded-full bg-green-500 text-[10px] font-bold text-white">✓</span>
                                    <span v-if="i < 3" class="w-0.5 flex-1 bg-green-200"></span>
                                </div>
                                <p class="pb-4 text-sm font-medium text-gray-700">{{ s }}</p>
                            </div>
                        </div>
                        <div class="mt-2 rounded-xl bg-orange-50 p-3 text-center text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-100">Setiap progres tercatat & bisa dipantau</div>
                    </div>
                    <div class="absolute -right-4 -top-4 -z-10 size-40 rounded-full bg-orange-100 blur-2xl"></div>
                    <div class="absolute -bottom-6 -left-6 -z-10 size-40 rounded-full bg-amber-100 blur-2xl"></div>
                </div>
            </div>
        </section>

        <!-- KATEGORI -->
        <section id="kategori" class="border-y border-gray-100 bg-gray-50/70">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mau lapor apa hari ini?</h2>
                <p class="mt-2 text-gray-600">Pilih kategori yang paling sesuai — laporanmu otomatis diteruskan ke unit terkait.</p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div v-for="(k, i) in stats.kategori" :key="k.id" class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <span :class="['flex size-11 items-center justify-center rounded-xl text-lg font-extrabold', avatarBg[i % avatarBg.length]]">{{ k.nama_kategori.charAt(0) }}</span>
                        <p class="mt-3 font-bold">{{ k.nama_kategori }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ k.unit_penanggung_jawab ?? 'Semua unit' }} · {{ k.pengaduan_count }} laporan</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CARA MELAPOR -->
        <section id="cara" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Dari laporan sampai selesai</h2>
            <p class="mt-2 max-w-2xl text-gray-600">Empat langkah sederhana. Kamu tidak perlu datang ke kantor mana pun.</p>
            <ol class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="s in steps" :key="s.n" class="relative rounded-2xl bg-white">
                    <p class="text-5xl font-extrabold text-orange-100">{{ s.n }}</p>
                    <p class="-mt-6 font-bold">{{ s.t }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ s.d }}</p>
                </li>
            </ol>
            <div class="mt-10 rounded-2xl bg-gray-900 p-6 text-white sm:p-8">
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-lg font-bold">Punya nomor tiket? Lacak langsung statusnya.</p>
                        <p class="mt-1 text-sm text-gray-300">Masuk untuk melihat timeline lengkap setiap laporanmu.</p>
                    </div>
                    <Link :href="route('login')" class="shrink-0 rounded-xl bg-orange-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-orange-400">Lacak Laporanku</Link>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="border-t border-gray-100 bg-gray-50/70">
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
                <h2 class="text-center text-2xl font-extrabold tracking-tight sm:text-3xl">Sering ditanyakan</h2>
                <div class="mt-8 space-y-3">
                    <details v-for="f in faqs" :key="f.q" class="group rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm open:ring-1 open:ring-orange-200">
                        <summary class="cursor-pointer list-none font-bold marker:hidden [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center justify-between gap-4">{{ f.q }}<span class="text-orange-500 transition group-open:rotate-45">+</span></span>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ f.a }}</p>
                    </details>
                </div>
            </div>
        </section>

        <!-- CTA + FOOTER -->
        <section class="bg-orange-600">
            <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-5 px-4 py-12 sm:px-6 lg:flex-row lg:items-center lg:px-8">
                <div>
                    <p class="text-2xl font-extrabold text-white sm:text-3xl">Suara warga, perubahan nyata.</p>
                    <p class="mt-1 text-orange-100">Daftar gratis dalam 1 menit, laporan pertamamu 5 menit kemudian.</p>
                </div>
                <Link v-if="canRegister" :href="route('register')" class="shrink-0 rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-orange-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-orange-50">Daftar & Lapor Sekarang</Link>
            </div>
        </section>
        <footer class="border-t border-gray-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-gray-500 sm:flex-row sm:px-6 lg:px-8">
                <p class="font-bold text-gray-800">Lapor<span class="text-orange-600">Warga</span></p>
                <p>Transparan · Terpantau · Tuntas</p>
            </div>
        </footer>
    </div>
</template>
