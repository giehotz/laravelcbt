<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    materi: Object,
});

const user = usePage().props.auth.user;
const canCreate = computed(() => {
    return user.roles.some((role) =>
        ['superadmin', 'operator', 'guru', 'kepsek'].includes(role.name),
    );
});

const form = useForm({});

const deleteMateri = (id) => {
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus materi ini beserta semua file lampirannya?',
        )
    ) {
        form.delete(route('lms.materi.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Materi Pembelajaran" />

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 sm:gap-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl leading-tight font-bold text-gray-900">
                Materi Pembelajaran
            </h2>

            <Link
                v-if="canCreate"
                :href="route('lms.materi.create')"
                class="rounded bg-indigo-600 px-4 py-2 font-bold text-white hover:bg-indigo-700"
            >
                + Buat Materi
            </Link>
        </div>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <ul role="list" class="divide-y divide-gray-200">
                <li
                    v-for="item in materi.data"
                    :key="item.id"
                    class="px-6 py-4 hover:bg-gray-50"
                >
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-medium text-indigo-600"
                            >
                                {{ item.mapel?.nama_mapel }}
                            </p>
                            <Link
                                :href="route('lms.materi.show', item.id)"
                                class="mt-1 block"
                            >
                                <p
                                    class="truncate text-xl font-bold text-gray-900"
                                >
                                    {{ item.judul }}
                                </p>
                            </Link>
                            <p class="mt-1 text-sm text-gray-500">
                                Oleh: {{ item.guru?.nama }} &middot;
                                {{
                                    new Date(
                                        item.created_at,
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                        <div
                            class="flex items-center space-x-4"
                            v-if="canCreate"
                        >
                            <Link
                                :href="route('lms.materi.edit', item.id)"
                                class="text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteMateri(item.id)"
                                class="text-sm text-red-600 hover:text-red-900"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </li>
                <li
                    v-if="materi.data.length === 0"
                    class="px-6 py-4 text-center text-gray-500"
                >
                    Belum ada materi pembelajaran.
                </li>
            </ul>

            <!-- Pagination -->
            <div
                class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
                v-if="materi.links && materi.links.length > 3"
            >
                <div class="flex space-x-1">
                    <template
                        v-for="(link, index) in materi.links"
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
</template>
