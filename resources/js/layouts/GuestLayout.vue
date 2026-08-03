<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const guest = computed(() => page.props.guest);
const wedding = computed(() => page.props.wedding);
const flash = computed(() => page.props.flash);

const currentPath = computed(() => (typeof window !== 'undefined' ? window.location.pathname : '/'));

type NavItem = { href: string; label: string; icon: string };

const primary: NavItem[] = [
    { href: '/tasks', label: 'Misje', icon: 'target' },
    { href: '/bingo', label: 'Bingo', icon: 'grid' },
    { href: '/gallery', label: 'Galeria', icon: 'image' },
    { href: '/ranking', label: 'Ranking', icon: 'trophy' },
];

const secondary: NavItem[] = [
    { href: '/zdrapka', label: 'Zdrapka', icon: 'ticket' },
    { href: '/cytaty', label: 'Cytaty', icon: 'quote' },
    { href: '/wiadomosc', label: 'List do Młodych', icon: 'mail' },
];

const menuOpen = ref(false);

const secondaryActive = computed(() =>
    secondary.some((item) => currentPath.value.startsWith(item.href)),
);

function isActive(href: string) {
    return currentPath.value.startsWith(href);
}

function closeMenu() {
    menuOpen.value = false;
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-dvh flex flex-col bg-gradient-to-b from-rose-50 via-white to-rose-50 text-rose-950">
        <header class="sticky top-0 z-30 border-b border-rose-100/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3">
                <Link href="/tasks" class="flex items-center gap-2">
                    <span class="text-xl">❤️</span>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold">{{ wedding.couple }}</div>
                        <div class="text-[10px] uppercase tracking-widest text-rose-400">Wesele</div>
                    </div>
                </Link>
                <div v-if="guest" class="flex items-center gap-3 text-sm">
                    <div class="text-right leading-tight">
                        <div class="font-medium">{{ guest.nickname }}</div>
                        <div class="text-xs text-rose-500">LVL {{ guest.level }} · {{ guest.xp }} XP</div>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border border-rose-200 px-2 py-1 text-xs text-rose-500 hover:bg-rose-100"
                        @click="logout"
                    >
                        wyjdź
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-3xl flex-1 px-4 pb-32 pt-4">
            <transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flash"
                    :key="flash.message"
                    class="mb-4 rounded-xl px-4 py-3 text-sm shadow-sm"
                    :class="{
                        'bg-emerald-50 text-emerald-800 border border-emerald-200': flash.type === 'success',
                        'bg-amber-50 text-amber-900 border border-amber-200': flash.type === 'info',
                        'bg-red-50 text-red-800 border border-red-200': flash.type === 'error',
                    }"
                >
                    {{ flash.message }}
                </div>
            </transition>

            <slot />
        </main>

        <template v-if="guest">
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="menuOpen"
                    class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                    @click="closeMenu"
                />
            </transition>

            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition duration-200"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div
                    v-if="menuOpen"
                    class="fixed inset-x-0 bottom-0 z-40 rounded-t-3xl bg-white pb-[calc(env(safe-area-inset-bottom)+1rem)] shadow-2xl"
                >
                    <div class="mx-auto flex max-w-3xl flex-col gap-1 p-4">
                        <div class="mx-auto mb-2 h-1 w-10 rounded-full bg-rose-200"></div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-rose-400">
                            Więcej zabaw
                        </p>

                        <Link
                            v-for="item in secondary"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-3 py-3 transition"
                            :class="isActive(item.href) ? 'bg-rose-500 text-white' : 'text-rose-700 hover:bg-rose-50'"
                            @click="closeMenu"
                        >
                            <span
                                class="grid h-10 w-10 place-items-center rounded-xl"
                                :class="isActive(item.href) ? 'bg-white/20' : 'bg-rose-100 text-rose-500'"
                            >
                                <svg
                                    v-if="item.icon === 'ticket'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 8v8m6-8v8M5 5h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2Z" />
                                </svg>
                                <svg
                                    v-else-if="item.icon === 'quote'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5c-1.65 0-3 1.35-3 3v3.5c0 1.65 1.35 3 3 3H9v3.5c0 .55-.45 1-1 1H7m9.5-14c-1.65 0-3 1.35-3 3v3.5c0 1.65 1.35 3 3 3H18v3.5c0 .55-.45 1-1 1h-1" />
                                </svg>
                                <svg
                                    v-else-if="item.icon === 'mail'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <div class="flex-1">
                                <p class="font-medium">{{ item.label }}</p>
                            </div>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-4 w-4 opacity-60"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </transition>

            <nav
                class="fixed inset-x-0 bottom-0 z-30 border-t border-rose-100 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-6px_20px_-8px_rgba(244,63,94,0.15)] backdrop-blur"
            >
                <div class="mx-auto grid max-w-3xl grid-cols-5 items-end">
                    <Link
                        v-for="item in primary"
                        :key="item.href"
                        :href="item.href"
                        class="group relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition"
                        :class="isActive(item.href) ? 'text-rose-600' : 'text-rose-400 hover:text-rose-600'"
                    >
                        <span
                            v-if="isActive(item.href)"
                            class="absolute -top-0.5 h-1 w-8 rounded-full bg-rose-500"
                        />
                        <svg
                            v-if="item.icon === 'target'"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.75"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.63 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'grid'"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.75"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'image'"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.75"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <svg
                            v-else-if="item.icon === 'trophy'"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.75"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                        <span>{{ item.label }}</span>
                    </Link>

                    <button
                        type="button"
                        class="group relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition"
                        :class="secondaryActive || menuOpen ? 'text-rose-600' : 'text-rose-400 hover:text-rose-600'"
                        aria-label="Więcej zabaw"
                        @click="menuOpen = !menuOpen"
                    >
                        <span
                            v-if="secondaryActive"
                            class="absolute -top-0.5 h-1 w-8 rounded-full bg-rose-500"
                        />
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.75"
                            stroke="currentColor"
                            class="h-6 w-6 transition"
                            :class="{ 'rotate-45': menuOpen }"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Więcej</span>
                    </button>
                </div>
            </nav>
        </template>
    </div>
</template>
