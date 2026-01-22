// vite.config.js
import { defineConfig } from "file:///Users/amitpatel/Documents/GitHub/GainTradeX/node_modules/vite/dist/node/index.js";
import laravel from "file:///Users/amitpatel/Documents/GitHub/GainTradeX/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///Users/amitpatel/Documents/GitHub/GainTradeX/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import path from "path";
var __vite_injected_original_dirname = "/Users/amitpatel/Documents/GitHub/GainTradeX";
var vite_config_default = defineConfig({
  transpileDependencies: true,
  server: {
    host: "0.0.0.0",
    port: 5173,
    https: false
  },
  build: {
    manifest: "manifest.json",
    outDir: "public/build",
    rollupOptions: {
      output: {
        manualChunks: void 0
      }
    },
    // Ensure assets work with both HTTP and HTTPS
    assetsInlineLimit: 0
  },
  plugins: [
    vue(),
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true,
      buildDirectory: "build"
    })
  ],
  resolve: {
    alias: {
      "@axios": path.resolve(__vite_injected_original_dirname, "resources/js/src/axios.js"),
      // Define the alias for Axios
      "@": path.resolve(__vite_injected_original_dirname, "resources/js/src")
      // Adjust to your project structure
    }
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCIvVXNlcnMvYW1pdHBhdGVsL0RvY3VtZW50cy9HaXRIdWIvTWFjeGdhaW5cIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIi9Vc2Vycy9hbWl0cGF0ZWwvRG9jdW1lbnRzL0dpdEh1Yi9NYWN4Z2Fpbi92aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vVXNlcnMvYW1pdHBhdGVsL0RvY3VtZW50cy9HaXRIdWIvTWFjeGdhaW4vdml0ZS5jb25maWcuanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJztcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xuXG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSdcbmltcG9ydCBwYXRoIGZyb20gJ3BhdGgnOyAvLyBJbXBvcnQgcGF0aCBtb2R1bGVcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gICAgdHJhbnNwaWxlRGVwZW5kZW5jaWVzOiB0cnVlLFxuICAgIHNlcnZlcjoge1xuICAgICAgICBob3N0OiAnMC4wLjAuMCcsXG4gICAgICAgIHBvcnQ6IDUxNzMsXG4gICAgICAgIGh0dHBzOiBmYWxzZSxcbiAgICB9LFxuICAgIGJ1aWxkOiB7XG4gICAgICAgIG1hbmlmZXN0OiAnbWFuaWZlc3QuanNvbicsXG4gICAgICAgIG91dERpcjogJ3B1YmxpYy9idWlsZCcsXG4gICAgICAgIHJvbGx1cE9wdGlvbnM6IHtcbiAgICAgICAgICAgIG91dHB1dDoge1xuICAgICAgICAgICAgICAgIG1hbnVhbENodW5rczogdW5kZWZpbmVkLFxuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuICAgICAgICAvLyBFbnN1cmUgYXNzZXRzIHdvcmsgd2l0aCBib3RoIEhUVFAgYW5kIEhUVFBTXG4gICAgICAgIGFzc2V0c0lubGluZUxpbWl0OiAwLFxuICAgIH0sXG4gICAgcGx1Z2luczogW1xuICAgICAgICB2dWUoKSxcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogWydyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLCAncmVzb3VyY2VzL2pzL2FwcC5qcyddLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgICAgIGJ1aWxkRGlyZWN0b3J5OiAnYnVpbGQnLFxuICAgICAgICB9KSxcbiAgICBdLFxuICAgIHJlc29sdmU6IHtcbiAgICAgICAgYWxpYXM6IHtcbiAgICAgICAgICAgICdAYXhpb3MnOiBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCAncmVzb3VyY2VzL2pzL3NyYy9heGlvcy5qcycpLCAgLy8gRGVmaW5lIHRoZSBhbGlhcyBmb3IgQXhpb3NcbiAgICAgICAgICAgICdAJzogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgJ3Jlc291cmNlcy9qcy9zcmMnKSAvLyBBZGp1c3QgdG8geW91ciBwcm9qZWN0IHN0cnVjdHVyZVxuICAgICAgICB9LFxuICAgIH0sXG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBZ1QsU0FBUyxvQkFBb0I7QUFDN1UsT0FBTyxhQUFhO0FBRXBCLE9BQU8sU0FBUztBQUNoQixPQUFPLFVBQVU7QUFKakIsSUFBTSxtQ0FBbUM7QUFLekMsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsdUJBQXVCO0FBQUEsRUFDdkIsUUFBUTtBQUFBLElBQ0osTUFBTTtBQUFBLElBQ04sTUFBTTtBQUFBLElBQ04sT0FBTztBQUFBLEVBQ1g7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNILFVBQVU7QUFBQSxJQUNWLFFBQVE7QUFBQSxJQUNSLGVBQWU7QUFBQSxNQUNYLFFBQVE7QUFBQSxRQUNKLGNBQWM7QUFBQSxNQUNsQjtBQUFBLElBQ0o7QUFBQTtBQUFBLElBRUEsbUJBQW1CO0FBQUEsRUFDdkI7QUFBQSxFQUNBLFNBQVM7QUFBQSxJQUNMLElBQUk7QUFBQSxJQUNKLFFBQVE7QUFBQSxNQUNKLE9BQU8sQ0FBQyx5QkFBeUIscUJBQXFCO0FBQUEsTUFDdEQsU0FBUztBQUFBLE1BQ1QsZ0JBQWdCO0FBQUEsSUFDcEIsQ0FBQztBQUFBLEVBQ0w7QUFBQSxFQUNBLFNBQVM7QUFBQSxJQUNMLE9BQU87QUFBQSxNQUNILFVBQVUsS0FBSyxRQUFRLGtDQUFXLDJCQUEyQjtBQUFBO0FBQUEsTUFDN0QsS0FBSyxLQUFLLFFBQVEsa0NBQVcsa0JBQWtCO0FBQUE7QUFBQSxJQUNuRDtBQUFBLEVBQ0o7QUFDSixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
