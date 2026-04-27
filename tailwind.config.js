import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#f5f7fa',
                    100: '#e4e9f1',
                    200: '#c8d2e2',
                    300: '#9eb0c9',
                    400: '#6e85ab',
                    500: '#4d6691',
                    600: '#3d5278',
                    700: '#324362',
                    800: '#2c3a52',
                    900: '#283246',
                    950: '#1a2030',
                },
                accent: {
                    500: '#d97706',
                    600: '#b45309',
                },
            },
        },
    },
    plugins: [forms],
};
