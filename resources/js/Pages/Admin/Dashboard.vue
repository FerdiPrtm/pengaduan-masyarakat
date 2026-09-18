<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { STATUS_LABEL as statusLabel, STATUS_ORDER } from '@/lib/status.js';

const props = defineProps({ perStatus: Object, perKategori: Object, total: Number });

const barColor = { menunggu_verifikasi: 'bg-amber-500', butuh_info_tambahan: 'bg-purple-500', diverifikasi: 'bg-blue-500', ditolak: 'bg-red-500', diproses: 'bg-indigo-500', selesai: 'bg-green-600' };

const selesai = computed(() => props.perStatus.selesai ?? 0);
const antre = computed(() => (props.perStatus.menunggu_verifikasi ?? 0) + (props.perStatus.butuh_info_tambahan ?? 0));
const pct = (v) => (props.total ? Math.round((v / props.total) * 100) : 0);

const statusBars = computed(() => STATUS_ORDER.map((k) => ({ key: k, label: statusLabel[k], v: props.perStatus[k] ?? 0 })));
const katBars = computed(() => Object.entries(props.perKategori).sort((a, b) => b[1] - a[1]));

const ICON = {
    file: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z|M14 2v6h6',
    clock: 'M12 6v6l4 2|M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z',
    check: 'M20 6 9 17l-5-5',
};
</script>

<template>
    <Head title="Dasbor Admin" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- BILAH RINGKASAN -->
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-orange-700 px-6 py-5 text-white shadow-sm">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-orange-100">Dasbor Admin</p>
                    <h1 class="mt-1 text-xl font-extrabold tracking-tight sm:text-2xl">Ringkasan Laporan</h1>
                    <p class="mt-0.5 text-sm text-orange-100">{{ total }} laporan · {{ antre }} menunggu verifikasi · {{ selesai }} selesai</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.users')" class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-inset ring-white/25 transition hover:bg-white/20">Kelola User</Link>
                    <Link :href="route('admin.kategoris')" class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-inset ring-white/25 transition hover:bg-white/20">Kelola Kategori</Link>
                    <a :href="route('admin.export')" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-orange-700 shadow-sm transition hover:bg-orange-50">Export CSV</a>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <StatCard title="Total Laporan" :value="total" sub="keseluruhan" :href="route('pengaduan.index')" :icon="ICON.file" chip="bg-gray-100 text-gray-700" />
                <StatCard title="Antre Verifikasi" :value="antre" sub="menunggu keputusan" :href="route('pengaduan.index', { status: 'menunggu_verifikasi' })" :icon="ICON.clock" :accent="true" />
                <StatCard title="Selesai" :value="selesai" :sub="pct(selesai) + '% tuntas'" :href="route('pengaduan.index', { status: 'selesai' })" :icon="ICON.check" chip="bg-green-100 text-green-700" />
            </div>

            <div v-if="total === 0" class="rounded-2xl border border-gray-200/70 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-orange-50">
                    <svg class="size-8 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /></svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-gray-900">Belum ada laporan masuk</h2>
                <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Saat warga mulai melapor, ringkasan status dan kategori akan muncul di sini.</p>
                <Link :href="route('pengaduan.index')" class="mt-5 inline-flex rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700">Lihat Pengaduan</Link>
            </div>

            <div v-else class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold">Berdasarkan Status</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-600">{{ total }} laporan</span>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div v-for="s in statusBars" :key="s.key">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 font-semibold text-gray-700"><span class="size-2 rounded-full" :class="barColor[s.key]"></span>{{ s.label }}</span>
                                <span class="font-extrabold tabular-nums text-gray-900">{{ s.v }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full transition-all" :class="barColor[s.key]" :style="{ width: pct(s.v) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-5 border-t border-gray-100 pt-3 text-xs text-gray-500">{{ selesai }} dari {{ total }} laporan selesai ditangani.</p>
                </div>

                <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold">Berdasarkan Kategori</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-600">{{ katBars.length }} kategori</span>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div v-for="[nama, v] in katBars" :key="nama">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-gray-700">{{ nama }}</span>
                                <span class="font-extrabold tabular-nums text-gray-900">{{ v }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-orange-500 transition-all" :style="{ width: pct(v) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-5 border-t border-gray-100 pt-3 text-xs text-gray-500">Distribusi {{ total }} laporan di seluruh kategori.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>