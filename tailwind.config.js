/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
    ],
    safelist: [
        'bg-santander-red',
        'text-santander-red',
        'border-santander-red',
    ],
    theme: {
        extend: {
            colors: {
                'santander': {
                    'red':      '#EC0000',
                    'red-dark': '#B30000',
                    'red-light':'#FF4D4D',
                },
                'neutral': {
                    'bg':   '#F5F5F5',
                    'text': '#444444',
                    'muted':'#888888',
                    'card': '#FFFFFF',
                    'border':'#E0E0E0',
                    'sidebar': '#1C1C1E',
                    'sidebar-hover': '#2C2C2E',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
            },
            boxShadow: {
                'card':   '0 2px 8px rgba(0,0,0,0.08)',
                'card-lg':'0 4px 16px rgba(0,0,0,0.12)',
            },
            borderRadius: {
                'card': '0.75rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
