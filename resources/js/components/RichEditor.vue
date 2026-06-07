<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { StarterKit } from '@tiptap/starter-kit';
import { Image } from '@tiptap/extension-image';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { Underline } from '@tiptap/extension-underline';
import { TextAlign } from '@tiptap/extension-text-align';
import { Link as TiptapLink } from '@tiptap/extension-link';
import { Highlight } from '@tiptap/extension-highlight';
import { Color } from '@tiptap/extension-color';
import { TextStyle } from '@tiptap/extension-text-style';
import { FontFamily } from '@tiptap/extension-font-family';
import { Node, Extension, mergeAttributes } from '@tiptap/core';
import {
    Bold,
    Italic,
    Underline as UnderlineIcon,
    Eraser,
    AlignLeft,
    AlignCenter,
    AlignRight,
    AlignJustify,
    Link as LinkIcon,
    Image as ImageIcon,
    Video as VideoIcon,
    Upload,
    Sigma,
    X,
    Maximize2,
    Minimize2,
    Code,
    HelpCircle,
    ChevronDown,
    Highlighter,
    Paintbrush,
    Plus,
    Trash2,
    Table as TableIcon,
    Wand2,
    List,
    ListOrdered,
} from 'lucide-vue-next';
import axios from 'axios';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        minimal?: boolean;
    }>(),
    {
        modelValue: '',
        minimal: false,
    },
);

const emit = defineEmits(['update:modelValue']);

// Custom Image Extension to support direct HTML width attribute
const CustomImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                renderHTML: (attributes) => {
                    if (!attributes.width) {
                        return {};
                    }
                    return {
                        width: attributes.width,
                    };
                },
                parseHTML: (element) => element.getAttribute('width'),
            },
        };
    },
});

// Custom Font Size Extension
const FontSize = Extension.create({
    name: 'fontSize',
    addOptions() {
        return {
            types: ['textStyle'],
        };
    },
    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    fontSize: {
                        default: null,
                        parseHTML: (element) =>
                            element.style.fontSize?.replace('px', ''),
                        renderHTML: (attributes) => {
                            if (!attributes.fontSize) {
                                return {};
                            }
                            return {
                                style: `font-size: ${attributes.fontSize}px`,
                            };
                        },
                    },
                },
            },
        ];
    },
    addCommands() {
        return {
            setFontSize:
                (fontSize: string) =>
                ({ chain }: any) => {
                    return chain()
                        .setMark('textStyle', {
                            fontSize: fontSize ? `${fontSize}` : null,
                        })
                        .run();
                },
            unsetFontSize:
                () =>
                ({ chain }: any) => {
                    return chain()
                        .setMark('textStyle', { fontSize: null })
                        .run();
                },
        } as any;
    },
});

// Custom Video Node Extension to handle direct mp4 files and YouTube/Vimeo embeds
const VideoExtension = Node.create({
    name: 'video',
    group: 'block',
    selectable: true,
    draggable: true,
    atom: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            width: {
                default: '100%',
            },
            height: {
                default: '315',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'iframe[src]',
            },
            {
                tag: 'video[src]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        const src = HTMLAttributes.src || '';
        if (
            src.includes('youtube.com') ||
            src.includes('youtu.be') ||
            src.includes('vimeo.com')
        ) {
            let embedUrl = src;
            if (src.includes('watch?v=')) {
                embedUrl = src.replace('watch?v=', 'embed/');
            } else if (src.includes('youtu.be/')) {
                const videoId = src.split('/').pop()?.split('?')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            } else if (src.includes('vimeo.com/')) {
                const videoId = src.split('/').pop()?.split('?')[0];
                embedUrl = `https://player.vimeo.com/video/${videoId}`;
            }
            return [
                'iframe',
                mergeAttributes(HTMLAttributes, {
                    src: embedUrl,
                    frameborder: '0',
                    allowfullscreen: 'true',
                    class: 'aspect-video w-full rounded-lg shadow-md my-2',
                }),
            ];
        }
        return [
            'video',
            mergeAttributes(HTMLAttributes, {
                controls: 'true',
                class: 'w-full rounded-lg shadow-md my-2',
            }),
        ];
    },
});

// Custom KaTeX Extension
const MathExtension = Node.create({
    name: 'math',
    group: 'inline',
    inline: true,
    atom: true,

    addAttributes() {
        return {
            formula: {
                default: '',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'math',
            },
            {
                tag: 'span.katex',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'math',
            mergeAttributes(HTMLAttributes, {
                xmlns: 'http://www.w3.org/1998/Math/MathML',
            }),
            [
                'semantics',
                {},
                [
                    'annotation',
                    { encoding: 'application/x-tex' },
                    HTMLAttributes.formula,
                ],
            ],
        ];
    },
});

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            link: false,
            underline: false,
        }),
        CustomImage,
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        MathExtension,
        Underline.configure(),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        TiptapLink.configure({
            openOnClick: false,
        }),
        Highlight.configure({ multicolor: true }),
        Color.configure(),
        TextStyle.configure(),
        FontFamily.configure(),
        FontSize,
        VideoExtension,
    ],
    onUpdate: () => {
        emit('update:modelValue', editor.value?.getHTML() || '');
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl max-w-none focus:outline-none min-h-[150px] p-4 bg-white dark:bg-zinc-950 text-neutral-900 dark:text-neutral-100',
        },
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const isSame = editor.value?.getHTML() === value;
        if (isSame) return;
        editor.value?.commands.setContent(value || '', false as any);
    },
);

