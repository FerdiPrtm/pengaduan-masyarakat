<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ items: Object });

const setRole = (id, role) => {
    useForm({ role }).patch(route('admin.users.role', id));
};
</script>

<template>
    <Head title="Kelola User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Kelola User</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y text-sm">
                        <thead class="bg-gray-50">
                            <tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Email</th><th class="px-4 py-2 text-left">Role</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="u in items.data" :key="u.id">
                                <td class="px-4 py-2">{{ u.name }}</td>
                                <td class="px-4 py-2">{{ u.email }}</td>
                                <td class="px-4 py-2">
                                    <select :value="u.role" @change="setRole(u.id, $event.target.value)" class="rounded-md border-gray-300 text-sm">
                                        <option value="pelapor">pelapor</option>
                                        <option value="petugas">petugas</option>
                                        <option value="admin">admin</option>
                                    </select>
                                </td>
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
