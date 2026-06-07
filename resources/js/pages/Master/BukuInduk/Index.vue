<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    siswa: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(
    search,
    debounce((value) => {
        router.get(
            route('master.buku-induk.index'),
            { search: value },
            { preserveState: true, replace: true },
        );
    }, 300),
);

const getStatusBadge = (status) => {
    switch (status) {
        case 1:
            return '<span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Aktif</span>';
        case 2:
            return '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Lulus</span>';
        case 3:
            return '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pindah</span>';
        case 4:
            return '<span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Keluar</span>';
        default:
            return '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Unknown</span>';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Buku Induk Siswa" />

        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl leading-tight font-bold text-gray-900">
                    Buku Induk Siswa
                </h2>

                <div class="w-1/3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari siswa (Nama/NIS/NISN)..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        NIS/NISN
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Nama Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Kelas Aktif
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Status Akhir
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in siswa.data" :key="item.id">
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                                    >
                                        {{ item.nis }}<br />
                                        <span class="text-xs text-gray-500">{{
                                            item.nisn
                                        }}</span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900"
                                    >
                                        {{ item.nama }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        {{
                                            item.kelas_aktif
                                                ? item.kelas_aktif.kelas.nama
                                                : '-'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <div
                                            v-html="
                                                getStatusBadge(
                                                    item.buku_induk?.status ??
                                                        1,
                                                )
                                            "
                                        ></div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium whitespace-nowrap"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'master.buku-induk.edit',
                                                    item.id,
                                                )
                                            "
                                            class="rounded bg-indigo-50 px-3 py-1 text-indigo-600 hover:text-indigo-900"
                                        >
                                            Lengkapi Data
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="siswa.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-4 text-center text-gray-500"
                                    >
                                        Tidak ada data siswa ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="mt-4 flex items-center justify-between"
                        v-if="siswa.links && siswa.links.length > 3"
                    >
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ siswa.from }} sampai
                            {{ siswa.to }} dari {{ siswa.total }} data
                        </div>
                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in siswa.links"
                                :key="index"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="rounded border px-3 py-1 text-sm"
                                    :class="
                                        link.active
                                            ? 'border-indigo-600 bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50'
                                    "
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="cursor-not-allowed rounded border bg-gray-100 px-3 py-1 text-sm text-gray-400"
                                    v-html="link.label"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
