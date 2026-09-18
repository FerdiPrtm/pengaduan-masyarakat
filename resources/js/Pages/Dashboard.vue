<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    role: String,
    stats: Object,
    recent: { type: Array, default: () => [] },
    antrean: { type: Array, default: () => [] },
});

const fmt = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">
                        Halo, {{ $page.props.auth.user.name.split(' ')[0] }}!
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ role === 'petugas' ? 'Ada laporan menunggu verifikasimu hari ini.' : 'Pantau semua laporanmu dari sini.' }}
                    </p>
                </div>
                <Link v-if="role === 'pelapor'" :href="route('pengaduan.create')" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-600/25 transition hover:-translate-y-0.5 hover:bg-orange-700">
                    <span class="text-lg leading-none">+</span> Buat Pengaduan
                </Link>
                <Link v-else :href="route('pengaduan.index')" class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-gray-700">Lihat Semua Laporan</Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
                <!-- PELAPOR -->
                <template v-if="role === 'pelapor'">
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <div class="rounded-2xl bg-gray-900 p-5 text-white shadow-sm"><p class="text-sm text-white/70">Total Laporan</p><p class="mt-1 text-4xl font-extrabold">{{ stats.total }}</p></div>
                        <div class="rounded-2xl bg-amber-500 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Menunggu</p><p class="mt-1 text-4xl font-extrabold">{{ stats.menunggu }}</p></div>
                        <div class="rounded-2xl bg-indigo-500 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Diproses</p><p class="mt-1 text-4xl font-extrabold">{{ stats.aktif }}</p></div>
                        <div class="rounded-2xl bg-green-600 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Selesai</p><p class="mt-1 text-4xl font-extrabold">{{ stats.selesai }}</p></div>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h3 class="font-extrabold">Laporan Terbaru</h3>
                            <Link :href="route('pengaduan.index')" class="text-sm font-bold text-orange-700 hover:underline">Lihat semua →</Link>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="p in recent" :key="p.id">
                                <Link :href="route('pengaduan.show', p.nomor_tiket)" class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-orange-50/50">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-bold text-gray-900">{{ p.judul }}</span>
                                        <span class="mt-0.5 block text-sm text-gray-500"><span class="font-mono text-xs text-orange-700">{{ p.nomor_tiket }}</span> · {{ p.kategori?.nama_kategori }} · {{ fmt(p.created_at) }}</span>
                                    </span>
                                    <StatusBadge :status="p.status" />
                                </Link>
                            </li>
                        </ul>
                        <p v-if="!recent.length" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada laporan. <Link :href="route('pengaduan.create')" class="font-bold text-orange-700 hover:underline">Buat yang pertama →</Link></p>
                    </div>
                </template>

                <!-- PETUGAS -->
                <template v-else>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-2xl bg-amber-500 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Antre Verifikasi</p><p class="mt-1 text-4xl font-extrabold">{{ stats.antre }}</p></div>
                        <div class="rounded-2xl bg-indigo-500 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Diproses</p><p class="mt-1 text-4xl font-extrabold">{{ stats.diproses }}</p></div>
                        <div class="rounded-2xl bg-green-600 p-5 text-white shadow-sm"><p class="text-sm text-white/80">Selesai</p><p class="mt-1 text-4xl font-extrabold">{{ stats.selesai }}</p></div>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-amber-200/70 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 bg-amber-50/60 px-5 py-4">
                            <h3 class="font-extrabold">Antrean Verifikasi</h3>
                            <Link :href="route('pengaduan.index', { status: 'menunggu_verifikasi' })" class="text-sm font-bold text-orange-700 hover:underline">Lihat semua →</Link>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="p in antrean" :key="p.id" class="flex flex-wrap items-center gap-3 px-5 py-3.5">
                                <span class="min-w-0 flex-1">
                                    <Link :href="route('pengaduan.show', p.nomor_tiket)" class="block truncate font-bold text-gray-900 hover:text-orange-700">{{ p.judul }}</Link>
                                    <span class="mt-0.5 block text-sm text-gray-500">{{ p.kategori?.nama_kategori }} · oleh {{ p.pelapor?.name }} · {{ fmt(p.created_at) }}</span>
                                </span>
                                <Link :href="route('pengaduan.verify', p.id)" class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-orange-700">Verifikasi</Link>
                            </li>
                        </ul>
                        <p v-if="!antrean.length" class="px-5 py-10 text-center text-sm text-gray-500">Antrean kosong. Kerja bagus!</p>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
