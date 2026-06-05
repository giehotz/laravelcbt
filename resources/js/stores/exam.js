import { defineStore } from 'pinia';
import { simpan } from '@/routes/ujian';

export const useExamStore = defineStore('exam', {
    state: () => ({
        soalList: [],
        jadwal: null,
        durasi: null,
        activeSoalId: null,
        offlineQueue: [],
        isOnline: navigator.onLine,
        isFlushing: false,
    }),
    
    getters: {
        activeSoal: (state) => {
            if (!state.activeSoalId) return state.soalList[0];
            return state.soalList.find(s => s.id === state.activeSoalId) || state.soalList[0];
        },
        unansweredCount: (state) => {
            return state.soalList.filter(s => !s.jawaban_siswa).length;
        },
        hasOfflineQueue: (state) => {
            return state.offlineQueue.length > 0;
        }
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

        setSoalList(list) {
            this.soalList = list;
            if (this.soalList.length > 0 && !this.activeSoalId) {
                this.activeSoalId = this.soalList[0].id;
            }
        },

        setActiveSoal(id) {
            this.activeSoalId = id;
        },

        setJadwal(jadwal) {
            this.jadwal = jadwal;
        },

        async simpanJawaban(soalId, jawaban, raguRagu = false) {
            // Update local state immediately (Optimistic UI)
            const soalIndex = this.soalList.findIndex(s => s.id === soalId);
            if (soalIndex !== -1) {
                this.soalList[soalIndex].jawaban_siswa = jawaban;
                this.soalList[soalIndex].ragu_ragu = raguRagu;
            }

            const payload = {
                soalId,
                data: {
                    jawaban: jawaban,
                    ragu_ragu: raguRagu
                }
            };

            if (this.isOnline) {
                await this.sendToServer(payload);
            } else {
                this.addToQueue(payload);
            }
        },

        addToQueue(payload) {
            // Replace if already in queue for the same soal
            const existingIndex = this.offlineQueue.findIndex(q => q.soalId === payload.soalId);
            if (existingIndex !== -1) {
                this.offlineQueue[existingIndex] = payload;
            } else {
                this.offlineQueue.push(payload);
            }
        },

        async sendToServer(payload) {
            try {
                // Gunakan wayfinder endpoint `ujian.simpan`
                const url = simpan({ soalSiswa: payload.soalId });
                const response = await fetch(url.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload.data)
                });

                if (!response.ok) {
                    if (response.status === 401 || response.status === 403 || response.status === 419) {
                        // Jangan push ke offline queue jika session expired
                        console.error('Session expired or forbidden', response);
                    } else {
                        throw new Error('Server error');
                    }
                }
            } catch (error) {
                console.error('Failed to save to server, moving to queue', error);
                this.addToQueue(payload);
            }
        },

        async flushOfflineQueue() {
            if (this.isFlushing || this.offlineQueue.length === 0 || !this.isOnline) return;
            this.isFlushing = true;

            const queueToProcess = [...this.offlineQueue];
            this.offlineQueue = []; // Clear queue optimistically

            for (const item of queueToProcess) {
                await this.sendToServer(item);
            }

            this.isFlushing = false;
        }
    }
});