// State definitions
const fileInputRef = ref<HTMLInputElement | null>(null);
const fileUploadInputRef = ref<HTMLInputElement | null>(null);

const isFullscreen = ref(false);
const isCodeView = ref(false);
const codeContentText = ref('');

const showWandMenu = ref(false);
const activeFontDropdown = ref(false);
const activeFontSizeDropdown = ref(false);
const activeColorDropdown = ref(false);
const activeAlignDropdown = ref(false);
const activeTableDropdown = ref(false);

const showImageModal = ref(false);
const imageTab = ref<'upload' | 'url'>('upload');
const imageUrlInput = ref('');

const showResizeModal = ref(false);
const resizeWidthInput = ref('');

const showMathModal = ref(false);
const mathFormulaInput = ref('');

const showLinkModal = ref(false);
const linkUrlInput = ref('');

const showVideoModal = ref(false);
const videoUrlInput = ref('');

const showHelpModal = ref(false);
const showUploadModal = ref(false);

// Config options
const fonts = [
    { name: 'Default', value: '' },
    { name: 'Poppins', value: 'Poppins, sans-serif' },
    { name: 'Inter', value: 'Inter, sans-serif' },
    { name: 'Roboto', value: 'Roboto, sans-serif' },
    { name: 'Outfit', value: 'Outfit, sans-serif' },
    { name: 'Courier New', value: 'Courier New, monospace' },
];

const fontSizes = [
    { name: 'Bawaan', value: '' },
    { name: '12 px', value: '12' },
    { name: '14 px', value: '14' },
    { name: '16 px', value: '16' },
    { name: '18 px', value: '18' },
    { name: '20 px', value: '20' },
    { name: '24 px', value: '24' },
    { name: '30 px', value: '30' },
    { name: '36 px', value: '36' },
];

const textColors = [
    { name: 'Hitam', value: '#000000' },
    { name: 'Abu-abu', value: '#4b5563' },
    { name: 'Merah', value: '#ef4444' },
    { name: 'Oranye', value: '#f97316' },
    { name: 'Kuning', value: '#eab308' },
    { name: 'Hijau', value: '#22c55e' },
    { name: 'Biru', value: '#3b82f6' },
    { name: 'Ungu', value: '#a855f7' },
];

const highlightColors = [
    { name: 'Kuning', value: '#fef08a' },
    { name: 'Hijau', value: '#bbf7d0' },
    { name: 'Biru', value: '#bfdbfe' },
    { name: 'Pink', value: '#fbcfe8' },
    { name: 'Ungu', value: '#e9d5ff' },
    { name: 'Oranye', value: '#fed7aa' },
];

// Helper methods
const toggleDropdown = (
    type: 'font' | 'fontSize' | 'color' | 'align' | 'table',
) => {
    activeFontDropdown.value =
        type === 'font' ? !activeFontDropdown.value : false;
    activeFontSizeDropdown.value =
        type === 'fontSize' ? !activeFontSizeDropdown.value : false;
    activeColorDropdown.value =
        type === 'color' ? !activeColorDropdown.value : false;
    activeAlignDropdown.value =
        type === 'align' ? !activeAlignDropdown.value : false;
    activeTableDropdown.value =
        type === 'table' ? !activeTableDropdown.value : false;
    showWandMenu.value = false;
};

const closeAllDropdowns = () => {
    activeFontDropdown.value = false;
    activeFontSizeDropdown.value = false;
    activeColorDropdown.value = false;
    activeAlignDropdown.value = false;
    activeTableDropdown.value = false;
    showWandMenu.value = false;
};

const setFontFamily = (value: string) => {
    if (value) {
        editor.value?.chain().focus().setFontFamily(value).run();
    } else {
        editor.value?.chain().focus().unsetFontFamily().run();
    }
    activeFontDropdown.value = false;
};

const setFontSizeValue = (value: string) => {
    if (value) {
        (editor.value?.chain().focus() as any).setFontSize(value).run();
    } else {
        (editor.value?.chain().focus() as any).unsetFontSize().run();
    }
    activeFontSizeDropdown.value = false;
};

const setTextColor = (color: string) => {
    editor.value?.chain().focus().setColor(color).run();
    activeColorDropdown.value = false;
};

const unsetTextColor = () => {
    editor.value?.chain().focus().unsetColor().run();
    activeColorDropdown.value = false;
};

const setHighlightColor = (color: string) => {
    editor.value?.chain().focus().toggleHighlight({ color }).run();
    activeColorDropdown.value = false;
};

const unsetHighlightColor = () => {
    editor.value?.chain().focus().unsetHighlight().run();
    activeColorDropdown.value = false;
};

const toggleCodeView = () => {
    if (isCodeView.value) {
        editor.value?.commands.setContent(
            codeContentText.value || '',
            false as any,
        );
    } else {
        codeContentText.value = editor.value?.getHTML() || '';
    }
    isCodeView.value = !isCodeView.value;
};

