import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    hmr: {
      host: 'localhost',
    },
    watch: {
      usePolling: true, // Essencial para Docker/WSL2
      interval: 100,    // Checa mudanças a cada 100ms
    },
    host: '0.0.0.0',    // Permite conexões externas
    port: 5173,         // Garanta que esta porta está exposta no seu docker-compose
  }
})
