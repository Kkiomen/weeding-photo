<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import type { Photo } from '../../types/global';

interface OrgMessage {
    id: number;
    body: string;
    guest: string | null;
    photo_url: string | null;
    created_at: string;
}

const props = defineProps<{
    photos: Photo[];
    messages: OrgMessage[];
    stats: { total_photos: number; total_guests: number; total_messages: number };
}>();

const tab = ref<'photos' | 'messages'>('photos');
const lightbox = ref<Photo | null>(null);

function removePhoto(p: Photo) {
    if (!confirm(`Usunąć zdjęcie od ${p.guest ?? 'gościa'}?`)) return;
    router.delete(`/organizer/photos/${p.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            lightbox.value = null;
        },
    });
}

function removeMessage(m: OrgMessage) {
    if (!confirm(`Usunąć wiadomość od ${m.guest ?? 'gościa'}?`)) return;
    router.delete(`/organizer/messages/${m.id}`, { preserveScroll: true });
}

function logout() {
    router.post('/organizer/logout');
}
</script>

<template>
    <Head title="Panel Pary Młodej" />
    <div class="min-h-dvh bg-rose-950/95 text-rose-50">
        <header class="sticky top-0 z-10 border-b border-white/10 bg-rose-950/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div>
                    <h1 class="text-lg font-semibold">Panel Pary Młodej</h1>
                    <p class="text-xs text-rose-200/70">
                        {{ stats.total_photos }} zdjęć · {{ stats.total_guests }} gości · {{ stats.total_messages }} wiadomości
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="/organizer/download"
                        class="rounded-full bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600"
                    >
                        ⬇ Pobierz ZIP
                    </a>
                    <button
                        type="button"
                        class="rounded-full border border-white/20 px-3 py-2 text-xs text-rose-100 hover:bg-white/10"
                        @click="logout"
                    >
                        wyloguj
                    </button>
                </div>
            </div>

            <div class="mx-auto flex max-w-6xl gap-2 px-4 pb-2">
                <button
                    type="button"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-widest transition"
                    :class="tab === 'photos' ? 'bg-rose-500 text-white' : 'text-rose-200 hover:bg-white/10'"
                    @click="tab = 'photos'"
                >
                    📸 Zdjęcia ({{ stats.total_photos }})
                </button>
                <button
                    type="button"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-widest transition"
                    :class="tab === 'messages' ? 'bg-rose-500 text-white' : 'text-rose-200 hover:bg-white/10'"
                    @click="tab = 'messages'"
                >
                    💌 Wiadomości ({{ stats.total_messages }})
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6">
            <template v-if="tab === 'photos'">
                <div v-if="photos.length === 0" class="rounded-2xl border border-dashed border-white/10 p-12 text-center text-rose-200/60">
                    Brak zdjęć jeszcze.
                </div>

                <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6">
                    <button
                        v-for="p in photos"
                        :key="p.id"
                        type="button"
                        class="group relative aspect-square overflow-hidden rounded-lg bg-black/40"
                        @click="lightbox = p"
                    >
                        <img
                            :src="p.thumb"
                            :alt="p.guest ?? ''"
                            loading="lazy"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-1.5 text-left text-[10px]">
                            <p class="truncate">{{ p.guest }}</p>
                            <p v-if="p.task" class="truncate text-rose-200/80">{{ p.task.icon }} {{ p.task.title }}</p>
                        </div>
                    </button>
                </div>
            </template>

            <template v-else>
                <div v-if="messages.length === 0" class="rounded-2xl border border-dashed border-white/10 p-12 text-center text-rose-200/60">
                    Brak wiadomości. Goście jeszcze się nie wyzwolili.
                </div>

                <div v-else class="grid gap-3 sm:grid-cols-2">
                    <article
                        v-for="m in messages"
                        :key="m.id"
                        class="rounded-2xl border border-white/10 bg-white/5 p-4"
                    >
                        <img
                            v-if="m.photo_url"
                            :src="m.photo_url"
                            :alt="m.guest ?? ''"
                            class="mb-3 max-h-64 w-full rounded-xl object-cover"
                        />
                        <blockquote class="font-serif text-lg leading-snug text-white">
                            „{{ m.body }}"
                        </blockquote>
                        <div class="mt-3 flex items-center justify-between text-xs text-rose-200/70">
                            <span>— {{ m.guest ?? 'gość' }}</span>
                            <button
                                type="button"
                                class="rounded-full border border-red-400/50 px-2 py-0.5 text-red-300 hover:bg-red-500/20"
                                @click="removeMessage(m)"
                            >
                                🗑 usuń
                            </button>
                        </div>
                    </article>
                </div>
            </template>
        </main>

        <div
            v-if="lightbox"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/95 p-4"
            @click="lightbox = null"
        >
            <img
                :src="lightbox.url"
                :alt="lightbox.guest ?? ''"
                class="max-h-[75vh] max-w-full rounded-lg"
                @click.stop
            />
            <div class="mt-4 flex items-center gap-3" @click.stop>
                <div class="text-center text-sm text-white">
                    <p v-if="lightbox.task">{{ lightbox.task.icon }} {{ lightbox.task.title }}</p>
                    <p class="text-rose-200/80">od {{ lightbox.guest ?? 'gościa' }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-full bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600"
                    @click="removePhoto(lightbox)"
                >
                    🗑 Usuń
                </button>
                <button
                    type="button"
                    class="rounded-full border border-white/30 px-4 py-2 text-sm text-white hover:bg-white/10"
                    @click="lightbox = null"
                >
                    zamknij
                </button>
            </div>
        </div>
    </div>
</template>
