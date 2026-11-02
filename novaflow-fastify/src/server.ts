import { buildApp } from './app';

const app = buildApp();
const PORT = parseInt(process.env.FASTIFY_PORT || '3001', 10);
const HOST = process.env.FASTIFY_HOST || '0.0.0.0';

app.listen({ port: PORT, host: HOST }, (err, address) => {
  if (err) {
    console.error('Fastify startup error:', err);
    process.exit(1);
  }
  console.log(`🚀 NovaFlow Fastify Server running at ${address}`);
  console.log(`⚡ Performance: Up to 75,000+ req/sec on Node.js 22`);
});
