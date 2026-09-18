<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ items: Array });

const form = useForm({ nama_kategori: '', unit_penanggung_jawab: '' });
const submit = () => form.post(route('admin.kategoris.store'), { onSuccess: () => form.reset() });
</script>

<template>
    <Head title="Kelola Kategori" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Kelola Kategori</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto grid max-w-5xl gap-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold">Tambah Kategori</h3>
                    <form @submit.prevent="submit" class="mt-3 space-y-3">
                        <input v-model="form.nama_kategori" placeholder="Nama kategori" class="block w-full rounded-md border-gray-300 text-sm" />
                        <input v-model="form.unit_penanggung_jawab" placeholder="Unit penanggung jawab (opsional)" class="block w-full rounded-md border-gray-300 text-sm" />
                        <p v-if="form.errors.nama_kategori" class="text-sm text-red-600">{{ form.errors.nama_kategori }}</p>
                        <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Tambah</button>
                    </form>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <ul class="divide-y text-sm">
                        <li v-for="k in items" :key="k.id" class="flex items-center justify-between py-2">
                            <span>{{ k.nama_kategori }} <span class="text-gray-400">({{ k.pengaduan_count }})</span></span>
                            <button @click="$inertia.delete(route('admin.kategoris.destroy', k.id))" class="text-red-600 hover:underline">Hapus</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
