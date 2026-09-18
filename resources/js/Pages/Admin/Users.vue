<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ items: Object });

const roleBadge = { pelapor: 'bg-gray-100 text-gray-700', petugas: 'bg-blue-100 text-blue-800', admin: 'bg-orange-100 text-orange-800' };

const setRole = (id, role) => {
    router.patch(route('admin.users.role', id), { role }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="Kelola User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Kelola User</h2>
            <p class="mt-1 text-sm text-gray-500">{{ items.total }} akun terdaftar · ubah role langsung dari daftar.</p>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="u in items.data" :key="u.id" class="flex flex-wrap items-center gap-3 px-4 py-3.5 sm:px-6">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-extrabold text-white">{{ u.name.charAt(0).toUpperCase() }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-bold text-gray-900">{{ u.name }}</span>
                                <span class="block truncate text-sm text-gray-500">{{ u.email }}</span>
                            </span>
                            <span :class="['rounded-full px-2.5 py-1 text-xs font-bold', roleBadge[u.role] ?? roleBadge.pelapor]">{{ u.role }}</span>
                            <select :value="u.role" @change="setRole(u.id, $event.target.value)" class="rounded-xl border-gray-200 text-sm font-semibold shadow-sm focus:border-orange-400 focus:ring-orange-200">
                                <option value="pelapor">Pelapor</option>
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 flex gap-2">
                    <Link v-if="items.prev_page_url" :href="items.prev_page_url" preserve-scroll class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold transition hover:border-orange-300">← Sebelumnya</Link>
                    <Link v-if="items.next_page_url" :href="items.next_page_url" preserve-scroll class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold transition hover:border-orange-300">Berikutnya →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
