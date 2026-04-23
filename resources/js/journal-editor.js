import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';

document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.getElementById('tiptap-editor');
    if (!editorElement) {
        return;
    }

    const contentInput = document.getElementById('content-input');
    const wordCountEl = document.getElementById('word-count');
    const initialContent = contentInput ? contentInput.value : '';

    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit,
            Placeholder.configure({
                placeholder: 'Schrijf hier je dagboekentry...',
            }),
            CharacterCount,
        ],
        content: initialContent || '',
        onUpdate({ editor }) {
            if (contentInput) {
                contentInput.value = editor.getHTML();
            }
            if (wordCountEl) {
                wordCountEl.textContent = editor.storage.characterCount.words();
            }
        },
    });

    // Set initial word count
    if (wordCountEl) {
        wordCountEl.textContent = editor.storage.characterCount.words();
    }

    // Toolbar button handlers
    document.querySelectorAll('[data-tiptap-action]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = btn.dataset.tiptapAction;

            switch (action) {
                case 'bold':
                    editor.chain().focus().toggleBold().run();
                    break;
                case 'italic':
                    editor.chain().focus().toggleItalic().run();
                    break;
                case 'strike':
                    editor.chain().focus().toggleStrike().run();
                    break;
                case 'h2':
                    editor.chain().focus().toggleHeading({ level: 2 }).run();
                    break;
                case 'h3':
                    editor.chain().focus().toggleHeading({ level: 3 }).run();
                    break;
                case 'bullet':
                    editor.chain().focus().toggleBulletList().run();
                    break;
                case 'ordered':
                    editor.chain().focus().toggleOrderedList().run();
                    break;
                case 'blockquote':
                    editor.chain().focus().toggleBlockquote().run();
                    break;
                case 'hr':
                    editor.chain().focus().setHorizontalRule().run();
                    break;
                case 'undo':
                    editor.chain().focus().undo().run();
                    break;
                case 'redo':
                    editor.chain().focus().redo().run();
                    break;
            }

            updateToolbarState();
        });
    });

    function updateToolbarState() {
        document.querySelectorAll('[data-tiptap-action]').forEach((btn) => {
            const action = btn.dataset.tiptapAction;
            let isActive = false;

            switch (action) {
                case 'bold':       isActive = editor.isActive('bold'); break;
                case 'italic':     isActive = editor.isActive('italic'); break;
                case 'strike':     isActive = editor.isActive('strike'); break;
                case 'h2':         isActive = editor.isActive('heading', { level: 2 }); break;
                case 'h3':         isActive = editor.isActive('heading', { level: 3 }); break;
                case 'bullet':     isActive = editor.isActive('bulletList'); break;
                case 'ordered':    isActive = editor.isActive('orderedList'); break;
                case 'blockquote': isActive = editor.isActive('blockquote'); break;
            }

            btn.classList.toggle('is-active', isActive);
        });
    }

    editor.on('selectionUpdate', updateToolbarState);
    editor.on('update', updateToolbarState);
});
