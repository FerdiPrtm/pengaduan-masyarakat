<script setup>
import { computed } from 'vue';
import { STATUS_LABEL } from '@/lib/status.js';

const props = defineProps({ status: String });

const meta = {
    menunggu_verifikasi: 'bg-amber-100 text-amber-800 ring-amber-200',
    butuh_info_tambahan: 'bg-purple-100 text-purple-800 ring-purple-200',
    diverifikasi: 'bg-blue-100 text-blue-800 ring-blue-200',
    ditolak: 'bg-red-100 text-red-800 ring-red-200',
    diproses: 'bg-indigo-100 text-indigo-800 ring-indigo-200',
    selesai: 'bg-green-100 text-green-800 ring-green-200',
};

const label = computed(() => STATUS_LABEL[props.status] ?? props.status);
const cls = computed(() => meta[props.status] ?? 'bg-gray-100 text-gray-800 ring-gray-200');
</script>

<template>
    <span :class="['inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', cls]">
        <span class="size-1.5 rounded-full bg-current"></span>
        {{ label }}
    </span>
</template>
