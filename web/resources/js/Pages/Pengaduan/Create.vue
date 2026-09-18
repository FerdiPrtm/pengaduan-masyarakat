<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ kategoris: Array });

const form = useForm({
    kategori_id: '',
    judul: '',
    deskripsi: '',
    lokasi: '',
    latitude: '',
    longitude: '',
    foto: [],
});

const previews = ref([]);

function onFiles(e) {
    const files = [...e.target.files].slice(0, 5);
    form.foto = files;
    previews.value = [];
    files.forEach((f) => {
        const r = new FileReader();
        r.onload = (ev) => previews.value.push(ev.target.result);
        r.readAsDataURL(f);
    });
}

function submit() {
    form.post(route('pengaduan.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Buat Pengaduan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Buat Pengaduan</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-4 bg-white p-6 shadow-sm sm:rounded-lg">
                    <div>
                        <label class="text-sm font-medium">Kategori</label>
                        <select v-model="form.kategori_id" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">— pilih —</option>
                            <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                        </select>
                        <InputError :message="form.errors.kategori_id" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Judul</label>
                        <input v-model="form.judul" class="mt-1 block w-full rounded-md border-gray-300" maxlength="255" />
                        <InputError :message="form.errors.judul" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Deskripsi (min. 10 karakter)</label>
                        <textarea v-model="form.deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300" />
                        <InputError :message="form.errors.deskripsi" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">Lokasi</label>
                        <input v-model="form.lokasi" class="mt-1 block w-full rounded-md border-gray-300" />
                        <InputError :message="form.errors.lokasi" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium">Latitude (opsional)</label>
                            <input v-model="form.latitude" type="number" step="any" class="mt-1 block w-full rounded-md border-gray-300" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Longitude (opsional)</label>
                            <input v-model="form.longitude" type="number" step="any" class="mt-1 block w-full rounded-md border-gray-300" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Foto bukti (1–5, jpg/png, maks 2MB/file)</label>
                        <input type="file" multiple accept=".jpg,.jpeg,.png" @change="onFiles" class="mt-1 block w-full text-sm" />
                        <InputError :message="form.errors.foto || form.errors['foto.0']" />
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <img v-for="(p, i) in previews" :key="i" :src="p" class="h-24 w-full rounded object-cover" loading="lazy" />
                        </div>
                    </div>
                    <button :disabled="form.processing" class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700 disabled:opacity-50">
                        Kirim Pengaduan
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
