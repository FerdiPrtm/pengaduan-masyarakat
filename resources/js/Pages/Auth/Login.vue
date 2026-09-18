<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Masuk ke akunmu</h2>
            <p class="mt-1 text-sm text-gray-500">Pantau dan tindaklanjuti laporanmu.</p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@contoh.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Kata Sandi" />
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-semibold text-orange-600 hover:text-orange-700">
                        Lupa kata sandi?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-gray-600">Ingat saya</span>
            </label>

            <div>
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Masuk…' : 'Masuk' }}
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Belum punya akun?
            <Link :href="route('register')" class="font-bold text-orange-600 hover:text-orange-700">Daftar gratis</Link>
        </p>
    </GuestLayout>
</template>