<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const wedding = computed(() => page.props.wedding);

const form = useForm({ nickname: '' });

function submit() {
    form.post('/register', {
        preserveScroll: true,
        onSuccess: () => form.reset('nickname'),
    });
}

const formattedDate = computed(() => {
    const d = new Date(wedding.value.date);
    return d.toLocaleDateString('pl-PL', { day: 'numeric', month: 'long', year: 'numeric' });
});
</script>

<template>
    <Head title="Witaj" />

    <div class="relative min-h-dvh overflow-hidden bg-gradient-to-b from-rose-100 via-white to-rose-50 text-rose-950">
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -right-16 h-72 w-72 rounded-full bg-rose-200/60 blur-3xl" />
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-pink-200/50 blur-3xl" />

        <div class="relative mx-auto flex min-h-dvh max-w-md flex-col items-center justify-center px-6 py-10 text-center">
            <div class="mb-2 text-6xl">❤️</div>
            <h1 class="font-serif text-3xl leading-tight">
                {{ wedding.couple }}
            </h1>
            <p class="mt-1 text-sm uppercase tracking-[0.3em] text-rose-400">
                {{ formattedDate }}
            </p>
            <p class="mt-1 text-xs text-rose-500">{{ wedding.venue }}</p>

            <div class="mt-10 w-full rounded-3xl bg-white/80 p-6 shadow-xl shadow-rose-100 backdrop-blur">
                <h2 class="text-lg font-semibold">Zostań fotografem wesela</h2>
                <p class="mt-1 text-sm text-rose-600">
                    Wykonuj misje, zbieraj XP, twórz wspólny album ze zdjęć wszystkich gości.
                </p>

                <form class="mt-6 space-y-3 text-left" @submit.prevent="submit">
                    <label class="block text-xs font-medium uppercase tracking-widest text-rose-400">
                        Twoje imię lub ksywka
                    </label>
                    <input
                        v-model="form.nickname"
                        type="text"
                        maxlength="40"
                        required
                        placeholder="np. Kasia, Bartek…"
                        class="w-full rounded-2xl border border-rose-200 bg-white px-4 py-3 text-base outline-none ring-rose-400/40 transition focus:border-rose-400 focus:ring-4"
                    />
                    <p v-if="form.errors.nickname" class="text-xs text-red-600">
                        {{ form.errors.nickname }}
                    </p>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-rose-500 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-rose-300 transition hover:bg-rose-600 disabled:opacity-60"
                    >
                        Zaczynam grę →
                    </button>
                </form>
            </div>

            <p class="mt-6 text-[11px] text-rose-400">
                Bez rejestracji · Bez aplikacji · Zdjęcia dla Pary Młodej
            </p>
        </div>
    </div>
</template>
