<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps<{
    jawaban: any; // Can be array of {kiri, kanan} or JSON string
}>();

const containerRef = ref<HTMLElement | null>(null);
const lines = ref<Array<{ d: string; color: string }>>([]);

const pairs = computed(() => {
    let raw = props.jawaban;
    if (typeof raw === 'string') {
        try {
            raw = JSON.parse(raw);
        } catch (e) {
            return [];
        }
    }

    if (!Array.isArray(raw) || raw.length === 0) {
        return [];
    }

    // Check if it's already an array of {kiri, kanan} objects (for safety)
    if (
        raw[0] &&
        typeof raw[0] === 'object' &&
        !Array.isArray(raw[0]) &&
        'kiri' in raw[0]
    ) {
        return raw;
    }

    // Otherwise, decode 2D matrix format (requires length >= 2)
    if (raw.length < 2) {
        return [];
    }

    const stripHtml = (html: string) => {
        if (!html) return '';
        return html.replace(/<[^>]*>/g, '').trim();
    };

    const headers = raw[0].slice(1).map((h: string) => stripHtml(h)); // Choices on right
    const decodedPairs: Array<{ kiri: string; kanan: string }> = [];
    for (let i = 1; i < raw.length; i++) {
        const row = raw[i];
        if (!Array.isArray(row)) continue;
        const kiri = stripHtml(row[0]);
        const matchIdx = row.slice(1).indexOf('1');
        const kanan = matchIdx !== -1 ? headers[matchIdx] : '';
        decodedPairs.push({ kiri, kanan });
    }
    return decodedPairs;
});

// Shuffled right list to show matching layout
const rightList = ref<Array<{ id: number; text: string }>>([]);

// Simple LCG or seeding to keep shuffle stable per question render, or just regular shuffle
const stableShuffle = (arr: Array<any>) => {
    const newArr = [...arr];
    // Simple deterministic sort based on text content to keep it stable on re-renders
    return newArr.sort((a, b) => {
        const scoreA = a.text
            .split('')
            .reduce((acc: number, char: string) => acc + char.charCodeAt(0), 0);
        const scoreB = b.text
            .split('')
            .reduce((acc: number, char: string) => acc + char.charCodeAt(0), 0);
        return scoreA - scoreB;
    });
};

watch(
    () => pairs.value,
    (newPairs) => {
        const list = newPairs.map((p, idx) => ({ id: idx, text: p.kanan }));
        rightList.value = stableShuffle(list);
        nextTick(() => {
            setTimeout(updateLines, 150);
        });
    },
    { immediate: true, deep: true },
);

const updateLines = () => {
    if (!containerRef.value) return;
    const containerRect = containerRef.value.getBoundingClientRect();
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

    pairs.value.forEach((pair, leftIdx) => {
        const leftEl = containerRef.value?.querySelector(
            `.left-dot-${leftIdx}`,
        );
        // Right dot has class right-dot-{leftIdx} because its id is leftIdx
        const rightEl = containerRef.value?.querySelector(
            `.right-dot-${leftIdx}`,
        );

        if (leftEl && rightEl) {
            const leftRect = leftEl.getBoundingClientRect();
            const rightRect = rightEl.getBoundingClientRect();

            const x1 = leftRect.left + leftRect.width / 2 - containerRect.left;
            const y1 = leftRect.top + leftRect.height / 2 - containerRect.top;

            const x2 =
                rightRect.left + rightRect.width / 2 - containerRect.left;
            const y2 = rightRect.top + rightRect.height / 2 - containerRect.top;

            // Cubic bezier curve for smooth connectors
            const controlX = x1 + (x2 - x1) / 2;
            const d = `M ${x1} ${y1} C ${controlX} ${y1}, ${controlX} ${y2}, ${x2} ${y2}`;
            const color = colors[leftIdx % colors.length];

            tempLines.push({ d, color });
        }
    });

    lines.value = tempLines;
};

