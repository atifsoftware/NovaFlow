import { FastifyInstance, FastifyPluginOptions } from 'fastify';
import { InvoiceController } from './controllers/InvoiceController';

export async function salesModuleRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  const controller = new InvoiceController();

  // HTML MVC View Route
  fastify.get('/invoices', (req, reply) => controller.index(req, reply));

  // REST API Route
  fastify.get('/api/invoices', (req, reply) => controller.apiIndex(req, reply));
}
