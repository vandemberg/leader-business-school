import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.tsx",
    ],

    theme: {
        extend: {
            colors: {
                primary: "#4A00E0",
                secondary: "#8E2DE2",
                "background-light": "#f6f6f8",
                "background-dark": "#101622",
                "surface-dark": "#1A1A2E",
                "border-dark": "#2A2A35",
                "text-primary-dark": "#FFFFFF",
                "text-secondary-dark": "#A0A0B0",
            },
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                display: ["Inter", "sans-serif"],
                heading: ["Poppins", "sans-serif"],
            },
            borderRadius: {
                DEFAULT: "0.5rem",
                lg: "0.75rem",
                xl: "1rem",
                full: "9999px",
            },
        },
    },

    plugins: [forms],
};
