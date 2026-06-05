<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header Ujian -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-10 px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ jadwal.bank_soal?.mapel?.nama_mapel || 'Ujian' }}</h1>
                    <p class="text-sm text-gray-500">{{ jadwal.bank_soal?.nama }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div v-if="!examStore.isOnline" class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    Offline (Menunggu Koneksi)
                </div>
                <div v-if="examStore.hasOfflineQueue" class="text-amber-600 text-sm font-medium">
                    Menyimpan...
                </div>
                
                <!-- Sisa Waktu (Placeholder, need actual implementation based on DurasiSiswa) -->
                <div class="bg-gray-900 text-white px-4 py-2 rounded-lg font-mono text-lg font-bold">
                    {{ remainingTimeDisplay }}
                </div>
            </div>
        </header>

        <div class="flex-1 flex flex-col lg:flex-row max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 gap-6 relative">
            
            <!-- Area Soal -->
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 min-h-[500px] flex flex-col">
                <div v-if="isLoading" class="flex-1 flex justify-center items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>
                <div v-else-if="activeSoal" class="flex-1 flex flex-col">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <span class="text-lg font-bold text-gray-800">Soal Nomor {{ activeSoal.no_soal_alias }}</span>
                        <label class="flex items-center gap-2 cursor-pointer bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
                            <input type="checkbox" v-model="isRaguRagu" @change="saveAnswer" class="rounded text-amber-500 focus:ring-amber-500 border-amber-300">
                            <span class="text-sm font-medium text-amber-700">Ragu-ragu</span>
                        </label>
                    </div>

                    <div class="prose max-w-none text-gray-800 mb-8 flex-1" v-html="activeSoal.soal.soal"></div>

                    <!-- Opsi Jawaban (Jika PG) -->
                    <div v-if="activeSoal.jenis_soal === 1" class="space-y-3 mt-auto">
                        <label v-for="(aliasOpsi, originalOpsi) in pgOptionsMapping" :key="originalOpsi" 
                            class="flex items-start gap-4 p-4 rounded-xl border-2 transition-all cursor-pointer hover:bg-gray-50"
                            :class="activeSoal.jawaban_siswa === aliasOpsi ? 'border-blue-500 bg-blue-50 hover:bg-blue-50' : 'border-gray-100'">
                            
                            <div class="pt-1">
                                <input type="radio" :value="aliasOpsi" v-model="selectedAnswer" @change="saveAnswer" 
                                    class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 mt-0.5">
                            </div>
                            <div class="font-medium text-lg min-w-[24px]">{{ aliasOpsi }}.</div>
                            <div class="flex-1 prose max-w-none text-gray-700" v-html="activeSoal.soal[`file_${originalOpsi.toLowerCase()}`] || activeSoal.soal[`opsi_${originalOpsi.toLowerCase()}`]"></div>
                        </label>
                    </div>

                    <!-- Opsi Jawaban (Jika PG Kompleks) -->
                    <div v-else-if="activeSoal.jenis_soal === 2" class="space-y-3 mt-auto">
                        <label v-for="(aliasOpsi, originalOpsi) in pgOptionsMapping" :key="originalOpsi" 
                            class="flex items-start gap-4 p-4 rounded-xl border-2 transition-all cursor-pointer hover:bg-gray-50"
                            :class="selectedComplexAnswers.includes(aliasOpsi) ? 'border-blue-500 bg-blue-50 hover:bg-blue-50' : 'border-gray-100'">
                            
                            <div class="pt-1">
                                <input type="checkbox" :value="aliasOpsi" :checked="selectedComplexAnswers.includes(aliasOpsi)" @change="toggleStudentComplexAnswer(aliasOpsi)" 
                                    class="w-5 h-5 rounded text-blue-600 border-gray-300 focus:ring-blue-500 mt-0.5 cursor-pointer">
                            </div>
                            <div class="font-medium text-lg min-w-[24px]">{{ aliasOpsi }}.</div>
                            <div class="flex-1 prose max-w-none text-gray-700" v-html="activeSoal.soal[`file_${originalOpsi.toLowerCase()}`] || activeSoal.soal[`opsi_${originalOpsi.toLowerCase()}`]"></div>
                        </label>
                    </div>

                    <!-- Opsi Jawaban (Jika Menjodohkan) -->
                    <div v-else-if="activeSoal.jenis_soal === 3" class="mt-auto">
                        <p class="text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-100 rounded-lg p-3 mb-6 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full animate-ping"></span>
                            Cara menjawab: Klik satu baris di kolom kiri, lalu klik baris pasangannya di kolom kanan.
                        </p>

                        <div ref="studentContainerRef" class="relative grid grid-cols-12 gap-8 p-4 border border-gray-100 rounded-xl bg-gray-50/50">
                            <!-- Connecting SVG Overlay -->
                            <svg class="absolute inset-0 w-full h-full pointer-events-none z-20">
                                <defs>
                                    <marker 
                                        v-for="(line, idx) in studentLines" 
                                        :key="'marker-'+idx"
                                        :id="'student-arrow-'+idx" 
                                        viewBox="0 0 10 10" 
                                        refX="6" 
                                        refY="5" 
                                        markerWidth="6" 
                                        markerHeight="6" 
                                        orient="auto-start-reverse"
                                    >
                                        <path d="M 0 2 L 8 5 L 0 8 z" :fill="line.color" />
                                    </marker>
                                </defs>
                                <path 
                                    v-for="(line, idx) in studentLines" 
                                    :key="'line-'+idx"
                                    :d="line.d" 
                                    fill="none" 
                                    :stroke="line.color" 
                                    stroke-width="2.5"
                                    :marker-end="'url(#student-arrow-'+idx+')'"
                                    class="opacity-80"
                                />
                            </svg>

                            <!-- Left Column: Statements -->
                            <div class="col-span-5 space-y-3 z-10">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kolom Kiri</div>
                                <div 
                                    v-for="(item, idx) in matchingLeftItems" 
                                    :key="'left-'+item.id" 
                                    @click="handleLeftItemClick(item.id)"
                                    class="flex items-center justify-between p-4 bg-white border-2 rounded-lg shadow-sm relative min-h-[50px] transition-all hover:shadow-md cursor-pointer"
                                    :class="[
                                        selectedLeftId === item.id ? 'border-blue-500 ring-2 ring-blue-100 bg-blue-50/30' : 'border-gray-200',
                                        studentConnections[item.id] !== undefined ? 'border-emerald-200 bg-emerald-50/10' : ''
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100 shrink-0">
                                            {{ idx + 1 }}
                                        </span>
                                        <div class="text-sm font-medium text-gray-700 leading-relaxed prose max-w-none" v-html="item.text"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            v-if="studentConnections[item.id] !== undefined"
                                            @click.stop="removeConnection(item.id)"
                                            class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                                            title="Hapus Koneksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        <!-- Connector Dot -->
                                        <div 
                                            :class="['student-left-dot-' + item.id]"
                                            class="w-3.5 h-3.5 rounded-full bg-blue-500 border-2 border-white shadow-md cursor-pointer hover:scale-110 transition-transform"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Column: Spacing for SVG Lines -->
                            <div class="col-span-2 pointer-events-none"></div>

                            <!-- Right Column: Choices -->
                            <div class="col-span-5 space-y-3 z-10">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kolom Kanan</div>
                                <div 
                                    v-for="(item, idx) in matchingRightItems" 
                                    :key="'right-'+item.id" 
                                    @click="handleRightItemClick(item.id)"
                                    class="flex items-center p-4 bg-white border-2 rounded-lg shadow-sm relative min-h-[50px] transition-all hover:shadow-md cursor-pointer"
                                    :class="[
                                        selectedLeftId !== null ? 'border-blue-200 hover:border-blue-400' : 'border-gray-200'
                                    ]"
                                >
                                    <!-- Connector Dot -->
                                    <div 
                                        :class="['student-right-dot-' + item.id]"
                                        class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-md absolute -left-[7px] top-1/2 -translate-y-1/2 cursor-pointer hover:scale-110 transition-transform"
                                    ></div>
                                    <div class="flex items-center gap-3 pl-3">
                                        <span class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-100 shrink-0">
                                            {{ String.fromCharCode(65 + idx) }}
                                        </span>
                                        <div class="text-sm font-medium text-gray-700 leading-relaxed prose max-w-none" v-html="item.text"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Soal Sidebar -->
            <div class="w-full lg:w-80 shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center justify-between">
                        Navigasi Soal
                        <span class="text-xs font-normal bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ examStore.unansweredCount }} belum dijawab</span>
                    </h3>
                    
                    <div class="grid grid-cols-5 gap-2">
                        <button v-for="soal in examStore.soalList" :key="soal.id"
                            @click="examStore.setActiveSoal(soal.id)"
                            class="aspect-square flex items-center justify-center text-sm font-bold rounded-lg border-2 transition-colors relative"
                            :class="[
                                soal.id === examStore.activeSoalId ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600',
                                soal.jawaban_siswa && !soal.ragu_ragu ? 'bg-blue-600 border-blue-600 text-white' : '',
                                soal.jawaban_siswa && soal.ragu_ragu ? 'bg-amber-400 border-amber-400 text-white' : ''
                            ]"
                        >
                            {{ soal.no_soal_alias }}
                            <div v-if="soal.ragu_ragu" class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full border-2 border-white"></div>
                        </button>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <button @click="confirmSelesai" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Selesai Ujian
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useExamStore } from '@/stores/exam';
import { mulai, soal, selesai } from '@/routes/ujian';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    jadwal: Object,
});

