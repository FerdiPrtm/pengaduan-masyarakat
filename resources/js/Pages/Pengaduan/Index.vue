<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    items: Object,
    filters: Object,
    kategoris: Array,
    statuses: Array,
});

const statusLabel = { menunggu_verifikasi: 'Menunggu Verifikasi', butuh_info_tambahan: 'Butuh Info', diverifikasi: 'Diverifikasi', ditolak: 'Ditolak', diproses: 'Diproses', selesai: 'Selesai' };

const f = reactive({
    status: props.filters?.status ?? '',
    kategori_id: props.filters?.kategori_id ?? '',
    q: props.filters?.q ?? '',
});

let t;
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('pengaduan.index'), { ...f }, { preserveState: true, replace: true });
    }, 300);
});

const avatarBg = ['bg-orange-100 text-orange-700', 'bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-purple-100 text-purple-700', 'bg-rose-100 text-rose-700'];
const fmt = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Pengaduan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Daftar Pengaduan</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ items.total }} laporan · klik tiket untuk detail & timeline</p>
                </div>
                <Link :href="route('pengaduan.create')" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-600/25 transition hover:-translate-y-0.5 hover:bg-orange-700">
                    <span class="text-lg leading-none">+</span> Buat Pengaduan
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 grid gap-3 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm sm:grid-cols-3">
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-400">Pencarian</span>
                        <input v-model="f.q" placeholder="Cari judul / nomor tiket…" class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-400">Status</span>
                        <select v-model="f.status" class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200">
                            <option value="">Semua status</option>
                            <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel[s] ?? s }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-400">Kategori</span>
                        <select v-model="f.kategori_id" class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200">
                            <option value="">Semua kategori</option>
                            <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                        </select>
                    </label>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="p in items.data" :key="p.id">
                            <Link :href="route('pengaduan.show', p.nomor_tiket)" class="flex items-center gap-4 px-4 py-4 transition hover:bg-orange-50/50 sm:px-6">
                                <span :class="['hidden size-11 shrink-0 items-center justify-center rounded-xl text-lg font-extrabold sm:flex', avatarBg[p.kategori_id % avatarBg.length]]">{{ (p.kategori?.nama_kategori ?? '?').charAt(0) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="truncate font-bold text-gray-900">{{ p.judul }}</span>
                                        <StatusBadge :status="p.status" />
                                    </span>
                                    <span class="mt-1 block truncate text-sm text-gray-500">
                                        <span class="font-mono text-xs text-orange-700">{{ p.nomor_tiket }}</span>
                                        · {{ p.kategori?.nama_kategori }} · {{ fmt(p.created_at) }}
                                    </span>
                                </span>
                                <span class="shrink-0 text-xl text-gray-300">›</span>
                            </Link>
                        </li>
                    </ul>
                    <div v-if="!items.data.length" class="px-6 py-16 text-center">
                        <p class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl font-bold text-orange-300">?</p>
                        <p class="mt-4 font-bold text-gray-800">Belum ada pengaduan</p>
                        <p class="mt-1 text-sm text-gray-500">Coba ubah filter, atau buat laporan pertama.</p>
                        <Link :href="route('pengaduan.create')" class="mt-4 inline-block rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-700">Buat Pengaduan</Link>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                    <p>Halaman {{ items.current_page }} dari {{ items.last_page }}</p>
                    <div class="flex gap-2">
                        <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="rounded-xl border border-gray-200 bg-white px-4 py-2 font-semibold transition hover:border-orange-300 hover:text-orange-700">← Sebelumnya</Link>
                        <Link v-if="items.next_page_url" :href="items.next_page_url" class="rounded-xl border border-gray-200 bg-white px-4 py-2 font-semibold transition hover:border-orange-300 hover:text-orange-700">Berikutnya →</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
