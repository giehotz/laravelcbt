<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    kelas: Array,
    mapel: Array,
    selectedKelasId: String,
    selectedMapelId: String,
    siswaList: Array,
    kkm: Object,
});

const filterForm = useForm({
    kelas_id: props.selectedKelasId || '',
    mapel_id: props.selectedMapelId || '',
});

const applyFilter = () => {
    filterForm.get(route('rapor.input_nilai'));
};

const importForm = useForm({
    kelas_id: props.selectedKelasId,
    mapel_id: props.selectedMapelId,
    komponen: '',
});

const importCbt = (komp) => {
    if (
        !confirm(
            `Apakah Anda yakin ingin import nilai ${komp.toUpperCase()} dari CBT untuk seluruh siswa di kelas ini? Nilai yang belum dikunci akan tertimpa.`,
        )
    ) {
        return;
    }
    importForm.komponen = komp;
    importForm.post(route('rapor.import_cbt'), {
        preserveScroll: true,
    });
};

// State for manual input
const nilaiData = ref(
    props.siswaList.map((item) => ({
        siswa_id: item.siswa.id,
        nama: item.siswa.nama,
        nilai_ph: item.nilai.nilai_ph,
        nilai_pts: item.nilai.nilai_pts,
        nilai_pas: item.nilai.nilai_pas,
        sumber_ph: item.nilai.sumber_ph,
        sumber_pts: item.nilai.sumber_pts,
        sumber_pas: item.nilai.sumber_pas,
        final: item.nilai.final,
    })),
);

// Computed logic for Nilai Akhir and Predikat on the fly
const calculateAkhir = (n) => {
    if (!props.kkm) return 0;
    const akhir =
        (Number(n.nilai_ph) * props.kkm.bobot_ph) / 100 +
        (Number(n.nilai_pts) * props.kkm.bobot_pts) / 100 +
        (Number(n.nilai_pas) * props.kkm.bobot_pas) / 100;
    return Number(akhir).toFixed(2);
};

const getPredikat = (akhirVal) => {
    if (!props.kkm) return '-';
    const val = Number(akhirVal);
    const kkmVal = Number(props.kkm.kkm);
    if (val >= 90) return 'A';
    if (val >= kkmVal + 10) return 'B';
    if (val >= kkmVal) return 'C';
    return 'D';
};

const saveForm = useForm({
    kelas_id: props.selectedKelasId,
    mapel_id: props.selectedMapelId,
    nilai: [],
});

const saveNilai = () => {
    saveForm.nilai = nilaiData.value;
    saveForm.post(route('rapor.simpan_nilai'), {
        preserveScroll: true,
    });
};

const isAllFinal = computed(() => {
    if (!nilaiData.value.length) return false;
    return nilaiData.value.every((n) => n.final);
});

