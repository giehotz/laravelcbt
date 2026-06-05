<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    materi: Object
});

const user = usePage().props.auth.user;
const canCreate = computed(() => {
    return user.roles.some(role => ['superadmin', 'operator', 'guru', 'kepsek'].includes(role.name));
});

const form = useForm({});

const deleteMateri = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus materi ini beserta semua file lampirannya?')) {
        form.delete(route('lms.materi.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Materi Pembelajaran" />

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold leading-tight text-gray-900">
                    Materi Pembelajaran
                </h2>
                
                <Link
                    v-if="canCreate"
                    :href="route('lms.materi.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Buat Materi
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="item in materi.data" :key="item.id" class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ item.mapel?.nama_mapel }}
                                </p>
                                <Link :href="route('lms.materi.show', item.id)" class="block mt-1">
                                    <p class="text-xl font-bold text-gray-900 truncate">
                                        {{ item.judul }}
                                    </p>
                                </Link>
                                <p class="text-sm text-gray-500 mt-1">
                                    Oleh: {{ item.guru?.nama }} &middot; {{ new Date(item.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-4" v-if="canCreate">
                                <Link :href="route('lms.materi.edit', item.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    Edit
                                </Link>
                                <button @click="deleteMateri(item.id)" class="text-red-600 hover:text-red-900 text-sm">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </li>
                    <li v-if="materi.data.length === 0" class="px-6 py-4 text-center text-gray-500">
                        Belum ada materi pembelajaran.
                    </li>
                </ul>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center" v-if="materi.links && materi.links.length > 3">
                    <div class="flex space-x-1">
                        <template v-for="(link, index) in materi.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 border rounded text-sm"
                                :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1 border rounded text-sm bg-gray-100 text-gray-400 cursor-not-allowed"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
