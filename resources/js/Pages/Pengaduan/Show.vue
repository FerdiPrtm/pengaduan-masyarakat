<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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

const flow = ['menunggu_verifikasi', 'diverifikasi', 'diproses', 'selesai'];
const flowLabel = { menunggu_verifikasi: 'Diajukan', diverifikasi: 'Diverifikasi', diproses: 'Diproses', selesai: 'Selesai' };
const stepIdx = computed(() => flow.indexOf(props.item.status));
const inFlow = computed(() => stepIdx.value >= 0);
const fmt = (d) => new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
const inputCls = 'block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200';
</script>

<template>
    <Head :title="item.nomor_tiket" />
    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('pengaduan.index')" class="text-sm font-semibold text-orange-700 hover:underline">← Kembali ke daftar</Link>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="font-mono text-2xl font-extrabold tracking-tight">{{ item.nomor_tiket }}</h2>
                <StatusBadge :status="item.status" />
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto grid max-w-7xl gap-5 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="space-y-5 lg:col-span-2">
                    <!-- Progress -->
                    <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <div v-if="inFlow" class="flex items-center">
                            <template v-for="(s, i) in flow" :key="s">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span :class="['flex size-8 items-center justify-center rounded-full text-xs font-extrabold ring-4', i <= stepIdx ? 'bg-orange-600 text-white ring-orange-100' : 'bg-gray-100 text-gray-400 ring-gray-50']">{{ i <= stepIdx ? '✓' : i + 1 }}</span>
                                    <span :class="['text-[11px] font-bold', i <= stepIdx ? 'text-gray-900' : 'text-gray-400']">{{ flowLabel[s] }}</span>
                                </div>
                                <div v-if="i < flow.length - 1" :class="['mx-1 mb-5 h-1 flex-1 rounded-full', i < stepIdx ? 'bg-orange-500' : 'bg-gray-100']"></div>
                            </template>
                        </div>
                        <div v-else-if="item.status === 'ditolak'" class="rounded-xl bg-red-50 p-4 text-sm font-medium text-red-800 ring-1 ring-inset ring-red-100">Laporan ini <strong>ditolak</strong> oleh petugas. Lihat alasan pada timeline, lalu buat laporan baru yang lebih lengkap.</div>
                        <div v-else class="rounded-xl bg-purple-50 p-4 text-sm font-medium text-purple-800 ring-1 ring-inset ring-purple-100">Petugas <strong>meminta info tambahan</strong>. {{ isOwner ? 'Lengkapi lewat formulir di bawah agar laporanmu kembali diverifikasi.' : 'Menunggu pelapor melengkapi laporannya.' }}</div>
                    </div>

                    <!-- Isi laporan -->
                    <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-gray-700">{{ item.kategori?.nama_kategori }}</span>
                            <span class="text-gray-400">oleh {{ item.pelapor?.name }}</span>
                        </div>
                        <h1 class="mt-3 text-xl font-extrabold tracking-tight sm:text-2xl">{{ item.judul }}</h1>
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
                            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ item.lokasi }}
                        </p>
                        <p class="mt-4 whitespace-pre-line leading-relaxed text-gray-800">{{ item.deskripsi }}</p>
                        <div v-if="item.foto?.length" class="mt-5">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">Bukti foto ({{ item.foto.length }})</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <a v-for="ft in item.foto" :key="ft.id" :href="`/storage/${ft.path_file}`" target="_blank" class="group overflow-hidden rounded-xl ring-1 ring-gray-200">
                                    <img :src="`/storage/${ft.path_file}`" class="h-36 w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- Verifikasi (petugas) -->
                    <div v-if="isPetugas && item.status === 'menunggu_verifikasi'" class="rounded-2xl border border-orange-200 bg-orange-50/60 p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Verifikasi Laporan</h3>
                        <p class="mt-1 text-sm text-gray-600">Keputusanmu tercatat permanen di riwayat beserta namamu.</p>
                        <form @submit.prevent="verif.post(route('pengaduan.verifikasi', item.id))" class="mt-4 space-y-3">
                            <div class="grid gap-2 sm:grid-cols-3">
                                <label v-for="o in [{v:'valid',t:'Valid',d:'Teruskan ke tindak lanjut'},{v:'tidak_valid',t:'Tolak',d:'Tidak memenuhi syarat'},{v:'butuh_info',t:'Minta Info',d:'Perlu dilengkapi pelapor'}]" :key="o.v" :class="['cursor-pointer rounded-xl border-2 p-3 text-center transition', verif.hasil === o.v ? 'border-orange-500 bg-white shadow-sm' : 'border-transparent bg-white/60 hover:border-orange-200']">
                                    <input v-model="verif.hasil" :value="o.v" type="radio" class="sr-only" />
                                    <p class="text-sm font-extrabold">{{ o.t }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ o.d }}</p>
                                </label>
                            </div>
                            <textarea v-model="verif.catatan" rows="2" placeholder="Catatan / alasan (wajib jika menolak atau meminta info)" :class="inputCls" />
                            <InputError :message="verif.errors.catatan" />
                            <button class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-bold text-white shadow transition hover:bg-orange-700">Simpan Verifikasi</button>
                        </form>
                    </div>

                    <!-- Update status (petugas) -->
                    <div v-if="isPetugas && ['diverifikasi', 'diproses'].includes(item.status)" class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Update Tindak Lanjut</h3>
                        <form @submit.prevent="statusForm.post(route('pengaduan.status', item.id))" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            <select v-model="statusForm.status_baru" class="rounded-xl border-gray-200 text-sm font-semibold shadow-sm focus:border-orange-400 focus:ring-orange-200">
                                <option v-if="item.status === 'diverifikasi'" value="diproses">Mulai diproses →</option>
                                <option v-if="item.status === 'diproses'" value="selesai">Tandai selesai →</option>
                            </select>
                            <input v-model="statusForm.catatan" placeholder="Catatan progres (cth: tim meluncur ke lokasi)" class="flex-1 rounded-xl border-gray-200 text-sm shadow-sm focus:border-orange-400 focus:ring-orange-200" />
                            <button class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-gray-700">Update</button>
                        </form>
                    </div>

                    <!-- Edit + resubmit -->
                    <div v-if="isOwner && item.status === 'butuh_info_tambahan'" class="rounded-2xl border border-purple-200 bg-purple-50/60 p-5 shadow-sm sm:p-6">
                        <h3 class="font-extrabold">Lengkapi Info & Kirim Ulang</h3>
                        <form @submit.prevent="edit.post(route('pengaduan.update', item.id))" class="mt-3 space-y-3">
                            <input v-model="edit.judul" aria-label="Judul" :class="inputCls" />
                            <textarea v-model="edit.deskripsi" rows="4" aria-label="Deskripsi" :class="inputCls" />
                            <input v-model="edit.lokasi" aria-label="Lokasi" :class="inputCls" />
                            <InputError :message="edit.errors.judul || edit.errors.deskripsi" />
                            <button class="rounded-xl bg-purple-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-purple-800">Simpan & Kirim Ulang</button>
                        </form>
                    </div>
                </div>

                <!-- Timeline -->
                <aside class="h-fit rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm sm:p-6 lg:sticky lg:top-24">
                    <h3 class="font-extrabold">Riwayat Status</h3>
                    <ol class="mt-4 space-y-0">
                        <li v-for="(l, i) in item.logs" :key="l.id" class="relative flex gap-3 pb-5 last:pb-0">
                            <div class="flex flex-col items-center">
                                <span :class="['z-10 flex size-6 items-center justify-center rounded-full text-[10px] font-extrabold text-white', i === 0 ? 'bg-orange-600' : 'bg-gray-300']">{{ item.logs.length - i }}</span>
                                <span v-if="i < item.logs.length - 1" class="w-0.5 flex-1 bg-gray-200"></span>
                            </div>
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="font-bold text-gray-900">{{ l.status_baru.replaceAll('_', ' ') }}</p>
                                <p v-if="l.hasil_verifikasi" class="mt-0.5 inline-block rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600">hasil: {{ l.hasil_verifikasi }}</p>
                                <p v-if="l.catatan" class="mt-1 leading-relaxed text-gray-600">{{ l.catatan }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ l.updater?.name }} · {{ fmt(l.created_at) }}</p>
                            </div>
                        </li>
                    </ol>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
