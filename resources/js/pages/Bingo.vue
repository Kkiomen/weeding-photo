<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';
import { resizeImage } from '../lib/resize-image';

interface BingoCell {
    position: number;
    field_id: number;
    icon: string | null;
    title: string | null;
    description: string | null;
    is_center: boolean;
    marked: boolean;
    thumb: string | null;
}

interface BingoCard {
    cells: BingoCell[];
    lines_won: number;
    full_card_won: boolean;
}

const props = defineProps<{ card: BingoCard }>();

const selected = ref<BingoCell | null>(null);
const uploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const markedCount = computed(() => props.card.cells.filter((c) => c.marked).length);
const totalCells = computed(() => props.card.cells.length);

function openCell(cell: BingoCell) {
    if (cell.is_center) return;
    if (cell.marked) return;
    selected.value = cell;
}

async function onFile(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file || !selected.value) return;

    uploading.value = true;
    const resized = await resizeImage(file, 1920, 0.85);
    const fieldId = selected.value.field_id;

    router.post(
        `/bingo/${fieldId}`,
        { photo: resized },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                uploading.value = false;
                if (target) target.value = '';
                selected.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Bingo weselne" />
    <GuestLayout>
        <section class="rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 p-6 text-white shadow-lg shadow-purple-200">
            <div class="text-4xl">🎯</div>
            <h1 class="mt-2 font-serif text-2xl leading-tight">Bingo weselne</h1>
            <p class="mt-1 text-sm text-white/90">
                Twoja karta 5×5. Trzeba udokumentować zdarzenie zdjęciem. Pełny rząd/kolumna/przekątna = +50 XP. Pełna karta = +300 XP!
            </p>
            <div class="mt-4 flex gap-6 text-sm">
                <div>
                    <p class="text-2xl font-bold">{{ markedCount }} / {{ totalCells }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">pól</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ card.lines_won }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">linie</p>
                </div>
                <div v-if="card.full_card_won">
                    <p class="text-2xl">👑</p>
                    <p class="text-[10px] uppercase tracking-widest text-white/70">PEŁNA!</p>
                </div>
            </div>
        </section>

        <div class="mt-6 grid grid-cols-5 gap-1.5">
            <button
                v-for="cell in card.cells"
                :key="cell.position"
                type="button"
                :disabled="cell.marked || cell.is_center"
                class="group relative aspect-square overflow-hidden rounded-lg border p-1.5 text-center transition"
                :class="{
                    'border-emerald-300 bg-emerald-50': cell.marked && !cell.is_center,
                    'border-rose-300 bg-rose-100': cell.is_center,
                    'border-rose-200 bg-white hover:border-rose-400 hover:bg-rose-50 active:scale-95': !cell.marked && !cell.is_center,
                }"
                @click="openCell(cell)"
            >
                <img
                    v-if="cell.thumb"
                    :src="cell.thumb"
                    :alt="cell.title ?? ''"
                    class="absolute inset-0 h-full w-full object-cover opacity-60"
                />
                <div class="relative flex h-full flex-col items-center justify-center">
                    <div class="text-lg leading-none sm:text-2xl">{{ cell.icon }}</div>
                    <p class="mt-1 line-clamp-2 text-[8px] font-medium leading-tight text-rose-950 sm:text-[10px]">
                        {{ cell.title }}
                    </p>
                    <span
                        v-if="cell.marked && !cell.is_center"
                        class="absolute right-0.5 top-0.5 rounded-full bg-emerald-500 px-1 text-[10px] font-bold text-white"
                    >
                        ✓
                    </span>
                </div>
            </button>
        </div>

        <div v-if="card.full_card_won" class="mt-6 rounded-2xl border-2 border-yellow-400 bg-gradient-to-br from-yellow-100 to-orange-100 p-6 text-center">
            <div class="text-5xl">👑</div>
            <p class="mt-2 font-serif text-xl text-orange-900">Pełna karta!</p>
            <p class="mt-1 text-sm text-orange-700">Wygrałeś BINGO. Legenda wesela.</p>
        </div>

        <transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div
                v-if="selected"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6 backdrop-blur"
                @click="selected = null"
            >
                <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl" @click.stop>
                    <div class="text-center">
                        <div class="text-5xl">{{ selected.icon }}</div>
                        <h3 class="mt-3 font-serif text-2xl text-rose-950">{{ selected.title }}</h3>
                        <p v-if="selected.description" class="mt-1 text-sm text-rose-600">
                            {{ selected.description }}
                        </p>
                    </div>

                    <p class="mt-5 text-center text-sm text-rose-500">
                        Aby zaznaczyć pole – zrób zdjęcie jako dowód.
                    </p>

                    <button
                        type="button"
                        :disabled="uploading"
                        class="mt-4 w-full rounded-2xl bg-rose-500 px-4 py-3 text-base font-semibold text-white hover:bg-rose-600 disabled:opacity-60"
                        @click="fileInput?.click()"
                    >
                        <span v-if="!uploading">📸 Zrób zdjęcie</span>
                        <span v-else>Wysyłam…</span>
                    </button>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onFile"
                    />

                    <button
                        type="button"
                        class="mt-2 w-full py-2 text-xs text-rose-400 underline"
                        @click="selected = null"
                    >
                        anuluj
                    </button>
                </div>
            </div>
        </transition>
    </GuestLayout>
</template>
