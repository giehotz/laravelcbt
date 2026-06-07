<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    materi: Object,
    hasRead: Boolean,
});

const user = usePage().props.auth.user;

onMounted(() => {
    // Only log baca if the user is a siswa and hasn't read it yet (to avoid unnecessary requests)
    if (user.roles.some((r) => r.name === 'siswa') && !props.hasRead) {
        axios
            .post(route('lms.materi.log-baca', props.materi.id))
            .catch((err) => console.error('Failed to log read status:', err));
    }
});

const getYoutubeId = (url) => {
    if (!url) return null;
    const regExp =
        /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
};

const youtubeId = computed(() => getYoutubeId(props.materi.youtube));

const getFileName = (path) => {
    return path.split('/').pop();
};
</script>

<template>
    <Head :title="materi.judul" />

    <div class="mx-auto max-w-4xl py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <Link
                :href="route('lms.materi.index')"
                class="font-medium text-gray-500 hover:text-gray-700"
            >
                &larr; Kembali ke Daftar Materi
            </Link>
        </div>

        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-2xl leading-6 font-bold text-gray-900">
                    {{ materi.judul }}
                </h3>
                <p class="mt-2 max-w-2xl text-sm text-gray-500">
                    Mata Pelajaran:
                    <span class="font-semibold text-gray-700">{{
                        materi.mapel?.nama_mapel
                    }}</span>
                    &middot; Oleh:
                    <span class="font-semibold text-gray-700">{{
                        materi.guru?.nama
                    }}</span>
                    &middot;
                    {{ new Date(materi.created_at).toLocaleDateString() }}
                </p>
            </div>

            <!-- Video YouTube -->
            <div
                v-if="youtubeId"
                class="flex w-full justify-center bg-gray-900"
            >
                <iframe
                    class="aspect-video w-full max-w-4xl"
                    :src="`https://www.youtube.com/embed/${youtubeId}`"
                    title="YouTube video player"
                    frameborder="0"
                    allow="
                        accelerometer;
                        autoplay;
                        clipboard-write;
                        encrypted-media;
                        gyroscope;
                        picture-in-picture;
                    "
                    allowfullscreen
                >
                </iframe>
            </div>

            <div
                class="prose max-w-none px-4 py-5 whitespace-pre-wrap text-gray-800 sm:p-6"
            >
                {{ materi.deskripsi }}
            </div>

            <!-- Lampiran File -->
            <div
                v-if="materi.file && materi.file.length > 0"
                class="border-t border-gray-200 px-4 py-5 sm:px-6"
            >
                <h4 class="mb-4 text-lg font-medium text-gray-900">
                    Lampiran File
                </h4>
                <ul
                    role="list"
                    class="divide-y divide-gray-200 rounded-md border border-gray-200"
                >
                    <li
                        v-for="(file, index) in materi.file"
                        :key="index"
                        class="flex items-center justify-between py-3 pr-4 pl-3 text-sm"
                    >
                        <div class="flex w-0 flex-1 items-center">
                            <svg
                                class="h-5 w-5 flex-shrink-0 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="ml-2 w-0 flex-1 truncate">
                                {{ getFileName(file) }}
                            </span>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <a
                                :href="'/storage/' + file"
                                target="_blank"
                                download
                                class="font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Download
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
