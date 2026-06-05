<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/InputError.vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    guru: any;
    mapel: any[];
    kelas: any[];
    ekstra: any[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pengaturan', href: '#' },
            { title: 'User Guru', href: '/setting/user/guru' },
            { title: 'Penugasan Guru', href: '#' },
        ],
    },
});

// Map existing assignments to form structure
const existingJabatan = props.guru.jabatan_guru?.[0] || null;
const mapelKelasMap = new Map();

if (props.guru.guru_mapel_kelas) {
    props.guru.guru_mapel_kelas.forEach((mk: any) => {
        if (!mapelKelasMap.has(mk.mapel_id)) {
            mapelKelasMap.set(mk.mapel_id, {
                mapel_id: mk.mapel_id.toString(),
                kelas: []
            });
        }
        mapelKelasMap.get(mk.mapel_id).kelas.push(mk.kelas_id);
    });
}

const existingEkstra = props.guru.ekstra_pembina?.map((e: any) => e.ekstra_id) || [];

const form = useForm({
    jabatan: existingJabatan?.jabatan || '',
    is_aktif_jabatan: existingJabatan?.is_aktif ?? true,
    mapel_kelas: Array.from(mapelKelasMap.values()),
    ekstra: existingEkstra,
});

const addMapelKelas = () => {
    form.mapel_kelas.push({ mapel_id: '', kelas: [] });
};

const removeMapelKelas = (index: number) => {
    form.mapel_kelas.splice(index, 1);
};

// Cast to Record<string, string> to allow dynamic key access in template
// (e.g. `mapel_kelas.${index}.mapel_id`) without TypeScript errors
const formErrors = computed(() => form.errors as Record<string, string>);

const submit = () => {
    form.put(`/setting/user/guru/${props.guru.id}/assignment`);
};

const selectClass = 'w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring dark:bg-zinc-950 dark:border-zinc-700';
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="flex items-center space-x-4 mb-6">
            <Button variant="outline" size="icon" as-child>
                <Link href="/setting/user/guru">
                    <ArrowLeft class="w-4 h-4" />
                </Link>
            </Button>
            <Heading title="Penugasan Guru" :description="`Mengatur jabatan, penugasan mengajar, dan pembina ekstra untuk ${props.guru.nama_guru}`" />
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
            <!-- Jabatan Struktural -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                <h3 class="font-semibold text-lg border-b border-border pb-4">Jabatan Struktural</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <Label for="jabatan">Pilih Jabatan</Label>
                        <select id="jabatan" v-model="form.jabatan" :class="selectClass">
                            <option value="">Tidak ada jabatan khusus</option>
                            <option value="Kepala Sekolah">Kepala Sekolah</option>
                            <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                            <option value="Wali Kelas">Wali Kelas</option>
                            <option value="Guru BK">Guru BK</option>
                        </select>
                        <InputError :message="form.errors.jabatan" />
                    </div>

                    <div class="flex items-center space-x-3 pt-8">
                        <Switch id="is_aktif_jabatan" :checked="form.is_aktif_jabatan" @update:checked="(v: boolean) => form.is_aktif_jabatan = v" />
                        <Label for="is_aktif_jabatan">Status Jabatan Aktif</Label>
                    </div>
                </div>
            </div>

            <!-- Penugasan Mengajar -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-border pb-4">
                    <h3 class="font-semibold text-lg">Penugasan Mengajar (Mapel & Kelas)</h3>
                    <Button type="button" variant="outline" size="sm" @click="addMapelKelas">
                        <Plus class="w-4 h-4 mr-2" /> Tambah Penugasan
                    </Button>
                </div>
                
                <div v-if="form.mapel_kelas.length === 0" class="text-center py-6 text-muted-foreground border-2 border-dashed border-border rounded-lg">
                    Belum ada penugasan mengajar. Klik tombol "Tambah Penugasan" untuk mulai.
                </div>

                <div v-for="(mk, index) in form.mapel_kelas" :key="index" class="p-4 border border-border rounded-lg space-y-4 bg-muted/20 relative">
                    <Button type="button" variant="ghost" size="icon" class="absolute top-2 right-2 text-destructive hover:bg-destructive/10" @click="removeMapelKelas(index)">
                        <Trash2 class="w-4 h-4" />
                    </Button>

                    <div class="space-y-2 w-full md:w-1/2">
                        <Label>Mata Pelajaran <span class="text-destructive">*</span></Label>
                        <Select v-model="form.mapel_kelas[index].mapel_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Mata Pelajaran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="m in mapel" :key="m.id" :value="m.id.toString()">
                                    {{ m.nama_mapel }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="formErrors[`mapel_kelas.${index}.mapel_id`]" />
                    </div>

                    <div class="space-y-2">
                        <Label>Pilih Kelas <span class="text-destructive">*</span></Label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 bg-background p-3 rounded-md border border-border">
                            <label v-for="k in kelas" :key="k.id" class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" :value="k.id" v-model="form.mapel_kelas[index].kelas" class="rounded text-primary focus:ring-primary" />
                                <span class="text-sm">{{ k.nama_kelas }}</span>
                            </label>
                        </div>
                        <InputError :message="formErrors[`mapel_kelas.${index}.kelas`]" />
                    </div>
                </div>
            </div>

            <!-- Pembina Ekstrakurikuler -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                <h3 class="font-semibold text-lg border-b border-border pb-4">Pembina Ekstrakurikuler</h3>
                
                <div class="space-y-4">
                    <div v-if="ekstra.length === 0" class="text-muted-foreground text-sm">
                        Tidak ada data ekstrakurikuler di sistem.
                    </div>
                    <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label v-for="e in ekstra" :key="e.id" class="flex items-center gap-2 cursor-pointer p-3 border border-border rounded-lg hover:bg-muted/50 transition-colors">
                            <input type="checkbox" :value="e.id" v-model="form.ekstra" class="rounded text-primary focus:ring-primary" />
                            <span class="text-sm font-medium">{{ e.nama_ekstra }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pb-12">
                <Button type="button" variant="outline" @click="$inertia.visit('/setting/user/guru')">
                    Batal
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="w-4 h-4 mr-2" />
                    Simpan Penugasan
                </Button>
            </div>
        </form>
    </div>
</template>
