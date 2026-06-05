<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import RichEditor from '@/components/RichEditor.vue';
import { Plus, Trash2 } from 'lucide-vue-next';

interface Soal {
    id: number | null;
    nomor_soal: number;
    pairs?: Array<{ kiri: string; kanan: string }> | null;
}

// Intentional shared mutable form reference for Inertia form state
const props = defineProps<{
    form: ReturnType<typeof useForm>;
    soal: Soal | null;
}>();

// State
const leftItems = ref<Array<{ id: number; text: string }>>([]);
const rightItems = ref<Array<{ id: number; text: string }>>([]);
const editorConnections = ref<Record<number, number>>({});
const selectedLeftId = ref<number | null>(null);
const editorLines = ref<Array<{ d: string; color: string }>>([]);
const editorContainerRef = ref<HTMLElement | null>(null);

let nextLeftId = 1;
let nextRightId = 1;

const triggerLineUpdate = () => {
    nextTick(() => {
        setTimeout(updateEditorLines, 100);
    });
};

const getKiriError = (index: number) => {
    return (props.form.errors as any)[`jawaban.${index}.kiri`];
};

const getKananError = (rightId: number) => {
    const leftIndex = leftItems.value.findIndex(l => editorConnections.value[l.id] === rightId);
    if (leftIndex !== -1) {
        return (props.form.errors as any)[`jawaban.${leftIndex}.kanan`];
    }
    return null;
};

const addLeftItem = () => {
    leftItems.value.push({ id: nextLeftId++, text: '' });
    triggerLineUpdate();
};

const addRightItem = () => {
    rightItems.value.push({ id: nextRightId++, text: '' });
    triggerLineUpdate();
};

const removeLeftItem = (id: number) => {
    if (leftItems.value.length <= 1) return;
    leftItems.value = leftItems.value.filter(item => item.id !== id);
    delete editorConnections.value[id];
    triggerLineUpdate();
};

const removeRightItem = (id: number) => {
    if (rightItems.value.length <= 1) return;
    rightItems.value = rightItems.value.filter(item => item.id !== id);
    // Remove connections that point to this right item
    Object.keys(editorConnections.value).forEach(leftKey => {
        const lKey = parseInt(leftKey);
        if (editorConnections.value[lKey] === id) {
            delete editorConnections.value[lKey];
        }
    });
    triggerLineUpdate();
};

const updateEditorLines = () => {
    if (!editorContainerRef.value) return;
    const containerRect = editorContainerRef.value.getBoundingClientRect();
    const tempLines: Array<{ d: string; color: string }> = [];
    
    const colors = [
        '#3B82F6', // Blue
        '#10B981', // Emerald
        '#8B5CF6', // Violet
        '#F59E0B', // Amber
        '#EC4899', // Pink
        '#06B6D4', // Cyan
        '#EF4444', // Red
    ];
    
    Object.entries(editorConnections.value).forEach(([leftIdStr, rightId]) => {
        const leftId = parseInt(leftIdStr);
        const leftEl = editorContainerRef.value?.querySelector(`.editor-left-dot-${leftId}`);
        const rightEl = editorContainerRef.value?.querySelector(`.editor-right-dot-${rightId}`);
        
        if (leftEl && rightEl) {
            const leftRect = leftEl.getBoundingClientRect();
            const rightRect = rightEl.getBoundingClientRect();
            
            const x1 = leftRect.left + leftRect.width / 2 - containerRect.left;
            const y1 = leftRect.top + leftRect.height / 2 - containerRect.top;
            
            const x2 = rightRect.left + rightRect.width / 2 - containerRect.left;
            const y2 = rightRect.top + rightRect.height / 2 - containerRect.top;
            
            const controlX = x1 + (x2 - x1) / 2;
            const d = `M ${x1} ${y1} C ${controlX} ${y1}, ${controlX} ${y2}, ${x2} ${y2}`;
            const color = colors[leftId % colors.length];
            
            tempLines.push({ d, color });
        }
    });
    
    editorLines.value = tempLines;
};

const handleLeftDotClick = (leftId: number) => {
    if (selectedLeftId.value === leftId) {
        selectedLeftId.value = null;
    } else {
        selectedLeftId.value = leftId;
    }
};

const handleRightDotClick = (rightId: number) => {
    if (selectedLeftId.value === null) return;
    
    const leftId = selectedLeftId.value;
    editorConnections.value[leftId] = rightId;
    selectedLeftId.value = null;
    triggerLineUpdate();
};

