<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    items: Object,
    filters: Object,
    kategoris: Array,
    statuses: Array,
});

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

const badge = (s) =>
    ({
        menunggu_verifikasi: 'bg-yellow-100 text-yellow-800',
        butuh_info_tambahan: 'bg-purple-100 text-purple-800',
        diverifikasi: 'bg-blue-100 text-blue-800',
        ditolak: 'bg-red-100 text-red-800',
        diproses: 'bg-indigo-100 text-indigo-800',
        selesai: 'bg-green-100 text-green-800',
    })[s] ?? 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head title="Pengaduan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Pengaduan</h2>
                <Link
                    :href="route('pengaduan.create')"
                    class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700"
                >
                    Buat Pengaduan
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap gap-2 bg-white p-4 shadow-sm sm:rounded-lg">
                    <input v-model="f.q" placeholder="Cari judul / tiket…" class="rounded-md border-gray-300 text-sm" />
                    <select v-model="f.status" class="rounded-md border-gray-300 text-sm">
                        <option value="">Semua status</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <select v-model="f.kategori_id" class="rounded-md border-gray-300 text-sm">
                        <option value="">Semua kategori</option>
                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                    </select>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Tiket</th>
                                <th class="px-4 py-2 text-left">Judul</th>
                                <th class="px-4 py-2 text-left">Kategori</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in items.data" :key="p.id">
                                <td class="px-4 py-2">
                                    <Link :href="route('pengaduan.show', p.nomor_tiket)" class="font-mono text-orange-700 hover:underline">
                                        {{ p.nomor_tiket }}
                                    </Link>
                                </td>
                                <td class="px-4 py-2">{{ p.judul }}</td>
                                <td class="px-4 py-2">{{ p.kategori?.nama_kategori }}</td>
                                <td class="px-4 py-2">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', badge(p.status)]">{{ p.status }}</span>
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ new Date(p.created_at).toLocaleDateString('id-ID') }}</td>
                            </tr>
                            <tr v-if="!items.data.length">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada pengaduan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex gap-2">
                    <Link v-if="items.prev_page_url" :href="items.prev_page_url" class="rounded border px-3 py-1 text-sm">← Prev</Link>
                    <Link v-if="items.next_page_url" :href="items.next_page_url" class="rounded border px-3 py-1 text-sm">Next →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
