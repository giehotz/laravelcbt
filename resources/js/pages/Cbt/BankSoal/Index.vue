<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index, create, edit, destroy } from '@/routes/cbt/bank-soal';
import { index as soalIndex } from '@/routes/cbt/bank-soal/soal';
import { Plus, Edit2, Trash2, ListChecks, CheckCircle2, XCircle } from 'lucide-vue-next';

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
    if (confirm('Apakah Anda yakin ingin menghapus bank soal ini? Semua soal di dalamnya akan ikut terhapus!')) {
        router.delete(destroy.url({ bank_soal: id }));
    }
};
</script>

<template>
    <Head title="Bank Soal" />

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <Heading
                title="Bank Soal"
                description="Kelola bank soal untuk ujian CBT."
            />
            <Button @click="router.get(create.url())" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold flex items-center gap-2 shadow-sm transition-colors">
                <Plus class="w-4 h-4" />
                <span>Tambah Bank Soal</span>
            </Button>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3 w-16 text-center">ID</th>
                            <th class="px-6 py-3">Kode / Nama</th>
                            <th class="px-6 py-3">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-center">Soal PG</th>
                            <th class="px-6 py-3 text-center">Soal Esai</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="item in bankSoals.data" :key="item.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-3.5 text-center font-medium text-neutral-400">{{ item.id }}</td>
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-neutral-800 dark:text-neutral-200">{{ item.kode }}</div>
                                <div class="text-xs text-neutral-500 mt-0.5">{{ item.nama }}</div>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-neutral-600 dark:text-neutral-400">
                                {{ item.mapel?.nama_mapel }}
                                <span v-if="item.guru" class="block text-xs font-normal text-neutral-400">Oleh: {{ item.guru.nama }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-mono">{{ item.jml_pg }} / {{ item.tampil_pg }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-mono">{{ item.jml_esai }} / {{ item.tampil_esai }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <div v-if="item.status === 1" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold">
                                    <CheckCircle2 class="w-3.5 h-3.5" />
                                    <span>Aktif</span>
                                </div>
                                <div v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-neutral-100 dark:bg-zinc-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-zinc-700 text-xs font-semibold">
                                    <XCircle class="w-3.5 h-3.5" />
                                    <span>Draft</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="outline" size="sm" class="h-8 text-indigo-600 border-indigo-200 hover:bg-indigo-50 dark:border-indigo-900 dark:hover:bg-indigo-900/30" @click="router.get(soalIndex.url({ bank: item.id }))" title="Kelola Soal">
                                        <ListChecks class="w-4 h-4 mr-1" />
                                        Soal
                                    </Button>
                                    <Button variant="outline" size="sm" class="w-8 h-8 p-0 text-amber-600 border-amber-200 hover:bg-amber-50 dark:border-amber-900/50 dark:hover:bg-amber-900/30" @click="router.get(edit.url({ bank_soal: item.id }))">
                                        <Edit2 class="w-4 h-4" />
                                    </Button>
                                    <Button variant="outline" size="sm" class="w-8 h-8 p-0 text-rose-600 border-rose-200 hover:bg-rose-50 dark:border-rose-900/50 dark:hover:bg-rose-900/30" @click="confirmDelete(item.id)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="bankSoals.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada bank soal.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Component Here -->
            <div v-if="bankSoals.links && bankSoals.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-zinc-800 bg-neutral-50/30 dark:bg-zinc-900/30 flex justify-center">
                <div class="flex gap-1">
                    <template v-for="(link, key) in bankSoals.links" :key="key">
                        <div v-if="link.url === null" class="px-3 py-1 text-sm text-neutral-400 border border-neutral-200 dark:border-zinc-800 rounded bg-neutral-50 dark:bg-zinc-900 cursor-not-allowed" v-html="link.label"></div>
                        <a v-else :href="link.url" :class="['px-3 py-1 text-sm border rounded transition-colors', link.active ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-bold' : 'bg-white dark:bg-zinc-900 text-neutral-700 dark:text-neutral-300 border-neutral-200 dark:border-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-800']" v-html="link.label" @click.prevent="router.visit(link.url, { preserveState: true })"></a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
