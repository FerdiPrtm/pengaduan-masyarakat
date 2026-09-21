<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
    value: { type: [String, Number], required: true },
    sub: String,
    href: String,
    icon: String,
    chip: { type: String, default: 'bg-gray-100 text-gray-700' },
    accent: Boolean,
});
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href" class="group relative rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition" :class="href ? 'hover:border-orange-300 hover:shadow' : ''">
        <div class="flex items-center justify-between">
            <span :class="['flex size-10 items-center justify-center rounded-xl', accent ? 'bg-orange-600 text-white' : chip]">
                <svg v-if="icon" class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path :d="icon.split('|')[0]" /><path v-if="icon.split('|')[1]" :d="icon.split('|')[1]" /></svg>
            </span>
            <svg v-if="href" class="size-4 text-gray-300 transition group-hover:text-orange-500" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10" /></svg>
        </div>
        <p class="mt-3 text-3xl font-extrabold tracking-tight tabular-nums text-gray-900">{{ value }}</p>
        <p class="mt-0.5 text-sm font-bold text-gray-700">{{ title }}</p>
        <p v-if="sub" class="text-xs text-gray-400">{{ sub }}</p>
    </component>
</template>