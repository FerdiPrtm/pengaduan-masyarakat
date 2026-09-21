<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    images: { type: Array, default: () => [] },
    open: Boolean,
    start: { type: Number, default: 0 },
});
const emit = defineEmits(['close']);

const idx = ref(0);
watch(() => props.start, (v) => (idx.value = v));

const img = () => props.images[idx.value];
</script>

<template>
    <Teleport to="body">
        <div v-if="open && img()" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" @click.self="emit('close')">
            <button type="button" aria-label="Tutup" class="absolute right-4 top-4 flex size-10 items-center justify-center rounded-full text-white/80 transition hover:text-white" @click="emit('close')">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12" /></svg>
            </button>
            <button v-if="images.length > 1" type="button" aria-label="Sebelumnya" class="absolute left-2 flex size-11 items-center justify-center rounded-full text-white/80 transition hover:text-white sm:left-6" @click="idx = (idx - 1 + images.length) % images.length">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
            </button>
            <button v-if="images.length > 1" type="button" aria-label="Berikutnya" class="absolute right-2 flex size-11 items-center justify-center rounded-full text-white/80 transition hover:text-white sm:right-6" @click="idx = (idx + 1) % images.length">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6" /></svg>
            </button>
            <figure class="flex max-h-full max-w-full flex-col items-center">
                <img :src="img().url" :alt="img().original_name" class="max-h-[85vh] max-w-full rounded-lg object-contain" />
                <figcaption class="mt-3 text-sm text-white/70">{{ img().original_name }}</figcaption>
            </figure>
            <p v-if="images.length > 1" class="absolute bottom-4 right-6 text-sm font-semibold text-white/60">{{ idx + 1 }} / {{ images.length }}</p>
        </div>
    </Teleport>
</template>