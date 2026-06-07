<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index, create, edit, destroy } from '@/routes/cbt/bank-soal';
import { index as soalIndex, importMethod } from '@/routes/cbt/bank-soal/soal';
import {
    Plus,
    Edit2,
    Trash2,
    ListChecks,
    CheckCircle2,
    XCircle,
    Upload,
} from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'CBT', href: '#' },
            { title: 'Bank Soal', href: index.url() },
        ],
    },
});

defineProps<{
    bankSoals: any;
}>();

const confirmDelete = (id: number) => {
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus bank soal ini? Semua soal di dalamnya akan ikut terhapus!',
        )
    ) {
        router.delete(destroy.url({ bank_soal: id }));
    }
};
</script>

<template>
    <Head title="Bank Soal" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Bank Soal"
                description="Kelola bank soal untuk ujian CBT."
            />
            <Button
                @click="router.get(create.url())"
                class="flex items-center gap-2 bg-amber-500 font-semibold text-white shadow-sm transition-colors hover:bg-amber-600"
            >
                <Plus class="h-4 w-4" />
                <span>Tambah Bank Soal</span>
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-16 px-6 py-3 text-center">ID</th>
                            <th class="px-6 py-3">Kode / Nama</th>
                            <th class="px-6 py-3">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-center">Soal PG</th>
                            <th class="px-6 py-3 text-center">Soal Esai</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="item in bankSoals.data"
                            :key="item.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-6 py-3.5 text-center font-medium text-neutral-400"
                            >
                                {{ item.id }}
                            </td>
                            <td class="px-6 py-3.5">
                                <div
                                    class="font-bold text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ item.kode }}
                                </div>
                                <div class="mt-0.5 text-xs text-neutral-500">
                                    {{ item.nama }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-3.5 font-medium text-neutral-600 dark:text-neutral-400"
                            >
                                {{ item.mapel?.nama_mapel }}
                                <span
                                    v-if="item.guru"
                                    class="block text-xs font-normal text-neutral-400"
                                    >Oleh: {{ item.guru.nama }}</span
                                >
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-mono"
                                    >{{ item.jml_pg }} /
                                    {{ item.tampil_pg }}</span
                                >
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-mono"
                                    >{{ item.jml_esai }} /
                                    {{ item.tampil_esai }}</span
                                >
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <div
                                    v-if="item.status === 1"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    <span>Aktif</span>
                                </div>
                                <div
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-neutral-400"
                                >
                                    <XCircle class="h-3.5 w-3.5" />
                                    <span>Draft</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 border-indigo-200 text-indigo-600 hover:bg-indigo-50 dark:border-indigo-900 dark:hover:bg-indigo-900/30"
                                        @click="
                                            router.get(
                                                soalIndex.url({
                                                    bank: item.id,
                                                }),
                                            )
                                        "
                                        title="Kelola Soal"
                                    >
                                        <ListChecks class="mr-1 h-4 w-4" />
                                        Soal
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 border-emerald-200 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-900/30 dark:hover:bg-emerald-950/20"
                                        @click="
                                            router.get(
                                                importMethod.url({
                                                    bank: item.id,
                                                }),
                                            )
                                        "
                                        title="Import Soal dari Excel"
                                    >
                                        <Upload class="mr-1 h-4 w-4" />
                                        Import
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 w-8 border-amber-200 p-0 text-amber-600 hover:bg-amber-50 dark:border-amber-900/50 dark:hover:bg-amber-900/30"
                                        @click="
                                            router.get(
                                                edit.url({
                                                    bank_soal: item.id,
                                                }),
                                            )
                                        "
                                    >
                                        <Edit2 class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 w-8 border-rose-200 p-0 text-rose-600 hover:bg-rose-50 dark:border-rose-900/50 dark:hover:bg-rose-900/30"
                                        @click="confirmDelete(item.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="bankSoals.data.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada bank soal.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Component Here -->
            <div
                v-if="bankSoals.links && bankSoals.links.length > 3"
                class="flex justify-center border-t border-neutral-200 bg-neutral-50/30 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/30"
            >
                <div class="flex gap-1">
                    <template v-for="(link, key) in bankSoals.links" :key="key">
                        <div
                            v-if="link.url === null"
                            class="cursor-not-allowed rounded border border-neutral-200 bg-neutral-50 px-3 py-1 text-sm text-neutral-400 dark:border-zinc-800 dark:bg-zinc-900"
                            v-html="link.label"
                        ></div>
                        <a
                            v-else
                            :href="link.url"
                            :class="[
                                'rounded border px-3 py-1 text-sm transition-colors',
                                link.active
                                    ? 'border-zinc-900 bg-zinc-900 font-bold text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900'
                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-neutral-300 dark:hover:bg-zinc-800',
                            ]"
                            v-html="link.label"
                            @click.prevent="
                                router.visit(link.url, { preserveState: true })
                            "
                        ></a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
