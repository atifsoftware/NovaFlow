import fastify, { FastifyInstance, FastifyReply, FastifyRequest } from 'fastify';
import cors from '@fastify/cors';
import jwt from '@fastify/jwt';
import pointOfView from '@fastify/view';
import ejs from 'ejs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import { authRoutes } from './routes/auth';
import { userRoutes } from './routes/users';
import { healthRoutes } from './routes/health';
import { registerModules } from './modules';
import { HomeController } from './controllers/HomeController';
import { UserController } from './controllers/UserController';
import { Kernel } from './core/kernel';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

declare module 'fastify' {
  interface FastifyInstance {
    authenticate: (request: FastifyRequest, reply: FastifyReply) => Promise<void>;
  }
}

export function buildApp(): FastifyInstance {
  const app = fastify({
    logger: false
  });

  // Boot Kernel Global Middlewares (Timing, Headers, Logging)
  Kernel.bootstrap(app);

  // Enable CORS
  app.register(cors, {
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']
  });

  // JWT Registration
  app.register(jwt, {
    secret: process.env.JWT_SECRET || 'novaflow_fastify_super_secret_jwt_key_2026'
  });

  // Register View Engine (EJS templates with master layout)
  app.register(pointOfView, {
    engine: { ejs },
    root: path.join(__dirname, 'views'),
    layout: 'layouts/main.ejs',
    viewExt: 'ejs'
  });

  // Authentication Hook decorator
  app.decorate('authenticate', async (request: FastifyRequest, reply: FastifyReply) => {
    try {
      await request.jwtVerify();
    } catch (err) {
      reply.status(401).send({
        status: 'error',
        message: 'Unauthorized: Invalid or missing Bearer token'
      });
    }
  });

  // Web MVC Routes
  const homeController = new HomeController();
  const userController = new UserController();

  app.get('/', (req, reply) => homeController.index(req, reply));
  app.get('/mvc/users', (req, reply) => userController.index(req, reply));

  // Register HMVC Business Modules (Sales, Inventory)
  registerModules(app);

  // Register REST API Routes
  app.register(authRoutes, { prefix: '/api/v1/auth' });
  app.register(userRoutes, { prefix: '/api/v1' });
  app.register(healthRoutes);

  // Global Error Handler
  app.setErrorHandler((error, _request, reply) => {
    if (error.validation) {
      return reply.status(422).send({
        status: 'error',
        message: 'Schema validation error',
        errors: error.validation
      });
    }
    return reply.status(error.statusCode || 500).send({
      status: 'error',
      message: error.message || 'Internal Server Error'
    });
  });

  return app;
}
