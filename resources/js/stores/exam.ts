import { defineStore } from 'pinia';
import { simpan } from '@/routes/ujian';

export interface SoalSiswa {
    id: string;
    jenis_soal: number;
    no_soal_alias: number;
    opsi_alias_a?: string | null;
    opsi_alias_b?: string | null;
    opsi_alias_c?: string | null;
    opsi_alias_d?: string | null;
    opsi_alias_e?: string | null;
    jawaban_siswa?: string | null;
    ragu_ragu: boolean;
    soal_end: boolean;
    opsi?: number;
    soal?: {
        soal?: string;
        opsi_a?: string;
        opsi_b?: string;
        opsi_c?: string;
        opsi_d?: string;
        opsi_e?: string;
        file1?: string;
        fileA?: string;
        fileB?: string;
        fileC?: string;
        fileD?: string;
        fileE?: string;
        matching_left?: Array<{ id: number; text: string }>;
        matching_right?: Array<{ id: number; text: string }>;
    } | null;
}

export interface Jadwal {
    id: number;
    bank_soal?: {
        nama: string;
        mapel?: {
            nama_mapel: string;
        };
    };
}

export interface OfflinePayload {
    soalId: string;
    data: {
        jawaban: string | null;
        ragu_ragu: boolean;
    };
}

export const useExamStore = defineStore('exam', {
    state: () => ({
        soalList: [] as SoalSiswa[],
        jadwal: null as Jadwal | null,
        durasi: null as number | null,
        activeSoalId: null as string | null,
        offlineQueue: [] as OfflinePayload[],
        isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
        isFlushing: false,
    }),

    getters: {
        activeSoal: (state): SoalSiswa | null => {
            if (state.soalList.length === 0) return null;
            if (!state.activeSoalId) return state.soalList[0];
            return (
                state.soalList.find((s) => s.id === state.activeSoalId) ||
                state.soalList[0]
            );
        },
        unansweredCount: (state): number => {
            return state.soalList.filter((s) => !s.jawaban_siswa).length;
        },
        hasOfflineQueue: (state): boolean => {
            return state.offlineQueue.length > 0;
        },
    },

    actions: {
        initNetworkListeners() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.flushOfflineQueue();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });
        },

        setSoalList(list: SoalSiswa[]) {
            this.soalList = list;
            if (this.soalList.length > 0 && !this.activeSoalId) {
                this.activeSoalId = this.soalList[0].id;
            }
        },

        setActiveSoal(id: string) {
            this.activeSoalId = id;
        },

        setJadwal(jadwal: Jadwal) {
            this.jadwal = jadwal;
        },

        async simpanJawaban(soalId: string, jawaban: string | null, raguRagu: boolean = false) {
            // Update local state immediately (Optimistic UI)
            const soalIndex = this.soalList.findIndex((s) => s.id === soalId);
            if (soalIndex !== -1) {
                this.soalList[soalIndex].jawaban_siswa = jawaban;
                this.soalList[soalIndex].ragu_ragu = raguRagu;
            }

            const payload: OfflinePayload = {
                soalId,
                data: {
                    jawaban: jawaban,
                    ragu_ragu: raguRagu,
                },
            };

            if (this.isOnline) {
                await this.sendToServer(payload);
            } else {
                this.addToQueue(payload);
            }
        },

        addToQueue(payload: OfflinePayload) {
            // Replace if already in queue for the same soal
            const existingIndex = this.offlineQueue.findIndex(
                (q) => q.soalId === payload.soalId,
            );
            if (existingIndex !== -1) {
                this.offlineQueue[existingIndex] = payload;
            } else {
                this.offlineQueue.push(payload);
            }
        },

        async sendToServer(payload: OfflinePayload) {
            try {
                // Gunakan wayfinder endpoint `ujian.simpan`
                const url = simpan({ soalSiswa: payload.soalId });
                const tokenElement = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = tokenElement ? tokenElement.getAttribute('content') : '';
                const response = await fetch(url.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(payload.data),
                });

                if (!response.ok) {
                    if (
                        response.status === 401 ||
                        response.status === 403 ||
                        response.status === 419
                    ) {
                        // Jangan push ke offline queue jika session expired
                        console.error('Session expired or forbidden', response);
                    } else {
                        throw new Error('Server error');
                    }
                }
            } catch (error) {
                console.error(
                    'Failed to save to server, moving to queue',
                    error,
                );
                this.addToQueue(payload);
            }
        },

        async flushOfflineQueue() {
            if (
                this.isFlushing ||
                this.offlineQueue.length === 0 ||
                !this.isOnline
            )
                return;
            this.isFlushing = true;

            const queueToProcess = [...this.offlineQueue];
            this.offlineQueue = []; // Clear queue optimistically

            for (const item of queueToProcess) {
                await this.sendToServer(item);
            }

            this.isFlushing = false;
        },
    },
});
