import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            hotFile: "public/editorjs.hot",
            buildDirectory: "editorjs",
            input: [
                "resources/css/editorjs.css",
                "resources/js/editorjs/editorjs.js",
            ],
            refresh: true,
            valetTls: "responserocket.test",
        }),
    ],
});
