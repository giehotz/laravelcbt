<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    sync as syncPengawasRoute,
    index as indexPengawasRoute,
} from '@/routes/cbt/pengawas';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ArrowLeft, Save, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
    jadwal: any;
    ruangs: any[];
    sesis: any[];
    validPairs: string[]; // 'ruang_id-sesi_id' format
    gurus: any[];
    pengawas: any[]; // current pengawas mapping
}>();

// Build form state
// Array of objects { ruang_id, sesi_id, guru_id }
const initialState = props.pengawas.map((p) => ({
    ruang_id: p.ruang_id,
    sesi_id: p.sesi_id,
    guru_id: p.guru_id.toString(),
}));

const form = useForm({
    pengawas: initialState as {
        ruang_id: number;
        sesi_id: number;
        guru_id: string;
    }[],
});

const getGuruForCell = (ruangId: number, sesiId: number) => {
    const found = form.pengawas.find(
        (p) => p.ruang_id === ruangId && p.sesi_id === sesiId,
    );
    return found ? found.guru_id : undefined;
};

const setGuruForCell = (ruangId: number, sesiId: number, guruId: string) => {
    const index = form.pengawas.findIndex(
        (p) => p.ruang_id === ruangId && p.sesi_id === sesiId,
    );
    if (guruId) {
        if (index >= 0) {
            form.pengawas[index].guru_id = guruId;
        } else {
            form.pengawas.push({
                ruang_id: ruangId,
                sesi_id: sesiId,
                guru_id: guruId,
            });
        }
    } else {
        if (index >= 0) {
            form.pengawas.splice(index, 1);
        }
    }
};

const submit = () => {
    form.post(syncPengawasRoute.url({ jadwal: props.jadwal.id }));
};

const isComplete = computed(() => {
    let requiredPairs = 0;
    let filledPairs = 0;

    for (const r of props.ruangs) {
        for (const s of props.sesis) {
            if (props.validPairs.includes(`${r.id}-${s.id}`)) {
                requiredPairs++;
                if (getGuruForCell(r.id, s.id)) {
                    filledPairs++;
                }
            }
        }
    }

    return {
        required: requiredPairs,
        filled: filledPairs,
        done: requiredPairs > 0 && requiredPairs === filledPairs,
    };
});
</script>

<template>
    <Head title="Atur Pengawas Ujian" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="mb-4 flex items-center gap-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="indexPengawasRoute.url()">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">
                Atur Pengawas: {{ jadwal.bankSoal?.mapel?.nama_mapel }}
            </h2>
        </div>

        <div>
            <div class="mx-auto max-w-7xl space-y-6">
                <Alert variant="destructive" v-if="form.errors.pengawas">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Gagal Menyimpan</AlertTitle>
                    <AlertDescription>
                        {{ form.errors.pengawas }}
                    </AlertDescription>
                </Alert>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardTitle>Matriks Ruang & Sesi</CardTitle>
                            <CardDescription>
                                Jadwal: {{ jadwal.tgl_mulai }} s/d
                                {{ jadwal.tgl_selesai }}
                            </CardDescription>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-sm">
                                Progress:
                                <span
                                    :class="
                                        isComplete.done
                                            ? 'font-bold text-green-600'
                                            : 'font-bold text-orange-600'
                                    "
                                    >{{ isComplete.filled }} /
                                    {{ isComplete.required }}</span
                                >
                            </div>
                            <Button @click="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Simpan Pengawas
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead
                                            class="w-[200px] border-r bg-muted/50"
                                            >Ruang \ Sesi</TableHead
                                        >
                                        <TableHead
                                            v-for="sesi in sesis"
                                            :key="sesi.id"
                                            class="min-w-[200px] text-center"
                                        >
                                            {{ sesi.nama_sesi }}
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="ruangs.length === 0">
                                        <TableCell
                                            :colspan="sesis.length + 1"
                                            class="py-8 text-center text-muted-foreground"
                                        >
                                            Tidak ada ruang/sesi yang digunakan
                                            pada aktif TP/SMT ini. Pastikan Anda
                                            telah mengatur distribusi siswa di
                                            menu Atur Ruang/Sesi.
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-for="ruang in ruangs"
                                        :key="ruang.id"
                                    >
                                        <TableCell
                                            class="border-r bg-muted/50 font-medium"
                                        >
                                            {{ ruang.nama_ruang }}
                                        </TableCell>
                                        <TableCell
                                            v-for="sesi in sesis"
                                            :key="sesi.id"
                                            :class="{
                                                'bg-red-50/50':
                                                    validPairs.includes(
                                                        `${ruang.id}-${sesi.id}`,
                                                    ) &&
                                                    !getGuruForCell(
                                                        ruang.id,
                                                        sesi.id,
                                                    ),
                                                'bg-green-50/50':
                                                    getGuruForCell(
                                                        ruang.id,
                                                        sesi.id,
                                                    ),
                                            }"
                                        >
                                            <div
                                                v-if="
                                                    validPairs.includes(
                                                        `${ruang.id}-${sesi.id}`,
                                                    )
                                                "
                                            >
                                                <Select
                                                    :model-value="
                                                        getGuruForCell(
                                                            ruang.id,
                                                            sesi.id,
                                                        )
                                                    "
                                                    @update:model-value="
                                                        (val) =>
                                                            setGuruForCell(
                                                                ruang.id,
                                                                sesi.id,
                                                                val as string,
                                                            )
                                                    "
                                                >
                                                    <SelectTrigger
                                                        class="w-full bg-white"
                                                    >
                                                        <SelectValue
                                                            placeholder="Pilih Pengawas..."
                                                        />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="guru in gurus"
                                                            :key="guru.id"
                                                            :value="
                                                                guru.id.toString()
                                                            "
                                                        >
                                                            {{ guru.nama_guru }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div
                                                v-else
                                                class="p-2 text-center text-xs text-muted-foreground"
                                            >
                                                - (Tidak Digunakan) -
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
