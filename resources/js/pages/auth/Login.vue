<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Eye, EyeOff, User, Lock, Calendar, AlertCircle } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const page = usePage();
const showPassword = ref(false);

const settingSekolah = computed(() => page.props.setting_sekolah as { nama_sekolah: string; logo: string | null } | null);
const tpAktif = computed(() => page.props.tp_aktif as { id: number; tahun: string } | null);
const smtAktif = computed(() => page.props.smt_aktif as { id: number; nama_smt: string; smt: string } | null);
</script>

<template>
    <Head title="Log In - GarudaCBT" />

    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen bg-slate-50 dark:bg-zinc-950 font-sans antialiased">
        <!-- Panel Kiri: Ilustrasi & Informasi CBT (Desktop Only) -->
        <div class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-12 bg-gradient-to-br from-indigo-950 via-slate-950 to-blue-950 text-white overflow-hidden border-r border-indigo-950/20">
            <!-- Glow Bulat Background -->
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <!-- Grid Background Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>

            <!-- Logo & Brand Header -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600/30 border border-indigo-500/30 backdrop-blur-xs">
                    <svg class="h-6 w-6 text-indigo-400 fill-current" viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3ZM-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400">GarudaCBT</h1>
                    <p class="text-[10px] text-indigo-300 tracking-widest uppercase font-semibold">Computer Based Test</p>
                </div>
            </div>

            <!-- CBT Mockup Ilustrasi -->
            <div class="relative z-10 my-auto flex flex-col items-center">
                <!-- Monitor Mockup -->
                <div class="relative w-full max-w-lg aspect-video bg-slate-900 border border-slate-800 rounded-xl shadow-2xl p-4 flex flex-col justify-between overflow-hidden">
                    <!-- Top bar -->
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2 mb-2">
                        <div class="flex gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <div class="text-[10px] font-medium text-slate-400">Ujian Akhir Semester - Matematika</div>
                        <div class="flex items-center gap-1 bg-rose-500/15 border border-rose-500/35 text-rose-300 px-2 py-0.5 rounded text-[9px] font-bold animate-pulse">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            <span>01:14:52</span>
                        </div>
                    </div>

                    <!-- Content area -->
                    <div class="grid grid-cols-12 gap-3 flex-1 overflow-hidden">
                        <!-- Left pane: Soal -->
                        <div class="col-span-8 bg-slate-950/40 border border-slate-800/80 rounded-lg p-3 text-[10px] leading-relaxed text-slate-300 flex flex-col gap-2">
                            <div class="font-semibold text-slate-400">Soal No. 24</div>
                            <div class="line-clamp-3">Diketahui sebuah segitiga siku-siku dengan panjang alas 6 cm dan tinggi 8 cm. Berapakah panjang sisi miring segitiga tersebut?</div>
                            <div class="mt-1 space-y-1">
                                <div class="flex items-center gap-2 px-2 py-1 rounded border border-slate-800 bg-slate-900/35">
                                    <span class="w-4 h-4 rounded-full border border-slate-700 flex items-center justify-center text-[8px] font-bold">A</span>
                                    <span>9 cm</span>
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1 rounded border border-blue-500/40 bg-blue-500/10 text-blue-300">
                                    <span class="w-4 h-4 rounded-full bg-blue-500 text-white flex items-center justify-center text-[8px] font-bold">B</span>
                                    <span class="font-medium">10 cm</span>
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1 rounded border border-slate-800 bg-slate-900/35">
                                    <span class="w-4 h-4 rounded-full border border-slate-700 flex items-center justify-center text-[8px] font-bold">C</span>
                                    <span>12 cm</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right pane: Navigasi Soal -->
                        <div class="col-span-4 bg-slate-950/40 border border-slate-800/80 rounded-lg p-2 flex flex-col gap-1.5">
                            <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Navigasi Soal</div>
                            <div class="grid grid-cols-4 gap-1">
                                <span v-for="i in 12" :key="i" 
                                      :class="[
                                          'text-[8px] font-bold rounded flex items-center justify-center h-5 transition-colors',
                                          i < 11 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                                          i === 11 ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' :
                                          'bg-slate-800 text-slate-400'
                                      ]">
                                    {{ i }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom bar -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-[9px] text-slate-500 mt-2">
                        <span>Pilihan Ganda</span>
                        <div class="flex gap-1.5">
                            <span class="px-2 py-0.5 rounded bg-slate-850 text-slate-400">Sebelumnya</span>
                            <span class="px-2 py-0.5 rounded bg-blue-650 text-white font-medium">Ragu-Ragu</span>
                            <span class="px-2 py-0.5 rounded bg-emerald-650 text-white font-medium">Selesai</span>
                        </div>
                    </div>
                </div>

                <!-- Floating badges -->
                <div class="absolute -top-6 -right-6 bg-emerald-500/90 text-white font-semibold rounded-lg px-3 py-1.5 shadow-lg flex items-center gap-1.5 text-xs backdrop-blur-md animate-bounce">
                    <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                    <span>Ujian Berjalan Aman</span>
                </div>
                <div class="absolute -bottom-4 -left-6 bg-indigo-600/90 text-white font-medium rounded-lg px-3 py-2 shadow-lg flex flex-col gap-0.5 text-left backdrop-blur-md border border-indigo-500/30">
                    <span class="text-[9px] text-indigo-200">Keamanan Ujian</span>
                    <span class="text-xs font-semibold">Sistem Anti-Cheat Aktif</span>
                </div>
            </div>

            <!-- Footer Panel Kiri -->
            <div class="relative z-10 flex items-center justify-between text-xs text-indigo-300">
                <span>Integrasi Database Sekolah Terpusat</span>
                <span>v3.0.0 • Laravel & Vue 3</span>
            </div>
        </div>

        <!-- Panel Kanan: Form Login -->
        <div class="col-span-1 lg:col-span-5 flex flex-col justify-between p-6 sm:p-10 md:p-16 relative">
            <div class="my-auto flex flex-col justify-center w-full max-w-[420px] mx-auto">
                
                <!-- Status Server & Brand Header (Mobile Only) -->
                <div class="flex lg:hidden items-center justify-between mb-8">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3ZM-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-lg text-foreground">GarudaCBT</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 dark:text-emerald-400 dark:bg-emerald-400/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>SERVER AKTIF</span>
                    </div>
                </div>

                <!-- Main Card Wrapper -->
                <div class="bg-card/70 backdrop-blur-md border border-border/80 shadow-2xl rounded-2xl p-6 sm:p-8 transition-all hover:shadow-indigo-500/5">
                    
                    <!-- Server & Program Badges (Desktop Only) -->
                    <div class="hidden lg:flex justify-between items-center mb-6">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold text-emerald-600 bg-emerald-500/10 dark:text-emerald-400 dark:bg-emerald-400/10 border border-emerald-500/25">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span>SERVER AKTIF</span>
                        </div>
                        <div class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold text-indigo-600 bg-indigo-500/10 dark:text-indigo-400 dark:bg-indigo-400/10 border border-indigo-500/25">
                            <span>ONLINE</span>
                        </div>
                    </div>

                    <!-- School Logo & Header -->
                    <div class="flex flex-col items-center text-center mb-6">
                        <!-- Render School Logo if set, else render beautiful fallback SVG -->
                        <img v-if="settingSekolah?.logo" 
                             :src="settingSekolah.logo" 
                             alt="Logo Sekolah" 
                             class="h-16 w-auto object-contain mb-3 drop-shadow-sm transition-transform hover:scale-105" />
                        <div v-else class="h-16 w-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-3 border border-indigo-100 dark:border-indigo-900/30">
                            <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M12 8v8M9 11h6" />
                            </svg>
                        </div>
                        
                        <h2 class="text-lg font-extrabold text-foreground tracking-tight max-w-full leading-snug line-clamp-2">
                            {{ settingSekolah?.nama_sekolah || 'Nama Sekolah Belum Diatur' }}
                        </h2>

                        <!-- Info Tahun Pelajaran / Semester -->
                        <div class="mt-2.5 text-xs font-semibold text-muted-foreground bg-muted/60 border border-border/60 px-3 py-1 rounded-lg flex items-center gap-1.5">
                            <Calendar class="size-3.5 text-indigo-500 dark:text-indigo-400" />
                            <span>TP: {{ tpAktif?.tahun ?? '2025/2026' }} • Semester {{ smtAktif?.nama_smt ?? 'Ganjil' }}</span>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative flex py-2 items-center text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-4">
                        <div class="flex-grow border-t border-border"></div>
                        <span class="flex-shrink mx-4">Masuk Sesi Ujian</span>
                        <div class="flex-grow border-t border-border"></div>
                    </div>

                    <!-- Status Session Message -->
                    <div v-if="status" class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-center text-xs font-semibold text-green-600 dark:text-green-400">
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-5"
                    >
                        <!-- Input Username / Email -->
                        <div class="grid gap-1.5">
                            <Label for="email" class="text-xs font-semibold text-muted-foreground">Username atau Email</Label>
                            <div class="relative mt-0.5">
                                <User class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground size-4" />
                                <Input
                                    id="email"
                                    type="text"
                                    name="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="username"
                                    placeholder="Masukkan username atau email"
                                    class="pl-10 py-5 text-sm dark:bg-zinc-900/30 focus-visible:ring-indigo-500/30 focus-visible:border-indigo-500 border-border/80"
                                />
                            </div>
                            <InputError :message="errors.email" />
                        </div>

                        <!-- Input Password -->
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-xs font-semibold text-muted-foreground">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium"
                                    :tabindex="5"
                                >
                                    Lupa Password?
                                </TextLink>
                            </div>
                            <div class="relative mt-0.5">
                                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground size-4" />
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Masukkan password Anda"
                                    class="pl-10 pr-10 py-5 text-sm dark:bg-zinc-900/30 focus-visible:ring-indigo-500/30 focus-visible:border-indigo-500 border-border/80"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted-foreground hover:text-foreground focus:outline-none"
                                    :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                    :tabindex="-1"
                                >
                                    <EyeOff v-if="showPassword" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </button>
                            </div>
                            <InputError :message="errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between text-xs mt-1">
                            <Label for="remember" class="flex items-center space-x-2.5 cursor-pointer text-muted-foreground hover:text-foreground">
                                <Checkbox id="remember" name="remember" :tabindex="3" class="border-border/80 text-indigo-600 focus:ring-indigo-500/20" />
                                <span class="font-medium">Ingat saya di perangkat ini</span>
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="mt-2 w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/10 hover:shadow-indigo-600/20 active:scale-98 transition-all cursor-pointer flex items-center justify-center gap-2"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" class="text-white" />
                            <span>{{ processing ? 'Memproses Masuk...' : 'Masuk Sekarang' }}</span>
                        </Button>
                    </Form>

                    <!-- Alert / Advice Box -->
                    <div class="mt-6 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 dark:bg-amber-500/5 dark:border-amber-500/10 text-[11px] text-amber-800 dark:text-amber-300 flex gap-2.5 leading-relaxed">
                        <AlertCircle class="size-4.5 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" />
                        <div>
                            <span class="font-bold">PENTING:</span> Gunakan akun (Username/Email & Password) yang valid sesuai kartu peserta ujian. Hubungi <strong>Proktor</strong> atau <strong>Panitia</strong> jika berkendala.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Panel Kanan -->
            <p class="text-[11px] text-center text-muted-foreground mt-8">
                &copy; {{ new Date().getFullYear() }} {{ settingSekolah?.nama_sekolah || 'GarudaCBT' }} • Hak Cipta Dilindungi
            </p>
        </div>
    </div>
</template>
