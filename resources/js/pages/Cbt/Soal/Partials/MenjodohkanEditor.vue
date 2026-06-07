<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import RichEditor from '@/components/RichEditor.vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

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
const { addToast } = useToast();
const leftItems = ref<Array<{ id: number; text: string }>>([]);
const rightItems = ref<Array<{ id: number; text: string }>>([]);
const editorConnections = ref<Record<number, number>>({});
const selectedLeftId = ref<number | null>(null);
const editorLines = ref<Array<{ d: string; color: string }>>([]);
const editorContainerRef = ref<HTMLElement | null>(null);

let nextLeftId = 1;
let nextRightId = 1;
let lineUpdateTimeout: ReturnType<typeof setTimeout> | null = null;

const triggerLineUpdate = () => {
    if (lineUpdateTimeout) clearTimeout(lineUpdateTimeout);
    nextTick(() => {
        lineUpdateTimeout = setTimeout(() => {
            if (editorContainerRef.value) updateEditorLines();
            lineUpdateTimeout = null;
        }, 100);
    });
};

const getKiriError = (index: number) => {
    return (props.form.errors as any)[`jawaban.${index}.kiri`];
};

const getKananError = (rightId: number) => {
    const leftIndex = leftItems.value.findIndex(
        (l) => editorConnections.value[l.id] === rightId,
    );
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
    leftItems.value = leftItems.value.filter((item) => item.id !== id);
    delete editorConnections.value[id];
    triggerLineUpdate();
};

