<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';

interface Quote {
    id: number;
    body: string;
    likes_count: number;
    liked: boolean;
    mine: boolean;
}

const props = defineProps<{ quotes: Quote[] }>();

const quotes = ref<Quote[]>([...props.quotes]);
const form = useForm({ body: '' });
let pollTimer: number | null = null;

function submit() {
    form.post('/cytaty', {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
}

async function refresh() {
    try {
        const res = await fetch('/api/cytaty', { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = (await res.json()) as { quotes: Quote[] };
        // preserve local liked state overrides recent changes
        quotes.value = data.quotes;
    } catch {
        // ignore
    }
}

async function toggleLike(q: Quote) {
    // optimistic
    const prev = q.liked;
    q.liked = !q.liked;
    q.likes_count += q.liked ? 1 : -1;

    try {
        const xsrf = decodeURIComponent(
            (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) ?? [, ''])[1] ?? '',
        );
        const res = await fetch(`/cytaty/${q.id}/like`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrf,
            },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('like failed');
        const data = (await res.json()) as { liked: boolean; likes_count: number };
        q.liked = data.liked;
        q.likes_count = data.likes_count;
    } catch {
        // rollback
        q.liked = prev;
        q.likes_count += q.liked ? 1 : -1;
    }
}

onMounted(() => {
    pollTimer = window.setInterval(refresh, 6000);
});

onBeforeUnmount(() => {
    if (pollTimer) window.clearInterval(pollTimer);
});

</script>

<template>
    <Head title="Cytaty" />
    <GuestLayout>
        <section class="rounded-3xl bg-gradient-to-br from-violet-500 via-fuchsia-500 to-rose-500 p-6 text-white shadow-lg shadow-fuchsia-200">
            <div class="text-4xl">🎙️</div>
            <h1 class="mt-2 font-serif text-2xl leading-tight">Cytaty z wesela</h1>
            <p class="mt-1 text-sm text-white/90">
                Coś śmiesznego usłyszałeś? Wpisz anonimowo. Para po weselu zgadnie, kto to powiedział.
            </p>
            <p class="mt-3 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest">
                +15 XP · Anonimowo dla gości
            </p>
        </section>

        <form class="mt-6 space-y-3" @submit.prevent="submit">
            <textarea
                v-model="form.body"
                required
                minlength="3"
                maxlength="200"
                rows="2"
                :placeholder="`„Nie no dziękuję, już nie mogę…” — trzeci raz w ciągu godziny.`"
                class="w-full resize-none rounded-2xl border border-fuchsia-200 bg-white px-4 py-3 outline-none ring-fuchsia-400/40 focus:border-fuchsia-400 focus:ring-4"
            />
            <div class="flex items-center justify-between">
                <p class="text-xs text-rose-400">
                    {{ form.errors.body ?? `${form.body.length}/200` }}
                </p>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-full bg-fuchsia-500 px-5 py-2 text-sm font-semibold text-white hover:bg-fuchsia-600 disabled:opacity-60"
                >
                    Dodaj cytat
                </button>
            </div>
        </form>

        <h2 class="mt-8 mb-3 text-xs font-semibold uppercase tracking-widest text-fuchsia-400">
            Top cytaty ({{ quotes.length }})
        </h2>

        <div v-if="quotes.length === 0" class="rounded-2xl border border-dashed border-fuchsia-200 p-8 text-center text-fuchsia-400">
            Cisza jak makiem zasiał. Bądź pierwszy.
        </div>

        <ol v-else class="space-y-3">
            <li
                v-for="(q, i) in quotes"
                :key="q.id"
                class="flex gap-3 rounded-2xl border p-4 shadow-sm"
                :class="q.mine ? 'border-fuchsia-300 bg-fuchsia-50' : 'border-rose-100 bg-white'"
            >
                <div class="w-8 shrink-0 text-center text-lg font-bold text-fuchsia-500">
                    {{ i < 3 ? ['🥇', '🥈', '🥉'][i] : `#${i + 1}` }}
                </div>
                <div class="flex-1">
                    <blockquote class="font-serif text-lg italic leading-snug text-rose-950">
                        „{{ q.body }}"
                    </blockquote>
                    <p v-if="q.mine" class="mt-1 text-[10px] font-bold uppercase tracking-widest text-fuchsia-500">
                        Twój cytat
                    </p>
                </div>
                <button
                    type="button"
                    class="flex flex-col items-center gap-1 rounded-2xl px-3 py-2 text-sm transition"
                    :class="q.liked ? 'bg-rose-500 text-white' : 'bg-rose-50 text-rose-500 hover:bg-rose-100'"
                    @click="toggleLike(q)"
                >
                    <span>{{ q.liked ? '❤️' : '🤍' }}</span>
                    <span class="text-xs font-bold">{{ q.likes_count }}</span>
                </button>
            </li>
        </ol>
    </GuestLayout>
</template>
