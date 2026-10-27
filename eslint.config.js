import js from '@eslint/js'
import { defineConfig } from 'eslint/config'
import globals from 'globals'
import pluginVue from 'eslint-plugin-vue'

export default defineConfig([
    {
        ignores: ['public/build/**', 'node_modules/**', 'vendor/**', 'storage/**'],
    },
    {
        files: ['resources/js/**/*.{js,vue}'],
        extends: [js.configs.recommended, ...pluginVue.configs['flat/essential']],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
            },
        },
        rules: {
            'no-unused-vars': ['error', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
            'vue/multi-word-component-names': 'off',
            'vue/no-reserved-component-names': 'off',
        },
    },
])