const lockNilai = () => {
    if (
        confirm(
            'PERINGATAN: Mengunci nilai akan membuat data ini menjadi permanen dan tidak dapat diedit lagi oleh siapapun. Lanjutkan?',
        )
    ) {
        router.post(
            route('rapor.kunci', {
                kelas: props.selectedKelasId,
                mapel: props.selectedMapelId,
            }),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Input Nilai Rapor" />

        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-2xl leading-tight font-bold text-gray-900">
                Input Nilai Rapor
            </h2>

            <!-- Filter Card -->
            <div class="mb-6 bg-white p-6 shadow sm:rounded-lg">
                <form
                    @submit.prevent="applyFilter"
                    class="flex flex-col items-end gap-4 md:flex-row"
                >
                    <div class="w-full md:w-1/3">
                        <label class="block text-sm font-medium text-gray-700"
                            >Kelas</label
                        >
                        <select
                            v-model="filterForm.kelas_id"
                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pr-10 pl-3 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            required
                        >
                            <option value="">-- Pilih Kelas --</option>
                            <option
                                v-for="k in kelas"
                                :key="k.id"
                                :value="k.id"
                            >
                                {{ k.nama }}
                            </option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/3">
                        <label class="block text-sm font-medium text-gray-700"
                            >Mata Pelajaran</label
                        >
                        <select
                            v-model="filterForm.mapel_id"
                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pr-10 pl-3 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm"
                            required
                        >
                            <option value="">-- Pilih Mapel --</option>
                            <option
                                v-for="m in mapel"
                                :key="m.id"
                                :value="m.id"
                            >
                                {{ m.nama_mapel }}
                            </option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button
                            type="submit"
                            class="w-full rounded bg-indigo-600 px-6 py-2 font-bold text-white shadow hover:bg-indigo-700"
                        >
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="selectedKelasId && selectedMapelId">
                <div
                    v-if="!kkm"
                    class="mb-6 border-l-4 border-yellow-400 bg-yellow-50 p-4"
                >
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                KKM dan bobot nilai belum diatur untuk kelas dan
                                mapel ini. Silakan atur di menu
                                <Link
                                    :href="route('rapor.setting')"
                                    class="font-bold underline"
                                    >Setting Rapor</Link
                                >.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="overflow-hidden bg-white shadow sm:rounded-lg"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-5 sm:px-6"
                    >
                        <div>
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900"
                            >
                                Data Nilai Siswa
                            </h3>
                            <p class="text-sm text-gray-500">
                                Bobot: PH ({{ kkm.bobot_ph }}%) | PTS ({{
                                    kkm.bobot_pts
                                }}%) | PAS ({{ kkm.bobot_pas }}%) &middot; KKM:
                                {{ kkm.kkm }}
                            </p>
                        </div>
                        <div
                            class="flex space-x-2"
                            v-if="!isAllFinal && siswaList.length > 0"
                        >
                            <button
                                @click="importCbt('ph')"
                                class="rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                            >
                                Import PH CBT
                            </button>
                            <button
                                @click="importCbt('pts')"
                                class="rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                            >
                                Import PTS CBT
                            </button>
                            <button
                                @click="importCbt('pas')"
                                class="rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                            >
                                Import PAS CBT
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Nama Siswa
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        PH
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        PTS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        PAS
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-3 text-center text-xs font-bold tracking-wider text-indigo-600 uppercase"
                                    >
                                        Akhir
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Predikat
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="(n, index) in nilaiData"
                                    :key="n.siswa_id"
                                    :class="
                                        n.final
                                            ? 'bg-gray-50'
                                            : 'hover:bg-blue-50'
                                    "
                                >
                                    <td
                                        class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900"
                                    >
                                        {{ n.nama }}
                                        <span
                                            v-if="n.final"
                                            class="ml-2 inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800"
                                        >
                                            Terkunci
                                        </span>
                                    </td>

                                    <!-- Input PH -->
                                    <td
                                        class="px-3 py-2 text-center whitespace-nowrap"
                                    >
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            v-model="n.nilai_ph"
                                            @input="n.sumber_ph = 'manual'"
                                            :disabled="n.final"
                                            class="w-20 rounded-md border-gray-300 text-center focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="
                                                n.sumber_ph === 'cbt'
                                                    ? 'bg-green-50 text-green-700'
                                                    : ''
                                            "
                                            :title="
                                                n.sumber_ph === 'cbt'
                                                    ? 'Diambil dari CBT'
                                                    : 'Input Manual'
                                            "
                                        />
                                    </td>

                                    <!-- Input PTS -->
                                    <td
                                        class="px-3 py-2 text-center whitespace-nowrap"
                                    >
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            v-model="n.nilai_pts"
                                            @input="n.sumber_pts = 'manual'"
                                            :disabled="n.final"
                                            class="w-20 rounded-md border-gray-300 text-center focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="
                                                n.sumber_pts === 'cbt'
                                                    ? 'bg-green-50 text-green-700'
                                                    : ''
                                            "
                                            :title="
                                                n.sumber_pts === 'cbt'
                                                    ? 'Diambil dari CBT'
                                                    : 'Input Manual'
                                            "
                                        />
                                    </td>

                                    <!-- Input PAS -->
                                    <td
                                        class="px-3 py-2 text-center whitespace-nowrap"
                                    >
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            v-model="n.nilai_pas"
                                            @input="n.sumber_pas = 'manual'"
                                            :disabled="n.final"
                                            class="w-20 rounded-md border-gray-300 text-center focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="
                                                n.sumber_pas === 'cbt'
                                                    ? 'bg-green-50 text-green-700'
                                                    : ''
                                            "
                                            :title="
                                                n.sumber_pas === 'cbt'
                                                    ? 'Diambil dari CBT'
                                                    : 'Input Manual'
                                            "
                                        />
                                    </td>

                                    <!-- Nilai Akhir (Computed) -->
                                    <td
                                        class="bg-indigo-50 px-3 py-4 text-center text-sm font-bold whitespace-nowrap text-indigo-600"
                                    >
                                        {{ calculateAkhir(n) }}
                                    </td>

                                    <!-- Predikat (Computed) -->
                                    <td
                                        class="px-3 py-4 text-center text-sm font-bold whitespace-nowrap"
                                    >
                                        <span
                                            :class="{
                                                'text-green-600':
                                                    getPredikat(
                                                        calculateAkhir(n),
                                                    ) === 'A',
                                                'text-blue-600':
                                                    getPredikat(
                                                        calculateAkhir(n),
                                                    ) === 'B',
                                                'text-yellow-600':
                                                    getPredikat(
                                                        calculateAkhir(n),
                                                    ) === 'C',
                                                'text-red-600':
                                                    getPredikat(
                                                        calculateAkhir(n),
                                                    ) === 'D',
                                            }"
                                        >
                                            {{ getPredikat(calculateAkhir(n)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="siswaList.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-4 text-center text-gray-500"
                                    >
                                        Tidak ada siswa di kelas ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="flex justify-end space-x-3 border-t border-gray-200 bg-gray-50 px-4 py-3 text-right sm:px-6"
                        v-if="siswaList.length > 0"
                    >
                        <template v-if="!isAllFinal">
                            <button
                                @click="saveNilai"
                                type="button"
                                :disabled="saveForm.processing"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                            >
                                Simpan Semua Nilai
                            </button>
                            <button
                                @click="lockNilai"
                                type="button"
                                class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
                            >
                                ⚠️ Kunci Nilai
                            </button>
                        </template>
                        <template v-else>
                            <span
                                class="inline-flex items-center rounded bg-red-50 px-3 py-2 text-sm font-medium text-red-600"
                            >
                                Data telah dikunci dan tidak dapat diubah lagi.
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
