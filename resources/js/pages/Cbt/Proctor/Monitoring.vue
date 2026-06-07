<template>
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-zinc-950">
        <!-- Header Monitoring -->
        <header
            class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-indigo-600 p-2 dark:bg-indigo-700">
                    <svg
                        class="h-6 w-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        ></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-neutral-200">
                        Proctor Monitoring
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-neutral-500">
                        Pantau aktivitas ujian siswa secara real-time
                    </p>
                </div>
            </div>
        </header>

        <main
            class="mx-auto w-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- Summary Stats & Info Section -->
            <div v-if="selectedJadwalId" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-2">
                <!-- Info Mapel -->
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-indigo-50 p-2 dark:bg-indigo-950/30">
                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500">Mata Pelajaran</p>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-neutral-200 truncate max-w-[180px]">
                                {{ selectedJadwal?.bank_soal?.mapel?.nama_mapel || '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Guru -->
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-950/30">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500">Guru Pengampu</p>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-neutral-200 truncate max-w-[180px]">
                                {{ selectedJadwal?.bank_soal?.guru?.nama_guru || '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Status -->
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-50 p-2 dark:bg-amber-950/30">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500">Status Monitoring</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-extrabold text-gray-900 dark:text-neutral-200">
                                    {{ filteredSiswaList.length }} / {{ siswaList.length }} Siswa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Soal -->
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-950/30">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500">Total Soal</p>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-neutral-200">
                                {{ totalSoal }} Butir Soal
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <!-- Main Content: Filters, Table, Bulk Actions -->
                <div class="flex flex-col gap-6">
                    
                    <!-- Filter Jadwal dan Kategori -->
                    <div
                        class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <!-- Pilih Jadwal (Primary Action) -->
                            <div :class="selectedJadwalId ? 'md:col-span-4' : 'md:col-span-12'" class="transition-all duration-500">
                                <label class="mb-3 block text-[11px] font-extrabold uppercase tracking-[0.15em] text-indigo-700 dark:text-indigo-400 flex items-center gap-2"
                                    >
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-600 shadow-sm">
                                        <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    Langkah 1: Pilih Jadwal
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="selectedJadwalId"
                                        @change="handleJadwalChange"
                                        class="block w-full appearance-none rounded-xl border-2 border-indigo-100 bg-indigo-50/20 px-4 py-3 text-sm font-bold text-gray-900 shadow-sm transition-all hover:border-indigo-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800/40 dark:text-neutral-100 dark:focus:border-indigo-500 dark:focus:bg-zinc-900"
                                    >
                                        <option value="" class="font-normal text-gray-500">-- Pilih Jadwal Ujian Aktif --</option>
                                        <option
                                            v-for="jadwal in jadwalAktif"
                                            :key="jadwal.id"
                                            :value="jadwal.id"
                                        >
                                            {{ jadwal.bank_soal?.mapel?.nama_mapel }} - {{ jadwal.bank_soal?.nama }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Grouped Filters (Secondary Actions) -->
                            <div class="md:col-span-8" v-if="selectedJadwalId">
                                <label class="mb-3 block text-[11px] font-extrabold uppercase tracking-[0.15em] text-gray-500 dark:text-neutral-500 flex items-center gap-2"
                                    >
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-800">
                                        <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                        </svg>
                                    </div>
                                    Langkah 2: Saring Data (Opsional)
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 rounded-xl border border-gray-100 bg-gray-50/40 p-3 dark:border-zinc-800 dark:bg-zinc-800/20">
                                    <!-- Filter Kelas -->
                                    <div>
                                        <select
                                            v-model="selectedKelasId"
                                            class="block w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-neutral-300"
                                        >
                                            <option value="">Semua Kelas</option>
                                            <option
                                                v-for="kelas in kelasList"
                                                :key="kelas.id"
                                                :value="kelas.id"
                                            >
                                                {{ kelas.nama_kelas }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Filter Sesi -->
                                    <div>
                                        <select
                                            v-model="selectedSesiId"
                                            class="block w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-neutral-300"
                                        >
                                            <option value="">Semua Sesi</option>
                                            <option
                                                v-for="sesi in sesiList"
                                                :key="sesi.id"
                                                :value="sesi.id"
                                            >
                                                {{ sesi.nama_sesi }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Filter Status -->
                                    <div>
                                        <select
                                            v-model="selectedStatus"
                                            class="block w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-neutral-300"
                                        >
                                            <option value="">Semua Status</option>
                                            <option value="0">Belum Mulai</option>
                                            <option value="1">Sedang Ujian</option>
                                            <option value="2">Selesai</option>
                                        </select>
                                    </div>

                                    <!-- Cari Siswa -->
                                    <div class="relative">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            placeholder="Nama / NIS..."
                                            class="block w-full rounded-lg border-gray-200 bg-white pl-8 pr-3 py-2 text-xs font-bold text-gray-700 shadow-sm transition-all placeholder:font-medium placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-neutral-300"
                                        />
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5">
                                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Refresh Indicator & Manual Actions -->
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-xs text-gray-500 dark:text-neutral-450" v-if="selectedJadwalId">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="inline-block h-2 w-2 rounded-full"
                                    :class="isRefreshing ? 'bg-amber-500 animate-pulse' : 'bg-green-500'"
                                ></span>
                                <span>Auto-refresh setiap 10 detik. {{ isRefreshing ? 'Menyegarkan data...' : 'Monitoring real-time aktif.' }}</span>
                            </div>
                            <button
                                @click="loadStatusSiswa"
                                class="rounded-lg bg-indigo-50 px-3.5 py-1.5 font-medium text-indigo-700 transition-colors hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:hover:bg-indigo-950/50"
                            >
                                Segarkan Sekarang
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions Bar -->
                    <div
                        v-if="selectedJadwalId && selectedSiswaIds.length > 0"
                        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-indigo-200 bg-indigo-50/80 p-4 shadow-sm dark:border-indigo-900/50 dark:bg-indigo-950/30"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 shadow-md">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-700 dark:text-indigo-400">Aksi Terpilih</p>
                                <p class="text-sm font-black text-indigo-950 dark:text-indigo-100">
                                    {{ selectedSiswaIds.length }} Siswa <span class="font-normal text-indigo-700/70">Telah Ditandai</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <select
                                v-model="bulkActionSelected"
                                class="rounded-lg border-2 border-indigo-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-xs font-bold text-gray-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-neutral-200 py-2 px-4"
                            >
                                <option value="">-- Pilih Aksi Massal --</option>
                                <option value="reset">RESET IZIN & WAKTU (Reset Login)</option>
                                <option value="force-selesai">PAKSA SELESAI</option>
                                <option value="ulang">ULANG UJIAN DARI AWAL</option>
                            </select>
                            <button
                                @click="applyBulkAction"
                                :disabled="!bulkActionSelected || isBulkProcessing"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-xs font-black uppercase tracking-widest text-white shadow-md transition-all hover:bg-indigo-700 active:scale-95 disabled:opacity-50 cursor-pointer flex items-center gap-2"
                            >
                                <svg
                                    v-if="isBulkProcessing"
                                    class="h-4 w-4 animate-spin text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span>Eksekusi</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Status Siswa -->
                    <div
                        v-if="selectedJadwalId"
                        class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800 border-collapse">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-center border border-gray-200 dark:border-zinc-800">
                                            <input
                                                type="checkbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="rounded border-gray-300 text-indigo-650 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                                            />
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            No.
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            No. Peserta
                                        </th>
                                        <th rowspan="2" scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Nama
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Kelas
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Sesi
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Ruang
                                        </th>
                                        <th colspan="2" scope="col" class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Status
                                        </th>
                                        <th rowspan="2" scope="col" class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Reset Waktu
                                        </th>
                                        <th colspan="3" scope="col" class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Aksi
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Mulai
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Durasi
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Reset Izin
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Paksa Selesai
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            Ulang
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    <tr v-if="isLoading && !siswaList.length">
                                        <td
                                            colspan="13"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800"
                                        >
                                            Memuat data...
                                        </td>
                                    </tr>
                                    <tr v-else-if="!siswaList.length">
                                        <td
                                            colspan="13"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800"
                                        >
                                            Belum ada siswa yang login ke jadwal ini.
                                        </td>
                                    </tr>
                                    <tr v-else-if="siswaList.length > 0 && !filteredSiswaList.length">
                                        <td
                                            colspan="13"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800"
                                        >
                                            Tidak ada data siswa yang cocok dengan filter.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(siswa, index) in filteredSiswaList"
                                        :key="siswa.id"
                                        class="transition-colors hover:bg-gray-50 dark:hover:bg-zinc-800/50"
                                    >
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <input
                                                type="checkbox"
                                                v-model="selectedSiswaIds"
                                                :value="siswa.id"
                                                class="rounded border-gray-300 text-indigo-650 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800"
                                            />
                                        </td>
                                        <td class="px-3 py-4 text-center text-sm text-gray-500 dark:text-neutral-405 border border-gray-200 dark:border-zinc-800">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-3 py-4 text-sm font-semibold text-gray-900 dark:text-neutral-200 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.nisn || siswa.nis || '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-950 dark:text-neutral-200 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.nama }}
                                        </td>
                                        <td class="px-3 py-4 text-sm font-medium text-gray-950 dark:text-neutral-200 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.nama_kelas }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.nama_sesi }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.nama_ruang }}
                                        </td>
                                        <td class="px-3 py-4 text-center text-sm text-gray-500 dark:text-neutral-400 border border-gray-200 dark:border-zinc-800">
                                            {{ siswa.status > 0 ? formatTime(siswa.mulai) : '-' }}
                                        </td>
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <div class="flex flex-col items-center gap-1 min-w-[95px]">
                                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-zinc-700 overflow-hidden">
                                                    <div
                                                        class="h-full bg-indigo-600 dark:bg-indigo-500"
                                                        :style="{ width: calculateProgress(siswa) + '%' }"
                                                    ></div>
                                                </div>
                                                <span class="text-[10px] font-semibold text-gray-600 dark:text-neutral-400">
                                                    {{ siswa.dijawab }}/{{ siswa.total_soal }} Soal
                                                </span>
                                            </div>
                                        </td>
                                        <!-- Reset Waktu -->
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <button
                                                v-if="siswa.status > 0"
                                                @click="resetLogin(siswa.id, siswa.nama)"
                                                class="rounded bg-amber-50 px-2 py-1.5 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-100 hover:text-amber-900 dark:bg-amber-950/30 dark:text-amber-400 dark:hover:bg-amber-950/50 cursor-pointer"
                                                title="Reset Waktu"
                                            >
                                                Reset
                                            </button>
                                            <span v-else class="text-xs text-gray-400 dark:text-neutral-600">-</span>
                                        </td>
                                        <!-- Reset Izin -->
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <button
                                                v-if="siswa.status > 0"
                                                @click="resetLogin(siswa.id, siswa.nama)"
                                                class="rounded bg-amber-50 px-2 py-1.5 text-xs font-semibold text-amber-600 transition-colors hover:bg-amber-100 hover:text-amber-900 dark:bg-amber-950/30 dark:text-amber-400 dark:hover:bg-amber-950/50 cursor-pointer"
                                                title="Reset Izin"
                                            >
                                                Reset
                                            </button>
                                            <span v-else class="text-xs text-gray-400 dark:text-neutral-600">-</span>
                                        </td>
                                        <!-- Paksa Selesai -->
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <button
                                                v-if="siswa.status === 1"
                                                @click="forceSelesai(siswa.id, siswa.nama)"
                                                class="rounded bg-red-50 px-2 py-1.5 text-xs font-semibold text-red-650 transition-colors hover:bg-red-100 hover:text-red-900 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
                                                title="Paksa Selesai"
                                            >
                                                Selesai
                                            </button>
                                            <span v-else class="text-xs text-gray-400 dark:text-neutral-600">-</span>
                                        </td>
                                        <!-- Ulang -->
                                        <td class="px-3 py-4 text-center border border-gray-200 dark:border-zinc-800">
                                            <button
                                                v-if="siswa.status > 0"
                                                @click="ulangUjian(siswa.id, siswa.nama)"
                                                class="rounded bg-rose-50 px-2 py-1.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-100 hover:text-rose-950 dark:bg-rose-950/30 dark:text-rose-450 dark:hover:bg-rose-950/50 cursor-pointer"
                                                title="Ulang"
                                            >
                                                Ulang
                                            </button>
                                            <span v-else class="text-xs text-gray-400 dark:text-neutral-600">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-gray-100 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400 dark:text-neutral-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-neutral-200">
                            Belum Ada Jadwal Terpilih
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                            Silakan pilih jadwal dari dropdown di atas untuk memulai
                            monitoring.
                        </p>
                    </div>

                    <!-- Petunjuk Aksi (Subtle Footer) -->
                    <div
                        v-if="selectedJadwalId"
                        class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 text-xs"
                    >
                        <h3 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-1.5">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <span>Petunjuk Monitoring</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-gray-600 dark:text-neutral-350 leading-relaxed text-[11px]">
                            <ul class="space-y-2">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span>Gunakan tombol <strong>Refresh</strong> untuk merefresh halaman secara manual jika dibutuhkan.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span><strong>RESET WAKTU</strong>. Digunakan jika siswa logout/terputus dan waktu pengerjaan habis di sistem.</span>
                                </li>
                            </ul>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span><strong>RESET IZIN</strong>. Mengizinkan siswa login kembali jika terdeteksi ganti perangkat atau browser.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span><strong>PAKSA SELESAI</strong>. Menghentikan ujian siswa seketika dan menghitung nilainya.</span>
                                </li>
                            </ul>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span><strong>ULANG</strong>. Menghapus seluruh progres pengerjaan siswa dan memulai dari awal.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-indigo-500 font-bold">•</span>
                                    <span><strong>Aksi Massal</strong>. Mempermudah eksekusi aksi yang sama ke banyak siswa sekaligus.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
    status as statusSiswa,
    reset as resetLoginSiswa,
    forceSelesai as forceSelesaiUjian,
} from '@/routes/cbt/monitoring';

const props = defineProps({
    jadwalAktif: Array,
    kelasList: Array,
    sesiList: Array,
});

const selectedJadwalId = ref('');
const selectedKelasId = ref('');
const selectedSesiId = ref('');
const selectedStatus = ref('');
const searchQuery = ref('');

const selectedSiswaIds = ref([]);
const bulkActionSelected = ref('');
const isBulkProcessing = ref(false);

const siswaList = ref([]);
const isLoading = ref(false);
const isRefreshing = ref(false);
let refreshInterval = null;

const formatTime = (timeStr) => {
    if (!timeStr) return '-';
    // Remove seconds for cleaner display if it's full time string
    return timeStr.substring(0, 5);
};

const calculateProgress = (siswa) => {
    if (siswa.total_soal === 0) return 0;
    return (siswa.dijawab / siswa.total_soal) * 100;
};

const handleJadwalChange = () => {
    selectedKelasId.value = '';
    selectedSesiId.value = '';
    selectedStatus.value = '';
    searchQuery.value = '';
    selectedSiswaIds.value = [];
    loadStatusSiswa();
};

const loadStatusSiswa = async () => {
    if (!selectedJadwalId.value) {
        siswaList.value = [];
        return;
    }

    if (siswaList.value.length === 0) isLoading.value = true;
    else isRefreshing.value = true;

    try {
        const response = await fetch(
            statusSiswa({ jadwal: selectedJadwalId.value }).url,
            {
                headers: { Accept: 'application/json' },
            },
        );
        const result = await response.json();
        siswaList.value = result.data;
    } catch (e) {
        console.error('Gagal memuat status siswa', e);
    } finally {
        isLoading.value = false;
        setTimeout(() => {
            isRefreshing.value = false;
        }, 500); // Visual delay for refresh indicator
    }
};

const resetLogin = async (durasiId, namaSiswa) => {
    if (
        !confirm(
            `Yakin ingin mereset login untuk ${namaSiswa}? Sesi perangkat siswa akan dibebaskan (Reset Izin) dan waktu pengerjaan akan disesuaikan. Lanjutkan?`,
        )
    ) {
        return;
    }

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');
        const response = await fetch(
            resetLoginSiswa({ durasi: durasiId }).url,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            loadStatusSiswa(); // Refresh table
            selectedSiswaIds.value = selectedSiswaIds.value.filter(id => id !== durasiId);
        } else {
            alert('Gagal mereset login.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

const forceSelesai = async (durasiId, namaSiswa) => {
    if (
        !confirm(
            `PERINGATAN: Ujian ${namaSiswa} akan diselesaikan secara paksa dan nilainya akan dihitung sekarang. Lanjutkan?`,
        )
    ) {
        return;
    }

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');
        const response = await fetch(
            forceSelesaiUjian({ durasi: durasiId }).url,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            loadStatusSiswa(); // Refresh table
            selectedSiswaIds.value = selectedSiswaIds.value.filter(id => id !== durasiId);
        } else {
            alert('Gagal menyelesaikan ujian secara paksa.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

const ulangUjian = async (durasiId, namaSiswa) => {
    if (
        !confirm(
            `PERINGATAN KRITIS: Semua jawaban dan durasi ujian untuk ${namaSiswa} akan DIHAPUS PERMANEN dan ujian akan diulang dari awal. Lanjutkan?`,
        )
    ) {
        return;
    }

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');
        const response = await fetch(
            `/cbt/monitoring/durasi/${durasiId}/ulang`,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            loadStatusSiswa(); // Refresh table
            selectedSiswaIds.value = selectedSiswaIds.value.filter(id => id !== durasiId);
        } else {
            alert('Gagal mengulang ujian.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

const applyBulkAction = async () => {
    if (!bulkActionSelected.value || selectedSiswaIds.value.length === 0) return;

    let actionLabel = '';
    if (bulkActionSelected.value === 'reset') actionLabel = 'RESET IZIN & WAKTU (Reset Login)';
    else if (bulkActionSelected.value === 'force-selesai') actionLabel = 'PAKSA SELESAI';
    else if (bulkActionSelected.value === 'ulang') actionLabel = 'ULANG UJIAN DARI AWAL (Menghapus Semua Jawaban)';

    if (
        !confirm(
            `Yakin ingin menerapkan aksi "${actionLabel}" ke ${selectedSiswaIds.value.length} siswa terpilih?`,
        )
    ) {
        return;
    }

    isBulkProcessing.value = true;
    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');

        const response = await fetch(
            '/cbt/monitoring/bulk-action',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    ids: selectedSiswaIds.value,
                    action: bulkActionSelected.value,
                }),
            },
        );

        if (response.ok) {
            alert('Aksi massal berhasil diterapkan!');
            selectedSiswaIds.value = [];
            bulkActionSelected.value = '';
            loadStatusSiswa(); // Refresh table
        } else {
            const data = await response.json();
            alert(data.message || 'Gagal menerapkan aksi massal.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        isBulkProcessing.value = false;
    }
};

const selectedJadwal = computed(() => {
    return props.jadwalAktif.find(j => j.id === Number(selectedJadwalId.value));
});

const totalSoal = computed(() => {
    if (!selectedJadwal.value?.bank_soal) return 0;
    const bs = selectedJadwal.value.bank_soal;
    return (bs.tampil_pg || 0) + (bs.tampil_esai || 0) + (bs.tampil_kompleks || 0) + (bs.tampil_jodohkan || 0) + (bs.tampil_isian || 0);
});

const filteredSiswaList = computed(() => {
    return siswaList.value.filter((siswa) => {
        // Filter by Kelas
        if (selectedKelasId.value && siswa.kelas_id !== Number(selectedKelasId.value)) {
            return false;
        }
        
        // Filter by Sesi
        if (selectedSesiId.value && siswa.sesi_id !== Number(selectedSesiId.value)) {
            return false;
        }

        // Filter by Status
        if (selectedStatus.value !== '' && siswa.status !== Number(selectedStatus.value)) {
            return false;
        }

        // Filter by Search Query (Nama or NIS)
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            const namaMatch = siswa.nama ? siswa.nama.toLowerCase().includes(query) : false;
            const nisMatch = siswa.nis ? siswa.nis.toLowerCase().includes(query) : false;
            if (!namaMatch && !nisMatch) {
                return false;
            }
        }

        return true;
    });
});

const isAllSelected = computed(() => {
    return filteredSiswaList.value.length > 0 && selectedSiswaIds.value.length === filteredSiswaList.value.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedSiswaIds.value = filteredSiswaList.value.map(s => s.id);
    } else {
        selectedSiswaIds.value = [];
    }
};

onMounted(() => {
    // Auto refresh every 10 seconds if a jadwal is selected
    refreshInterval = setInterval(() => {
        if (selectedJadwalId.value) {
            loadStatusSiswa();
        }
    }, 10000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
