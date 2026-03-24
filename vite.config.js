import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

const contains = (chunk, names) => names.some((part) => chunk.includes(part));

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (contains(id, ['/@tiptap/', '/prosemirror-'])) {
                        return 'vendor-rte';
                    }

                    if (contains(id, ['node_modules'])) {
                         return 'vendor';
                    }
                },
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/export.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
