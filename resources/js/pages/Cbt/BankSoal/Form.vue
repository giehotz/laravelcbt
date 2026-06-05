<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store, update, index } from '@/routes/cbt/bank-soal';
import { Save, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    bankSoal: any;
    jenis: Array<any>;
    mapel: Array<any>;
    jurusan: Array<any>;
    kelas: Array<any>;
    levelKelas: Array<any>;
    isGuru: boolean;
    guruAssignments: Array<any>;
}>();

const filteredKelas = computed(() => {
    let availableKelas = props.kelas;

    // Filter by level if selected
    if (form.level) {
        const selectedLevel = props.levelKelas.find(l => l.level === form.level);
        if (selectedLevel) {
            availableKelas = availableKelas.filter(k => k.level_id === selectedLevel.id);
        }
    }

    // Filter by assignment if the user is a guru and mapel is selected
    if (props.isGuru && form.mapel_id) {
        const allowedKelasIds = props.guruAssignments
            .filter(assignment => assignment.mapel_id === form.mapel_id)
            .map(assignment => assignment.kelas_id);
            
        availableKelas = availableKelas.filter(k => allowedKelasIds.includes(k.id));
    }

    return availableKelas;
});

const isEdit = !!props.bankSoal;

const form = useForm({
    kode: props.bankSoal?.kode || '',
    nama: props.bankSoal?.nama || '',
    jenis_id: props.bankSoal?.jenis_id || '',
    level: props.bankSoal?.level || '',
    kelas: props.bankSoal?.kelas || [],
    mapel_id: props.bankSoal?.mapel_id || '',
    jurusan_id: props.bankSoal?.jurusan_id || '',
    kkm: props.bankSoal?.kkm || 0,
    status: props.bankSoal?.status || 0,
    opsi: props.bankSoal?.opsi || 5,

    tampil_pg: props.bankSoal?.tampil_pg || 0,
    bobot_pg: props.bankSoal?.bobot_pg || 0,
    tampil_esai: props.bankSoal?.tampil_esai || 0,
    bobot_esai: props.bankSoal?.bobot_esai || 0,
    tampil_kompleks: props.bankSoal?.tampil_kompleks || 0,
    bobot_kompleks: props.bankSoal?.bobot_kompleks || 0,
    skoring_kompleks: props.bankSoal?.skoring_kompleks || 'all_or_nothing',
    tampil_jodohkan: props.bankSoal?.tampil_jodohkan || 0,
    bobot_jodohkan: props.bankSoal?.bobot_jodohkan || 0,
    tampil_isian: props.bankSoal?.tampil_isian || 0,
    bobot_isian: props.bankSoal?.bobot_isian || 0,
});

const submit = () => {
    if (isEdit) {
        form.put(update.url({ bank_soal: props.bankSoal.id }));
    } else {
        form.post(store.url());
    }
};

