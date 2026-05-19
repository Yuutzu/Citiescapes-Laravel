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
                sans:  ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Lora', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // brand: maroon / brick — drawn from the building's structural beams
                brand: {
                    50:  '#FDF6F2',
                    100: '#F8E2D7',
                    200: '#EFC0AB',
                    300: '#E39E80',
                    400: '#D2774E',
                    500: '#B65A36',
                    600: '#9A4525',
                    700: '#7A3322',
                    800: '#5E2818',
                    900: '#422018',
                    950: '#2C1612',
                },
                // marigold: warm yellow — drawn from the building's painted walls
                marigold: {
                    50:  '#FFFCF2',
                    100: '#FBF4DA',
                    200: '#F6E5A5',
                    300: '#EFD06F',
                    400: '#E8B832',
                    500: '#D9A226',
                    600: '#B6831C',
                    700: '#8A6213',
                    800: '#5D420C',
                    900: '#3D2C08',
                },
                // paper: soft warm backgrounds (cream / butter / parchment)
                paper: {
                    50:  '#FDFAF1',
                    100: '#FBF7EC',
                    200: '#F4EDD9',
                    300: '#E8DCB4',
                    400: '#DAC988',
                },
                accent: {
                    500: '#D9A226',
                    600: '#B6831C',
                },
            },
        },
    },
    plugins: [forms],
};