const updateJawabanInForm = () => {
    const pairs: Array<{ kiri: string; kanan: string }> = [];
    leftItems.value.forEach(lItem => {
        const rId = editorConnections.value[lItem.id];
        const rItem = rightItems.value.find(r => r.id === rId);
        if (rItem) {
            pairs.push({
                kiri: lItem.text,
                kanan: rItem.text,
            });
        }
    });
    props.form.jawaban = pairs as any;
};

// Expose validation logic to parent component
const validate = (): boolean => {
    if (leftItems.value.length !== rightItems.value.length) {
        alert("Jumlah Pernyataan Kiri dan Jawaban Kanan harus sama!");
        return false;
    }
    
    const unconnected = leftItems.value.some(item => editorConnections.value[item.id] === undefined);
    if (unconnected) {
        alert("Semua pernyataan kiri harus memiliki garis penghubung ke jawaban kanan!");
        return false;
    }

    const pairs: Array<{ kiri: string; kanan: string }> = [];
    leftItems.value.forEach(lItem => {
        const rId = editorConnections.value[lItem.id];
        const rItem = rightItems.value.find(r => r.id === rId);
        if (rItem) {
            pairs.push({
                kiri: lItem.text,
                kanan: rItem.text,
            });
        }
    });

    const hasEmpty = pairs.some(p => p.kiri.trim() === '' || p.kanan.trim() === '');
    if (hasEmpty) {
        alert("Pernyataan dan jawaban tidak boleh kosong!");
        return false;
    }

    props.form.jawaban = pairs as any;
    return true;
};

defineExpose({
    validate
});

// Watchers
watch(editorConnections, () => {
    triggerLineUpdate();
}, { deep: true });

// Auto-sync state edits back to Inertia form state
watch([leftItems, rightItems, editorConnections], () => {
    updateJawabanInForm();
}, { deep: true });

// Watch for question changes using a compound key to prevent state bleeding on unsaved questions
watch(
    () => props.soal ? `${props.soal.id ?? 'new'}-${props.soal.nomor_soal}` : null,
    (newKey, oldKey) => {
        if (newKey !== oldKey) {
            initializeEditor();
        }
    },
    { immediate: true }
);

const initializeEditor = () => {
    if (props.soal && Array.isArray(props.soal.pairs) && props.soal.pairs.length > 0) {
        leftItems.value = [];
        rightItems.value = [];
        editorConnections.value = {};
        nextLeftId = 1;
        nextRightId = 1;
        props.soal.pairs.forEach((p: any) => {
            const lId = nextLeftId++;
            const rId = nextRightId++;
            leftItems.value.push({ id: lId, text: p.kiri || '' });
            rightItems.value.push({ id: rId, text: p.kanan || '' });
            editorConnections.value[lId] = rId;
        });
    } else {
        leftItems.value = [{ id: 1, text: '' }];
        rightItems.value = [{ id: 1, text: '' }];
        editorConnections.value = { 1: 1 };
        nextLeftId = 2;
        nextRightId = 2;
    }
    triggerLineUpdate();
};

// Resize & Mutation Observers
let editorObserver: MutationObserver | null = null;

onMounted(() => {
    window.addEventListener('resize', updateEditorLines);
    window.addEventListener('scroll', updateEditorLines, true);
    
    if (window.MutationObserver && editorContainerRef.value) {
        editorObserver = new MutationObserver(updateEditorLines);
        editorObserver.observe(editorContainerRef.value, { childList: true, subtree: true, attributes: true });
    }
    
    triggerLineUpdate();
});

