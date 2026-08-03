<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';
import { resizeImage } from '../lib/resize-image';
import type { Photo } from '../types/global';

const props = defineProps<{ photos: Photo[]; total_photos: number; sort: 'newest' | 'random' }>();

const photos = ref<Photo[]>([...props.photos]);
const latestId = ref<number>(photos.value[0]?.id ?? 0);
const lightbox = ref<Photo | null>(null);
const view = ref<'polaroid' | 'grid' | 'stream'>('polaroid');
const reshuffling = ref(false);
let timer: number | null = null;

async function poll() {
    if (props.sort !== 'newest') return;
    try {
        const res = await fetch(`/api/photos?since=${latestId.value}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) return;
        const data = (await res.json()) as { photos: Photo[]; latest_id: number };
        if (data.photos.length) {
            photos.value = [...data.photos.reverse(), ...photos.value];
            latestId.value = data.latest_id;
        }
    } catch {
        // ignore
    }
}

function switchSort(next: 'newest' | 'random') {
    // "Najnowsze" gdy już aktywne — nic nie rób. "Losowo" zawsze przetasowuje.
    if (next === 'newest' && props.sort === 'newest') return;
    reshuffling.value = next === 'random';
    router.visit(next === 'random' ? '/gallery?sort=random' : '/gallery', {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            reshuffling.value = false;
        },
    });
}

onMounted(() => {
    if (props.sort === 'newest') {
        timer = window.setInterval(poll, 4000);
    }
});
onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer);
});

const tilt = (id: number) => {
    const seed = (id * 9301 + 49297) % 233280;
    return ((seed / 233280) - 0.5) * 8;
};

const guests = computed(() => new Set(photos.value.map((p) => p.guest)).size);

function timeAgo(iso: string): string {
    const diff = (Date.now() - new Date(iso).getTime()) / 1000;
    if (diff < 60) return 'przed chwilą';
    if (diff < 3600) return `${Math.floor(diff / 60)} min temu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} godz. temu`;
    return `${Math.floor(diff / 86400)} dni temu`;
}

function openLightbox(p: Photo) {
    lightbox.value = p;
}

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const uploadProgress = ref(0);

function pickPhoto() {
    if (uploading.value) return;
    fileInput.value?.click();
}

async function onFilePicked(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    uploading.value = true;
    uploadProgress.value = 0;

    try {
        const resized = await resizeImage(file, 1920, 0.85);
        router.post(
            '/photos',
            { photo: resized },
            {
                forceFormData: true,
                preserveScroll: true,
                onProgress: (ev) => {
                    uploadProgress.value = ev?.percentage ?? 0;
                },
                onSuccess: () => {
                    // Instant feed refresh po sukcesie
                    poll();
                },
                onFinish: () => {
                    uploading.value = false;
                    uploadProgress.value = 0;
                    if (target) target.value = '';
                },
            },
        );
    } catch {
        uploading.value = false;
        if (target) target.value = '';
    }
}
</script>

<template>
    <Head title="Galeria" />
    <GuestLayout>
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-rose-400 p-6 text-white shadow-xl shadow-rose-200">
            <div class="absolute -right-8 -top-8 text-8xl opacity-20 select-none">❤️</div>
            <p class="text-xs uppercase tracking-[0.3em] text-white/70">Wspólny album</p>
            <h1 class="mt-1 font-serif text-3xl leading-tight">Galeria wesela</h1>
            <div class="mt-4 flex items-baseline gap-6 text-sm">
                <div>
                    <p class="text-2xl font-bold">{{ total_photos }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">zdjęć</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ guests }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">fotografów</p>
                </div>
                <div class="ml-auto rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest">
                    +5 XP
                </div>
            </div>

            <button
                type="button"
                :disabled="uploading"
                class="mt-5 w-full rounded-2xl bg-white/95 px-4 py-3 text-base font-bold text-rose-600 shadow-md transition hover:bg-white active:scale-[0.98] disabled:opacity-70"
                @click="pickPhoto"
            >
                <span v-if="!uploading">📸 Wrzuć zdjęcie do galerii</span>
                <span v-else>Wysyłam… {{ uploadProgress }}%</span>
            </button>

            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                capture="environment"
                class="hidden"
                @change="onFilePicked"
            />
        </section>

        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
            <button
                v-for="v in [
                    { key: 'polaroid', label: '🎞 Polaroidy' },
                    { key: 'grid', label: '⚊ Mozaika' },
                    { key: 'stream', label: '📱 Feed' },
                ]"
                :key="v.key"
                type="button"
                class="rounded-full px-3 py-1.5 font-medium transition"
                :class="view === v.key ? 'bg-rose-500 text-white shadow' : 'bg-white text-rose-500 border border-rose-200 hover:bg-rose-50'"
                @click="view = v.key as typeof view"
            >
                {{ v.label }}
            </button>

            <div class="ml-auto flex items-center gap-1 rounded-full border border-rose-200 bg-white p-0.5">
                <button
                    type="button"
                    class="rounded-full px-2.5 py-1 font-medium transition"
                    :class="sort === 'newest' ? 'bg-rose-500 text-white' : 'text-rose-500 hover:bg-rose-50'"
                    @click="switchSort('newest')"
                >
                    Najnowsze
                </button>
                <button
                    type="button"
                    :disabled="reshuffling"
                    class="flex items-center gap-1 rounded-full px-2.5 py-1 font-medium transition disabled:opacity-60"
                    :class="sort === 'random' ? 'bg-rose-500 text-white' : 'text-rose-500 hover:bg-rose-50'"
                    @click="switchSort('random')"
                >
                    <span :class="{ 'animate-spin': reshuffling }">🎲</span>
                    <span>{{ sort === 'random' ? 'Wylosuj' : 'Losowo' }}</span>
                </button>
            </div>
        </div>

        <div v-if="photos.length === 0" class="mt-6 rounded-2xl border border-dashed border-rose-200 bg-white p-10 text-center">
            <div class="text-4xl">📷</div>
            <p class="mt-3 font-medium text-rose-600">Jeszcze nikt nic nie wrzucił.</p>
            <p class="text-sm text-rose-400">Bądź pierwszy!</p>
        </div>

        <div v-else-if="view === 'polaroid'" class="relative mt-6 grid grid-cols-2 gap-4 pb-6 sm:grid-cols-3">
            <button
                v-for="(p, i) in photos"
                :key="p.id"
                type="button"
                class="group relative rounded-md bg-white p-2 pb-8 shadow-lg shadow-rose-300/30 transition duration-300 hover:z-10 hover:scale-105"
                :style="{ transform: `rotate(${tilt(p.id)}deg)`, transitionDelay: `${(i % 6) * 30}ms` }"
                @click="openLightbox(p)"
            >
                <div class="relative aspect-square overflow-hidden rounded-sm bg-rose-100">
                    <img :src="p.thumb" :alt="p.guest ?? ''" loading="lazy" class="h-full w-full object-cover" />
                    <span
                        v-if="p.task"
                        class="absolute left-1 top-1 rounded-full bg-white/90 px-1.5 py-0.5 text-[10px] font-medium text-rose-600 shadow"
                    >
                        {{ p.task.icon }}
                    </span>
                    <span
                        v-if="p.bingo"
                        class="absolute right-1 top-1 rounded-full bg-purple-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow"
                        title="Zdjęcie z Bingo"
                    >
                        🎯
                    </span>
                </div>
                <p class="absolute inset-x-2 bottom-1.5 truncate text-center font-serif text-[11px] text-rose-800">
                    {{ p.guest ?? 'gość' }}
                </p>
            </button>
        </div>

        <div v-else-if="view === 'grid'" class="mt-6 grid grid-cols-3 gap-1.5 sm:grid-cols-4">
            <button
                v-for="p in photos"
                :key="p.id"
                type="button"
                class="group relative aspect-square overflow-hidden rounded-lg bg-rose-100"
                @click="openLightbox(p)"
            >
                <img
                    :src="p.thumb"
                    :alt="p.guest ?? ''"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 transition group-hover:opacity-100" />
                <span
                    v-if="p.task"
                    class="absolute left-1.5 top-1.5 rounded-full bg-white/90 px-1.5 py-0.5 text-[10px] text-rose-600 shadow"
                >
                    {{ p.task.icon }}
                </span>
                <span
                    v-if="p.bingo"
                    class="absolute right-1.5 top-1.5 rounded-full bg-purple-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow"
                    title="Zdjęcie z Bingo"
                >
                    🎯
                </span>
                <p class="absolute inset-x-1.5 bottom-1.5 truncate text-left text-[10px] font-medium text-white opacity-0 transition group-hover:opacity-100">
                    {{ p.guest }}
                </p>
            </button>
        </div>

        <div v-else class="mt-6 space-y-4">
            <article
                v-for="p in photos"
                :key="p.id"
                class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm"
            >
                <header class="flex items-center gap-2 p-3">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-gradient-to-br from-rose-400 to-pink-500 text-sm font-bold text-white">
                        {{ (p.guest ?? '?').charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-rose-950">{{ p.guest ?? 'gość' }}</p>
                        <p class="text-[10px] text-rose-400">{{ timeAgo(p.created_at) }}</p>
                    </div>
                    <span
                        v-if="p.task"
                        class="rounded-full bg-rose-50 px-2 py-1 text-[10px] font-medium text-rose-600"
                    >
                        {{ p.task.icon }} {{ p.task.title }}
                    </span>
                    <a
                        v-else-if="p.bingo"
                        href="/bingo"
                        class="group/tag rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 px-2 py-1 text-[10px] font-bold text-white shadow transition hover:from-purple-600 hover:to-fuchsia-600"
                        title="Zagraj w Bingo!"
                    >
                        🎯 Bingo · {{ p.bingo.title }}
                    </a>
                </header>
                <button type="button" class="block w-full" @click="openLightbox(p)">
                    <img :src="p.url" :alt="p.guest ?? ''" loading="lazy" class="w-full" />
                </button>
            </article>
        </div>

        <transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="lightbox"
                class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 p-4"
                @click="lightbox = null"
            >
                <img
                    :src="lightbox.url"
                    :alt="lightbox.guest ?? ''"
                    class="max-h-[80vh] max-w-full rounded-lg shadow-2xl"
                    @click.stop
                />
                <div class="mt-4 text-center text-white" @click.stop>
                    <p class="font-serif text-xl">
                        <span v-if="lightbox.task">{{ lightbox.task.icon }} {{ lightbox.task.title }}</span>
                        <span v-else-if="lightbox.bingo">{{ lightbox.bingo.icon }} {{ lightbox.bingo.title }}</span>
                        <span v-else>📸 Wolne zdjęcie</span>
                    </p>
                    <a
                        v-if="lightbox.bingo"
                        href="/bingo"
                        class="mt-3 inline-block rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition hover:scale-105"
                    >
                        🎯 Zagraj w Bingo!
                    </a>
                    <p class="mt-1 text-sm text-rose-200">
                        od {{ lightbox.guest ?? 'gościa' }} · {{ timeAgo(lightbox.created_at) }}
                    </p>
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
        <button
            v-show="!lightbox"
            type="button"
            :disabled="uploading"
            aria-label="Wrzuć zdjęcie"
            class="fixed bottom-24 right-4 z-40 grid h-14 w-14 place-items-center rounded-full bg-gradient-to-br from-rose-500 to-pink-500 text-white shadow-2xl shadow-rose-400/50 transition hover:scale-110 active:scale-95 disabled:opacity-70"
            @click="pickPhoto"
        >
            <svg
                v-if="!uploading"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="h-6 w-6"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75v-6m0 0-2.25 2.25M12 9.75l2.25 2.25" />
            </svg>
            <span v-else class="text-[10px] font-bold">{{ uploadProgress }}%</span>
        </button>
    </GuestLayout>
</template>
