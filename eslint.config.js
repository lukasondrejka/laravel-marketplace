import globals from "globals";
import pluginJs from "@eslint/js";
import pluginReactConfigRecommended from "eslint-plugin-react/configs/recommended.js";
import pluginPrettierRecommended from "eslint-plugin-prettier/recommended";
import { fixupConfigRules } from "@eslint/compat";

export default [
    pluginJs.configs.recommended,
    ...fixupConfigRules(pluginReactConfigRecommended),
    pluginPrettierRecommended,
    {
        files: ["**/*.{js,mjs,cjs,jsx}"],
        languageOptions: {
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
            globals: {
                ...globals.browser,
                'route': 'readonly',
            },
        },
        settings: {
            react: {
                version: "detect",
            },
        },
        rules: {
            'react/jsx-first-prop-new-line': ['error', 'multiline'],
            'react/jsx-max-props-per-line': ['error', { maximum: 1, when: 'multiline' }],
            'react/jsx-indent-props': ['error', 2],
            'react/jsx-closing-bracket-location': ['error', 'tag-aligned'],
            'react/react-in-jsx-scope': 'off',
            'react/prop-types': 'off',
            'no-console': 'warn',
            'linebreak-style': 'off',
            'quotes': ['error', 'single'],
            'semi': ['error', 'always'],
        },
    },
];
