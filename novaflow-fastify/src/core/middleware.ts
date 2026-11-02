/**
 * NovaFlow Fastify - Middleware Handlers
 */

import { FastifyReply, FastifyRequest, HookHandlerDoneFunction } from 'fastify';

export type MiddlewareHandler = (request: FastifyRequest, reply: FastifyReply) => Promise<void> | void;

export const logRequestMiddleware: MiddlewareHandler = (request, _reply) => {
  (request as any)._startTime = performance.now();
};

export const rateLimitMiddleware = (maxRequests = 60, windowSeconds = 60): MiddlewareHandler => {
  const ipStore = new Map<string, { count: number; resetTime: number }>();

  return async (request, reply) => {
    const ip = request.ip || '127.0.0.1';
    const now = Date.now();
    let record = ipStore.get(ip);

    if (!record || now > record.resetTime) {
      record = { count: 1, resetTime: now + windowSeconds * 1000 };
      ipStore.set(ip, record);
    } else {
      record.count++;
    }

    reply.header('X-RateLimit-Limit', maxRequests);
    reply.header('X-RateLimit-Remaining', Math.max(0, maxRequests - record.count));

    if (record.count > maxRequests) {
      return reply.status(429).send({
        status: 'error',
        message: 'Too many requests. Please slow down.'
      });
    }
  };
};

export const roleMiddleware = (requiredRole: string): MiddlewareHandler => {
  return async (request, reply) => {
    const user = request.user as { role?: string } | undefined;
    if (!user || user.role !== requiredRole) {
      return reply.status(403).send({
        status: 'error',
        message: `Forbidden: Requires [${requiredRole}] role access.`
      });
    }
  };
};