const totalBobot = () => {
    return Number(form.bobot_pg) + Number(form.bobot_esai) + 
           Number(form.bobot_kompleks) + Number(form.bobot_jodohkan) + 
           Number(form.bobot_isian);
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Bank Soal' : 'Tambah Bank Soal'" />

    <div class="px-6 py-6 max-w-5xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" @click="$inertia.visit(index.url())" class="rounded-full w-10 h-10 border-neutral-200 dark:border-zinc-800">
                <ArrowLeft class="w-5 h-5 text-neutral-600 dark:text-neutral-400" />
            </Button>
            <Heading
                :title="isEdit ? 'Edit Bank Soal' : 'Tambah Bank Soal'"
                description="Konfigurasi detail dan bobot penilaian bank soal."
            />
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Informasi Umum -->
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
                <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-200 border-b border-neutral-200 dark:border-zinc-800 pb-3">Informasi Umum</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <Label>Kode Bank Soal</Label>
                        <Input v-model="form.kode" placeholder="Misal: MTK-X-GANJIL" />
                        <div v-if="form.errors.kode" class="text-xs text-rose-500">{{ form.errors.kode }}</div>
                    </div>
                    <div class="space-y-2">
                        <Label>Nama / Deskripsi Ujian</Label>
                        <Input v-model="form.nama" placeholder="Misal: Penilaian Akhir Semester Ganjil" />
                        <div v-if="form.errors.nama" class="text-xs text-rose-500">{{ form.errors.nama }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label>Jenis Ujian</Label>
                        <select v-model="form.jenis_id" class="w-full h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">Pilih Jenis Ujian</option>
                            <option v-for="j in jenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                        </select>
                        <div v-if="form.errors.jenis_id" class="text-xs text-rose-500">{{ form.errors.jenis_id }}</div>
                    </div>
                    
                    <div class="space-y-2">
                        <Label>Mata Pelajaran</Label>
                        <select v-model="form.mapel_id" class="w-full h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">Pilih Mapel</option>
                            <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                        </select>
                        <div v-if="form.errors.mapel_id" class="text-xs text-rose-500">{{ form.errors.mapel_id }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label>Level Kelas</Label>
                        <select v-model="form.level" class="w-full h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">Pilih Level</option>
                            <option v-for="l in levelKelas" :key="l.id" :value="l.level">Level {{ l.level }}</option>
                        </select>
                        <div v-if="form.errors.level" class="text-xs text-rose-500">{{ form.errors.level }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label>Jurusan (Opsional)</Label>
                        <select v-model="form.jurusan_id" class="w-full h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">Semua Jurusan</option>
                            <option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama_jurusan }}</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <Label>Kelas (Pilih satu atau lebih)</Label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 border border-neutral-200 dark:border-zinc-800 rounded-lg p-4 bg-neutral-50/50 dark:bg-zinc-950/50">
                            <label v-for="k in filteredKelas" :key="k.id" class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" :value="k.id" v-model="form.kelas" class="rounded text-amber-600 focus:ring-amber-500 border-neutral-300 dark:border-zinc-700 bg-white dark:bg-zinc-900" />
                                <span class="text-sm font-medium">{{ k.nama_kelas }}</span>
                            </label>
                        </div>
                        <div v-if="form.errors.kelas" class="text-xs text-rose-500">{{ form.errors.kelas }}</div>
                    </div>
                    
                    <div class="space-y-2">
                        <Label>Jumlah Opsi Pilihan Ganda</Label>
                        <select v-model="form.opsi" class="w-full h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option :value="3">3 (A, B, C)</option>
                            <option :value="4">4 (A, B, C, D)</option>
                            <option :value="5">5 (A, B, C, D, E)</option>
                        </select>
                        <div v-if="form.errors.opsi" class="text-xs text-rose-500">{{ form.errors.opsi }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label>KKM</Label>
                        <Input type="number" v-model="form.kkm" min="0" max="100" />
                    </div>
                </div>
            </div>

            <!-- Konfigurasi Bobot & Soal -->
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
                <div class="flex justify-between items-end border-b border-neutral-200 dark:border-zinc-800 pb-3">
                    <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-200">Konfigurasi Tampil & Bobot</h3>
                    <div class="text-sm font-semibold p-2 rounded-lg" :class="totalBobot() === 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400'">
                        Total Bobot: {{ totalBobot() }}%
                    </div>
                </div>

                <div v-if="form.errors.status" class="bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 p-3 rounded-lg text-sm mb-4">
                    {{ form.errors.status }}
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/50 dark:bg-zinc-950 border-b border-neutral-200 dark:border-zinc-800">
                                <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">Tipe Soal</th>
                                <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 w-32 text-center">Tersedia di Bank</th>
                                <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 w-32">Ditampilkan</th>
                                <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 w-32">Bobot (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800/50">
                            <!-- PG -->
                            <tr>
                                <td class="py-3 px-4 font-medium">Pilihan Ganda</td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">{{ isEdit ? bankSoal.jml_pg : 0 }}</td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.tampil_pg" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.tampil_pg" class="text-[10px] text-rose-500 mt-1">{{ form.errors.tampil_pg }}</div>
                                </td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.bobot_pg" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.bobot_pg" class="text-[10px] text-rose-500 mt-1">{{ form.errors.bobot_pg }}</div>
                                </td>
                            </tr>
                            <!-- Kompleks -->
                            <tr>
                                <td class="py-3 px-4 font-medium">
                                    Ganda Kompleks
                                    <div class="text-[10px] text-neutral-400 mt-1 font-semibold flex items-center gap-1">
                                        Skoring:
                                        <select v-model="form.skoring_kompleks" class="border rounded p-0.5 bg-white dark:bg-zinc-950 font-normal">
                                            <option value="all_or_nothing">All or Nothing</option>
                                            <option value="partial">Sebagian (Partial)</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">{{ isEdit ? bankSoal.jml_kompleks : 0 }}</td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.tampil_kompleks" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.tampil_kompleks" class="text-[10px] text-rose-500 mt-1">{{ form.errors.tampil_kompleks }}</div>
                                </td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.bobot_kompleks" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.bobot_kompleks" class="text-[10px] text-rose-500 mt-1">{{ form.errors.bobot_kompleks }}</div>
                                </td>
                            </tr>
                            <!-- Jodohkan -->
                            <tr>
                                <td class="py-3 px-4 font-medium">Menjodohkan</td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">{{ isEdit ? bankSoal.jml_jodohkan : 0 }}</td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.tampil_jodohkan" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.tampil_jodohkan" class="text-[10px] text-rose-500 mt-1">{{ form.errors.tampil_jodohkan }}</div>
                                </td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.bobot_jodohkan" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.bobot_jodohkan" class="text-[10px] text-rose-500 mt-1">{{ form.errors.bobot_jodohkan }}</div>
                                </td>
                            </tr>
                            <!-- Isian -->
                            <tr>
                                <td class="py-3 px-4 font-medium">Isian Singkat</td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">{{ isEdit ? bankSoal.jml_isian : 0 }}</td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.tampil_isian" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.tampil_isian" class="text-[10px] text-rose-500 mt-1">{{ form.errors.tampil_isian }}</div>
                                </td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.bobot_isian" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.bobot_isian" class="text-[10px] text-rose-500 mt-1">{{ form.errors.bobot_isian }}</div>
                                </td>
                            </tr>
                            <!-- Esai -->
                            <tr>
                                <td class="py-3 px-4 font-medium">Uraian / Esai</td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">{{ isEdit ? bankSoal.jml_esai : 0 }}</td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.tampil_esai" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.tampil_esai" class="text-[10px] text-rose-500 mt-1">{{ form.errors.tampil_esai }}</div>
                                </td>
                                <td class="py-3 px-4"><Input type="number" v-model="form.bobot_esai" class="h-8 text-center" min="0" />
                                    <div v-if="form.errors.bobot_esai" class="text-[10px] text-rose-500 mt-1">{{ form.errors.bobot_esai }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Publish Section -->
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Label class="font-bold">Status Bank Soal:</Label>
                    <select v-model="form.status" class="h-10 px-3 py-2 rounded-md border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option :value="0">0 - Draft (Belum Selesai)</option>
                        <option :value="1">1 - Selesai & Aktif</option>
                    </select>
                </div>
                
                <Button type="submit" :disabled="form.processing" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold flex items-center gap-2">
                    <Save class="w-4 h-4" />
                    Simpan Bank Soal
                </Button>
            </div>
        </form>
    </div>
</template>
