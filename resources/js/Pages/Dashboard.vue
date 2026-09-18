<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: String,
    stats: Object,
    recent: { type: Array, default: () => [] },
    antrean: { type: Array, default: () => [] },
    perhatian: { type: Array, default: () => [] },
});

const isPetugas = computed(() => props.role === 'petugas');
const firstName = computed(() => usePage().props.auth.user.name.split(' ')[0]);
const fmt = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const tuntasPct = computed(() => (props.stats.total ? Math.round(((props.stats.selesai ?? 0) / props.stats.total) * 100) : 0));
const pct = (v) => (props.stats.total ? Math.round((v / props.stats.total) * 100) : 0);
const pertamaAntrean = computed(() => props.antrean[0] ?? null);

const ctaLabel = computed(() => (isPetugas.value && pertamaAntrean.value ? 'Mulai Verifikasi' : 'Lihat Semua Laporan'));
const ctaHref = computed(() =>
    pertamaAntrean.value ? route('pengaduan.verify', pertamaAntrean.value.id) : route('pengaduan.index'),
);

const summaryText = computed(() => {
    if (isPetugas.value) {
        const a = props.stats.antre ?? 0;
        return a ? `Ada ${a} laporan menunggu keputusanmu.` : 'Semua laporan sudah dikeputusan.';
    }
    const { total = 0, menunggu = 0, aktif = 0, selesai = 0 } = props.stats;
    return `${total} laporan · ${menunggu} menunggu · ${aktif} diproses · ${selesai} selesai`;
});

function waktuLalu(d) {
    const s = Math.max(0, (new Date() - new Date(d)) / 1000);
    if (s < 3600) return Math.max(1, Math.round(s / 60)) + ' menit lalu';
    if (s < 86400) return Math.round(s / 3600) + ' jam lalu';
    return Math.round(s / 86400) + ' hari lalu';
}

const ICON = {
    file: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z|M14 2v6h6',
    clock: 'M12 6v6l4 2|M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z',
    briefcase: 'M21 12a9 9 0 1 1-3-6.7|M21 3v6h-6',
    check: 'M20 6 9 17l-5-5',
};

const pelaporCards = computed(() => [
    { t: 'Total Laporan', v: props.stats.total ?? 0, d: 'keseluruhan', chip: 'bg-gray-100 text-gray-700', icon: ICON.file, href: route('pengaduan.index') },
    { t: 'Menunggu', v: props.stats.menunggu ?? 0, d: 'verifikasi / info', chip: 'bg-amber-100 text-amber-700', icon: ICON.clock, href: route('pengaduan.index', { status: 'menunggu_verifikasi' }) },
    { t: 'Diproses', v: props.stats.aktif ?? 0, d: 'ditindaklanjuti', chip: 'bg-indigo-100 text-indigo-700', icon: ICON.briefcase, href: route('pengaduan.index') },
    { t: 'Selesai', v: props.stats.selesai ?? 0, d: tuntasPct.value + '% tuntas', chip: 'bg-green-100 text-green-700', icon: ICON.check, href: route('pengaduan.index', { status: 'selesai' }) },
]);

const petugasCards = computed(() => [
    { t: 'Antre Verifikasi', v: props.stats.antre ?? 0, d: 'butuh keputusan', accent: true, icon: ICON.clock, href: route('pengaduan.index', { status: 'menunggu_verifikasi' }) },
    { t: 'Diproses', v: props.stats.diproses ?? 0, d: 'diverifikasi / diproses', chip: 'bg-indigo-100 text-indigo-700', icon: ICON.briefcase, href: route('pengaduan.index') },
    { t: 'Selesai', v: props.stats.selesai ?? 0, d: tuntasPct.value + '% tuntas', chip: 'bg-green-100 text-green-700', icon: ICON.check, href: route('pengaduan.index', { status: 'selesai' }) },
]);

const tahap = computed(() => [
    { t: 'Menunggu', v: props.stats.menunggu ?? 0, dot: 'bg-amber-500', bar: 'bg-amber-500' },
    { t: 'Diproses', v: props.stats.aktif ?? 0, dot: 'bg-indigo-500', bar: 'bg-indigo-500' },
    { t: 'Selesai', v: props.stats.selesai ?? 0, dot: 'bg-green-600', bar: 'bg-green-600' },
]);
</script>

