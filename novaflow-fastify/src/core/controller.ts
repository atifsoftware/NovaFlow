/**
 * NovaFlow Fastify - Base Controller
 * Provides view rendering, JSON responses, and request lifecycle helpers
 */

import { FastifyReply, FastifyRequest } from 'fastify';

export abstract class BaseController {
  /**
   * Render an HTML/EJS view template with data
   */
  protected view(reply: FastifyReply, viewPath: string, data: Record<string, any> = {}): void {
    reply.header('Content-Type', 'text/html; charset=utf-8');
    reply.view(viewPath, {
      ...data,
      currentYear: new Date().getFullYear(),
      appName: 'NovaFlow Fastify MVC'
    });
  }

  /**
   * Send structured JSON response
   */
  protected json(reply: FastifyReply, data: any, statusCode: number = 200): void {
    reply.status(statusCode).send({
      status: statusCode >= 200 && statusCode < 300 ? 'success' : 'error',
      data
    });
  }

  /**
   * Redirect to a specific URL
   */
  protected redirect(reply: FastifyReply, url: string, statusCode: number = 302): void {
    reply.redirect(statusCode, url);
  }
}
