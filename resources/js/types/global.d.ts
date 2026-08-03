import type { Auth } from '@/types/auth';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

export interface Guest {
    id: number;
    nickname: string;
    xp: number;
    level: number;
}

export interface Wedding {
    couple: string;
    date: string;
    venue: string;
}

export interface Flash {
    type: 'success' | 'info' | 'error';
    message: string;
}

export interface Task {
    id: number;
    title: string;
    description: string | null;
    icon: string;
    xp_reward: number;
    completed: boolean;
}

export interface Photo {
    id: number;
    url: string;
    thumb: string;
    guest: string | null;
    task: { title: string; icon: string } | null;
    bingo: { title: string; icon: string } | null;
    created_at: string;
}

export interface RankingRow {
    rank: number;
    id: number;
    nickname: string;
    xp: number;
    level: number;
    photos: number;
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            guest: Guest | null;
            wedding: Wedding;
            flash: Flash | null;
            [key: string]: unknown;
        };
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
