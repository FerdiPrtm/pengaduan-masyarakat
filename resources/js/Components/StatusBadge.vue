<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String });

const meta = {
    menunggu_verifikasi: ['Menunggu Verifikasi', 'bg-amber-100 text-amber-800 ring-amber-200'],
    butuh_info_tambahan: ['Butuh Info Tambahan', 'bg-purple-100 text-purple-800 ring-purple-200'],
    diverifikasi: ['Diverifikasi', 'bg-blue-100 text-blue-800 ring-blue-200'],
    ditolak: ['Ditolak', 'bg-red-100 text-red-800 ring-red-200'],
    diproses: ['Diproses', 'bg-indigo-100 text-indigo-800 ring-indigo-200'],
    selesai: ['Selesai', 'bg-green-100 text-green-800 ring-green-200'],
};

const label = computed(() => meta[props.status]?.[0] ?? props.status);
const cls = computed(() => meta[props.status]?.[1] ?? 'bg-gray-100 text-gray-800 ring-gray-200');
</script>

<template>
    <span :class="['inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', cls]">
        <span class="size-1.5 rounded-full bg-current"></span>
        {{ label }}
    </span>
</template>
