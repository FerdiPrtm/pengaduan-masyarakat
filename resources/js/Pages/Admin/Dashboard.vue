<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { STATUS_LABEL as statusLabel } from '@/lib/status.js';

const props = defineProps({ perStatus: Object, perKategori: Object, total: Number });

const barColor = { menunggu_verifikasi: 'bg-amber-400', butuh_info_tambahan: 'bg-purple-400', diverifikasi: 'bg-blue-400', ditolak: 'bg-red-400', diproses: 'bg-indigo-500', selesai: 'bg-green-500' };

const maxStatus = computed(() => Math.max(1, ...Object.values(props.perStatus)));
const maxKat = computed(() => Math.max(1, ...Object.values(props.perKategori)));
const selesai = computed(() => props.perStatus.selesai ?? 0);
const antre = computed(() => (props.perStatus.menunggu_verifikasi ?? 0) + (props.perStatus.butuh_info_tambahan ?? 0));
const pct = (v, m) => Math.round((v / m) * 100);

const cards = computed(() => [
    { t: 'Total Laporan', v: props.total, d: 'keseluruhan', bg: 'bg-gray-900' },
    { t: 'Antre Verifikasi', v: antre.value, d: 'perlu perhatian', bg: 'bg-amber-600' },
    { t: 'Selesai', v: selesai.value, d: props.total ? Math.round((selesai.value / props.total) * 100) + '% tuntas' : '—', bg: 'bg-green-700' },
]);
</script>

<template>
    <Head title="Dasbor Admin" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Ringkasan Laporan</h2>
                    <p class="mt-1 text-sm text-gray-500">Pantau beban antrean dan progres penanganan.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('admin.users')" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:border-orange-300 hover:text-orange-700">Kelola User</Link>
                    <Link :href="route('admin.kategoris')" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:border-orange-300 hover:text-orange-700">Kelola Kategori</Link>
                    <a :href="route('admin.export')" class="rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-orange-700">Export CSV</a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div v-for="c in cards" :key="c.t" class="overflow-hidden rounded-2xl text-white shadow-sm">
                        <div :class="['p-5', c.bg]">
                            <p class="text-sm font-medium text-white/80">{{ c.t }}</p>
                            <p class="mt-1 text-4xl font-extrabold">{{ c.v }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-white/70">{{ c.d }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Berdasarkan Status</h3>
                        <div class="mt-4 space-y-3">
                            <div v-for="(v, k) in perStatus" :key="k">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">{{ statusLabel[k] ?? k }}</span>
                                    <span class="font-extrabold">{{ v }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div :class="['h-full rounded-full transition-all', barColor[k] ?? 'bg-gray-400']" :style="{ width: pct(v, maxStatus) + '%' }"></div>
                                </div>
                            </div>
                            <p v-if="!Object.keys(perStatus).length" class="py-4 text-center text-sm text-gray-400">Belum ada data.</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Berdasarkan Kategori</h3>
                        <div class="mt-4 space-y-3">
                            <div v-for="(v, k) in perKategori" :key="k">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">{{ k }}</span>
                                    <span class="font-extrabold">{{ v }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-orange-500 transition-all" :style="{ width: pct(v, maxKat) + '%' }"></div>
                                </div>
                            </div>
                            <p v-if="!Object.keys(perKategori).length" class="py-4 text-center text-sm text-gray-400">Belum ada data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
