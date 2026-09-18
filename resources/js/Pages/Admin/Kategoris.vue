<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({ items: Array });

const form = useForm({ nama_kategori: '', unit_penanggung_jawab: '' });
const submit = () => form.post(route('admin.kategoris.store'), { onSuccess: () => form.reset() });
const inputCls = 'block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200';
</script>

<template>
    <Head title="Kelola Kategori" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Kelola Kategori</h2>
            <p class="mt-1 text-sm text-gray-500">Kategori yang sudah dipakai laporan tidak bisa dihapus.</p>
        </template>
        <div class="py-6">
            <div class="mx-auto grid max-w-5xl gap-5 sm:px-6 lg:grid-cols-5 lg:px-8">
                <div class="h-fit rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
                    <h3 class="font-extrabold">Tambah Kategori</h3>
                    <form @submit.prevent="submit" class="mt-4 space-y-3">
                        <div>
                            <label class="text-sm font-bold text-gray-700">Nama kategori</label>
                            <input v-model="form.nama_kategori" placeholder="cth: Penerangan Jalan" :class="inputCls + ' mt-1'" />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-gray-700">Unit penanggung jawab <span class="font-normal text-gray-400">(opsional)</span></label>
                            <input v-model="form.unit_penanggung_jawab" placeholder="cth: Dinas Perhubungan" :class="inputCls + ' mt-1'" />
                        </div>
                        <p v-if="form.errors.nama_kategori" class="text-sm font-medium text-red-600">{{ form.errors.nama_kategori }}</p>
                        <button class="w-full rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-orange-700">Tambah Kategori</button>
                    </form>
                </div>
                <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm lg:col-span-3">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="k in items" :key="k.id" class="flex items-center gap-3 px-4 py-3.5 sm:px-6">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-base font-extrabold text-orange-700">{{ k.nama_kategori.charAt(0) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-bold text-gray-900">{{ k.nama_kategori }}</span>
                                <span class="block text-sm text-gray-500">{{ k.unit_penanggung_jawab ?? 'Tanpa unit khusus' }} · {{ k.pengaduan_count }} laporan</span>
                            </span>
                            <button @click="router.delete(route('admin.kategoris.destroy', k.id))" class="rounded-lg px-3 py-1.5 text-sm font-bold text-red-600 transition hover:bg-red-50">Hapus</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