watch(codeContentText, (value) => {
    if (isCodeView.value) {
        emit('update:modelValue', value);
    }
});

const uploadImage = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const file = input.files[0];
    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await axios.post('/cbt/soal/upload-image', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.data.url) {
            editor.value
                ?.chain()
                .focus()
                .setImage({ src: response.data.url })
                .run();
            showImageModal.value = false;
        }
    } catch (error) {
        console.error('Image upload failed', error);
        alert('Gagal mengupload gambar.');
    }
    input.value = '';
};

const uploadFile = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const file = input.files[0];
    const formData = new FormData();
    formData.append('image', file); // Reuses standard image file upload endpoint

    try {
        const response = await axios.post('/cbt/soal/upload-image', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.data.url) {
            const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.name);
            if (isImg) {
                editor.value
                    ?.chain()
                    .focus()
                    .setImage({ src: response.data.url })
                    .run();
            } else {
                editor.value
                    ?.chain()
                    .focus()
                    .insertContent(
                        `<a href="${response.data.url}" target="_blank" class="text-amber-600 underline font-semibold">${file.name}</a>`,
                    )
                    .run();
            }
            showUploadModal.value = false;
        }
    } catch (error) {
        console.error('File upload failed', error);
        alert('Gagal mengupload berkas.');
    }
    input.value = '';
};

const handleImageClick = () => {
    imageUrlInput.value = '';
    imageTab.value = 'upload';
    showImageModal.value = true;
};

const insertUrlImage = () => {
    const url = imageUrlInput.value.trim();
    if (url) {
        editor.value?.chain().focus().setImage({ src: url }).run();
        showImageModal.value = false;
    }
};

const resizeImage = () => {
    if (!editor.value) return;
    const { selection } = editor.value.state;
    const node = (selection as any).node;
    if (node && node.type.name === 'image') {
        resizeWidthInput.value = node.attrs.width || '';
        showResizeModal.value = true;
    } else {
        alert('Silakan klik/pilih gambar terlebih dahulu.');
    }
};

const applyResize = () => {
    const newWidth = resizeWidthInput.value.trim();
    if (newWidth) {
        editor.value
            ?.chain()
            .focus()
            .updateAttributes('image', { width: newWidth })
            .run();
    }
    showResizeModal.value = false;
};

const insertMath = () => {
    mathFormulaInput.value = '';
    showMathModal.value = true;
};

const applyMath = () => {
    const formula = mathFormulaInput.value.trim();
    if (formula) {
        editor.value
            ?.chain()
            .focus()
            .insertContent(
                `<math xmlns="http://www.w3.org/1998/Math/MathML"><semantics><annotation encoding="application/x-tex">${formula}</annotation></semantics></math>`,
            )
            .run();
    }
    showMathModal.value = false;
};

const openLinkModal = () => {
    if (editor.value?.isActive('link')) {
        editor.value.chain().focus().unsetLink().run();
    } else {
        linkUrlInput.value = '';
        showLinkModal.value = true;
    }
};

const insertLink = () => {
    const url = linkUrlInput.value.trim();
    if (url) {
        let finalUrl = url;
        if (!/^https?:\/\//i.test(url)) {
            finalUrl = `https://${url}`;
        }
        editor.value
            ?.chain()
            .focus()
            .setLink({ href: finalUrl, target: '_blank' })
            .run();
    }
    showLinkModal.value = false;
};

const openVideoModal = () => {
    videoUrlInput.value = '';
    showVideoModal.value = true;
};

const insertVideo = () => {
    const url = videoUrlInput.value.trim();
    if (url) {
        editor.value
            ?.chain()
            .focus()
            .insertContent({
                type: 'video',
                attrs: { src: url },
            })
            .run();
    }
    showVideoModal.value = false;
};

const runWandAction = (action: string) => {
    if (!editor.value) return;
    showWandMenu.value = false;

    if (action === 'clean') {
        const html = editor.value.getHTML();
        const cleaned = html
            .replace(/&nbsp;&nbsp;/g, ' ')
            .replace(/ {2,}/g, ' ');
        editor.value.commands.setContent(cleaned);
    } else if (action === 'math') {
        insertMath();
    } else if (action === 'table') {
        insertTable();
    }
};

// Table actions
const insertTable = () => {
    editor.value
        ?.chain()
        .focus()
        .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
        .run();
    activeTableDropdown.value = false;
};
const addRowBefore = () => {
    editor.value?.chain().focus().addRowBefore().run();
    activeTableDropdown.value = false;
};
const addRowAfter = () => {
    editor.value?.chain().focus().addRowAfter().run();
    activeTableDropdown.value = false;
};
const deleteRow = () => {
    editor.value?.chain().focus().deleteRow().run();
    activeTableDropdown.value = false;
};
const addColBefore = () => {
    editor.value?.chain().focus().addColumnBefore().run();
    activeTableDropdown.value = false;
};
const addColAfter = () => {
    editor.value?.chain().focus().addColumnAfter().run();
    activeTableDropdown.value = false;
};
const deleteCol = () => {
    editor.value?.chain().focus().deleteColumn().run();
    activeTableDropdown.value = false;
};
const deleteTable = () => {
    editor.value?.chain().focus().deleteTable().run();
    activeTableDropdown.value = false;
};

