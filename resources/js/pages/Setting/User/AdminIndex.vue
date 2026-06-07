<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Shield, Trash2, Edit, Plus, X, User } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan',
                href: '#',
            },
            {
                title: 'User Admin',
                href: '/setting/user/admin',
            },
        ],
    },
});

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            username: string | null;
            roles: Array<{ name: string }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    roles: Array<{ id: number; name: string }>;
}>();

import { store, update, destroy } from '@/routes/setting/user/admin';

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    role: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingUserId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (user: any) => {
    isEditing.value = true;
    editingUserId.value = user.id;
    form.reset();
    form.name = user.name;
    form.email = user.email;
    form.username = user.username ?? '';
    form.role = user.roles[0]?.name ?? '';
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditing.value && editingUserId.value) {
        form.put(update.url(editingUserId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(store.url(), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus akun administrator ini?')) {
        form.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="User Admin" />

    <div class="mx-auto max-w-6xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="User Admin & Staf"
                description="Kelola akun Administrator, Operator TU, Proktor, dan Kepala Sekolah yang memiliki akses masuk ke panel kontrol."
            />
            <Button
                @click="openCreateModal"
                class="flex items-center gap-1.5 bg-zinc-900 font-semibold text-white shadow hover:bg-zinc-800"
            >
                <Plus class="h-4 w-4" />
                <span>Tambah Admin</span>
            </Button>
        </div>

        <!-- Table List -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr
                        class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-800/50"
                    >
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Username / Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                >
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-zinc-800/30"
                    >
                        <td
                            class="px-6 py-4 font-medium text-neutral-800 dark:text-neutral-200"
                        >
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4 text-neutral-400" />
                                <span>{{ user.name }}</span>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                        >
                            <div>{{ user.username || '-' }}</div>
                            <div class="text-xs text-neutral-400">
                                {{ user.email }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200"
                            >
                                <Shield class="h-3 w-3 text-zinc-500" />
                                <span>{{
                                    user.roles[0]?.name || 'staff'
                                }}</span>
                            </span>
                        </td>
                        <td class="space-x-1 px-6 py-4 text-right">
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="openEditModal(user)"
                            >
                                <Edit class="h-4 w-4 text-neutral-500" />
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="deleteUser(user.id)"
                                class="text-rose-600 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/20"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td
                            colspan="4"
                            class="px-6 py-12 text-center text-neutral-500"
                        >
                            Belum ada data administrator.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Simple Pagination Links -->
            <div
                v-if="users.links && users.links.length > 3"
                class="flex justify-end gap-1 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <template v-for="link in users.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded px-3 py-1.5 text-xs font-medium transition-colors"
                        :class="
                            link.active
                                ? 'bg-zinc-900 text-white dark:bg-zinc-50 dark:text-zinc-900'
                                : 'border border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'
                        "
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="rounded border border-neutral-200 bg-neutral-100/50 px-3 py-1.5 text-xs font-medium text-neutral-400 dark:border-zinc-800 dark:bg-zinc-800/30"
                    />
                </template>
            </div>
        </div>

        <!-- Modal Dialog Form -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
                >
                    <h3
                        class="font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        {{
                            isEditing ? 'Edit Akun Admin' : 'Tambah Akun Admin'
                        }}
                    </h3>
                    <button
                        @click="isModalOpen = false"
                        class="cursor-pointer text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form
                    @submit.prevent="submit"
                    class="flex-1 space-y-4 overflow-y-auto p-6"
                >
                    <div class="grid gap-2">
                        <Label for="name"
                            >Nama Lengkap
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            placeholder="Nama Lengkap"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email"
                            >Email <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="name@domain.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            v-model="form.username"
                            placeholder="Username untuk login"
                        />
                        <InputError :message="form.errors.username" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role"
                            >Pilih Hak Akses (Role)
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="role"
                            v-model="form.role"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-800"
                        >
                            <option value="">-- Pilih Role --</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password"
                            >Password
                            <span v-if="!isEditing" class="text-rose-500"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            :required="!isEditing"
                            placeholder="Minimal 8 karakter"
                        />
                        <InputError :message="form.errors.password" />
                        <p v-if="isEditing" class="text-xs text-neutral-400">
                            Kosongkan password jika tidak ingin diubah.
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="isModalOpen = false"
                            >Batal</Button
                        >
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800"
                        >
                            {{ isEditing ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
