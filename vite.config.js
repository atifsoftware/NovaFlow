import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';
import path from 'node:path';

function hotFilePlugin() {
  const hotPath = path.resolve('public/hot');
  return {
    name: 'novaflow-hot-file',
    configureServer(server) {
      server.httpServer?.once('listening', () => {
        let url = '';
        const resolved = server.resolvedUrls?.local?.[0];
        if (resolved) {
          url = resolved.replace(/\/$/, '');
        } else {
          const address = server.httpServer?.address();
          let host = typeof address === 'object' && address?.address ? address.address : 'localhost';
          if (host === '::' || host === '::1' || host === '0.0.0.0') {
            host = 'localhost';
          } else if (host.includes(':')) {
            host = `[${host}]`;
          }
          const port = typeof address === 'object' && address?.port ? address.port : 5173;
          const protocol = server.config.server.https ? 'https' : 'http';
          url = `${protocol}://${host}:${port}`;
        }
        fs.writeFileSync(hotPath, url);
      });

      const cleanHot = () => {
        if (fs.existsSync(hotPath)) {
          try { fs.unlinkSync(hotPath); } catch {}
        }
      };

      process.on('exit', cleanHot);
      process.on('SIGINT', () => { cleanHot(); process.exit(); });
      process.on('SIGTERM', () => { cleanHot(); process.exit(); });
      process.on('SIGHUP', () => { cleanHot(); process.exit(); });
    },
    buildStart() {
      if (fs.existsSync(hotPath)) {
        try { fs.unlinkSync(hotPath); } catch {}
      }
    },
    closeBundle() {
      // Re-create .gitkeep so empty build dir can be tracked cleanly by git
      const gitkeep = path.resolve('public/build/.gitkeep');
      try {
        if (!fs.existsSync(gitkeep)) {
          fs.writeFileSync(gitkeep, '');
        }
      } catch {}
    }
  };
}

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
    hotFilePlugin()
  ],
  publicDir: false, // Prevents copying public/ into outDir
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: 'manifest.json',
    rollupOptions: {
      input: 'resources/js/app.jsx',
      output: {
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]'
      }
    }
  }
});