onUnmounted(() => {
    window.removeEventListener('resize', updateEditorLines);
    window.removeEventListener('scroll', updateEditorLines, true);
    if (editorObserver) {
        editorObserver.disconnect();
    }
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Editor Menjodohkan Berbasis Koneksi</Label>
            <span v-if="form.errors.jawaban" class="text-xs text-red-500 font-semibold bg-red-50 px-2.5 py-1 rounded-full border border-red-100">{{ form.errors.jawaban }}</span>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-semibold flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-ping"></span>
            Cara Menghubungkan: Klik dot biru pada Pernyataan Kiri, lalu klik dot hijau pada Jawaban Kanan untuk menarik garis.
        </div>

        <div ref="editorContainerRef" class="relative grid grid-cols-12 gap-4 p-4 border border-gray-200 rounded-xl bg-gray-50/50 min-h-[400px]">
            <!-- Connecting SVG Overlay -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-20" style="overflow: visible;">
                <defs>
                    <marker 
                        v-for="(line, idx) in editorLines" 
                        :key="'marker-'+idx"
                        :id="'editor-arrow-'+idx" 
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
                    v-for="(line, idx) in editorLines" 
                    :key="'line-'+idx"
                    :d="line.d" 
                    fill="none" 
                    :stroke="line.color" 
                    stroke-width="2.5"
                    :marker-end="'url(#editor-arrow-'+idx+')'"
                    class="opacity-80"
                />
            </svg>

            <!-- Left Column: Statements -->
            <div class="col-span-5 space-y-3 z-10">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kolom Kiri (Pernyataan)</div>
                <div 
                    v-for="(item, idx) in leftItems" 
                    :key="'left-'+item.id" 
                    class="p-3 bg-white border-2 rounded-lg shadow-sm relative transition-all"
                    :class="[
                        selectedLeftId === item.id ? 'border-blue-500 ring-2 ring-blue-100 bg-blue-50/30' : 'border-gray-200',
                        editorConnections[item.id] !== undefined ? 'border-emerald-100' : ''
                    ]"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-extrabold text-blue-600 bg-blue-50 border border-blue-100 rounded-full px-2 py-0.5">
                            Kiri {{ idx + 1 }}
                        </span>
                        <button 
                            type="button"
                            @click="removeLeftItem(item.id)"
                            class="text-gray-400 hover:text-red-500 ml-auto transition-colors"
                            title="Hapus Pernyataan"
                            :disabled="leftItems.length <= 1"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <div class="border border-gray-300 rounded-lg overflow-hidden relative min-h-[85px] bg-white">
                        <RichEditor 
                            v-model="item.text"
                            :minimal="true"
                        />
                    </div>
                    <span v-if="getKiriError(idx)" class="text-[10px] text-red-500 mt-1 block">{{ getKiriError(idx) }}</span>

                    <!-- Connector Dot (Left Side) -->
                    <div 
                        @click="handleLeftDotClick(item.id)"
                        :class="[
                            'editor-left-dot-' + item.id,
                            selectedLeftId === item.id ? 'scale-125 ring-4 ring-blue-200 bg-blue-600' : 'bg-blue-500'
                        ]"
                        class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-md absolute -right-[7px] top-1/2 -translate-y-1/2 cursor-pointer hover:scale-110 transition-transform z-30 animate-pulse"
                        title="Hubungkan"
                    ></div>
                </div>

                <button 
                    type="button"
                    @click="addLeftItem"
                    class="w-full py-2 border border-dashed border-gray-300 rounded-lg text-gray-500 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50/50 flex items-center justify-center gap-1 transition-all font-semibold text-xs"
                >
                    <Plus class="w-3.5 h-3.5" /> Tambah Pernyataan
                </button>
            </div>

            <!-- Middle Column: Spacing for SVG Lines -->
            <div class="col-span-2 pointer-events-none"></div>

            <!-- Right Column: Choices -->
            <div class="col-span-5 space-y-3 z-10">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kolom Kanan (Jawaban)</div>
                <div 
                    v-for="(item, idx) in rightItems" 
                    :key="'right-'+item.id" 
                    class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm relative transition-all"
                >
                    <!-- Connector Dot (Right Side) -->
                    <div 
                        @click="handleRightDotClick(item.id)"
                        :class="['editor-right-dot-' + item.id]"
                        class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-md absolute -left-[7px] top-1/2 -translate-y-1/2 cursor-pointer hover:scale-110 transition-transform z-30"
                        title="Hubungkan di sini"
                    ></div>

                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-0.5">
                            Kanan {{ idx + 1 }}
                        </span>
                        <button 
                            type="button"
                            @click="removeRightItem(item.id)"
                            class="text-gray-400 hover:text-red-500 ml-auto transition-colors"
                            title="Hapus Jawaban"
                            :disabled="rightItems.length <= 1"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <div class="border border-gray-300 rounded-lg overflow-hidden relative min-h-[85px] bg-white">
                        <RichEditor 
                            v-model="item.text"
                            :minimal="true"
                        />
                    </div>
                    <span v-if="getKananError(item.id)" class="text-[10px] text-red-500 mt-1 block">{{ getKananError(item.id) }}</span>
                </div>

                <button 
                    type="button"
                    @click="addRightItem"
                    class="w-full py-2 border border-dashed border-gray-300 rounded-lg text-gray-500 hover:text-emerald-600 hover:border-emerald-400 hover:bg-emerald-50/50 flex items-center justify-center gap-1 transition-all font-semibold text-xs"
                >
                    <Plus class="w-3.5 h-3.5" /> Tambah Jawaban
                </button>
            </div>
        </div>
    </div>
</template>
