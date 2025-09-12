import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import vue from '@vitejs/plugin-vue'
import path from 'path'; // Import path module
export default defineConfig({
    transpileDependencies: true,
    server: {
        host: '0.0.0.0',
        port: 5173,
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            }
        }
    },
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
    ],
    resolve: {
        alias: {
            '@axios': path.resolve(__dirname, 'resources/js/src/axios.js'),  // Define the alias for Axios
            '@': path.resolve(__dirname, 'resources/js/src') // Adjust to your project structure
        },
    },
});
