<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profil Sekolah',
                href: '/setting/sekolah',
            },
        ],
    },
});

import { update as schoolUpdate } from '@/routes/setting/sekolah';

const props = defineProps<{
    settings: {
        nama_sekolah: string | null;
        nss: string | null;
        npsn: string | null;
        alamat: string | null;
        kecamatan: string | null;
        kabupaten: string | null;
        provinsi: string | null;
        kode_pos: string | null;
        telp: string | null;
        email: string | null;
        website: string | null;
        logo: string | null;
        kepala_sekolah: string | null;
        nip_kepsek: string | null;
        running_text: string | null;
    };
}>();

const form = useForm({
    nama_sekolah: props.settings.nama_sekolah ?? '',
    nss: props.settings.nss ?? '',
    npsn: props.settings.npsn ?? '',
    alamat: props.settings.alamat ?? '',
    kecamatan: props.settings.kecamatan ?? '',
    kabupaten: props.settings.kabupaten ?? '',
    provinsi: props.settings.provinsi ?? '',
    kode_pos: props.settings.kode_pos ?? '',
    telp: props.settings.telp ?? '',
    email: props.settings.email ?? '',
    website: props.settings.website ?? '',
    logo: null as File | null,
    kepala_sekolah: props.settings.kepala_sekolah ?? '',
    nip_kepsek: props.settings.nip_kepsek ?? '',
    running_text: props.settings.running_text ?? '',
});

const logoPreview = ref<string | null>(props.settings.logo ? `/storage/${props.settings.logo}` : null);

const onLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(schoolUpdate.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Profil Sekolah" />

    <div class="px-6 py-6 max-w-4xl mx-auto space-y-6">
        <Heading
            title="Profil Sekolah"
            description="Lengkapi informasi dasar sekolah Anda untuk digunakan pada kop surat, laporan, dan dokumen cetak lainnya."
        />

        <form @submit.prevent="submit" class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Side: Basic School Information -->
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="nama_sekolah">Nama Sekolah <span class="text-rose-500">*</span></Label>
                        <Input id="nama_sekolah" v-model="form.nama_sekolah" required placeholder="Contoh: SMA Negeri 1 Garuda" />
                        <InputError :message="form.errors.nama_sekolah" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="npsn">NPSN</Label>
                            <Input id="npsn" v-model="form.npsn" placeholder="Nomor NPSN" />
                            <InputError :message="form.errors.npsn" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="nss">NSS / NDS</Label>
                            <Input id="nss" v-model="form.nss" placeholder="Nomor NSS" />
                            <InputError :message="form.errors.nss" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="telp">Telepon</Label>
                        <Input id="telp" v-model="form.telp" placeholder="Telepon Sekolah" />
                        <InputError :message="form.errors.telp" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="email@sekolah.sch.id" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="website">Website</Label>
                        <Input id="website" v-model="form.website" placeholder="www.sekolah.sch.id" />
                        <InputError :message="form.errors.website" />
                    </div>
                </div>

                <!-- Right Side: Logo Upload and Headmaster details -->
                <div class="space-y-4">
                    <!-- Logo Upload Section -->
                    <div class="grid gap-2">
                        <Label>Logo Sekolah</Label>
                        <div class="flex items-center gap-4 p-4 border border-dashed border-neutral-300 dark:border-zinc-700 rounded-lg">
                            <div class="w-20 h-20 bg-neutral-50 dark:bg-zinc-800 rounded-lg overflow-hidden border border-neutral-200 dark:border-zinc-700 flex items-center justify-center flex-shrink-0">
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain" />
                                <span v-else class="text-xs text-neutral-400">No Logo</span>
                            </div>
                            <div class="space-y-2">
                                <input id="logo" type="file" @change="onLogoChange" accept="image/*" class="hidden" />
                                <Label for="logo" class="inline-flex h-9 items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-200 cursor-pointer">
                                    Pilih Gambar
                                </Label>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">JPG, PNG, WebP (Maks. 2MB). Min 100x100px.</p>
                            </div>
                        </div>
                        <InputError :message="form.errors.logo" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kepala_sekolah">Nama Kepala Sekolah</Label>
                        <Input id="kepala_sekolah" v-model="form.kepala_sekolah" placeholder="Nama Lengkap & Gelar" />
                        <InputError :message="form.errors.kepala_sekolah" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nip_kepsek">NIP Kepala Sekolah</Label>
                        <Input id="nip_kepsek" v-model="form.nip_kepsek" placeholder="NIP Kepala Sekolah" />
                        <InputError :message="form.errors.nip_kepsek" />
                    </div>
                </div>
            </div>

            <hr class="border-neutral-200 dark:border-zinc-800" />

            <!-- Additional details -->
            <div class="space-y-4">
                <div class="grid gap-2">
                    <Label for="alamat">Alamat Lengkap</Label>
                    <Input id="alamat" v-model="form.alamat" placeholder="Jalan, RT/RW, Dusun" />
                    <InputError :message="form.errors.alamat" />
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="grid gap-2">
                        <Label for="kecamatan">Kecamatan</Label>
                        <Input id="kecamatan" v-model="form.kecamatan" placeholder="Kecamatan" />
                        <InputError :message="form.errors.kecamatan" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="kabupaten">Kabupaten/Kota</Label>
                        <Input id="kabupaten" v-model="form.kabupaten" placeholder="Kabupaten" />
                        <InputError :message="form.errors.kabupaten" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="provinsi">Provinsi</Label>
                        <Input id="provinsi" v-model="form.provinsi" placeholder="Provinsi" />
                        <InputError :message="form.errors.provinsi" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="kode_pos">Kode Pos</Label>
                        <Input id="kode_pos" v-model="form.kode_pos" placeholder="Kode Pos" />
                        <InputError :message="form.errors.kode_pos" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="running_text">Teks Berjalan Pengumuman (Running Text)</Label>
                    <Input id="running_text" v-model="form.running_text" placeholder="Pesan pengumuman yang akan berjalan di dashboard siswa..." />
                    <InputError :message="form.errors.running_text" />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-4 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </Button>
            </div>
        </form>
    </div>
</template>
