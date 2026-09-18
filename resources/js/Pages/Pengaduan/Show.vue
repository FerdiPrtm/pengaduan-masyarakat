<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ item: Object });
const user = computed(() => usePage().props.auth.user);
const isPetugas = computed(() => ['petugas', 'admin'].includes(user.value.role));
const isOwner = computed(() => user.value.id === props.item.user_id);

const verif = useForm({ hasil: 'valid', catatan: '' });
const statusForm = useForm({ status_baru: 'diproses', catatan: '' });
const edit = useForm({
    kategori_id: props.item.kategori_id,
    judul: props.item.judul,
    deskripsi: props.item.deskripsi,
    lokasi: props.item.lokasi,
    latitude: props.item.latitude ?? '',
    longitude: props.item.longitude ?? '',
    _method: 'PUT',
});

const badge = (s) =>
    ({
        menunggu_verifikasi: 'bg-yellow-100 text-yellow-800',
        butuh_info_tambahan: 'bg-purple-100 text-purple-800',
        diverifikasi: 'bg-blue-100 text-blue-800',
        ditolak: 'bg-red-100 text-red-800',
        diproses: 'bg-indigo-100 text-indigo-800',
        selesai: 'bg-green-100 text-green-800',
    })[s] ?? 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head :title="item.nomor_tiket" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="font-mono text-xl font-semibold">{{ item.nomor_tiket }}</h2>
                <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', badge(item.status)]">{{ item.status }}</span>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto grid max-w-7xl gap-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="space-y-4 lg:col-span-2">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <p class="text-lg font-semibold">{{ item.judul }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ item.kategori?.nama_kategori }} · {{ item.lokasi }} · oleh {{ item.pelapor?.name }}</p>
                        <p class="mt-3 whitespace-pre-line text-gray-800">{{ item.deskripsi }}</p>
                        <div v-if="item.foto?.length" class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <a v-for="f in item.foto" :key="f.id" :href="`/storage/${f.path_file}`" target="_blank">
                                <img :src="`/storage/${f.path_file}`" class="h-32 w-full rounded object-cover" loading="lazy" />
                            </a>
                        </div>
                    </div>

                    <!-- Verifikasi (petugas) -->
                    <div v-if="isPetugas && item.status === 'menunggu_verifikasi'" class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold">Verifikasi Laporan</h3>
                        <form @submit.prevent="verif.post(route('pengaduan.verifikasi', item.id))" class="mt-3 space-y-3">
                            <select v-model="verif.hasil" class="block w-full rounded-md border-gray-300 text-sm">
                                <option value="valid">Valid → diverifikasi</option>
                                <option value="tidak_valid">Tidak valid → ditolak</option>
                                <option value="butuh_info">Minta info tambahan</option>
                            </select>
                            <textarea v-model="verif.catatan" rows="2" placeholder="Catatan / alasan (wajib jika tolak/minta info)" class="block w-full rounded-md border-gray-300 text-sm" />
                            <InputError :message="verif.errors.catatan" />
                            <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Simpan Verifikasi</button>
                        </form>
                    </div>

                    <!-- Update status (petugas) -->
                    <div v-if="isPetugas && ['diverifikasi', 'diproses'].includes(item.status)" class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold">Update Tindak Lanjut</h3>
                        <form @submit.prevent="statusForm.post(route('pengaduan.status', item.id))" class="mt-3 flex flex-wrap gap-2">
                            <select v-model="statusForm.status_baru" class="rounded-md border-gray-300 text-sm">
                                <option v-if="item.status === 'diverifikasi'" value="diproses">→ diproses</option>
                                <option v-if="item.status === 'diproses'" value="selesai">→ selesai</option>
                            </select>
                            <input v-model="statusForm.catatan" placeholder="Catatan tindak lanjut" class="flex-1 rounded-md border-gray-300 text-sm" />
                            <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Update</button>
                        </form>
                    </div>

                    <!-- Edit + resubmit (pelapor, saat butuh info) -->
                    <div v-if="isOwner && item.status === 'butuh_info_tambahan'" class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold">Lengkapi Info & Kirim Ulang</h3>
                        <form @submit.prevent="edit.post(route('pengaduan.update', item.id))" class="mt-3 space-y-3">
                            <input v-model="edit.judul" class="block w-full rounded-md border-gray-300 text-sm" />
                            <textarea v-model="edit.deskripsi" rows="3" class="block w-full rounded-md border-gray-300 text-sm" />
                            <input v-model="edit.lokasi" class="block w-full rounded-md border-gray-300 text-sm" />
                            <InputError :message="edit.errors.judul || edit.errors.deskripsi" />
                            <button class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Simpan & Kirim Ulang</button>
                        </form>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold">Riwayat Status</h3>
                    <ol class="mt-3 space-y-4 border-l-2 border-orange-200 pl-4">
                        <li v-for="l in item.logs" :key="l.id" class="text-sm">
                            <p class="font-semibold">{{ l.status_lama ?? '—' }} → {{ l.status_baru }}</p>
                            <p v-if="l.hasil_verifikasi" class="text-xs text-gray-500">hasil: {{ l.hasil_verifikasi }}</p>
                            <p v-if="l.catatan" class="mt-0.5 text-gray-700">{{ l.catatan }}</p>
                            <p class="text-xs text-gray-400">{{ l.updater?.name }} · {{ new Date(l.created_at).toLocaleString('id-ID') }}</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
