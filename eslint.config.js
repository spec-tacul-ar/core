import { defineConfig } from 'eslint/config';
import globals from "globals";
import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';

export default defineConfig([
    {
        ignores: ['node_modules/', 'public/', 'vendor/'],
    },
    ...pluginVue.configs['flat/essential'],
    js.configs.recommended,
    {
        files: ['resources/js/**/*.{js,vue}'],
        languageOptions: { globals: globals.browser },
        rules: {
            'indent': ['error', 4, {'SwitchCase': 1}],
            'semi': ['error', 'always'],
            'vue/multi-word-component-names': 'off',
        },
    },
]);