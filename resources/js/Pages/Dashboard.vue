<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

const firstName = computed(() => usePage().props.auth.user.name.split(' ')[0]);
const fmt = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const tuntasPct = computed(() => (props.stats.total ? Math.round(((props.stats.selesai ?? 0) / props.stats.total) * 100) : 0));

const statCards = computed(() =>
    props.role === 'petugas'
        ? [
            { t: 'Antre Verifikasi', v: props.stats.antre, d: 'butuh keputusan', chip: 'bg-amber-100 text-amber-600', icon: 'M12 6v6l4 2|M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z' },
            { t: 'Sedang Diproses', v: props.stats.diproses, d: 'ditindaklanjuti', chip: 'bg-indigo-100 text-indigo-600', icon: 'M21 12a9 9 0 1 1-3-6.7|M21 3v6h-6' },
            { t: 'Selesai', v: props.stats.selesai, d: 'tuntas', chip: 'bg-green-100 text-green-600', icon: 'M20 6 9 17l-5-5' },
        ]
        : [
            { t: 'Total Laporan', v: props.stats.total, d: 'keseluruhan', chip: 'bg-gray-100 text-gray-700', icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z|M14 2v6h6' },
            { t: 'Menunggu', v: props.stats.menunggu, d: 'verifikasi / info', chip: 'bg-amber-100 text-amber-600', icon: 'M12 6v6l4 2|M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z' },
            { t: 'Diproses', v: props.stats.aktif, d: 'ditindaklanjuti', chip: 'bg-indigo-100 text-indigo-600', icon: 'M21 12a9 9 0 1 1-3-6.7|M21 3v6h-6' },
            { t: 'Selesai', v: props.stats.selesai, d: tuntasPct.value + '% tuntas', chip: 'bg-green-100 text-green-600', icon: 'M20 6 9 17l-5-5' },
        ],
);

const avatarBg = ['bg-orange-100 text-orange-700', 'bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-purple-100 text-purple-700'];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- HERO -->
        <div class="relative overflow-hidden bg-gradient-to-r from-orange-700 via-orange-600 to-amber-500">
            <div class="pointer-events-none absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_85%_20%,white,transparent_45%),radial-gradient(circle_at_10%_90%,white,transparent_40%)]"></div>
            <div class="relative mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-white">
                    <p class="text-sm font-semibold text-orange-100">{{ role === 'petugas' ? 'Dasbor Petugas' : 'Dasbor Pelapor' }} · {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Halo, {{ firstName }}!</h2>
                    <p class="mt-1 text-orange-50">{{ role === 'petugas' ? 'Ada laporan yang menunggu keputusanmu.' : 'Pantau semua laporanmu dari satu tempat.' }}</p>
                </div>
                <Link v-if="role === 'pelapor'" :href="route('pengaduan.create')" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-orange-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-orange-50">
                    <span class="text-lg leading-none">+</span> Buat Pengaduan
                </Link>
                <Link v-else :href="route('pengaduan.index')" class="rounded-xl bg-gray-900 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-gray-800">Lihat Semua Laporan</Link>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
                <!-- STAT CARDS -->
                <div class="grid grid-cols-2 gap-4" :class="role === 'petugas' ? 'lg:grid-cols-3' : 'lg:grid-cols-4'">
                    <div v-for="c in statCards" :key="c.t" class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <span :class="['flex size-10 items-center justify-center rounded-xl', c.chip]">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path :d="c.icon.split('|')[0]" /><path v-if="c.icon.split('|')[1]" :d="c.icon.split('|')[1]" /></svg>
                        </span>
                        <p class="mt-3 text-3xl font-extrabold tracking-tight">{{ c.v }}</p>
                        <p class="mt-0.5 text-sm font-bold text-gray-700">{{ c.t }}</p>
                        <p class="text-xs text-gray-400">{{ c.d }}</p>
                    </div>
                </div>

                <!-- PELAPOR -->
                <template v-if="role === 'pelapor'">
                    <div v-if="perhatian.length" class="rounded-2xl border border-purple-200 bg-purple-50/70 p-5 shadow-sm">
                        <h3 class="font-extrabold text-purple-900">Perlu perhatianmu ({{ perhatian.length }})</h3>
                        <p class="mt-0.5 text-sm text-purple-800/80">Petugas meminta info tambahan — lengkapi agar laporanmu diproses.</p>
                        <div class="mt-3 space-y-2">
                            <Link v-for="p in perhatian" :key="p.id" :href="route('pengaduan.show', p.nomor_tiket)" class="flex items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 shadow-sm transition hover:shadow">
                                <span class="min-w-0"><span class="block truncate text-sm font-bold">{{ p.judul }}</span><span class="font-mono text-xs text-orange-700">{{ p.nomor_tiket }}</span></span>
                                <span class="shrink-0 rounded-lg bg-purple-700 px-3 py-1.5 text-xs font-bold text-white">Lengkapi →</span>
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
                            <p v-if="!recent.length" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada laporan. <Link :href="route('pengaduan.create')" class="font-bold text-orange-700 hover:underline">Buat yang pertama →</Link></p>
                        </div>
                        <div class="space-y-5">
                            <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm">
                                <h3 class="font-extrabold">Tingkat Ketuntasan</h3>
                                <p class="mt-3 text-4xl font-extrabold">{{ tuntasPct }}<span class="text-lg text-gray-400">%</span></p>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-green-500 transition-all" :style="{ width: tuntasPct + '%' }"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">{{ stats.selesai }} dari {{ stats.total }} laporanmu selesai ditangani.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-900 p-5 text-white shadow-sm">
                                <h3 class="font-extrabold">Butuh bantuan?</h3>
                                <p class="mt-1 text-sm text-gray-300">Lihat cara melapor yang baik dan jawaban atas pertanyaan umum.</p>
                                <a href="/#faq" class="mt-3 inline-block rounded-xl bg-white/10 px-4 py-2 text-sm font-bold ring-1 ring-inset ring-white/20 transition hover:bg-white/20">Buka FAQ →</a>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- PETUGAS -->
                <template v-else>
                    <div class="overflow-hidden rounded-2xl border border-amber-200/70 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 bg-amber-50/60 px-5 py-4">
                            <h3 class="font-extrabold">Antrean Verifikasi</h3>
                            <Link :href="route('pengaduan.index', { status: 'menunggu_verifikasi' })" class="text-sm font-bold text-orange-700 hover:underline">Lihat semua →</Link>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="(p, i) in antrean" :key="p.id" class="flex flex-wrap items-center gap-3 px-5 py-3.5 transition hover:bg-orange-50/50">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-extrabold text-amber-700">{{ i + 1 }}</span>
                                <span class="min-w-0 flex-1">
                                    <Link :href="route('pengaduan.show', p.nomor_tiket)" class="block truncate font-bold text-gray-900 hover:text-orange-700">{{ p.judul }}</Link>
                                    <span class="mt-0.5 block truncate text-sm text-gray-500">{{ p.kategori?.nama_kategori }} · oleh {{ p.pelapor?.name }} · {{ fmt(p.created_at) }}</span>
                                </span>
                                <Link :href="route('pengaduan.verify', p.id)" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-orange-700">Verifikasi</Link>
                            </li>
                        </ul>
                        <p v-if="!antrean.length" class="px-5 py-10 text-center text-sm text-gray-500">Antrean kosong. Kerja bagus!</p>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
