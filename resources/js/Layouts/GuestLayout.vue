<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-100">
        <main class="flex flex-1 items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <Link href="/" class="flex flex-col items-center gap-2.5">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-orange-600 text-white shadow-sm">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </span>
                    <span class="text-2xl font-extrabold tracking-tight text-gray-900">Lapor<span class="text-orange-600">Warga</span></span>
                </Link>

                <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-8 shadow-sm">
                    <slot />
                </div>

                <p class="mt-6 text-center text-sm text-gray-500">
                    <Link href="/" class="font-semibold text-orange-600 hover:text-orange-700">← Kembali ke beranda</Link>
                </p>
            </div>
        </main>
    </div>
</template>