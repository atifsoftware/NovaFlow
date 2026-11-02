/**
 * NovaFlow Fastify - HTTP Kernel
 * Central pipeline registering global and named route middlewares
 */

import { FastifyInstance } from 'fastify';
import { logRequestMiddleware, rateLimitMiddleware, roleMiddleware, MiddlewareHandler } from './middleware';

export class Kernel {
  /**
   * Named route middleware aliases
   */
  public static routeMiddleware: Record<string, (...args: any[]) => MiddlewareHandler> = {
    throttle: (max = 60, windowSec = 60) => rateLimitMiddleware(max, windowSec),
    role: (requiredRole: string) => roleMiddleware(requiredRole)
  };

  /**
   * Boot global middleware hooks on the Fastify instance
   */
  public static bootstrap(app: FastifyInstance): void {
    // 1. Request logging & timing
    app.addHook('onRequest', async (request, reply) => {
      logRequestMiddleware(request, reply);
    });

    // 2. Add response timing header
    app.addHook('onSend', async (request, reply, payload) => {
      const start = (request as any)._startTime;
      if (start) {
        const latency = (performance.now() - start).toFixed(3);
        reply.header('X-Response-Time', `${latency}ms`);
        reply.header('X-Powered-By', 'NovaFlow Fastify Kernel v2.1.0');
      }
      return payload;
    });
  }
}
