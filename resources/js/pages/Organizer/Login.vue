<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

function submit() {
    form.post('/organizer/login', { preserveScroll: true, onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Panel organizatora" />
    <div class="flex min-h-dvh items-center justify-center bg-rose-950 p-6 text-rose-50">
        <div class="w-full max-w-sm rounded-3xl bg-white/5 p-6 shadow-xl backdrop-blur">
            <h1 class="text-lg font-semibold">Panel Pary Młodej</h1>
            <p class="mt-1 text-sm text-rose-200/80">Wpisz hasło aby zobaczyć zdjęcia od gości.</p>

            <form class="mt-6 space-y-3" @submit.prevent="submit">
                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="Hasło"
                    class="w-full rounded-xl bg-white/10 px-4 py-3 text-white placeholder:text-rose-200/50 outline-none ring-rose-400/40 focus:ring-4"
                />
                <p v-if="form.errors.password" class="text-xs text-red-300">
                    {{ form.errors.password }}
                </p>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-rose-500 py-3 font-semibold text-white transition hover:bg-rose-600 disabled:opacity-60"
                >
                    Wejdź
                </button>
            </form>
        </div>
    </div>
</template>