// Handle resize and screen updates
onMounted(() => {
    setTimeout(updateLines, 300); // Give time for browser paint
    window.addEventListener('resize', updateLines);

    // MutationObserver to detect container size changes due to image loading or other DOM updates
    if (window.MutationObserver && containerRef.value) {
        const observer = new MutationObserver(updateLines);
        observer.observe(containerRef.value, {
            childList: true,
            subtree: true,
            attributes: true,
        });
        (containerRef.value as any)._observer = observer;
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', updateLines);
    if (containerRef.value && (containerRef.value as any)._observer) {
        (containerRef.value as any)._observer.disconnect();
    }
});
</script>

<template>
    <div
        ref="containerRef"
        class="dark:border-zinc-850 relative my-4 grid grid-cols-12 gap-8 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:bg-zinc-950/50"
    >
        <!-- Connecting SVG Overlay -->
        <svg class="pointer-events-none absolute inset-0 z-20 h-full w-full">
            <defs>
                <marker
                    v-for="(line, idx) in lines"
                    :key="'marker-' + idx"
                    :id="'arrow-' + idx"
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
                v-for="(line, idx) in lines"
                :key="'line-' + idx"
                :d="line.d"
                fill="none"
                :stroke="line.color"
                stroke-width="2.5"
                stroke-dasharray="0"
                :marker-end="'url(#arrow-' + idx + ')'"
                class="opacity-80 transition-all duration-300 hover:stroke-[3.5px] hover:opacity-100"
            />
        </svg>

        <!-- Left Column: Statements -->
        <div class="z-10 col-span-5 space-y-4">
            <div
                class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase dark:text-zinc-500"
            >
                Pernyataan (Kiri)
            </div>
            <div
                v-for="(item, idx) in pairs"
                :key="'left-' + idx"
                class="relative flex min-h-[50px] items-center justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded border border-blue-100 bg-blue-50 text-xs font-bold text-blue-600 dark:border-blue-900/30 dark:bg-blue-950/30 dark:text-blue-400"
                    >
                        {{ idx + 1 }}
                    </span>
                    <div
                        class="prose max-w-none text-sm leading-relaxed font-medium text-gray-700 dark:text-zinc-300"
                        v-html="item.kiri"
                    ></div>
                </div>
                <!-- Connector Dot -->
                <div
                    :class="['left-dot-' + idx]"
                    class="absolute top-1/2 -right-[7px] h-3.5 w-3.5 -translate-y-1/2 cursor-default rounded-full border-2 border-white bg-blue-500 shadow-md dark:border-zinc-900"
                ></div>
            </div>
        </div>

        <!-- Middle Column: Spacing for SVG Lines -->
        <div class="pointer-events-none col-span-2"></div>

        <!-- Right Column: Shuffled Answers -->
        <div class="z-10 col-span-5 space-y-4">
            <div
                class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase dark:text-zinc-500"
            >
                Jawaban Pasangan (Kanan)
            </div>
            <div
                v-for="(item, idx) in rightList"
                :key="'right-' + item.id"
                class="relative flex min-h-[50px] items-center rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900"
            >
                <!-- Connector Dot -->
                <div
                    :class="['right-dot-' + item.id]"
                    class="absolute top-1/2 -left-[7px] h-3.5 w-3.5 -translate-y-1/2 cursor-default rounded-full border-2 border-white bg-emerald-500 shadow-md dark:border-zinc-900"
                ></div>
                <div class="flex items-center gap-3 pl-3">
                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded border border-emerald-100 bg-emerald-50 text-xs font-bold text-emerald-600 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-400"
                    >
                        {{ String.fromCharCode(65 + idx) }}
                    </span>
                    <div
                        class="prose max-w-none text-sm leading-relaxed font-medium text-gray-700 dark:text-zinc-300"
                        v-html="item.text"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
svg {
    overflow: visible;
}
</style>
