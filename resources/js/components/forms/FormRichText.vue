<template>
    <div>
        <div class="flex mb-1">
            <label :for="id" class="font-bold">{{ label }}&nbsp;<span class="text-red-400" v-if="required">*</span></label>

            <InfoPopover class="ml-auto" v-if="has_help_slot">
                <slot name="help"></slot>
            </InfoPopover>
        </div>

        <div class="border border-gray-300 rounded overflow-hidden focus-within:ring-offset-0 focus-within:ring-1">
            <div class="bg-gray-50 flex items-center flex-wrap gap-1 p-2 px-2">
                <RichTextButton :editor="editor" name="bold" icon="bold" @click="editor.chain().focus().toggleBold().run()" />
                <RichTextButton :editor="editor" name="italic" icon="italic" @click="editor.chain().focus().toggleItalic().run()" />
                <RichTextButton :editor="editor" name="strike" icon="strike" @click="editor.chain().focus().toggleStrike().run()" />
                <RichTextButton :editor="editor" name="code" icon="code" @click="editor.chain().focus().toggleCode().run()" />
                <RichTextButton :editor="editor" name="link" icon="link" @click="setLink" />

                <RichTextButton :editor="editor" name="heading" icon="heading" @click="editor.chain().focus().toggleHeading({ level: heading }).run()" />
                <RichTextButton :editor="editor" name="blockquote" icon="quote" label="Quotation" @click="editor.chain().focus().toggleBlockquote().run()" />
                <RichTextButton :editor="editor" name="bulletList" icon="ul" label="Bullet list" @click="editor.chain().focus().toggleBulletList().run()" />
                <RichTextButton :editor="editor" name="orderedList" icon="ol" label="Numbered list" @click="editor.chain().focus().toggleOrderedList().run()" />
                <RichTextButton :editor="editor" name="codeBlock" icon="pre" label="Code block" @click="editor.chain().focus().toggleCodeBlock().run()" />
                <RichTextButton :editor="editor" name="hr" icon="hr" label="Divider" @click="editor.chain().focus().setHorizontalRule().run()" />
            </div>

            <EditorContent :editor="editor" class="max-h-[calc(100vh/2)] overflow-auto" />
        </div>
    </div>
</template>

<script>
import InfoPopover from '@/components/InfoPopover.vue';
import RichTextButton from '@/components/RichTextButton.vue';
import StarterKit from '@tiptap/starter-kit';
import { Editor, EditorContent } from '@tiptap/vue-3';

export default {
    beforeUnmount() {
        this.editor.destroy();
    },
    components: {
        EditorContent,
        InfoPopover,
        RichTextButton,
    },
    computed: {
        has_help_slot() {
            return this.$slots.help;
        },
    },
    data() {
        return {
            editor: null,
        };
    },
    emits: ['update:modelValue'],
    methods: {
        setLink() {
            const previousUrl = this.editor.getAttributes('link').href;
            const url = window.prompt('URL', previousUrl);

            if (url === null) {
                return;
            }

            if (url === '') {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run();

                return;
            }

            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
    },
    mounted() {
        this.editor = new Editor({
            content: this.modelValue,
            editorProps: {
                attributes: {
                    class: 'prose p-4 outline-none',
                },
            },
            extensions: [
                StarterKit.configure({
                    heading: {
                        levels: [this.heading],
                    },
                    link: {
                        openOnClick: false,
                    },
                }),
            ],
            onUpdate: () => {
                this.$emit('update:modelValue', this.editor.getHTML());
            },
        });
    },
    props: {
        error: Array,
        heading: {
            type: Number,
            default: 2
        },
        id: String,
        label: String,
        modelValue: {
            type: String,
            default: '',
        },
        required: Boolean,
    },
    watch: {
        modelValue(value) {
            if (this.editor.getHTML() !== value) {
                this.editor.commands.setContent(value);
            }
        },
    },
};
</script>
