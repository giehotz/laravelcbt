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
    kkm: [],
});

// Initialize KKM table state
const initKkmGrid = () => {
    let grid = [];
    props.mapel.forEach((m) => {
        props.kelas.forEach((k) => {
            // Find existing
            const existing = props.kkm.find(
                (x) => x.mapel_id === m.id && x.kelas_id === k.id,
            );
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
        if (
            Number(k.bobot_ph) + Number(k.bobot_pts) + Number(k.bobot_pas) !==
            100
        ) {
            alert(
                'Total bobot (PH + PTS + PAS) harus 100 untuk semua kelas & mapel.',
            );
            isValid = false;
            break;
        }
    }

    if (isValid) {
        form.post(route('rapor.setting.update'), {
            preserveScroll: true,
        });
    }
};

const getKkmField = (mapelId, kelasId) => {
    return form.kkm.find(
        (x) => x.mapel_id === mapelId && x.kelas_id === kelasId,
    );
};
</script>

<template>
    <AppLayout>
        <Head title="Setting Rapor" />

        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-2xl leading-tight font-bold text-gray-900">
                Setting Rapor (KKM & Mapping CBT)
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- CBT Mapping -->
                <div
                    class="border-t-4 border-indigo-500 bg-white p-6 shadow sm:rounded-lg"
                >
                    <h3
                        class="mb-4 text-lg leading-6 font-medium text-gray-900"
                    >
                        Mapping Jenis CBT ke Rapor
                    </h3>
                    <p class="mb-4 text-sm text-gray-500">
                        Tentukan jenis ujian CBT yang nilai rata-ratanya akan
                        diambil otomatis saat guru menekan tombol "Import dari
                        CBT".
                    </p>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Jenis Ujian untuk PH</label
                            >
                            <select
                                v-model="form.jenis_ph_id"
                                class="mt-1 block w-full rounded-md border-gray-300 py-2 pr-10 pl-3 text-base focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            >
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option
                                    v-for="j in cbtJenis"
                                    :key="j.id"
                                    :value="j.id"
                                >
                                    {{ j.nama_jenis }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Jenis Ujian untuk PTS</label
                            >
                            <select
                                v-model="form.jenis_pts_id"
                                class="mt-1 block w-full rounded-md border-gray-300 py-2 pr-10 pl-3 text-base focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            >
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option
                                    v-for="j in cbtJenis"
                                    :key="j.id"
                                    :value="j.id"
                                >
                                    {{ j.nama_jenis }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Jenis Ujian untuk PAS</label
                            >
                            <select
                                v-model="form.jenis_pas_id"
                                class="mt-1 block w-full rounded-md border-gray-300 py-2 pr-10 pl-3 text-base focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            >
                                <option value="">-- Pilih Jenis CBT --</option>
                                <option
                                    v-for="j in cbtJenis"
                                    :key="j.id"
                                    :value="j.id"
                                >
                                    {{ j.nama_jenis }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- KKM dan Bobot -->
                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <div
                        class="border-b border-gray-200 bg-gray-50 px-4 py-5 sm:px-6"
                    >
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            KKM & Bobot Nilai per Mapel
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Set KKM dan bobot persentase komponen nilai akhir
                            untuk tiap mapel dan kelas.
                            <strong>Pastikan total Bobot = 100</strong>.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        scope="col"
                                        class="sticky left-0 bg-gray-100 px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Mata Pelajaran
                                    </th>
                                    <th
                                        v-for="k in kelas"
                                        :key="k.id"
                                        scope="col"
                                        class="min-w-[250px] border-l border-gray-200 px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Kelas {{ k.nama }}
                                        <div
                                            class="mt-1 text-[10px] font-normal text-gray-400"
                                        >
                                            KKM | Bbt PH | Bbt PTS | Bbt PAS
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="m in mapel"
                                    :key="m.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="sticky left-0 bg-white px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900"
                                    >
                                        {{ m.nama_mapel }}
                                    </td>
                                    <td
                                        v-for="k in kelas"
                                        :key="k.id"
                                        class="border-l border-gray-200 px-4 py-2 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <div
                                            class="flex items-center justify-center space-x-1"
                                            v-if="getKkmField(m.id, k.id)"
                                        >
                                            <input
                                                v-model="
                                                    getKkmField(m.id, k.id).kkm
                                                "
                                                type="number"
                                                min="0"
                                                max="100"
                                                class="w-14 rounded border-gray-300 p-1 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                title="KKM"
                                            />
                                            <span class="text-gray-300">|</span>
                                            <input
                                                v-model="
                                                    getKkmField(m.id, k.id)
                                                        .bobot_ph
                                                "
                                                type="number"
                                                min="0"
                                                max="100"
                                                class="w-14 rounded border-gray-300 p-1 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                title="Bobot PH"
                                            />
                                            <input
                                                v-model="
                                                    getKkmField(m.id, k.id)
                                                        .bobot_pts
                                                "
                                                type="number"
                                                min="0"
                                                max="100"
                                                class="w-14 rounded border-gray-300 p-1 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                title="Bobot PTS"
                                            />
                                            <input
                                                v-model="
                                                    getKkmField(m.id, k.id)
                                                        .bobot_pas
                                                "
                                                type="number"
                                                min="0"
                                                max="100"
                                                class="w-14 rounded border-gray-300 p-1 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                title="Bobot PAS"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        Simpan Konfigurasi Rapor
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