const examStore = useExamStore();
const isLoading = ref(true);
const selectedAnswer = ref(null);
const selectedComplexAnswers = ref([]);
const isRaguRagu = ref(false);

const remainingTimeDisplay = ref('00:00:00');

const activeSoal = computed(() => examStore.activeSoal);

// Helper to safely parse JSON answers for Ganda Kompleks and Menjodohkan
const parseJawaban = (raw) => {
    if (!raw) return [];
    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

// Mapping original A,B,C,D,E to what user sees (alias)
const pgOptionsMapping = computed(() => {
    if (!activeSoal.value) return {};
    const s = activeSoal.value;
    const map = {};
    if (s.opsi_alias_a) map['A'] = s.opsi_alias_a;
    if (s.opsi_alias_b) map['B'] = s.opsi_alias_b;
    if (s.opsi_alias_c) map['C'] = s.opsi_alias_c;
    if (s.opsi_alias_d) map['D'] = s.opsi_alias_d;
    if (s.opsi_alias_e) map['E'] = s.opsi_alias_e;
    
    // Sort keys by alias value (A, B, C, D, E)
    return Object.fromEntries(
        Object.entries(map).sort(([, a], [, b]) => a.localeCompare(b))
    );
});

// Reset local v-model whenever active soal changes
watch(activeSoal, (newSoal) => {
    if (newSoal) {
        isRaguRagu.value = newSoal.ragu_ragu;
        if (newSoal.jenis_soal === 2) {
            selectedComplexAnswers.value = parseJawaban(newSoal.jawaban_siswa);
            selectedAnswer.value = selectedComplexAnswers.value.length > 0 ? JSON.stringify(selectedComplexAnswers.value) : null;
        } else {
            selectedAnswer.value = newSoal.jawaban_siswa;
            selectedComplexAnswers.value = [];
        }
    }
});

const toggleStudentComplexAnswer = (aliasOpsi) => {
    const current = [...selectedComplexAnswers.value];
    const idx = current.indexOf(aliasOpsi);
    if (idx > -1) {
        current.splice(idx, 1);
    } else {
        current.push(aliasOpsi);
        current.sort();
    }
    selectedComplexAnswers.value = current;
    // Serialize to JSON string for saving, or null if nothing is selected
    selectedAnswer.value = current.length > 0 ? JSON.stringify(current) : null;
    saveAnswer();
};

// Interactive matching (Menjodohkan) state & methods
const selectedLeftId = ref(null);
const studentConnections = ref({});
const studentContainerRef = ref(null);
const studentLines = ref([]);

const matchingLeftItems = computed(() => {
    if (!activeSoal.value || activeSoal.value.jenis_soal !== 3) return [];
    return activeSoal.value.soal?.matching_left || [];
});

const matchingRightItems = computed(() => {
    if (!activeSoal.value || activeSoal.value.jenis_soal !== 3) return [];
    return activeSoal.value.soal?.matching_right || [];
});

watch(() => activeSoal.value?.id, (newId) => {
    selectedLeftId.value = null;
    studentConnections.value = {};
    studentLines.value = [];
    
    if (activeSoal.value && activeSoal.value.jenis_soal === 3 && activeSoal.value.jawaban_siswa) {
        try {
            const savedAns = JSON.parse(activeSoal.value.jawaban_siswa);
            if (Array.isArray(savedAns)) {
                savedAns.forEach(conn => {
                    const kiriId = conn.kiri_id;
                    const kananId = conn.kanan_id;
                    if (kiriId !== undefined && kananId !== undefined) {
                        studentConnections.value[kiriId] = kananId;
                    }
                });
            }
        } catch (e) {
            console.error("Gagal memuat jawaban menjodohkan", e);
        }
    }
}, { immediate: true });

const updateStudentLines = () => {
    if (!studentContainerRef.value) return;
    const containerRect = studentContainerRef.value.getBoundingClientRect();
    const tempLines = [];
    
    const colors = [
        '#3B82F6', // Blue
        '#10B981', // Emerald
        '#8B5CF6', // Violet
        '#F59E0B', // Amber
        '#EC4899', // Pink
        '#06B6D4', // Cyan
        '#EF4444', // Red
    ];
    
    Object.entries(studentConnections.value).forEach(([leftIdxStr, rightIdx]) => {
        const leftIdx = parseInt(leftIdxStr);
        const leftEl = studentContainerRef.value?.querySelector(`.student-left-dot-${leftIdx}`);
        const rightEl = studentContainerRef.value?.querySelector(`.student-right-dot-${rightIdx}`);
        
        if (leftEl && rightEl) {
            const leftRect = leftEl.getBoundingClientRect();
            const rightRect = rightEl.getBoundingClientRect();
            
            const x1 = leftRect.left + leftRect.width / 2 - containerRect.left;
            const y1 = leftRect.top + leftRect.height / 2 - containerRect.top;
            
            const x2 = rightRect.left + rightRect.width / 2 - containerRect.left;
            const y2 = rightRect.top + rightRect.height / 2 - containerRect.top;
            
            const controlX = x1 + (x2 - x1) / 2;
            const d = `M ${x1} ${y1} C ${controlX} ${y1}, ${controlX} ${y2}, ${x2} ${y2}`;
            const color = colors[leftIdx % colors.length];
            
            tempLines.push({ d, color });
        }
    });
    
    studentLines.value = tempLines;
};

// Update lines when connections change
watch(studentConnections, () => {
    nextTick(() => {
        setTimeout(updateStudentLines, 50);
    });
}, { deep: true });

// Mutation observer for student container to handle size updates
let studentObserver = null;
watch(studentContainerRef, (newRef) => {
    if (newRef) {
        if (studentObserver) studentObserver.disconnect();
        studentObserver = new MutationObserver(updateStudentLines);
        studentObserver.observe(newRef, { childList: true, subtree: true, attributes: true });
        setTimeout(updateStudentLines, 200);
    } else {
        if (studentObserver) {
            studentObserver.disconnect();
            studentObserver = null;
        }
    }
});

const handleLeftItemClick = (leftId) => {
    if (selectedLeftId.value === leftId) {
        selectedLeftId.value = null;
    } else {
        selectedLeftId.value = leftId;
    }
};

const handleRightItemClick = (rightId) => {
    if (selectedLeftId.value === null) return;
    
    const leftId = selectedLeftId.value;
    studentConnections.value[leftId] = rightId;
    selectedLeftId.value = null;
    
    saveMatchingAnswer();
};

const removeConnection = (leftId) => {
    delete studentConnections.value[leftId];
    saveMatchingAnswer();
};

const saveMatchingAnswer = () => {
    const connectionsArray = [];
    Object.entries(studentConnections.value).forEach(([kiriId, kananId]) => {
        connectionsArray.push({
            kiri_id: parseInt(kiriId),
            kanan_id: parseInt(kananId)
        });
    });
    
    selectedAnswer.value = JSON.stringify(connectionsArray);
    saveAnswer();
};

onMounted(async () => {
    examStore.setJadwal(props.jadwal);
    examStore.initNetworkListeners();

    window.addEventListener('resize', updateStudentLines);
    window.addEventListener('scroll', updateStudentLines, true);

    try {
        // 1. Mulai ujian (Backend generate soal)
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        await fetch(mulai({ jadwal: props.jadwal.id }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        // 2. Fetch list soal
        const response = await fetch(soal({ jadwal: props.jadwal.id }).url, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        examStore.setSoalList(data.data);
        isLoading.value = false;
        
        // TODO: Start timer calculation based on props.jadwal and DurasiSiswa
        startTimer(props.jadwal.durasi_ujian * 60);

    } catch (e) {
        console.error("Gagal memuat ujian", e);
        alert("Gagal memuat soal ujian. Harap periksa koneksi internet.");
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', updateStudentLines);
    window.removeEventListener('scroll', updateStudentLines, true);
});

const saveAnswer = () => {
    if (activeSoal.value && selectedAnswer.value) {
        examStore.simpanJawaban(activeSoal.value.id, selectedAnswer.value, isRaguRagu.value);
    }
};

const confirmSelesai = async () => {
    if (examStore.unansweredCount > 0) {
        if (!confirm(`Masih ada ${examStore.unansweredCount} soal yang belum dijawab. Yakin ingin selesai?`)) {
            return;
        }
    } else {
        if (!confirm('Apakah Anda yakin ingin menyelesaikan ujian ini?')) {
            return;
        }
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(selesai({ jadwal: props.jadwal.id }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            router.visit(route('dashboard'));
        }
    } catch (e) {
        alert('Gagal mengakhiri ujian. Periksa koneksi internet Anda.');
    }
};

const startTimer = (durationSeconds) => {
    let timer = durationSeconds;
    setInterval(() => {
        let h = parseInt(timer / 3600, 10);
        let m = parseInt((timer % 3600) / 60, 10);
        let s = parseInt(timer % 60, 10);

        h = h < 10 ? "0" + h : h;
        m = m < 10 ? "0" + m : m;
        s = s < 10 ? "0" + s : s;

        remainingTimeDisplay.value = `${h}:${m}:${s}`;

        if (--timer < 0) {
            timer = 0;
            // TODO: Auto submit
        }
    }, 1000);
};
</script>
