<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { resizeImage } from '../lib/resize-image';

const props = defineProps<{
    endpoint: string;
    label?: string;
    accept?: string;
}>();

const input = ref<HTMLInputElement | null>(null);
const busy = ref(false);
const progress = ref(0);

function pick() {
    input.value?.click();
}

async function onFile(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    busy.value = true;
    progress.value = 0;

    try {
        const resized = await resizeImage(file, 1920, 0.85);

        router.post(
            props.endpoint,
            { photo: resized },
            {
                forceFormData: true,
                preserveScroll: true,
                onProgress: (e) => {
                    progress.value = e?.percentage ?? 0;
                },
                onFinish: () => {
                    busy.value = false;
                    progress.value = 0;
                    if (target) target.value = '';
                },
            },
        );
    } catch {
        busy.value = false;
        if (target) target.value = '';
    }
}
</script>

<template>
    <div>
        <button
            type="button"
            :disabled="busy"
            class="w-full rounded-2xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-200 transition hover:bg-rose-600 disabled:opacity-60"
            @click="pick"
        >
            <span v-if="!busy">📸 {{ label ?? 'Zrób zdjęcie' }}</span>
            <span v-else>Wysyłam… {{ progress }}%</span>
        </button>
        <input
            ref="input"
            type="file"
            :accept="accept ?? 'image/*'"
            capture="environment"
            class="hidden"
            @change="onFile"
        />
    </div>
</template>
