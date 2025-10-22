import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Soft Peachy/Coral palette for primary actions (replaces harsh orange/amber)
                primary: {
                    50: '#fdf5f3',
                    100: '#fbe9e4',
                    200: '#f7d3c9',
                    300: '#f0b5a5',
                    400: '#e78c76',
                    500: '#db6b4f',
                    600: '#c55242',
                    700: '#a43f36',
                    800: '#883833',
                    900: '#72342f',
                },
                // Soft Lavender palette for secondary elements (unique, not Bootstrap-like)
                secondary: {
                    50: '#f8f7fc',
                    100: '#f0eef9',
                    200: '#e4e0f4',
                    300: '#d1caeb',
                    400: '#b8acdf',
                    500: '#9f8bcf',
                    600: '#8a6ebe',
                    700: '#7558a6',
                    800: '#624a8a',
                    900: '#523f71',
                },
                // Soft Teal/Cyan for accents (replacing harsh indigo)
                accent: {
                    50: '#f0faf9',
                    100: '#d9f2f0',
                    200: '#b7e4e1',
                    300: '#88cfcc',
                    400: '#58b3b0',
                    500: '#3d9894',
                    600: '#307978',
                    700: '#2a6261',
                    800: '#274f4f',
                    900: '#244342',
                },
                // Softer neutral grays (less harsh than default)
                neutral: {
                    50: '#f9fafb',
                    100: '#f2f4f6',
                    200: '#e5e8ec',
                    300: '#d1d6dd',
                    400: '#9ea5b1',
                    500: '#6b7280',
                    600: '#4b5563',
                    700: '#374151',
                    800: '#252d3a',
                    900: '#1a202e',
                    950: '#0f1419',
                },
                // Soft success green (muted, not vibrant)
                success: {
                    50: '#f0faf4',
                    100: '#ddf3e4',
                    200: '#bce6cd',
                    300: '#8dd3ad',
                    400: '#5cbb88',
                    500: '#3a9e6a',
                    600: '#2a7f54',
                    700: '#236545',
                    800: '#1f5138',
                    900: '#1b4330',
                },
                // Soft warning amber (less harsh than Bootstrap warning)
                warning: {
                    50: '#fdfaf3',
                    100: '#faf2e0',
                    200: '#f4e3c1',
                    300: '#ebcd97',
                    400: '#e1b36b',
                    500: '#d69748',
                    600: '#c1793d',
                    700: '#a15c34',
                    800: '#834a31',
                    900: '#6d3e2b',
                },
                // Soft danger/error red (muted coral red)
                danger: {
                    50: '#fdf4f3',
                    100: '#fbe8e7',
                    200: '#f8d5d3',
                    300: '#f2b5b2',
                    400: '#e88882',
                    500: '#d96056',
                    600: '#c54239',
                    700: '#a5342e',
                    800: '#892f2b',
                    900: '#722c29',
                },
            },
        },
    },

    plugins: [forms],
};
