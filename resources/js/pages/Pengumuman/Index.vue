<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { create, destroy } from '@/routes/pengumuman';

const props = defineProps({
    posts: Object,
});

const form = useForm({});

const user = usePage().props.auth.user;
const canCreate = computed(() => {
    // Only superadmin, operator, guru can create posts. Adjust based on your roles/permissions
    return user.roles.some((role) =>
        ['superadmin', 'operator', 'guru', 'kepsek'].includes(role.name),
    );
});

const deletePost = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
        form.delete(destroy.url(id), {
            preserveScroll: true,
        });
    }
};
const formatTarget = (kepada) => {
    if (!kepada) return '-';
    if (kepada.type === 'all') return 'Semua Pengguna';
    if (kepada.type === 'siswa') return 'Semua Siswa';
    if (kepada.type === 'guru') return 'Semua Guru';
    if (kepada.type === 'kelas')
        return `Kelas Tertentu (${kepada.ids?.length || 0} kelas)`;
    return kepada.type;
};
</script>

<template>
    <div>
        <Head title="Pengumuman" />

        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl leading-tight font-bold text-gray-900">
                    Pengumuman
                </h2>

                <Link
                    v-if="canCreate"
                    :href="create.url()"
                    class="rounded bg-indigo-600 px-4 py-2 font-bold text-white hover:bg-indigo-700"
                >
                    + Buat Pengumuman
                </Link>
            </div>

            <div class="space-y-6">
                <div
                    v-for="post in posts.data"
                    :key="post.id"
                    class="overflow-hidden bg-white shadow sm:rounded-lg"
                >
                    <div
                        class="flex items-start justify-between px-4 py-5 sm:px-6"
                    >
                        <div>
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900"
                            >
                                Dari: {{ post.user?.name || 'Sistem' }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Untuk:
                                <span class="font-semibold">{{
                                    formatTarget(post.kepada)
                                }}</span>
                                &middot;
                                {{ new Date(post.created_at).toLocaleString() }}
                            </p>
                        </div>
                        <div
                            v-if="
                                user.id === post.dari_user_id ||
                                user.roles.some((r) =>
                                    ['superadmin', 'operator'].includes(r.name),
                                )
                            "
                        >
                            <button
                                @click="deletePost(post.id)"
                                class="text-sm text-red-600 hover:text-red-900"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                    <div
                        class="prose max-w-none border-t border-gray-200 px-4 py-5 whitespace-pre-wrap text-gray-800 sm:px-6"
                        v-html="post.text"
                    ></div>
                </div>

                <div
                    v-if="posts.data.length === 0"
                    class="bg-white p-6 text-center text-gray-500 shadow sm:rounded-lg"
                >
                    Belum ada pengumuman untuk Anda.
                </div>

                <!-- Pagination -->
                <div
                    class="mt-4 flex items-center justify-between"
                    v-if="posts.links && posts.links.length > 3"
                >
                    <div class="flex space-x-1">
                        <template
                            v-for="(link, index) in posts.links"
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
</template>
