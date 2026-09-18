<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ perStatus: Object, perKategori: Object, total: Number });
</script>

<template>
    <Head title="Admin Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Admin Dashboard</h2>
                <div class="flex gap-2">
                    <Link :href="route('admin.users')" class="rounded-md border px-3 py-2 text-sm">Kelola User</Link>
                    <Link :href="route('admin.kategoris')" class="rounded-md border px-3 py-2 text-sm">Kelola Kategori</Link>
                    <a :href="route('admin.export')" class="rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white hover:bg-orange-700">Export CSV</a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto grid max-w-7xl gap-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-500">Total Laporan</p>
                    <p class="text-3xl font-bold">{{ total }}</p>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="mb-2 font-semibold">Per Status</p>
                    <ul class="space-y-1 text-sm">
                        <li v-for="(v, k) in perStatus" :key="k" class="flex justify-between">
                            <span>{{ k }}</span><strong>{{ v }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="mb-2 font-semibold">Per Kategori</p>
                    <ul class="space-y-1 text-sm">
                        <li v-for="(v, k) in perKategori" :key="k" class="flex justify-between">
                            <span>{{ k }}</span><strong>{{ v }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
