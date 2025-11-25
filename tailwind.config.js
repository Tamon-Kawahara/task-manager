import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
    safelist: [
        "bg-gray-200",
        "text-gray-800",
        "bg-blue-200",
        "text-blue-800",
        "bg-green-200",
        "text-green-800",
        "bg-gray-100",
        "bg-red-50",
        "text-red-700",
        "bg-yellow-50",
        "text-yellow-700",
        "bg-orange-50",
        "text-orange-700",
    ],
};
