<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

interface PhotoSlide {
    type: 'photo';
    id: string;
    url: string;
    guest: string | null;
    task: { title: string; icon: string } | null;
}
interface MessageSlide {
    type: 'message';
    id: string;
    body: string;
    guest: string | null;
    photo_url: string | null;
}
type Slide = PhotoSlide | MessageSlide;

const page = usePage();
const wedding = computed(() => page.props.wedding);

const slides = ref<Slide[]>([]);
const idx = ref(0);
let pollTimer: number | null = null;
let advanceTimer: number | null = null;

async function fetchSlides() {
    try {
        const res = await fetch('/api/slideshow?limit=80', { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = (await res.json()) as { slides: Slide[] };
        slides.value = data.slides;
        if (idx.value >= slides.value.length) idx.value = 0;
    } catch {
        // ignore
    }
}

function next() {
    if (!slides.value.length) return;
    idx.value = (idx.value + 1) % slides.value.length;
}

onMounted(() => {
    fetchSlides();
    pollTimer = window.setInterval(fetchSlides, 5000);
    advanceTimer = window.setInterval(next, 6000);
});

onBeforeUnmount(() => {
    if (pollTimer) window.clearInterval(pollTimer);
    if (advanceTimer) window.clearInterval(advanceTimer);
});

const current = computed(() => slides.value[idx.value]);
</script>

<template>
    <Head title="Slideshow" />
    <div class="relative flex min-h-dvh flex-col items-center justify-center overflow-hidden bg-black text-white">
        <div v-if="!current" class="text-center">
            <div class="text-7xl">❤️</div>
            <h1 class="mt-6 font-serif text-4xl">{{ wedding.couple }}</h1>
            <p class="mt-2 text-rose-200/80">Zdjęcia od gości pojawią się tu na żywo…</p>
        </div>

        <template v-else>
            <transition
                v-if="current.type === 'photo'"
                enter-active-class="transition duration-1000"
                enter-from-class="opacity-0 scale-105"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-700 absolute inset-0"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div :key="current.id" class="absolute inset-0 flex items-center justify-center">
                    <img :src="current.url" :alt="current.guest ?? ''" class="max-h-dvh max-w-full object-contain" />
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 pt-24 text-center">
                        <p class="text-2xl font-semibold">
                            <span v-if="current.task">{{ current.task.icon }} {{ current.task.title }}</span>
                            <span v-else>📸 Wolne zdjęcie</span>
                        </p>
                        <p class="mt-1 text-lg text-rose-200/90">od {{ current.guest ?? 'gościa' }}</p>
                    </div>
                </div>
            </transition>

            <transition
                v-else
                enter-active-class="transition duration-1000"
                enter-from-class="opacity-0 -rotate-6 scale-95"
                enter-to-class="opacity-100 rotate-0 scale-100"
                leave-active-class="transition duration-700 absolute inset-0"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 rotate-3"
            >
                <div
                    :key="current.id"
                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-rose-500 via-pink-500 to-amber-400 p-8"
                >
                    <div class="w-full max-w-3xl rounded-3xl bg-white p-10 text-rose-950 shadow-2xl md:p-16" style="transform: rotate(-1.5deg)">
                        <div class="mb-4 text-5xl">💌</div>
                        <blockquote class="font-serif text-3xl leading-snug md:text-5xl">
                            „{{ current.body }}"
                        </blockquote>
                        <img
                            v-if="current.photo_url"
                            :src="current.photo_url"
                            class="mt-6 max-h-64 rounded-xl object-cover"
                            :alt="current.guest ?? ''"
                        />
                        <p class="mt-6 text-right text-lg text-rose-500">— {{ current.guest ?? 'gość' }}</p>
                    </div>
                </div>
            </transition>
        </template>

        <div class="absolute right-4 top-4 rounded-full bg-white/10 px-3 py-1 text-xs backdrop-blur">
            {{ wedding.couple }} · {{ slides.length }} slajdów
        </div>
    </div>
</template>
