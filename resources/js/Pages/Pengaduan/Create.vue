<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
const dragging = ref(false);
const fileInput = ref(null);

function setFiles(list) {
    const files = [...list].filter((f) => /jpe?g|png/i.test(f.type)).slice(0, 5);
    form.foto = files;
    previews.value = [];
    files.forEach((f) => {
        const r = new FileReader();
        r.onload = (ev) => previews.value.push({ src: ev.target.result, name: f.name });
        r.readAsDataURL(f);
    });
}

function submit() {
    form.post(route('pengaduan.store'), { forceFormData: true });
}

const inputCls = 'mt-1 block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200';
</script>

<template>
    <Head title="Buat Pengaduan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Buat Pengaduan Baru</h2>
            <p class="mt-1 text-sm text-gray-500">Ceritakan masalahnya sejujur-jujurnya — foto yang jelas mempercepat verifikasi.</p>
        </template>

        <div class="py-6">
            <div class="mx-auto grid max-w-7xl gap-5 sm:px-6 lg:grid-cols-3 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-bold text-gray-700">Kategori <span class="text-orange-600">*</span></label>
                            <select v-model="form.kategori_id" :class="inputCls">
                                <option value="">— Pilih kategori —</option>
                                <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                            </select>
                            <InputError :message="form.errors.kategori_id" />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-gray-700">Lokasi kejadian <span class="text-orange-600">*</span></label>
                            <input v-model="form.lokasi" placeholder="cth: Jl. Merdeka No. 10, dekat pasar" :class="inputCls" />
                            <InputError :message="form.errors.lokasi" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-700">Judul laporan <span class="text-orange-600">*</span></label>
                        <input v-model="form.judul" placeholder="cth: Jalan berlubang membahayakan pengendara" :class="inputCls" maxlength="255" />
                        <InputError :message="form.errors.judul" />
                    </div>
                    <div>
                        <div class="flex items-baseline justify-between">
                            <label class="text-sm font-bold text-gray-700">Deskripsi <span class="text-orange-600">*</span></label>
                            <span class="text-xs text-gray-400">{{ form.deskripsi.length }}/min 10 karakter</span>
                        </div>
                        <textarea v-model="form.deskripsi" rows="5" placeholder="Jelaskan: apa yang terjadi, sejak kapan, seberapa parah, siapa yang terdampak…" :class="inputCls" />
                        <InputError :message="form.errors.deskripsi" />
                    </div>
                    <details class="rounded-xl bg-gray-50 px-4 py-3 text-sm">
                        <summary class="cursor-pointer font-bold text-gray-600">Titik koordinat (opsional)</summary>
                        <div class="mt-3 grid grid-cols-2 gap-4">
                            <input v-model="form.latitude" type="number" step="any" placeholder="Latitude" :class="inputCls" />
                            <input v-model="form.longitude" type="number" step="any" placeholder="Longitude" :class="inputCls" />
                        </div>
                    </details>
                    <div>
                        <label class="text-sm font-bold text-gray-700">Foto bukti <span class="text-gray-400">(maks 5, JPG/PNG, 2 MB/file)</span></label>
                        <div
                            @click="fileInput.click()"
                            @dragover.prevent="dragging = true"
                            @dragleave="dragging = false"
                            @drop.prevent="(dragging = false, setFiles($event.dataTransfer.files))"
                            :class="['mt-1 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-8 text-center transition', dragging ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-gray-50 hover:border-orange-300 hover:bg-orange-50/50']"
                        >
                            <p class="flex size-11 items-center justify-center rounded-xl bg-white text-orange-500 shadow-sm">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
                            </p>
                            <p class="mt-3 text-sm font-bold text-gray-700">Seret foto ke sini atau <span class="text-orange-600 underline">pilih file</span></p>
                            <input ref="fileInput" type="file" multiple accept=".jpg,.jpeg,.png" @change="setFiles($event.target.files)" class="hidden" />
                        </div>
                        <InputError :message="form.errors.foto || form.errors['foto.0']" />
                        <div v-if="previews.length" class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                            <figure v-for="(p, i) in previews" :key="i" class="group relative">
                                <img :src="p.src" class="h-24 w-full rounded-xl object-cover ring-1 ring-gray-200" />
                                <figcaption class="mt-1 truncate text-[11px] text-gray-500">{{ p.name }}</figcaption>
                            </figure>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
                        <button :disabled="form.processing" class="rounded-xl bg-orange-600 px-7 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-50">
                            {{ form.processing ? 'Mengirim…' : 'Kirim Pengaduan' }}
                        </button>
                        <Link :href="route('pengaduan.index')" class="text-sm font-semibold text-gray-500 hover:text-gray-800">Batal</Link>
                    </div>
                </form>

                <aside class="space-y-4">
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-5">
                        <p class="font-bold text-orange-900">Tips laporan cepat diverifikasi</p>
                        <ul class="mt-3 space-y-2 text-sm text-orange-900/80">
                            <li class="flex gap-2"><span class="font-bold">1.</span> Judul spesifik, bukan "tolong bantu".</li>
                            <li class="flex gap-2"><span class="font-bold">2.</span> Foto dari dekat + dari jauh (konteks lokasi).</li>
                            <li class="flex gap-2"><span class="font-bold">3.</span> Tulis patokan lokasi yang mudah ditemukan.</li>
                            <li class="flex gap-2"><span class="font-bold">4.</span> Satu laporan = satu masalah.</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm">
                        <p class="font-bold">Setelah dikirim</p>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">Laporanmu berstatus <em>Menunggu Verifikasi</em> dan mendapat nomor tiket. Kamu akan melihat setiap progresnya di halaman detail.</p>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