const removeRightItem = (id: number) => {
    if (rightItems.value.length <= 1) return;
    rightItems.value = rightItems.value.filter((item) => item.id !== id);
    // Remove connections that point to this right item
    Object.keys(editorConnections.value).forEach((leftKey) => {
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
        const leftEl = editorContainerRef.value?.querySelector(
            `.editor-left-dot-${leftId}`,
        );
        const rightEl = editorContainerRef.value?.querySelector(
            `.editor-right-dot-${rightId}`,
        );

        if (leftEl && rightEl) {
            const leftRect = leftEl.getBoundingClientRect();
            const rightRect = rightEl.getBoundingClientRect();

            const x1 = leftRect.left + leftRect.width / 2 - containerRect.left;
            const y1 = leftRect.top + leftRect.height / 2 - containerRect.top;

            const x2 =
                rightRect.left + rightRect.width / 2 - containerRect.left;
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
    if (selectedLeftId.value !== null) {
        const leftId = selectedLeftId.value;
        editorConnections.value[leftId] = rightId;
        selectedLeftId.value = null;
    } else {
        // Disconnect if clicked when no left dot is selected
        const leftIdStr = Object.keys(editorConnections.value).find(
            (key) => editorConnections.value[parseInt(key)] === rightId,
        );
        if (leftIdStr) {
            delete editorConnections.value[parseInt(leftIdStr)];
        }
    }
    triggerLineUpdate();
};

const updateJawabanInForm = () => {
    const pairs: Array<{ kiri: string; kanan: string }> = [];
    leftItems.value.forEach((lItem) => {
        const rId = editorConnections.value[lItem.id];
        const rItem = rightItems.value.find((r) => r.id === rId);
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
        addToast(
            'Jumlah Pernyataan Kiri dan Jawaban Kanan harus sama!',
            'error',
        );
        return false;
    }

    const unconnected = leftItems.value.some(
        (item) => editorConnections.value[item.id] === undefined,
    );
    if (unconnected) {
        addToast(
            'Semua pernyataan kiri harus memiliki garis penghubung ke jawaban kanan!',
            'error',
        );
        return false;
    }

    const pairs: Array<{ kiri: string; kanan: string }> = [];
    leftItems.value.forEach((lItem) => {
        const rId = editorConnections.value[lItem.id];
        const rItem = rightItems.value.find((r) => r.id === rId);
        if (rItem) {
            pairs.push({
                kiri: lItem.text,
                kanan: rItem.text,
            });
        }
    });

    const hasEmpty = pairs.some(
        (p) => p.kiri.trim() === '' || p.kanan.trim() === '',
    );
    if (hasEmpty) {
        addToast('Pernyataan dan jawaban tidak boleh kosong!', 'error');
        return false;
    }

    props.form.jawaban = pairs as any;
    return true;
};

defineExpose({
    validate,
});

// Watchers
watch(
    editorConnections,
    () => {
        triggerLineUpdate();
    },
    { deep: true },
);

// Auto-sync state edits back to Inertia form state
watch(
    [leftItems, rightItems, editorConnections],
    () => {
        updateJawabanInForm();
    },
    { deep: true },
);

// Watch for question changes using a compound key (including serialized pairs) to prevent state bleeding
watch(
    () => {
        if (!props.soal) return null;
        const pairsStr = props.soal.pairs
            ? JSON.stringify(props.soal.pairs)
            : '[]';
        return `${props.soal.id ?? 'new'}-${props.soal.nomor_soal}-${pairsStr}`;
    },
    (newKey, oldKey) => {
        if (newKey !== oldKey) {
            initializeEditor();
        }
    },
    { immediate: true },
);

function initializeEditor() {
    if (
        props.soal &&
        Array.isArray(props.soal.pairs) &&
        props.soal.pairs.length > 0
    ) {
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
}

// Resize & Mutation Observers
let editorObserver: MutationObserver | null = null;

onMounted(() => {
    window.addEventListener('resize', updateEditorLines);
    window.addEventListener('scroll', updateEditorLines, true);

    if (window.MutationObserver && editorContainerRef.value) {
        editorObserver = new MutationObserver((mutations) => {
            // Ignore mutations originating from SVG lines/markers to prevent recursion loops
            const isSvgMutation = mutations.every((m) => {
                let target = m.target as Node;
                if (target.nodeType === Node.TEXT_NODE) {
                    target = target.parentElement as Node;
                }
                return (target as HTMLElement)?.closest?.('svg') !== null;
            });
            if (!isSvgMutation) {
                updateEditorLines();
            }
        });
        editorObserver.observe(editorContainerRef.value, {
            childList: true,
            subtree: true,
            attributes: true,
        });
    }

    triggerLineUpdate();
});

onUnmounted(() => {
    window.removeEventListener('resize', updateEditorLines);
    window.removeEventListener('scroll', updateEditorLines, true);
    if (editorObserver) {
        editorObserver.disconnect();
    }
    if (lineUpdateTimeout) {
        clearTimeout(lineUpdateTimeout);
    }
});
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label
                class="text-sm font-bold tracking-wider text-gray-700 uppercase dark:text-zinc-300"
                >Editor Menjodohkan Berbasis Koneksi</Label
            >
            <span
                v-if="form.errors.jawaban"
                class="rounded-full border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-500 dark:border-red-900/30 dark:bg-red-950/20"
                >{{ form.errors.jawaban }}</span
            >
        </div>

        <div
            class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs font-semibold text-amber-800 dark:border-amber-900/30 dark:bg-amber-950/10 dark:text-amber-400"
        >
            <span
                class="h-1.5 w-1.5 animate-ping rounded-full bg-amber-500"
            ></span>
            Cara Menghubungkan: Klik dot biru pada Pernyataan Kiri, lalu klik
            dot hijau pada Jawaban Kanan untuk menarik garis.
        </div>

        <div
            ref="editorContainerRef"
            class="relative grid min-h-[400px] grid-cols-12 gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/20"
        >
            <!-- Connecting SVG Overlay -->
            <svg
                class="pointer-events-none absolute inset-0 z-20 h-full w-full"
                style="overflow: visible"
            >
                <defs>
                    <marker
                        v-for="(line, idx) in editorLines"
                        :key="'marker-' + idx"
                        :id="'editor-arrow-' + idx"
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
                    :key="'line-' + idx"
                    :d="line.d"
                    fill="none"
                    :stroke="line.color"
                    stroke-width="2.5"
                    :marker-end="'url(#editor-arrow-' + idx + ')'"
                    class="opacity-80"
                />
            </svg>

            <!-- Left Column: Statements -->
            <div class="z-10 col-span-5 space-y-3">
                <div
                    class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase dark:text-zinc-500"
                >
                    Kolom Kiri (Pernyataan)
                </div>
                <div
                    v-for="(item, idx) in leftItems"
                    :key="'left-' + item.id"
                    class="relative rounded-lg border-2 bg-white p-3 shadow-sm transition-all dark:bg-zinc-900"
                    :class="[
                        selectedLeftId === item.id
                            ? 'border-blue-500 bg-blue-50/30 ring-2 ring-blue-100 dark:border-blue-500 dark:bg-blue-950/20 dark:ring-blue-900/30'
                            : 'border-gray-200 dark:border-zinc-800',
                        editorConnections[item.id] !== undefined
                            ? 'border-emerald-200 bg-emerald-50/10 dark:border-emerald-800 dark:bg-emerald-950/5'
                            : '',
                    ]"
                >
                    <div class="mb-2 flex items-center gap-2">
                        <span
                            class="rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-xs font-extrabold text-blue-600 dark:border-blue-900/30 dark:bg-blue-950/30 dark:text-blue-400"
                        >
                            Kiri {{ idx + 1 }}
                        </span>
                        <button
                            type="button"
                            @click="removeLeftItem(item.id)"
                            class="ml-auto text-gray-400 transition-colors hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400"
                            title="Hapus Pernyataan"
                            :disabled="leftItems.length <= 1"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <div
                        class="relative min-h-[85px] overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-zinc-700 dark:bg-zinc-950"
                    >
                        <RichEditor v-model="item.text" :minimal="true" />
                    </div>
                    <span
                        v-if="getKiriError(idx)"
                        class="mt-1 block text-[10px] text-red-500"
                        >{{ getKiriError(idx) }}</span
                    >

                    <!-- Connector Dot (Left Side) -->
                    <div
                        @click="handleLeftDotClick(item.id)"
                        :class="[
                            'editor-left-dot-' + item.id,
                            selectedLeftId === item.id
                                ? 'scale-125 bg-blue-600 ring-4 ring-blue-200 dark:ring-blue-900/40'
                                : 'bg-blue-500',
                        ]"
                        class="absolute top-1/2 -right-[7px] z-30 h-3.5 w-3.5 -translate-y-1/2 animate-pulse cursor-pointer rounded-full border-2 border-white shadow-md transition-transform hover:scale-110 dark:border-zinc-900"
                        title="Hubungkan"
                    ></div>
                </div>

                <button
                    type="button"
                    @click="addLeftItem"
                    class="flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-gray-300 py-2 text-xs font-semibold text-gray-500 transition-all hover:border-blue-400 hover:bg-blue-50/50 hover:text-blue-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-blue-800 dark:hover:bg-blue-950/20 dark:hover:text-blue-400"
                >
                    <Plus class="h-3.5 w-3.5" /> Tambah Pernyataan
                </button>
            </div>

            <!-- Middle Column: Spacing for SVG Lines -->
            <div class="pointer-events-none col-span-2"></div>

            <!-- Right Column: Choices -->
            <div class="z-10 col-span-5 space-y-3">
                <div
                    class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase dark:text-zinc-500"
                >
                    Kolom Kanan (Jawaban)
                </div>
                <div
                    v-for="(item, idx) in rightItems"
                    :key="'right-' + item.id"
                    class="relative rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <!-- Connector Dot (Right Side) -->
                    <div
                        @click="handleRightDotClick(item.id)"
                        :class="['editor-right-dot-' + item.id]"
                        class="absolute top-1/2 -left-[7px] z-30 h-3.5 w-3.5 -translate-y-1/2 cursor-pointer rounded-full border-2 border-white bg-emerald-500 shadow-md transition-transform hover:scale-110 dark:border-zinc-900"
                        title="Hubungkan di sini"
                    ></div>

                    <div class="mb-2 flex items-center gap-2">
                        <span
                            class="rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-xs font-extrabold text-emerald-600 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-400"
                        >
                            Kanan {{ idx + 1 }}
                        </span>
                        <button
                            type="button"
                            @click="removeRightItem(item.id)"
                            class="ml-auto text-gray-400 transition-colors hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400"
                            title="Hapus Jawaban"
                            :disabled="rightItems.length <= 1"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <div
                        class="relative min-h-[85px] overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-zinc-700 dark:bg-zinc-950"
                    >
                        <RichEditor v-model="item.text" :minimal="true" />
                    </div>
                    <span
                        v-if="getKananError(item.id)"
                        class="mt-1 block text-[10px] text-red-500"
                        >{{ getKananError(item.id) }}</span
                    >
                </div>

                <button
                    type="button"
                    @click="addRightItem"
                    class="flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-gray-300 py-2 text-xs font-semibold text-gray-500 transition-all hover:border-emerald-400 hover:bg-emerald-50/50 hover:text-emerald-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-400"
                >
                    <Plus class="h-3.5 w-3.5" /> Tambah Jawaban
                </button>
            </div>
        </div>
    </div>
</template>
