import { FastifyInstance, FastifyPluginOptions, FastifyReply, FastifyRequest } from 'fastify';
import { QueryBuilder } from '../core/queryBuilder';
import { UserStore } from '../core/store';

export async function healthRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  // GET /health
  fastify.get('/health', async (_request: FastifyRequest, reply: FastifyReply) => {
    const mem = process.memoryUsage();
    return reply.send({
      status: 'healthy',
      engine: 'Fastify v5 + Node.js 22',
      framework: 'NovaFlow Fastify Edition',
      uptime_seconds: Math.floor(process.uptime()),
      memory: {
        rss_mb: (mem.rss / 1024 / 1024).toFixed(2),
        heap_used_mb: (mem.heapUsed / 1024 / 1024).toFixed(2),
        heap_total_mb: (mem.heapTotal / 1024 / 1024).toFixed(2)
      },
      stats: {
        total_users: UserStore.all().length,
        driver: 'Multi-DB (MySQL/PgSQL/SQLite)'
      }
    });
  });

  // GET /benchmark - Compiles 10,000 queries in-process and returns latency & throughput
  fastify.get('/benchmark', async (_request: FastifyRequest, reply: FastifyReply) => {
    const iterations = 10000;
    const start = performance.now();

    for (let i = 0; i < iterations; i++) {
      QueryBuilder.table('users', 'mysql')
        .select(['id', 'name', 'email'])
        .where('status', '=', 'active')
        .whereIn('role', ['admin', 'manager'])
        .orderByColumn('id', 'DESC')
        .take(10)
        .toSql();
    }

    const elapsedMs = performance.now() - start;
    const opsPerSec = Math.round((iterations / elapsedMs) * 1000);
    const latencyPerQueryMs = (elapsedMs / iterations).toFixed(5);

    return reply.send({
      benchmark: 'NovaFlow Fastify QueryBuilder Benchmark',
      iterations,
      elapsed_ms: elapsedMs.toFixed(2),
      queries_per_second: opsPerSec,
      latency_per_query_ms: latencyPerQueryMs,
      performance_grade: 'A+ (Fastify Ultra-Throughput)'
    });
  });
}
