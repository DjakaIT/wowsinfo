import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: 'localhost',
        port: 8080,
        open: false,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss', 'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
