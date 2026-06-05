<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    config: Object,
    kkm: Array,
    cbtJenis: Array,
    kelas: Array,
    mapel: Array,
});

const form = useForm({
    jenis_ph_id: props.config?.jenis_ph_id || '',
    jenis_pts_id: props.config?.jenis_pts_id || '',
    jenis_pas_id: props.config?.jenis_pas_id || '',
    kkm: []
});

// Initialize KKM table state
const initKkmGrid = () => {
    let grid = [];
    props.mapel.forEach(m => {
        props.kelas.forEach(k => {
            // Find existing
            const existing = props.kkm.find(x => x.mapel_id === m.id && x.kelas_id === k.id);
            grid.push({
                mapel_id: m.id,
                kelas_id: k.id,
                kkm: existing?.kkm || 75,
                bobot_ph: existing?.bobot_ph || 40,
                bobot_pts: existing?.bobot_pts || 30,
                bobot_pas: existing?.bobot_pas || 30,
            });
        });
    });
    form.kkm = grid;
};

initKkmGrid();

const submit = () => {
    // Validate bobot
    let isValid = true;
    for (let k of form.kkm) {
        if (Number(k.bobot_ph) + Number(k.bobot_pts) + Number(k.bobot_pas) !== 100) {
            alert('Total bobot (PH + PTS + PAS) harus 100 untuk semua kelas & mapel.');
            isValid = false;
            break;
        }
    }

    if (isValid) {
        form.post(route('rapor.setting.update'), {
            preserveScroll: true
        });
    }
};

const getKkmField = (mapelId, kelasId) => {
    return form.kkm.find(x => x.mapel_id === mapelId && x.kelas_id === kelasId);
};
</script>

<template>
    <AppLayout>
        <Head title="Setting Rapor" />

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold leading-tight text-gray-900 mb-6">
                Setting Rapor (KKM & Mapping CBT)
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- CBT Mapping -->
                <div class="bg-white shadow sm:rounded-lg p-6 border-t-4 border-indigo-500">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Mapping Jenis CBT ke Rapor</h3>
                    <p class="text-sm text-gray-500 mb-4">Tentukan jenis ujian CBT yang nilai rata-ratanya akan diambil otomatis saat guru menekan tombol "Import dari CBT".</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Ujian untuk PH</label>
                            <select v-model="form.jenis_ph_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option v-for="j in cbtJenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Ujian untuk PTS</label>
                            <select v-model="form.jenis_pts_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option v-for="j in cbtJenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Ujian untuk PAS</label>
                            <select v-model="form.jenis_pas_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option v-for="j in cbtJenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- KKM dan Bobot -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">KKM & Bobot Nilai per Mapel</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Set KKM dan bobot persentase komponen nilai akhir untuk tiap mapel dan kelas. <strong>Pastikan total Bobot = 100</strong>.
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-100">
                                        Mata Pelajaran
                                    </th>
                                    <th v-for="k in kelas" :key="k.id" scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-200 min-w-[250px]">
                                        Kelas {{ k.nama }}
                                        <div class="text-[10px] mt-1 text-gray-400 font-normal">KKM | Bbt PH | Bbt PTS | Bbt PAS</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="m in mapel" :key="m.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white">
                                        {{ m.nama_mapel }}
                                    </td>
                                    <td v-for="k in kelas" :key="k.id" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 border-l border-gray-200">
                                        <div class="flex items-center justify-center space-x-1" v-if="getKkmField(m.id, k.id)">
                                            <input v-model="getKkmField(m.id, k.id).kkm" type="number" min="0" max="100" class="w-14 p-1 text-center text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" title="KKM">
                                            <span class="text-gray-300">|</span>
                                            <input v-model="getKkmField(m.id, k.id).bobot_ph" type="number" min="0" max="100" class="w-14 p-1 text-center text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" title="Bobot PH">
                                            <input v-model="getKkmField(m.id, k.id).bobot_pts" type="number" min="0" max="100" class="w-14 p-1 text-center text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" title="Bobot PTS">
                                            <input v-model="getKkmField(m.id, k.id).bobot_pas" type="number" min="0" max="100" class="w-14 p-1 text-center text-sm border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" title="Bobot PAS">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Simpan Konfigurasi Rapor
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
