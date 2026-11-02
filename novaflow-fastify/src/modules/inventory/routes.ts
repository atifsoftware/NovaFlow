import { FastifyInstance, FastifyPluginOptions } from 'fastify';
import { ItemController } from './controllers/ItemController';

export async function inventoryModuleRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  const controller = new ItemController();

  // HTML MVC View Route
  fastify.get('/items', (req, reply) => controller.index(req, reply));

  // REST API Route
  fastify.get('/api/items', (req, reply) => controller.apiIndex(req, reply));
}
