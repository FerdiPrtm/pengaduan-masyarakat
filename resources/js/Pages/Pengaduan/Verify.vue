<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import Lightbox from '@/Components/Lightbox.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { STATUS_LABEL as statusLabel } from '@/lib/status.js';

const props = defineProps({ item: Object });

const fotoUrl = (ft) => route('pengaduan.foto', [props.item.id, ft.id]);
const fotoImages = computed(() => props.item.foto?.map((f) => ({ url: fotoUrl(f), original_name: f.original_name })) ?? []);
const openFoto = ref(null);

const verif = useForm({ hasil: 'valid', catatan: '' });
const statusForm = useForm({ status_baru: props.item.status === 'diverifikasi' ? 'diproses' : 'selesai', catatan: '' });

const decisions = [
    { v: 'valid', t: 'Valid', d: 'Laporan benar & lengkap. Teruskan ke tindak lanjut.', dot: 'bg-blue-500', ring: 'peer-checked:border-blue-500 peer-checked:bg-blue-50/60' },
    { v: 'tidak_valid', t: 'Tolak', d: 'Laporan tidak memenuhi syarat. Wajib beri alasan.', dot: 'bg-red-500', ring: 'peer-checked:border-red-500 peer-checked:bg-red-50/60' },
    { v: 'butuh_info', t: 'Minta Info', d: 'Minta pelapor melengkapi. Wajib beri catatan.', dot: 'bg-purple-500', ring: 'peer-checked:border-purple-500 peer-checked:bg-purple-50/60' },
];
const inputCls = 'block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200';
const fmt = (d) => new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head :title="'Verifikasi ' + item.nomor_tiket" />
    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('pengaduan.show', item.nomor_tiket)" class="text-sm font-semibold text-orange-700 hover:underline">← Kembali ke detail</Link>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-extrabold tracking-tight">Verifikasi Laporan</h2>
                <StatusBadge :status="item.status" />
            </div>
            <p class="mt-1 font-mono text-sm text-gray-500">{{ item.nomor_tiket }}</p>
        </template>

        <div class="py-6">
            <div class="mx-auto grid max-w-7xl gap-5 sm:px-6 lg:grid-cols-5 lg:px-8">
                <!-- Detail + bukti -->
                <div class="space-y-5 lg:col-span-3">
                    <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ item.kategori?.nama_kategori }}</span>
                        <h1 class="mt-3 text-xl font-extrabold tracking-tight">{{ item.judul }}</h1>
                        <p class="mt-2 text-sm text-gray-500">Pelapor: <strong class="text-gray-700">{{ item.pelapor?.name }}</strong> · {{ item.lokasi }}</p>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-800">{{ item.deskripsi }}</p>
                    </article>
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Foto bukti ({{ item.foto?.length ?? 0 }}) — periksa keasliannya</p>
                        <div v-if="item.foto?.length" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button v-for="(ft, i) in item.foto" :key="ft.id" type="button" @click="openFoto = i" class="group overflow-hidden rounded-xl ring-1 ring-gray-200">
                                <img :src="fotoUrl(ft)" :alt="ft.original_name" class="h-44 w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                            </button>
                        </div>
                        <p v-else class="rounded-xl bg-amber-50 p-4 text-sm font-medium text-amber-800 ring-1 ring-inset ring-amber-100">Pelapor tidak melampirkan foto. Pertimbangkan meminta info tambahan.</p>
                        <Lightbox :images="fotoImages" :open="openFoto !== null" :start="openFoto ?? 0" @close="openFoto = null" />
                    </div>
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Riwayat singkat</h3>
                        <ol class="mt-3 space-y-3">
                            <li v-for="l in item.logs" :key="l.id" class="flex gap-3 text-sm">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full bg-orange-400"></span>
                                <p class="text-gray-600"><strong class="text-gray-900">{{ statusLabel[l.status_baru] ?? l.status_baru }}</strong> — {{ l.updater?.name }} · {{ fmt(l.created_at) }}<span v-if="l.catatan" class="block">{{ l.catatan }}</span></p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Form aksi -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-orange-200 bg-white p-5 shadow-sm sm:p-6 lg:sticky lg:top-24">
                        <template v-if="item.status === 'menunggu_verifikasi'">
                            <h3 class="text-lg font-extrabold">Keputusan Verifikasi</h3>
                            <p class="mt-1 text-sm text-gray-500">Tercatat permanen beserta namamu.</p>
                            <form @submit.prevent="verif.post(route('pengaduan.verifikasi', item.id), { preserveScroll: true })" class="mt-4 space-y-3">
                                <label v-for="o in decisions" :key="o.v" class="block cursor-pointer">
                                    <input v-model="verif.hasil" :value="o.v" type="radio" class="peer sr-only" />
                                    <span :class="['flex gap-3 rounded-xl border-2 border-gray-100 bg-white p-4 transition hover:border-orange-200', o.ring]">
                                        <span :class="['mt-1 size-3 shrink-0 rounded-full', o.dot]"></span>
                                        <span><span class="block font-extrabold">{{ o.t }}</span><span class="mt-0.5 block text-sm text-gray-500">{{ o.d }}</span></span>
                                    </span>
                                </label>
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Catatan / alasan <span v-if="verif.hasil !== 'valid'" class="text-orange-600">*</span></label>
                                    <textarea v-model="verif.catatan" rows="3" placeholder="Tulis alasan yang jelas — akan dibaca pelapor." :class="inputCls + ' mt-1'" />
                                    <InputError :message="verif.errors.catatan" />
                                </div>
                                <button :disabled="verif.processing" class="w-full rounded-xl bg-orange-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-50">{{ verif.processing ? 'Menyimpan…' : 'Simpan Keputusan' }}</button>
                            </form>
                        </template>

                        <template v-else-if="['diverifikasi', 'diproses'].includes(item.status)">
                            <h3 class="text-lg font-extrabold">Update Tindak Lanjut</h3>
                            <p class="mt-1 text-sm text-gray-500">Catat progres penanganan untuk dilihat pelapor.</p>
                            <form @submit.prevent="statusForm.post(route('pengaduan.status', item.id), { preserveScroll: true })" class="mt-4 space-y-3">
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Status baru</label>
                                    <select v-model="statusForm.status_baru" :class="inputCls + ' mt-1 font-semibold'">
                                        <option v-if="item.status === 'diverifikasi'" value="diproses">Mulai diproses</option>
                                        <option v-if="item.status === 'diproses'" value="selesai">Tandai selesai</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Catatan progres</label>
                                    <textarea v-model="statusForm.catatan" rows="3" placeholder="cth: Tim DLH meluncur ke lokasi pagi ini." :class="inputCls + ' mt-1'" />
                                </div>
                                <button :disabled="statusForm.processing" class="w-full rounded-xl bg-gray-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-gray-700 disabled:opacity-50">{{ statusForm.processing ? 'Menyimpan…' : 'Simpan Update' }}</button>
                            </form>
                        </template>

                        <template v-else>
                            <h3 class="text-lg font-extrabold">Tidak ada aksi</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">Laporan berstatus <strong>{{ statusLabel[item.status] ?? item.status }}</strong> dan tidak memerlukan tindakan lagi.</p>
                            <Link :href="route('pengaduan.index')" class="mt-4 inline-block rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white">Kembali ke antrean</Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
