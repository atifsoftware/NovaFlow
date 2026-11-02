import { FastifyInstance } from 'fastify';
import { salesModuleRoutes } from './sales/routes';
import { inventoryModuleRoutes } from './inventory/routes';

export function registerModules(app: FastifyInstance): void {
  // Sales Module mounted at /sales
  app.register(salesModuleRoutes, { prefix: '/sales' });

  // Inventory Module mounted at /inventory
  app.register(inventoryModuleRoutes, { prefix: '/inventory' });
}
