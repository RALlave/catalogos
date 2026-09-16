<!--
    Basic rich text editor: bold, italic, underline and lists.

    It saves HTML with only those tags; the API cleans it again before
    storing. `max` counts visible characters, the same as the API limit.
-->

<script setup>
import { watch } from 'vue'
import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { CharacterCount, Placeholder } from '@tiptap/extensions'

import AppIcon from '@/components/AppIcon.vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    id: { type: String, required: true },
    max: { type: Number, default: null },
    placeholder: { type: String, default: '' },
    hasError: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

/* An empty editor still holds `<p></p>`: it goes out as an empty string. */
function value(editor) {
    return editor.isEmpty ? '' : editor.getHTML()
}

const editor = useEditor({
    content: props.modelValue || '',
    extensions: [
        StarterKit.configure({
            heading: false,
            blockquote: false,
            code: false,
            codeBlock: false,
            horizontalRule: false,
            strike: false,
            link: false,
            trailingNode: false,
        }),
        CharacterCount.configure({ limit: props.max }),
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    editorProps: {
        attributes: {
            id: props.id,
            class: 'rich-editor-content',
            role: 'textbox',
            'aria-multiline': 'true',
        },
    },
    onUpdate: ({ editor }) => emit('update:modelValue', value(editor)),
})

/*
 * The form usually loads its data after the editor is created. Only an
 * outside change is written into the editor, and without emitting: the
 * loaded value stays as is, so the unsaved changes check does not fire.
 */
watch(() => props.modelValue, (html) => {
    if (editor.value && (html || '') !== value(editor.value)) {
        editor.value.commands.setContent(html || '', { emitUpdate: false })
    }
})

const tools = [
    { name: 'bold', icon: 'bold', title: 'Negrita', run: chain => chain.toggleBold() },
    { name: 'italic', icon: 'italic', title: 'Cursiva', run: chain => chain.toggleItalic() },
    { name: 'underline', icon: 'underline', title: 'Subrayado', run: chain => chain.toggleUnderline() },
    { name: 'bulletList', icon: 'list', title: 'Lista con viñetas', run: chain => chain.toggleBulletList() },
    { name: 'orderedList', icon: 'list-ordered', title: 'Lista numerada', run: chain => chain.toggleOrderedList() },
]

function apply(tool) {
    tool.run(editor.value.chain().focus()).run()
}
</script>

<template>
    <div class="rich-editor" :class="{ 'has-error': hasError }">
        <div v-if="editor" class="rich-editor-toolbar" role="toolbar" aria-label="Formato del texto">
            <button
                v-for="tool in tools"
                :key="tool.name"
                class="rich-editor-tool"
                :class="{ 'is-active': editor.isActive(tool.name) }"
                type="button"
                :title="tool.title"
                :aria-label="tool.title"
                :aria-pressed="editor.isActive(tool.name)"
                @click="apply(tool)"
            >
                <AppIcon :name="tool.icon" />
            </button>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>
