import { defineStore } from 'pinia';

export const useNotifikasiStore = defineStore('notifikasi', {
    state: () => ({
        items: [] as any[],
    }),
    getters: {
        total: (state) => state.items.length,
    },
    actions: {
        set(items: any[]) {
            this.items = items;
        }
    }
});
