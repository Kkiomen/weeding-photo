<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from '../layouts/GuestLayout.vue';
import type { Guest, RankingRow } from '../types/global';

const props = defineProps<{ ranking: RankingRow[]; guest: Guest }>();

const rows = ref<RankingRow[]>([...props.ranking]);
let timer: number | null = null;

async function refresh() {
    try {
        const res = await fetch('/api/ranking', { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const data = (await res.json()) as { ranking: RankingRow[] };
        rows.value = data.ranking;
    } catch {
        // ignore
    }
}

onMounted(() => {
    timer = window.setInterval(refresh, 5000);
});
onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer);
});

function medal(rank: number): string {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return `#${rank}`;
}
</script>

<template>
    <Head title="Ranking" />
    <GuestLayout>
        <h1 class="mb-4 text-xl font-semibold">Ranking fotografów</h1>

        <ol class="space-y-2">
            <li
                v-for="row in rows"
                :key="row.id"
                class="flex items-center gap-3 rounded-2xl border p-3 shadow-sm"
                :class="row.id === guest.id ? 'border-rose-400 bg-rose-50' : 'border-rose-100 bg-white'"
            >
                <div class="w-10 text-center text-lg font-bold text-rose-500">
                    {{ medal(row.rank) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ row.nickname }}</span>
                        <span v-if="row.id === guest.id" class="rounded bg-rose-500 px-1.5 py-0.5 text-[10px] text-white">
                            TY
                        </span>
                    </div>
                    <p class="text-xs text-rose-500">
                        {{ row.photos }} {{ row.photos === 1 ? 'zdjęcie' : 'zdjęć' }} · LVL {{ row.level }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-rose-600">{{ row.xp }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-rose-400">XP</p>
                </div>
            </li>
        </ol>

        <div v-if="rows.length === 0" class="rounded-2xl border border-dashed border-rose-200 p-8 text-center text-rose-400">
            Ranking jeszcze pusty. Zrób pierwsze zdjęcie!
        </div>
    </GuestLayout>
</template>
