<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';

interface Reward {
    id: number;
    title: string;
    description: string | null;
    icon: string;
    xp_bonus: number;
}
interface HistoryItem {
    id: number;
    icon: string;
    title: string;
    xp_bonus: number;
    claimed_at: string;
}

const props = defineProps<{
    cooldown_seconds: number;
    history: HistoryItem[];
}>();

const cooldown = ref(props.cooldown_seconds);
const history = ref<HistoryItem[]>([...props.history]);
const scratching = ref(false);
const revealed = ref<Reward | null>(null);
const error = ref<string | null>(null);
let cooldownTimer: number | null = null;

function fmt(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}

function startCooldownTicker() {
    if (cooldownTimer) window.clearInterval(cooldownTimer);
    cooldownTimer = window.setInterval(() => {
        if (cooldown.value > 0) cooldown.value -= 1;
        else if (cooldownTimer) {
            window.clearInterval(cooldownTimer);
            cooldownTimer = null;
        }
    }, 1000);
}

async function scratch() {
    if (scratching.value || cooldown.value > 0) return;
    scratching.value = true;
    error.value = null;

    try {
        const xsrf = decodeURIComponent(
            (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) ?? [, ''])[1] ?? '',
        );
        const res = await fetch('/zdrapka', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrf,
            },
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.cooldown_seconds !== undefined) {
                cooldown.value = data.cooldown_seconds;
                startCooldownTicker();
            }
            error.value = 'Musisz jeszcze poczekać.';
            scratching.value = false;
            return;
        }

        // 1.5s "drapania"
        await new Promise((r) => window.setTimeout(r, 1500));
        revealed.value = data.reward;
        cooldown.value = data.cooldown_seconds;
        history.value = data.history;
        scratching.value = false;
        startCooldownTicker();
    } catch {
        error.value = 'Coś poszło nie tak.';
        scratching.value = false;
    }
}

function close() {
    revealed.value = null;
}

onMounted(() => {
    if (cooldown.value > 0) startCooldownTicker();
});
onBeforeUnmount(() => {
    if (cooldownTimer) window.clearInterval(cooldownTimer);
});

const canScratch = computed(() => cooldown.value === 0 && !scratching.value);
</script>

<template>
    <Head title="Zdrapka" />
    <GuestLayout>
        <section class="rounded-3xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 p-6 text-white shadow-lg shadow-amber-200">
            <div class="text-4xl">🎰</div>
            <h1 class="mt-2 font-serif text-2xl leading-tight">Zdrapka szczęścia</h1>
            <p class="mt-1 text-sm text-white/90">
                XP, wyzwania, głupoty. Raz na 30 minut. Losowanie ważone – nie wszystko wygrasz.
            </p>
        </section>

        <div class="mt-6">
            <button
                type="button"
                :disabled="!canScratch"
                class="relative aspect-square w-full overflow-hidden rounded-3xl bg-gradient-to-br from-amber-300 via-yellow-500 to-orange-500 shadow-2xl shadow-amber-300 transition"
                :class="{
                    'hover:scale-[1.02] active:scale-95': canScratch,
                    'opacity-60 cursor-not-allowed': !canScratch,
                }"
                @click="scratch"
            >
                <div v-if="scratching" class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-8xl animate-spin">🎰</div>
                    <p class="mt-4 font-serif text-2xl text-white drop-shadow">Losuję…</p>
                </div>

                <div v-else-if="cooldown > 0" class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-7xl grayscale">⏳</div>
                    <p class="mt-4 font-serif text-4xl text-white drop-shadow">{{ fmt(cooldown) }}</p>
                    <p class="mt-1 text-sm text-white/90">do następnej zdrapki</p>
                </div>

                <div v-else class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-8xl">🎁</div>
                    <p class="mt-4 font-serif text-3xl text-white drop-shadow">DRAPNIJ!</p>
                    <p class="mt-1 text-sm text-white/90">i sprawdź co dziś dostajesz</p>
                </div>

                <div v-if="canScratch" class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-white/0 via-white/20 to-white/0 [animation:shine_3s_linear_infinite]" />
            </button>

            <p v-if="error" class="mt-2 text-center text-sm text-red-500">{{ error }}</p>
        </div>

        <h2 v-if="history.length > 0" class="mt-8 mb-3 text-xs font-semibold uppercase tracking-widest text-amber-500">
            Twoja historia
        </h2>
        <ul class="space-y-2">
            <li
                v-for="h in history"
                :key="h.id"
                class="flex items-center gap-3 rounded-2xl border border-amber-100 bg-white p-3 shadow-sm"
            >
                <div class="text-2xl">{{ h.icon }}</div>
                <div class="flex-1 text-sm">
                    <p class="font-medium text-rose-950">{{ h.title }}</p>
                </div>
                <div v-if="h.xp_bonus > 0" class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-amber-700">
                    +{{ h.xp_bonus }} XP
                </div>
            </li>
        </ul>

        <transition
            enter-active-class="transition duration-500 ease-out"
            enter-from-class="opacity-0 scale-50"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="revealed"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6 backdrop-blur"
                @click="close"
            >
                <div class="w-full max-w-md rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 p-8 text-center shadow-2xl" @click.stop>
                    <div class="text-8xl">{{ revealed.icon }}</div>
                    <h3 class="mt-4 font-serif text-3xl text-rose-950">{{ revealed.title }}</h3>
                    <p v-if="revealed.description" class="mt-2 text-rose-700">
                        {{ revealed.description }}
                    </p>
                    <div v-if="revealed.xp_bonus > 0" class="mt-4 inline-block rounded-full bg-emerald-500 px-4 py-1.5 text-sm font-bold uppercase tracking-widest text-white">
                        +{{ revealed.xp_bonus }} XP
                    </div>
                    <button
                        type="button"
                        class="mt-6 w-full rounded-2xl bg-rose-500 py-3 font-semibold text-white hover:bg-rose-600"
                        @click="close"
                    >
                        Świetnie!
                    </button>
                </div>
            </div>
        </transition>
    </GuestLayout>
</template>

<style>
@keyframes shine {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(100%) rotate(45deg); }
}
</style>
