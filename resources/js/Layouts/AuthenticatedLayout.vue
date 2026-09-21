<script setup>
import { computed, ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const user = computed(() => usePage().props.auth.user);
const roleLabel = { pelapor: 'Pelapor', petugas: 'Petugas', admin: 'Admin' };
const notif = computed(() => usePage().props.notifikasi || { count: 0, items: [] });

const flash = computed(() => usePage().props.flash || {});
const activeFlash = ref(null);
let flashTimer;

const showFlash = (msg) => {
    clearTimeout(flashTimer);
    activeFlash.value = msg;
    if (msg) {
        requestAnimationFrame(() => {
            flashTimer = setTimeout(() => (activeFlash.value = null), 4000);
        });
    }
};

watch(() => flash.value.success, (msg) => msg && showFlash({ type: 'success', msg }));
watch(() => flash.value.error, (msg) => msg && showFlash({ type: 'error', msg }));

const fmtNotif = (d) => {
    const diff = Math.max(0, Date.now() - new Date(d).getTime());
    const min = Math.floor(diff / 60000);
    if (min < 1) return 'baru saja';
    if (min < 60) return `${min} menit lalu`;
    if (min < 1440) return `${Math.floor(min / 60)} jam lalu`;
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-100">
            <nav class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 shadow-[0_1px_12px_rgba(0,0,0,0.04)] backdrop-blur print:hidden">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-2.5">
                                    <span class="flex size-9 items-center justify-center rounded-xl bg-orange-600 text-white shadow-sm">
                                        <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                                    </span>
                                    <span class="text-lg font-extrabold tracking-tight text-gray-900">Lapor<span class="text-orange-600">Warga</span></span>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dasbor</NavLink>
                                <NavLink :href="route('pengaduan.index')" :active="route().current('pengaduan.*')">Pengaduan</NavLink>
                                <NavLink v-if="user.role === 'admin'" :href="route('admin.dashboard')" :active="route().current('admin.*')">Admin</NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <span class="mr-3 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-bold text-orange-700 ring-1 ring-inset ring-orange-200">{{ roleLabel[user.role] ?? user.role }}</span>
                            <div class="relative me-1">
                                <Dropdown align="right" width="72">
                                    <template #trigger>
                                        <button type="button" class="relative rounded-full border border-gray-200 bg-white p-2.5 text-gray-500 transition hover:border-orange-300 hover:text-orange-600 focus:outline-none" aria-label="Notifikasi">
                                            <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></svg>
                                            <span v-if="notif.count" class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-extrabold text-white">{{ notif.count }}</span>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="rounded-t-md border-b border-gray-100 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-gray-400">Notifikasi</div>
                                        <div v-if="notif.items?.length" class="max-h-80 overflow-y-auto">
                                            <Link v-for="n in notif.items" :key="n.id" :href="route('pengaduan.show', n.pengaduan?.nomor_tiket)" class="block border-b border-gray-50 px-4 py-3 text-sm transition hover:bg-orange-50" :class="n.dibaca_at ? 'text-gray-500' : 'font-semibold text-gray-900'">
                                                <span class="block leading-snug">{{ n.pesan }}</span>
                                                <span class="mt-0.5 block text-xs" :class="n.dibaca_at ? 'text-gray-400' : 'text-orange-600'">{{ fmtNotif(n.created_at) }}</span>
                                            </Link>
                                        </div>
                                        <p v-else class="px-4 py-4 text-sm text-gray-500">Belum ada notifikasi.</p>
                                    </template>
                                </Dropdown>
                            </div>
                            <div class="relative ms-1">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white py-1 pl-1 pr-3 text-sm font-medium text-gray-700 transition hover:border-orange-300 hover:text-gray-900 focus:outline-none">
                                                <span class="flex size-8 items-center justify-center rounded-full bg-orange-600 text-xs font-extrabold text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
                                                {{ user.name }}
                                                <svg class="-me-0.5 ms-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none" aria-label="Menu navigasi" aria-expanded="showingNavigationDropdown ? 'true' : 'false'">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dasbor</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('pengaduan.index')" :active="route().current('pengaduan.*')">Pengaduan</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="user.role === 'admin'" :href="route('admin.dashboard')" :active="route().current('admin.*')">Admin</ResponsiveNavLink>
                    </div>

                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="flex items-center gap-3 px-4">
                            <span class="flex size-10 items-center justify-center rounded-full bg-orange-600 text-sm font-extrabold text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
                            <div>
                                <div class="text-base font-medium text-gray-800">{{ user.name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="border-b border-gray-200/70 bg-white print:hidden" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Transition
                        enter-active-class="transform ease-out duration-300"
                        enter-from-class="translate-y-[-8px] opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transform ease-in duration-200"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="translate-y-[-8px] opacity-0"
                    >
                        <div v-if="activeFlash" class="mt-4 flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm" :class="activeFlash.type === 'error' ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50'">
                            <svg class="mt-0.5 size-5 shrink-0" :class="activeFlash.type === 'error' ? 'text-red-600' : 'text-green-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><template v-if="activeFlash.type === 'error'"><circle cx="12" cy="12" r="10" /><path d="m15 9-6 6M9 9l6 6" /></template><template v-else><path d="M20 6 9 17l-5-5" /></template></svg>
                            <p class="flex-1 text-sm font-semibold" :class="activeFlash.type === 'error' ? 'text-red-800' : 'text-green-800'">{{ activeFlash.msg }}</p>
                            <button @click="activeFlash = null" class="rounded p-0.5 transition" :class="activeFlash.type === 'error' ? 'text-red-500 hover:bg-red-100 hover:text-red-700' : 'text-green-500 hover:bg-green-100 hover:text-green-700'" aria-label="Tutup notifikasi">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </Transition>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
