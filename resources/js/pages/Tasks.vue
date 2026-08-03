<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';
import TaskUploader from '../components/TaskUploader.vue';
import type { Task, Guest } from '../types/global';

const props = defineProps<{
    tasks: Task[];
    guest: Guest;
}>();

const pending = computed(() => props.tasks.filter((t) => !t.completed));
const completed = computed(() => props.tasks.filter((t) => t.completed));
const done = computed(() => completed.value.length);
const nextLevelXp = computed(() => props.guest.level * 100);
const progressPct = computed(() => {
    const inLevel = props.guest.xp % 100;
    return Math.min(100, inLevel);
});

type RandomTask = Pick<Task, 'id' | 'title' | 'description' | 'icon' | 'xp_reward'>;

const rolling = ref(false);
const rolled = ref<RandomTask | null>(null);
const rollPreview = ref<RandomTask | null>(null);
let rollTimer: number | null = null;

async function drawRandom() {
    if (rolling.value) return;
    rolling.value = true;
    rolled.value = null;

    const pool = pending.value.length > 0 ? pending.value : props.tasks;

    if (rollTimer) window.clearInterval(rollTimer);
    rollTimer = window.setInterval(() => {
        rollPreview.value = pool[Math.floor(Math.random() * pool.length)] ?? null;
    }, 80);

    try {
        const res = await fetch('/api/tasks/random', { headers: { Accept: 'application/json' } });
        const data = (await res.json()) as { task: RandomTask | null };

        window.setTimeout(() => {
            if (rollTimer) window.clearInterval(rollTimer);
            rollTimer = null;
            rollPreview.value = null;
            rolled.value = data.task;
            rolling.value = false;
        }, 1400);
    } catch {
        if (rollTimer) window.clearInterval(rollTimer);
        rollTimer = null;
        rolling.value = false;
    }
}

function closeRoll() {
    rolled.value = null;
}
</script>

