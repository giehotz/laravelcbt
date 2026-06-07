<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { index, store, update } from '@/routes/cbt/jadwal';
import { ArrowLeft, Save, Calendar, Settings2 } from 'lucide-vue-next';

const props = defineProps<{
    jadwal?: any;
    bankSoals: any[];
    jenisUjians: any[];
}>();

const isEdit = !!props.jadwal;

const form = useForm({
    bank_id: props.jadwal?.bank_id?.toString() || '',
    jenis_id: props.jadwal?.jenis_id?.toString() || '',
    tgl_mulai: props.jadwal?.tgl_mulai
        ? props.jadwal.tgl_mulai.split(' ')[0]
        : '',
    tgl_selesai: props.jadwal?.tgl_selesai
        ? props.jadwal.tgl_selesai.split(' ')[0]
        : '',
    durasi_ujian: props.jadwal?.durasi_ujian || 90,
    status: props.jadwal?.status?.toString() || '1',
    acak_soal: props.jadwal?.acak_soal ?? true,
    acak_opsi: props.jadwal?.acak_opsi ?? true,
    hasil_tampil: props.jadwal?.hasil_tampil ?? false,
    token: props.jadwal?.token ?? false,
    ulang: props.jadwal?.ulang ?? false,
    reset_login: props.jadwal?.reset_login ?? false,
});

const submit = () => {
    if (isEdit) {
        form.put(update.url({ jadwal: props.jadwal.id }));
    } else {
        form.post(store.url());
    }
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="mb-6 flex items-center space-x-4">
            <Button variant="outline" size="icon" as-child>
                <Link :href="index.url()">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <Heading
                :title="isEdit ? 'Edit Jadwal Ujian' : 'Tambah Jadwal Ujian'"
                description="Konfigurasi pelaksanaan ujian."
            />
        </div>

        <form
            @submit.prevent="submit"
            class="grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-3"
        >
            <!-- Kolom Kiri: Pengaturan Utama -->
            <div class="space-y-6 md:col-span-2">
                <div
                    class="space-y-6 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div
                        class="flex items-center space-x-2 border-b border-border pb-4"
                    >
                        <Calendar class="h-5 w-5 text-primary" />
                        <h3 class="text-lg font-semibold">Informasi Jadwal</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="bank_id"
                                >Bank Soal
                                <span class="text-destructive">*</span></Label
                            >
                            <Select v-model="form.bank_id">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih bank soal"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="bank in bankSoals"
                                        :key="bank.id"
                                        :value="bank.id.toString()"
                                    >
                                        {{ bank.kode }} - {{ bank.nama }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.bank_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="jenis_id"
                                >Jenis Ujian
                                <span class="text-destructive">*</span></Label
                            >
                            <Select v-model="form.jenis_id">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih jenis ujian"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="jenis in jenisUjians"
                                        :key="jenis.id"
                                        :value="jenis.id.toString()"
                                    >
                                        {{ jenis.nama_jenis }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.jenis_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="tgl_mulai"
                                >Tanggal Mulai
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="tgl_mulai"
                                type="date"
                                v-model="form.tgl_mulai"
                            />
                            <InputError :message="form.errors.tgl_mulai" />
                        </div>

                        <div class="space-y-2">
                            <Label for="tgl_selesai"
                                >Tanggal Selesai
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="tgl_selesai"
                                type="date"
                                v-model="form.tgl_selesai"
                            />
                            <InputError :message="form.errors.tgl_selesai" />
                        </div>

                        <div class="space-y-2">
                            <Label for="durasi_ujian"
                                >Durasi Ujian (Menit)
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="durasi_ujian"
                                type="number"
                                min="1"
                                v-model="form.durasi_ujian"
                            />
                            <InputError :message="form.errors.durasi_ujian" />
                        </div>

                        <div class="space-y-2">
                            <Label for="status"
                                >Status Jadwal
                                <span class="text-destructive">*</span></Label
                            >
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1"
                                        >Aktif (Tampil di Siswa)</SelectItem
                                    >
                                    <SelectItem value="0"
                                        >Tutup (Sembunyikan)</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pengaturan Lanjutan -->
            <div class="space-y-6">
                <div
                    class="space-y-6 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div
                        class="flex items-center space-x-2 border-b border-border pb-4"
                    >
                        <Settings2 class="h-5 w-5 text-primary" />
                        <h3 class="text-lg font-semibold">Konfigurasi Ujian</h3>
                    </div>

                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Acak Soal</Label>
                                <p class="text-xs text-muted-foreground">
                                    Urutan soal diacak untuk tiap siswa
                                </p>
                            </div>
                            <Switch
                                :checked="form.acak_soal"
                                @update:checked="
                                    (v: boolean) => (form.acak_soal = v)
                                "
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Acak Opsi</Label>
                                <p class="text-xs text-muted-foreground">
                                    Pilihan ganda diacak posisinya
                                </p>
                            </div>
                            <Switch
                                :checked="form.acak_opsi"
                                @update:checked="
                                    (v: boolean) => (form.acak_opsi = v)
                                "
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Gunakan Token</Label>
                                <p class="text-xs text-muted-foreground">
                                    Siswa wajib input token aktif
                                </p>
                            </div>
                            <Switch
                                :checked="form.token"
                                @update:checked="
                                    (v: boolean) => (form.token = v)
                                "
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Tampil Hasil</Label>
                                <p class="text-xs text-muted-foreground">
                                    Siswa dapat melihat skor akhir
                                </p>
                            </div>
                            <Switch
                                :checked="form.hasil_tampil"
                                @update:checked="
                                    (v: boolean) => (form.hasil_tampil = v)
                                "
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Boleh Mengulang</Label>
                                <p class="text-xs text-muted-foreground">
                                    Siswa dapat ujian ulang
                                </p>
                            </div>
                            <Switch
                                :checked="form.ulang"
                                @update:checked="
                                    (v: boolean) => (form.ulang = v)
                                "
                            />
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Reset Login Otomatis</Label>
                                <p class="text-xs text-muted-foreground">
                                    Tolak multi-login
                                </p>
                            </div>
                            <Switch
                                :checked="form.reset_login"
                                @update:checked="
                                    (v: boolean) => (form.reset_login = v)
                                "
                            />
                        </div>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    size="lg"
                    :disabled="form.processing"
                >
                    <Save class="mr-2 h-4 w-4" />
                    {{ isEdit ? 'Simpan Perubahan' : 'Buat Jadwal Ujian' }}
                </Button>
            </div>
        </form>
    </div>
</template>
