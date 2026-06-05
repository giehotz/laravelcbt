<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    posts: Object
});

const form = useForm({});

const user = usePage().props.auth.user;
const canCreate = computed(() => {
    // Only superadmin, operator, guru can create posts. Adjust based on your roles/permissions
    return user.roles.some(role => ['superadmin', 'operator', 'guru', 'kepsek'].includes(role.name));
});

const deletePost = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
        form.delete(route('pengumuman.destroy', id), {
            preserveScroll: true
        });
    }
};

const formatTarget = (kepada) => {
    if (!kepada) return '-';
    if (kepada.type === 'all') return 'Semua Pengguna';
    if (kepada.type === 'siswa') return 'Semua Siswa';
    if (kepada.type === 'guru') return 'Semua Guru';
    if (kepada.type === 'kelas') return `Kelas Tertentu (${kepada.ids?.length || 0} kelas)`;
    return kepada.type;
};
</script>

<template>
    <AppLayout>
        <Head title="Pengumuman" />

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold leading-tight text-gray-900">
                    Pengumuman
                </h2>
                
                <Link
                    v-if="canCreate"
                    :href="route('pengumuman.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Buat Pengumuman
                </Link>
            </div>

            <div class="space-y-6">
                <div v-for="post in posts.data" :key="post.id" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Dari: {{ post.user?.name || 'Sistem' }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Untuk: <span class="font-semibold">{{ formatTarget(post.kepada) }}</span> &middot; {{ new Date(post.created_at).toLocaleString() }}
                            </p>
                        </div>
                        <div v-if="user.id === post.dari_user_id || user.roles.some(r => ['superadmin', 'operator'].includes(r.name))">
                            <button @click="deletePost(post.id)" class="text-red-600 hover:text-red-900 text-sm">Hapus</button>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6 prose max-w-none whitespace-pre-wrap text-gray-800" v-html="post.text">
                    </div>
                </div>

                <div v-if="posts.data.length === 0" class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                    Belum ada pengumuman untuk Anda.
                </div>
                
                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center" v-if="posts.links && posts.links.length > 3">
                    <div class="flex space-x-1">
                        <template v-for="(link, index) in posts.links" :key="index">
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