<template>
    <Head title="Misje" />
    <GuestLayout>
        <section class="rounded-3xl bg-gradient-to-br from-rose-500 to-pink-500 p-5 text-white shadow-lg shadow-rose-200">
            <div class="flex items-baseline justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-white/70">Twój poziom</p>
                    <p class="mt-1 text-3xl font-semibold">LVL {{ guest.level }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-widest text-white/70">Misje</p>
                    <p class="mt-1 text-3xl font-semibold">{{ done }} / {{ tasks.length }}</p>
                </div>
            </div>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/20">
                <div
                    class="h-full rounded-full bg-white transition-all duration-500"
                    :style="{ width: `${progressPct}%` }"
                />
            </div>
            <p class="mt-2 text-right text-xs text-white/80">
                {{ guest.xp }} / {{ nextLevelXp }} XP
            </p>
        </section>

        <h2 class="mt-6 mb-3 text-xs font-semibold uppercase tracking-widest text-rose-400">
            🎮 Zabawy weselne
        </h2>
        <div class="grid grid-cols-2 gap-3">
            <a
                href="/bingo"
                class="rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 p-4 text-white shadow-md shadow-purple-200 transition hover:scale-[1.02]"
            >
                <div class="text-3xl">🎲</div>
                <p class="mt-2 font-serif text-lg leading-tight">Bingo</p>
                <p class="text-xs text-white/80">5×5 karta · dowody</p>
            </a>
            <a
                href="/zdrapka"
                class="rounded-2xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 p-4 text-white shadow-md shadow-amber-200 transition hover:scale-[1.02]"
            >
                <div class="text-3xl">🎰</div>
                <p class="mt-2 font-serif text-lg leading-tight">Zdrapka</p>
                <p class="text-xs text-white/90">raz na 30 min</p>
            </a>
            <a
                href="/cytaty"
                class="rounded-2xl bg-gradient-to-br from-violet-500 via-fuchsia-500 to-rose-500 p-4 text-white shadow-md shadow-fuchsia-200 transition hover:scale-[1.02]"
            >
                <div class="text-3xl">🎙️</div>
                <p class="mt-2 font-serif text-lg leading-tight">Cytaty</p>
                <p class="text-xs text-white/90">anonimowo</p>
            </a>
            <a
                href="/wiadomosc"
                class="rounded-2xl bg-gradient-to-br from-amber-400 via-rose-400 to-pink-500 p-4 text-white shadow-md shadow-rose-200 transition hover:scale-[1.02]"
            >
                <div class="text-3xl">💌</div>
                <p class="mt-2 font-serif text-lg leading-tight">List do Młodych</p>
                <p class="text-xs text-white/90">życzenia + zdjęcie</p>
            </a>
        </div>

        <section class="relative mt-6 overflow-hidden rounded-3xl border-2 border-dashed border-rose-300 bg-gradient-to-br from-white via-rose-50 to-pink-50 p-5 shadow-md">
            <div class="flex items-start gap-4">
                <div class="text-4xl" :class="{ 'animate-spin': rolling }">🎲</div>
                <div class="flex-1">
                    <h3 class="font-serif text-lg text-rose-900">Nie wiesz co robić?</h3>
                    <p class="mt-0.5 text-sm text-rose-600">Wylosuj misję i idź w bój!</p>
                </div>
            </div>
            <button
                type="button"
                :disabled="rolling"
                class="mt-4 w-full rounded-2xl bg-gradient-to-r from-rose-500 via-pink-500 to-rose-500 bg-[length:200%_100%] px-4 py-3 text-sm font-bold uppercase tracking-widest text-white shadow-lg shadow-rose-300 transition hover:bg-[position:100%_0] disabled:opacity-70"
                @click="drawRandom"
            >
                <span v-if="!rolling">🎯 Losuj misję</span>
                <span v-else>Losowanie…</span>
            </button>
        </section>

        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="rolling || rolled"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6 backdrop-blur"
                @click="!rolling && closeRoll()"
            >
                <div
                    class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
                    :class="rolling ? 'animate-pulse' : ''"
                    @click.stop
                >
                    <div v-if="rolling" class="text-center">
                        <div class="text-6xl">{{ rollPreview?.icon ?? '🎲' }}</div>
                        <p class="mt-3 truncate font-serif text-lg text-rose-900">
                            {{ rollPreview?.title ?? '...' }}
                        </p>
                        <p class="mt-1 text-xs uppercase tracking-widest text-rose-400">
                            losuję…
                        </p>
                    </div>

                    <div v-else-if="rolled" class="text-center">
                        <p class="text-xs uppercase tracking-[0.3em] text-rose-400">Twoja misja</p>
                        <div class="mt-3 text-6xl">{{ rolled.icon }}</div>
                        <h3 class="mt-3 font-serif text-2xl leading-tight text-rose-950">
                            {{ rolled.title }}
                        </h3>
                        <p v-if="rolled.description" class="mt-2 text-sm text-rose-600">
                            {{ rolled.description }}
                        </p>
                        <div class="mt-3 inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-bold uppercase tracking-widest text-rose-600">
                            +{{ rolled.xp_reward }} XP
                        </div>

                        <div class="mt-5 space-y-2">
                            <TaskUploader
                                :endpoint="`/tasks/${rolled.id}/upload`"
                                label="Zrób to zdjęcie!"
                            />
                            <button
                                type="button"
                                class="w-full rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-medium text-rose-500 hover:bg-rose-50"
                                @click="drawRandom"
                            >
                                🔄 Wylosuj inne
                            </button>
                            <button
                                type="button"
                                class="w-full text-xs text-rose-400 underline"
                                @click="closeRoll"
                            >
                                zamknij
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <h2 class="mt-8 mb-3 flex items-baseline justify-between text-xs font-semibold uppercase tracking-widest text-rose-400">
            <span>Misje do wykonania</span>
            <span class="text-rose-300">{{ pending.length }}</span>
        </h2>

        <div v-if="pending.length === 0" class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/60 p-6 text-center">
            <div class="text-3xl">🎉</div>
            <p class="mt-2 font-medium text-emerald-800">Wszystkie misje wykonane!</p>
            <p class="mt-1 text-sm text-emerald-700">Wrzuć bonusowe zdjęcia poniżej.</p>
        </div>

        <transition-group
            v-else
            tag="div"
            class="grid grid-cols-2 gap-3 sm:grid-cols-3"
            enter-active-class="transition duration-500"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-300 absolute"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 scale-95"
            move-class="transition duration-500"
        >
            <article
                v-for="task in pending"
                :key="task.id"
                class="flex flex-col rounded-2xl border border-rose-100 bg-white p-3 shadow-sm transition hover:border-rose-200"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="text-3xl leading-none">{{ task.icon }}</div>
                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-rose-600">
                        +{{ task.xp_reward }}
                    </span>
                </div>
                <h3 class="mt-2 text-sm font-semibold leading-snug text-rose-950">
                    {{ task.title }}
                </h3>
                <p v-if="task.description" class="mt-1 line-clamp-3 text-xs leading-snug text-rose-600">
                    {{ task.description }}
                </p>
                <div class="mt-auto pt-3">
                    <TaskUploader
                        :endpoint="`/tasks/${task.id}/upload`"
                        label="Wykonaj"
                    />
                </div>
            </article>
        </transition-group>

        <template v-if="completed.length > 0">
            <h2 class="mt-8 mb-3 flex items-baseline justify-between text-xs font-semibold uppercase tracking-widest text-emerald-500">
                <span>✓ Zrobione</span>
                <span class="text-emerald-400">{{ completed.length }}</span>
            </h2>

            <div class="space-y-2 opacity-90">
                <transition-group
                    enter-active-class="transition duration-500"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    move-class="transition duration-500"
                >
                    <article
                        v-for="task in completed"
                        :key="task.id"
                        class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div class="text-2xl leading-none opacity-70">{{ task.icon }}</div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-emerald-900">{{ task.title }}</h3>
                            </div>
                            <span class="rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-white">
                                ✓ +{{ task.xp_reward }} XP
                            </span>
                        </div>
                    </article>
                </transition-group>
            </div>
        </template>

        <div class="mt-8 rounded-2xl border border-rose-100 bg-white p-4">
            <h3 class="font-semibold">📷 Bonusowe zdjęcie</h3>
            <p class="mt-1 text-sm text-rose-600">
                Zobaczyłeś coś ładnego bez zadania? Wrzuć wolne zdjęcie i dostań +5 XP.
            </p>
            <div class="mt-3">
                <TaskUploader endpoint="/photos" label="Wrzuć wolne zdjęcie" />
            </div>
        </div>
    </GuestLayout>
</template>