<template>
    <Head title="Dasbor" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- BILAH SELAMAT DATANG -->
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-orange-700 px-6 py-5 text-white shadow-sm">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-orange-100">Dasbor {{ isPetugas ? 'Petugas' : 'Pelapor' }}</p>
                    <h1 class="mt-1 text-xl font-extrabold tracking-tight sm:text-2xl">Halo, {{ firstName }}!</h1>
                    <p class="mt-0.5 text-sm text-orange-100">{{ summaryText }}</p>
                </div>
                <Link :href="isPetugas ? ctaHref : route('pengaduan.create')" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-orange-700 shadow-sm transition hover:bg-orange-50">
                    <template v-if="isPetugas">{{ ctaLabel }}</template>
                    <template v-else><span class="text-lg leading-none">+</span> Buat Pengaduan</template>
                </Link>
            </section>

            <!-- PELAPOR -->
            <template v-if="!isPetugas">
                <div v-if="stats.total === 0" class="rounded-2xl border border-gray-200/70 bg-white px-6 py-14 text-center shadow-sm">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-orange-50">
                        <svg class="size-8 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z" /><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6" /></svg>
                    </div>
                    <h2 class="mt-5 text-lg font-extrabold text-gray-900">Belum ada laporan</h2>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Sampaikan pengaduan pertamamu dan pantau penanganannya sampai tuntas.</p>
                    <Link :href="route('pengaduan.create')" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700">
                        <span class="text-lg leading-none">+</span> Buat Pengaduan Pertama
                    </Link>
                </div>

                <template v-else>
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <StatCard v-for="c in pelaporCards" :key="c.t" :title="c.t" :value="c.v" :sub="c.d" :href="c.href" :icon="c.icon" :chip="c.chip" />
                    </div>

                    <div v-if="perhatian.length" class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-extrabold text-amber-900">Perlu perhatianmu ({{ perhatian.length }})</h3>
                            <span class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-900">Butuh Info Tambahan</span>
                        </div>
                        <p class="mt-0.5 text-sm text-amber-800/80">Petugas meminta info tambahan — lengkapi agar laporanmu terus diproses.</p>
                        <div class="mt-3 space-y-2">
                            <Link v-for="p in perhatian" :key="p.id" :href="route('pengaduan.show', p.nomor_tiket)" class="flex items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 shadow-sm transition hover:shadow">
                                <span class="min-w-0"><span class="block truncate text-sm font-bold text-gray-900">{{ p.judul }}</span><span class="font-mono text-xs text-amber-700">{{ p.nomor_tiket }}</span></span>
                                <span class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white">Lengkapi →</span>
                            </Link>
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm lg:col-span-2">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                <h3 class="font-extrabold">Laporan Terbaru</h3>
                                <Link :href="route('pengaduan.index')" class="text-sm font-bold text-orange-700 hover:underline">Lihat semua →</Link>
                            </div>
                            <ul class="divide-y divide-gray-100">
                                <li v-for="p in recent" :key="p.id">
                                    <Link :href="route('pengaduan.show', p.nomor_tiket)" class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-orange-50/50">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-sm font-extrabold text-orange-700">{{ (p.kategori?.nama_kategori ?? '?').charAt(0) }}</span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-bold text-gray-900">{{ p.judul }}</span>
                                            <span class="mt-0.5 block truncate text-sm text-gray-500"><span class="font-mono text-xs text-orange-700">{{ p.nomor_tiket }}</span> · {{ p.kategori?.nama_kategori }} · {{ fmt(p.created_at) }}</span>
                                        </span>
                                        <StatusBadge :status="p.status" />
                                    </Link>
                                </li>
                            </ul>
                            <p v-if="!recent.length" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada laporan untuk ditampilkan.</p>
                        </div>

                        <div class="space-y-5">
                            <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm">
                                <h3 class="font-extrabold">Tahapan laporanmu</h3>
                                <div class="mt-4 space-y-4">
                                    <div v-for="t in tahap" :key="t.t">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="flex items-center gap-2 font-semibold text-gray-700"><span class="size-2 rounded-full" :class="t.dot"></span>{{ t.t }}</span>
                                            <span class="font-extrabold tabular-nums text-gray-900">{{ t.v }}</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full transition-all" :class="t.bar" :style="{ width: pct(t.v) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-4 border-t border-gray-100 pt-3 text-xs text-gray-500">{{ tuntasPct }}% laporanmu sudah selesai. Setiap perubahan status muncul di timeline.</p>
                            </div>

                            <div class="rounded-2xl bg-gray-900 p-5 text-white shadow-sm">
                                <h3 class="font-extrabold">Butuh bantuan?</h3>
                                <p class="mt-1 text-sm text-gray-300">Lihat cara melapor yang baik dan jawaban atas pertanyaan umum.</p>
                                <a href="/#faq" class="mt-3 inline-block rounded-xl bg-white/10 px-4 py-2 text-sm font-bold ring-1 ring-inset ring-white/20 transition hover:bg-white/20">Buka FAQ →</a>
                            </div>
                        </div>
                    </div>
                </template>
            </template>

            <!-- PETUGAS -->
            <template v-else>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <StatCard v-for="c in petugasCards" :key="c.t" :title="c.t" :value="c.v" :sub="c.d" :href="c.href" :icon="c.icon" :chip="c.chip" :accent="c.accent" />
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="font-extrabold">Antrean Verifikasi</h3>
                        <Link :href="route('pengaduan.index', { status: 'menunggu_verifikasi' })" class="text-sm font-bold text-orange-700 hover:underline">Buka semua →</Link>
                    </div>
                    <ul v-if="antrean.length" class="divide-y divide-gray-100">
                        <li v-for="(p, i) in antrean" :key="p.id" class="flex flex-wrap items-center gap-3 px-5 py-3.5 transition hover:bg-orange-50/50">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-extrabold text-amber-700">{{ i + 1 }}</span>
                            <span class="min-w-0 flex-1">
                                <Link :href="route('pengaduan.show', p.nomor_tiket)" class="block truncate font-bold text-gray-900 hover:text-orange-700">{{ p.judul }}</Link>
                                <span class="mt-0.5 block truncate text-sm text-gray-500">{{ p.kategori?.nama_kategori }} · oleh {{ p.pelapor?.name }} · menunggu {{ waktuLalu(p.created_at) }}</span>
                            </span>
                            <Link :href="route('pengaduan.verify', p.id)" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700">Verifikasi →</Link>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-14 text-center">
                        <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-green-50">
                            <svg class="size-8 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-extrabold text-gray-900">Antrean kosong</h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Tidak ada laporan yang menunggu keputusanmu saat ini. Kerja bagus!</p>
                        <Link :href="route('pengaduan.index')" class="mt-5 inline-flex rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700">Lihat Semua Laporan</Link>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>