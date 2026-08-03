<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';
import { resizeImage } from '../lib/resize-image';

interface MessageEntry {
    id: number;
    body: string;
    guest: string | null;
    photo_url: string | null;
    thumb_url: string | null;
    mine: boolean;
    created_at: string;
}

const props = defineProps<{ messages: MessageEntry[] }>();
const page = usePage();

const messages = ref<MessageEntry[]>([...props.messages]);
const showForm = ref(false);
const lightbox = ref<MessageEntry | null>(null);
const justSent = ref<number | null>(null);

const form = useForm<{ body: string; photo: File | null }>({
    body: '',
    photo: null,
});

const preview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const myCount = computed(() => messages.value.filter((m) => m.mine).length);

// Refresh gdy Inertia zaktualizuje propsy po redirect
watch(
    () => props.messages,
    (newMessages) => {
        messages.value = [...newMessages];
    },
);

async function onFile(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;
    form.photo = await resizeImage(file, 1600, 0.85);
    preview.value = URL.createObjectURL(form.photo);
}

function clearPhoto() {
    form.photo = null;
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function openForm() {
    showForm.value = true;
    setTimeout(() => {
        document.querySelector<HTMLTextAreaElement>('#msg-body')?.focus();
    }, 100);
}

function closeForm() {
    showForm.value = false;
    form.reset('body');
    clearPhoto();
}

function submit() {
    form.post('/wiadomosc', {
        forceFormData: true,
        preserveScroll: false,
        onSuccess: () => {
            form.reset('body');
            clearPhoto();
            showForm.value = false;
            // Podświetlenie najnowszego wpisu
            const flash = (page.props as { flash?: { type: string } }).flash;
            if (flash?.type === 'success' && messages.value[0]) {
                justSent.value = messages.value[0].id;
                setTimeout(() => (justSent.value = null), 3000);
            }
        },
    });
}

function timeAgo(iso: string): string {
    const diff = (Date.now() - new Date(iso).getTime()) / 1000;
    if (diff < 60) return 'przed chwilą';
    if (diff < 3600) return `${Math.floor(diff / 60)} min temu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} godz. temu`;
    return `${Math.floor(diff / 86400)} dni temu`;
}

// Deterministyczna rotacja "polaroidów" (per id)
function tilt(id: number): number {
    const seed = (id * 9301 + 49297) % 233280;
    return ((seed / 233280) - 0.5) * 6;
}

// Deterministyczny odcień żółty/różowy dla karty (per id)
function cardHue(id: number): string {
    const hues = [
        'bg-amber-50 border-amber-200',
        'bg-rose-50 border-rose-200',
        'bg-pink-50 border-pink-200',
        'bg-orange-50 border-orange-200',
        'bg-yellow-50 border-yellow-200',
    ];
    return hues[id % hues.length];
}

onMounted(() => {
    if (messages.value[0] && messages.value[0].mine) {
        const age = (Date.now() - new Date(messages.value[0].created_at).getTime()) / 1000;
        if (age < 5) {
            justSent.value = messages.value[0].id;
            setTimeout(() => (justSent.value = null), 3000);
        }
    }
});

onBeforeUnmount(() => {
    if (preview.value) URL.revokeObjectURL(preview.value);
});
</script>

<template>
    <Head title="Księga gości" />
    <GuestLayout>
        <section class="rounded-3xl bg-gradient-to-br from-amber-400 via-rose-400 to-pink-500 p-6 text-white shadow-lg shadow-rose-200">
            <div class="text-4xl">💌</div>
            <h1 class="mt-2 font-serif text-2xl leading-tight">Księga gości</h1>
            <p class="mt-1 text-sm text-white/90">
                Zostaw życzenie Młodym. Wszyscy zobaczą – jak w prawdziwej księdze pamiątkowej, tylko online.
            </p>
            <div class="mt-4 flex items-baseline gap-6 text-sm">
                <div>
                    <p class="text-2xl font-bold">{{ messages.length }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">wpisów</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ myCount }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">Twoich</p>
                </div>
                <div class="ml-auto rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest">
                    +20 XP
                </div>
            </div>
        </section>

        <button
            type="button"
            class="mt-5 w-full rounded-2xl bg-rose-500 px-5 py-4 text-base font-semibold text-white shadow-lg shadow-rose-300 transition hover:bg-rose-600 active:scale-[0.98]"
            @click="openForm"
        >
            ✍️ Napisz życzenie
        </button>

        <h2 class="mt-8 mb-4 flex items-baseline justify-between text-xs font-semibold uppercase tracking-widest text-rose-400">
            <span>Życzenia od gości</span>
            <span class="text-rose-300">{{ messages.length }}</span>
        </h2>

        <div v-if="messages.length === 0" class="rounded-2xl border border-dashed border-rose-200 bg-white p-10 text-center">
            <div class="text-4xl">📖</div>
            <p class="mt-3 font-medium text-rose-600">Księga jest pusta</p>
            <p class="text-sm text-rose-400">Bądź pierwszy!</p>
        </div>

        <div v-else class="space-y-4">
            <article
                v-for="m in messages"
                :key="m.id"
                class="relative rounded-2xl border p-5 shadow-sm transition"
                :class="[
                    m.mine ? 'ring-2 ring-rose-400 ring-offset-2 ring-offset-rose-50 bg-white border-rose-200' : cardHue(m.id),
                    justSent === m.id ? 'animate-[pulse_1.5s_ease-in-out_2]' : '',
                ]"
                :style="{ transform: `rotate(${tilt(m.id)}deg)` }"
            >
                <span
                    v-if="m.mine"
                    class="absolute -top-2 -right-2 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-white shadow"
                >
                    Twój
                </span>

                <button
                    v-if="m.thumb_url"
                    type="button"
                    class="mb-3 block w-full overflow-hidden rounded-xl"
                    @click="lightbox = m"
                >
                    <img
                        :src="m.thumb_url"
                        :alt="m.guest ?? ''"
                        class="max-h-52 w-full object-cover transition hover:scale-[1.02]"
                        loading="lazy"
                    />
                </button>

                <blockquote class="font-serif text-lg italic leading-snug text-rose-950">
                    „{{ m.body }}"
                </blockquote>

                <footer class="mt-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <div
                            class="grid h-7 w-7 place-items-center rounded-full bg-gradient-to-br from-rose-400 to-pink-500 text-xs font-bold text-white"
                        >
                            {{ (m.guest ?? '?').charAt(0).toUpperCase() }}
                        </div>
                        <span class="font-medium text-rose-700">— {{ m.guest ?? 'gość' }}</span>
                    </div>
                    <span class="text-rose-400">{{ timeAgo(m.created_at) }}</span>
                </footer>
            </article>
        </div>

        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm sm:items-center sm:p-6"
                @click.self="closeForm"
            >
                <div
                    class="max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-6 pb-8 shadow-2xl sm:rounded-3xl"
                    @click.stop
                >
                    <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-rose-200 sm:hidden"></div>

                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-serif text-xl text-rose-950">Napisz życzenie</h3>
                            <p class="text-xs text-rose-500">Zobaczą wszyscy goście oraz Para Młoda.</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full p-1 text-rose-400 hover:bg-rose-50 hover:text-rose-600"
                            @click="closeForm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <div>
                            <textarea
                                id="msg-body"
                                v-model="form.body"
                                required
                                minlength="3"
                                maxlength="500"
                                rows="5"
                                placeholder="Życzenia, anegdota, prognoza na 25 lat…"
                                class="w-full resize-none rounded-2xl border border-rose-200 bg-rose-50/30 px-4 py-3 outline-none ring-rose-400/40 focus:border-rose-400 focus:ring-4"
                            />
                            <div class="mt-1 flex justify-between text-xs text-rose-400">
                                <span>{{ form.errors.body ?? ' ' }}</span>
                                <span>{{ form.body.length }} / 500</span>
                            </div>
                        </div>

                        <div>
                            <div v-if="!preview">
                                <button
                                    type="button"
                                    class="w-full rounded-2xl border-2 border-dashed border-rose-200 bg-rose-50/50 px-4 py-4 text-sm text-rose-500 hover:border-rose-300"
                                    @click="fileInput?.click()"
                                >
                                    📸 Dołącz zdjęcie (opcjonalnie)
                                </button>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onFile"
                                />
                            </div>

                            <div v-else class="relative">
                                <img :src="preview" alt="podgląd" class="max-h-48 w-full rounded-2xl object-cover" />
                                <button
                                    type="button"
                                    class="absolute right-2 top-2 rounded-full bg-black/60 px-3 py-1 text-xs text-white"
                                    @click="clearPhoto"
                                >
                                    ✕ usuń
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-2xl bg-rose-500 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-rose-300 transition hover:bg-rose-600 disabled:opacity-60"
                        >
                            <span v-if="!form.processing">💌 Wyślij życzenie</span>
                            <span v-else>Wysyłam…</span>
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div
                v-if="lightbox"
                class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 p-4"
                @click="lightbox = null"
            >
                <img
                    :src="lightbox.photo_url ?? ''"
                    :alt="lightbox.guest ?? ''"
                    class="max-h-[75vh] max-w-full rounded-lg"
                    @click.stop
                />
                <div class="mt-4 max-w-md text-center text-white" @click.stop>
                    <blockquote class="font-serif text-xl italic">„{{ lightbox.body }}"</blockquote>
                    <p class="mt-2 text-sm text-rose-200">— {{ lightbox.guest ?? 'gość' }}</p>
                    <button
                        type="button"
                        class="mt-3 text-xs text-white/50 underline"
                        @click="lightbox = null"
                    >
                        zamknij
                    </button>
                </div>
            </div>
        </transition>
    </GuestLayout>
</template>