onMounted(() => {
    document.addEventListener('click', closeAllDropdowns);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeAllDropdowns);
    editor.value?.destroy();
});
</script>

<template>
    <div
        :class="[
            isFullscreen
                ? 'fixed inset-0 z-[8888] flex h-screen flex-col overflow-hidden bg-white p-6 dark:bg-zinc-950'
                : 'overflow-hidden rounded-xl border border-neutral-300 bg-white transition-shadow focus-within:ring-2 focus-within:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950',
        ]"
    >
        <!-- Toolbar -->
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-neutral-50 p-2 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <!-- Wand Menu -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="showWandMenu = !showWandMenu"
                    class="flex items-center gap-0.5 rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Asisten Cepat"
                >
                    <Wand2 class="h-4 w-4 animate-pulse text-amber-500" />
                    <ChevronDown class="h-3 w-3" />
                </button>
                <div
                    v-if="showWandMenu"
                    class="absolute left-0 z-50 mt-1 w-[180px] rounded-lg border border-neutral-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        type="button"
                        @click="runWandAction('clean')"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Bersihkan Spasi Ganda
                    </button>
                    <button
                        type="button"
                        @click="runWandAction('math')"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Rumus LaTeX
                    </button>
                    <button
                        type="button"
                        @click="runWandAction('table')"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Tabel Template
                    </button>
                </div>
            </div>

            <!-- Basic formatting -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{
                    'bg-neutral-200 font-bold text-amber-600 dark:bg-zinc-800':
                        editor.isActive('bold'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Tebal (Ctrl+B)"
            >
                <Bold class="h-4 w-4" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{
                    'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                        editor.isActive('italic'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Miring (Ctrl+I)"
            >
                <Italic class="h-4 w-4" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="{
                    'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                        editor.isActive('underline'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Garis Bawah (Ctrl+U)"
            >
                <UnderlineIcon class="h-4 w-4" />
            </button>

            <!-- Eraser / Clear style -->
            <button
                type="button"
                @click="
                    editor.chain().focus().clearNodes().unsetAllMarks().run()
                "
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Hapus Semua Format"
            >
                <Eraser class="h-4 w-4" />
            </button>

            <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-zinc-700"></div>

            <!-- Font Family -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="toggleDropdown('font')"
                    class="flex min-w-[80px] items-center justify-between gap-1 rounded border border-neutral-300 px-2 py-1 text-xs text-neutral-700 transition-colors hover:bg-neutral-100 dark:border-zinc-700 dark:text-neutral-300 dark:hover:bg-zinc-800"
                >
                    <span>Font</span>
                    <ChevronDown class="h-3 w-3 text-neutral-400" />
                </button>
                <div
                    v-if="activeFontDropdown"
                    class="absolute left-0 z-50 mt-1 max-h-[200px] w-[140px] overflow-y-auto rounded-lg border border-neutral-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        v-for="f in fonts"
                        :key="f.value"
                        type="button"
                        @click="setFontFamily(f.value)"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        {{ f.name }}
                    </button>
                </div>
            </div>

            <!-- Font Size -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="toggleDropdown('fontSize')"
                    class="flex min-w-[60px] items-center justify-between gap-1 rounded border border-neutral-300 px-2 py-1 text-xs text-neutral-700 transition-colors hover:bg-neutral-100 dark:border-zinc-700 dark:text-neutral-300 dark:hover:bg-zinc-800"
                >
                    <span>Ukuran</span>
                    <ChevronDown class="h-3 w-3 text-neutral-400" />
                </button>
                <div
                    v-if="activeFontSizeDropdown"
                    class="absolute left-0 z-50 mt-1 max-h-[200px] w-[100px] overflow-y-auto rounded-lg border border-neutral-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        v-for="s in fontSizes"
                        :key="s.value"
                        type="button"
                        @click="setFontSizeValue(s.value)"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        {{ s.name }}
                    </button>
                </div>
            </div>

            <!-- Text & Highlight Color -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="toggleDropdown('color')"
                    class="flex items-center gap-0.5 rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Warna Teks & Highlight"
                >
                    <div class="flex flex-col items-center">
                        <span class="-mb-0.5 text-xs leading-none font-bold"
                            >A</span
                        >
                        <div class="h-1 w-3.5 rounded-sm bg-amber-500"></div>
                    </div>
                    <ChevronDown class="h-3 w-3" />
                </button>
                <div
                    v-if="activeColorDropdown"
                    class="absolute left-0 z-50 mt-1 flex w-[240px] flex-col gap-3 rounded-lg border border-neutral-200 bg-white p-3 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="font-bold text-neutral-500"
                                >Warna Teks</span
                            >
                            <button
                                type="button"
                                @click="unsetTextColor"
                                class="text-[10px] text-red-500 hover:underline"
                            >
                                Reset
                            </button>
                        </div>
                        <div class="grid grid-cols-8 gap-1">
                            <button
                                v-for="c in textColors"
                                :key="c.value"
                                type="button"
                                @click="setTextColor(c.value)"
                                class="h-5 w-5 rounded border border-neutral-200 dark:border-zinc-700"
                                :style="{ backgroundColor: c.value }"
                                :title="c.name"
                            ></button>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="font-bold text-neutral-500"
                                >Highlight</span
                            >
                            <button
                                type="button"
                                @click="unsetHighlightColor"
                                class="text-[10px] text-red-500 hover:underline"
                            >
                                Reset
                            </button>
                        </div>
                        <div class="grid grid-cols-6 gap-1">
                            <button
                                v-for="h in highlightColors"
                                :key="h.value"
                                type="button"
                                @click="setHighlightColor(h.value)"
                                class="h-6 w-6 rounded border border-neutral-200 dark:border-zinc-700"
                                :style="{ backgroundColor: h.value }"
                                :title="h.name"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-zinc-700"></div>

            <!-- Bullet List, Ordered List, Alignment -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{
                    'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                        editor.isActive('bulletList'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Bullet List"
            >
                <List class="h-4 w-4" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{
                    'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                        editor.isActive('orderedList'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Ordered List"
            >
                <ListOrdered class="h-4 w-4" />
            </button>

            <!-- Align Dropdown -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="toggleDropdown('align')"
                    class="flex items-center gap-0.5 rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Perataan Paragraf"
                >
                    <AlignLeft
                        v-if="
                            editor.isActive({ textAlign: 'left' }) ||
                            (!editor.isActive({ textAlign: 'center' }) &&
                                !editor.isActive({ textAlign: 'right' }) &&
                                !editor.isActive({ textAlign: 'justify' }))
                        "
                        class="h-4 w-4"
                    />
                    <AlignCenter
                        v-else-if="editor.isActive({ textAlign: 'center' })"
                        class="h-4 w-4"
                    />
                    <AlignRight
                        v-else-if="editor.isActive({ textAlign: 'right' })"
                        class="h-4 w-4"
                    />
                    <AlignJustify
                        v-else-if="editor.isActive({ textAlign: 'justify' })"
                        class="h-4 w-4"
                    />
                    <ChevronDown class="h-3 w-3" />
                </button>
                <div
                    v-if="activeAlignDropdown"
                    class="absolute left-0 z-50 mt-1 w-[120px] rounded-lg border border-neutral-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        type="button"
                        @click="
                            editor.chain().focus().setTextAlign('left').run();
                            activeAlignDropdown = false;
                        "
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <AlignLeft class="h-3.5 w-3.5" /> Rata Kiri
                    </button>
                    <button
                        type="button"
                        @click="
                            editor.chain().focus().setTextAlign('center').run();
                            activeAlignDropdown = false;
                        "
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <AlignCenter class="h-3.5 w-3.5" /> Rata Tengah
                    </button>
                    <button
                        type="button"
                        @click="
                            editor.chain().focus().setTextAlign('right').run();
                            activeAlignDropdown = false;
                        "
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <AlignRight class="h-3.5 w-3.5" /> Rata Kanan
                    </button>
                    <button
                        type="button"
                        @click="
                            editor
                                .chain()
                                .focus()
                                .setTextAlign('justify')
                                .run();
                            activeAlignDropdown = false;
                        "
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <AlignJustify class="h-3.5 w-3.5" /> Rata Kiri Kanan
                    </button>
                </div>
            </div>

            <!-- Table dropdown -->
            <div class="relative">
                <button
                    type="button"
                    @click.stop="toggleDropdown('table')"
                    class="flex items-center gap-0.5 rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Tabel"
                >
                    <TableIcon class="h-4 w-4" />
                    <ChevronDown class="h-3 w-3" />
                </button>
                <div
                    v-if="activeTableDropdown"
                    class="absolute left-0 z-50 mt-1 w-[180px] rounded-lg border border-neutral-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        type="button"
                        @click="insertTable"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <Plus class="h-3.5 w-3.5 text-green-500" /> Sisipkan
                        Tabel 3x3
                    </button>
                    <div
                        class="my-1 h-px bg-neutral-200 dark:bg-zinc-800"
                    ></div>
                    <button
                        type="button"
                        @click="addRowBefore"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Tambah Baris Sebelum
                    </button>
                    <button
                        type="button"
                        @click="addRowAfter"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Tambah Baris Sesudah
                    </button>
                    <button
                        type="button"
                        @click="deleteRow"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 text-red-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Hapus Baris
                    </button>
                    <div
                        class="my-1 h-px bg-neutral-200 dark:bg-zinc-800"
                    ></div>
                    <button
                        type="button"
                        @click="addColBefore"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Tambah Kolom Sebelum
                    </button>
                    <button
                        type="button"
                        @click="addColAfter"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Tambah Kolom Sesudah
                    </button>
                    <button
                        type="button"
                        @click="deleteCol"
                        class="w-full px-3 py-1.5 text-left text-neutral-700 text-red-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        Hapus Kolom
                    </button>
                    <div
                        class="my-1 h-px bg-neutral-200 dark:bg-zinc-800"
                    ></div>
                    <button
                        type="button"
                        @click="deleteTable"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-left font-semibold text-neutral-700 text-red-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-zinc-800"
                    >
                        <Trash2 class="h-3.5 w-3.5" /> Hapus Tabel
                    </button>
                </div>
            </div>

            <!-- Links -->
            <button
                type="button"
                @click="openLinkModal"
                :class="{
                    'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                        editor.isActive('link'),
                }"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Sisipkan Link"
            >
                <LinkIcon class="h-4 w-4" />
            </button>

            <!-- Image icon -->
            <button
                type="button"
                @click="handleImageClick"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Sisipkan Gambar (Upload atau URL)"
            >
                <ImageIcon class="h-4 w-4" />
            </button>
            <input
                ref="fileInputRef"
                type="file"
                class="hidden"
                accept="image/*"
                @change="uploadImage"
            />

            <!-- Video icon -->
            <button
                type="button"
                @click="openVideoModal"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Sisipkan Video (YouTube / Direct Link)"
            >
                <VideoIcon class="h-4 w-4" />
            </button>

            <!-- Upload icon -->
            <button
                type="button"
                @click="showUploadModal = true"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Unggah Berkas"
            >
                <Upload class="h-4 w-4" />
            </button>

            <!-- Formula math -->
            <button
                type="button"
                @click="insertMath"
                class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                title="Sisipkan Rumus Matematika"
            >
                <Sigma class="h-4 w-4" />
            </button>

            <!-- Image sizing options (Active when image selected) -->
            <button
                v-if="editor.isActive('image')"
                type="button"
                @click="resizeImage"
                class="flex items-center gap-1 rounded border border-amber-200 bg-amber-100 p-1.5 text-xs font-semibold text-amber-800 transition-colors hover:bg-amber-200 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/40"
                title="Ubah Ukuran Gambar Terpilih"
            >
                Ubah Ukuran
            </button>

            <!-- Fullscreen, Code, Help aligned to the right -->
            <div class="ml-auto flex items-center gap-1">
                <button
                    type="button"
                    @click="isFullscreen = !isFullscreen"
                    class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    :title="isFullscreen ? 'Layar Kecil' : 'Layar Penuh'"
                >
                    <Minimize2
                        v-if="isFullscreen"
                        class="h-4 w-4 text-amber-600"
                    />
                    <Maximize2 v-else class="h-4 w-4" />
                </button>
                <button
                    type="button"
                    @click="toggleCodeView"
                    :class="{
                        'bg-neutral-200 text-amber-600 dark:bg-zinc-800':
                            isCodeView,
                    }"
                    class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Kode HTML"
                >
                    <Code class="h-4 w-4" />
                </button>
                <button
                    type="button"
                    @click="showHelpModal = true"
                    class="rounded p-1.5 text-neutral-600 transition-colors hover:bg-neutral-200 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    title="Bantuan"
                >
                    <HelpCircle class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Editor Content / HTML Editor -->
        <div
            :class="[isFullscreen ? 'flex-1 overflow-y-auto' : '']"
            class="min-h-[150px] bg-white dark:bg-zinc-950"
        >
            <EditorContent
                v-show="!isCodeView"
                :editor="editor"
                class="h-full"
            />
            <textarea
                v-show="isCodeView"
                v-model="codeContentText"
                class="min-h-[150px] w-full resize-none border-t border-neutral-200 bg-zinc-900 p-4 font-mono text-xs text-zinc-100 focus:outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-200"
                :class="[isFullscreen ? 'h-full' : '']"
                placeholder="<!-- Masukkan Kode HTML Anda di sini -->"
            ></textarea>
        </div>

        <!-- Custom Modals -->

        <!-- Image Modal -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[450px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Sisipkan Gambar
                    </h3>
                    <button
                        type="button"
                        @click="showImageModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div
                    class="flex rounded-lg bg-neutral-100 p-1 dark:bg-zinc-800"
                >
                    <button
                        type="button"
                        @click="imageTab = 'upload'"
                        class="flex-1 rounded-md py-1.5 text-xs font-bold transition-all"
                        :class="
                            imageTab === 'upload'
                                ? 'bg-white text-neutral-900 shadow-sm dark:bg-zinc-700 dark:text-neutral-100'
                                : 'text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        Upload Komputer
                    </button>
                    <button
                        type="button"
                        @click="imageTab = 'url'"
                        class="flex-1 rounded-md py-1.5 text-xs font-bold transition-all"
                        :class="
                            imageTab === 'url'
                                ? 'bg-white text-neutral-900 shadow-sm dark:bg-zinc-700 dark:text-neutral-100'
                                : 'text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        URL Gambar
                    </button>
                </div>

                <div class="py-2">
                    <div v-show="imageTab === 'upload'" class="space-y-4">
                        <div
                            @click="fileInputRef?.click()"
                            class="group flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-neutral-300 p-8 text-center transition-all hover:border-amber-500 hover:bg-neutral-50 dark:border-zinc-700 dark:hover:border-amber-500 dark:hover:bg-zinc-800/30"
                        >
                            <div
                                class="rounded-full bg-neutral-100 p-3 text-neutral-500 transition-colors group-hover:text-amber-600 dark:bg-zinc-800"
                            >
                                <ImageIcon class="h-6 w-6" />
                            </div>
                            <span
                                class="text-xs font-bold text-neutral-600 dark:text-neutral-400"
                            >
                                Klik untuk memilih file dari komputer Anda
                            </span>
                            <span class="text-[10px] text-neutral-400">
                                Mendukung JPEG, PNG, GIF (Maks. 2MB)
                            </span>
                        </div>
                    </div>

                    <div v-show="imageTab === 'url'" class="space-y-3">
                        <label class="block text-xs font-bold text-neutral-500"
                            >Masukkan Link URL Gambar:</label
                        >
                        <input
                            v-model="imageUrlInput"
                            type="text"
                            class="h-9 w-full rounded-lg border-neutral-300 bg-white px-3 text-xs text-neutral-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
                            placeholder="Contoh: https://site.com/image.jpg"
                            @keyup.enter="insertUrlImage"
                        />
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showImageModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 transition-colors hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                    <button
                        v-if="imageTab === 'url'"
                        type="button"
                        @click="insertUrlImage"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white transition-colors hover:bg-amber-600"
                    >
                        Sisipkan Gambar
                    </button>
                </div>
            </div>
        </div>

        <!-- Image Resize Modal -->
        <div
            v-if="showResizeModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[400px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Ubah Ukuran Gambar
                    </h3>
                    <button
                        type="button"
                        @click="showResizeModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-neutral-500"
                        >Masukkan lebar gambar:</label
                    >
                    <input
                        v-model="resizeWidthInput"
                        type="text"
                        class="h-9 w-full rounded-lg border-neutral-300 bg-white px-3 text-xs text-neutral-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
                        placeholder="Contoh: 200, 350, atau 50%"
                        @keyup.enter="applyResize"
                    />
                    <div class="mt-1 flex flex-wrap gap-1">
                        <button
                            type="button"
                            @click="resizeWidthInput = '150'"
                            class="rounded bg-neutral-100 px-2 py-1 text-[10px] hover:bg-neutral-200 dark:bg-zinc-800"
                        >
                            150px
                        </button>
                        <button
                            type="button"
                            @click="resizeWidthInput = '300'"
                            class="rounded bg-neutral-100 px-2 py-1 text-[10px] hover:bg-neutral-200 dark:bg-zinc-800"
                        >
                            300px
                        </button>
                        <button
                            type="button"
                            @click="resizeWidthInput = '25%'"
                            class="rounded bg-neutral-100 px-2 py-1 text-[10px] hover:bg-neutral-200 dark:bg-zinc-800"
                        >
                            25%
                        </button>
                        <button
                            type="button"
                            @click="resizeWidthInput = '50%'"
                            class="rounded bg-neutral-100 px-2 py-1 text-[10px] hover:bg-neutral-200 dark:bg-zinc-800"
                        >
                            50%
                        </button>
                        <button
                            type="button"
                            @click="resizeWidthInput = '100%'"
                            class="rounded bg-neutral-100 px-2 py-1 text-[10px] hover:bg-neutral-200 dark:bg-zinc-800"
                        >
                            100%
                        </button>
                    </div>
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showResizeModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="applyResize"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600"
                    >
                        Terapkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Math Modal -->
        <div
            v-if="showMathModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[400px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Rumus Matematika (LaTeX)
                    </h3>
                    <button
                        type="button"
                        @click="showMathModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-neutral-500"
                        >Masukkan formula LaTeX:</label
                    >
                    <input
                        v-model="mathFormulaInput"
                        type="text"
                        class="h-9 w-full rounded-lg border-neutral-300 bg-white px-3 text-xs text-neutral-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
                        placeholder="Contoh: \frac{a}{b} atau x^2 + y^2 = r^2"
                        @keyup.enter="applyMath"
                    />
                    <div class="text-[10px] text-neutral-400">
                        Rumus akan ditampilkan secara otomatis dalam format
                        matematika terstruktur.
                    </div>
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showMathModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="applyMath"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600"
                    >
                        Sisipkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Link Modal -->
        <div
            v-if="showLinkModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[400px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Sisipkan Link
                    </h3>
                    <button
                        type="button"
                        @click="showLinkModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-neutral-500"
                        >URL Link:</label
                    >
                    <input
                        v-model="linkUrlInput"
                        type="text"
                        class="h-9 w-full rounded-lg border-neutral-300 bg-white px-3 text-xs text-neutral-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
                        placeholder="Contoh: www.google.com"
                        @keyup.enter="insertLink"
                    />
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showLinkModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="insertLink"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600"
                    >
                        Sisipkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Video Modal -->
        <div
            v-if="showVideoModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[400px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Sisipkan Video
                    </h3>
                    <button
                        type="button"
                        @click="showVideoModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-neutral-500"
                        >URL Video (YouTube / Vimeo / Direct Link):</label
                    >
                    <input
                        v-model="videoUrlInput"
                        type="text"
                        class="h-9 w-full rounded-lg border-neutral-300 bg-white px-3 text-xs text-neutral-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-100"
                        placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                        @keyup.enter="insertVideo"
                    />
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showVideoModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="insertVideo"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600"
                    >
                        Sisipkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload File Modal -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[400px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Unggah Berkas / Dokumen
                    </h3>
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="space-y-4">
                    <div
                        @click="fileUploadInputRef?.click()"
                        class="group flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-neutral-300 p-8 text-center transition-all hover:border-amber-500 hover:bg-neutral-50 dark:border-zinc-700 dark:hover:border-amber-500 dark:hover:bg-zinc-800/30"
                    >
                        <div
                            class="rounded-full bg-neutral-100 p-3 text-neutral-500 transition-colors group-hover:text-amber-600 dark:bg-zinc-800"
                        >
                            <Upload class="h-6 w-6" />
                        </div>
                        <span
                            class="text-xs font-bold text-neutral-600 dark:text-neutral-400"
                        >
                            Klik untuk memilih berkas dari komputer Anda
                        </span>
                        <span class="text-[10px] text-neutral-400">
                            Mendukung berkas PDF, Word, Excel, Gambar, dll.
                            (Maks. 5MB)
                        </span>
                    </div>
                    <input
                        ref="fileUploadInputRef"
                        type="file"
                        class="hidden"
                        @change="uploadFile"
                    />
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="rounded-lg border border-neutral-200 px-4 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-50 dark:border-zinc-700 dark:text-neutral-400 dark:hover:bg-zinc-800"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Help Modal -->
        <div
            v-if="showHelpModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex w-[450px] animate-in flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl duration-150 zoom-in-95 fade-in dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-2 dark:border-zinc-800"
                >
                    <h3
                        class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Panduan & Pintasan
                    </h3>
                    <button
                        type="button"
                        @click="showHelpModal = false"
                        class="rounded p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-300"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div
                    class="max-h-[300px] space-y-3 overflow-y-auto pr-1 text-xs text-neutral-600 dark:text-neutral-400"
                >
                    <div>
                        <h4
                            class="mb-1 font-bold text-neutral-800 dark:text-neutral-200"
                        >
                            Pintasan Keyboard:
                        </h4>
                        <ul class="list-inside list-disc space-y-1 pl-1">
                            <li>
                                <kbd
                                    class="rounded border border-neutral-200 bg-neutral-100 px-1.5 py-0.5 dark:border-zinc-700 dark:bg-zinc-800"
                                    >Ctrl + B</kbd
                                >
                                : Tebal (Bold)
                            </li>
                            <li>
                                <kbd
                                    class="rounded border border-neutral-200 bg-neutral-100 px-1.5 py-0.5 dark:border-zinc-700 dark:bg-zinc-800"
                                    >Ctrl + I</kbd
                                >
                                : Miring (Italic)
                            </li>
                            <li>
                                <kbd
                                    class="rounded border border-neutral-200 bg-neutral-100 px-1.5 py-0.5 dark:border-zinc-700 dark:bg-zinc-800"
                                    >Ctrl + U</kbd
                                >
                                : Garis Bawah (Underline)
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4
                            class="mb-1 font-bold text-neutral-800 dark:text-neutral-200"
                        >
                            Rumus Matematika:
                        </h4>
                        <p>
                            Gunakan tombol
                            <Sigma
                                class="mx-0.5 inline h-3.5 w-3.5 text-amber-500"
                            />
                            untuk menyisipkan rumus LaTeX. Rumus akan di-render
                            secara dinamis menggunakan KaTeX.
                        </p>
                        <p class="mt-1">
                            Contoh penulisan:
                            <code
                                class="rounded bg-neutral-100 px-1 font-mono dark:bg-zinc-800"
                                >\frac{a}{b}</code
                            >,
                            <code
                                class="rounded bg-neutral-100 px-1 font-mono dark:bg-zinc-800"
                                >x^2 + y^2 = r^2</code
                            >.
                        </p>
                    </div>
                    <div>
                        <h4
                            class="mb-1 font-bold text-neutral-800 dark:text-neutral-200"
                        >
                            Pengaturan Gambar:
                        </h4>
                        <p>
                            Klik pada gambar untuk menampilkan tombol "Ubah
                            Ukuran". Masukkan ukuran dalam piksel (misal
                            <code
                                class="rounded bg-neutral-100 px-1 font-mono dark:bg-zinc-800"
                                >200</code
                            >) atau persentase (misal
                            <code
                                class="rounded bg-neutral-100 px-1 font-mono dark:bg-zinc-800"
                                >50%</code
                            >).
                        </p>
                    </div>
                </div>
                <div
                    class="flex justify-end border-t border-neutral-100 pt-2 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="showHelpModal = false"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Prose overrides for Editor content to match Tailwind Typography */
.ProseMirror p {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}
.ProseMirror table {
    border-collapse: collapse;
    margin: 0;
    overflow: hidden;
    table-layout: fixed;
    width: 100%;
}
.ProseMirror td,
.ProseMirror th {
    border: 1px solid #d4d4d8; /* zinc-300 */
    box-sizing: border-box;
    min-width: 1em;
    padding: 3px 5px;
    position: relative;
    vertical-align: top;
}
.dark .ProseMirror td,
.dark .ProseMirror th {
    border-color: #3f3f46; /* zinc-700 */
}
.ProseMirror th {
    background-color: #f4f4f5; /* zinc-100 */
    font-weight: bold;
    text-align: left;
}
.dark .ProseMirror th {
    background-color: #18181b; /* zinc-900 */
}
/* Ensure editor contents wrap cleanly */
.ProseMirror {
    outline: none;
    min-height: 150px;
    height: 100%;
    word-break: break-word;
}
/* Style text selection */
.ProseMirror ::selection {
    background: rgba(245, 158, 11, 0.3);
}
</style>
