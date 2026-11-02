import { FastifyRequest, FastifyReply } from 'fastify';
import { BaseController } from '../../../core/controller';
import { Item } from '../models/Item';

export class ItemController extends BaseController {
  public async index(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    const items = Item.all();
    this.view(reply, 'inventory/items.ejs', {
      title: 'Inventory Items — NovaFlow HMVC',
      items
    });
  }

  public async apiIndex(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    const items = Item.all();
    this.json(reply, items);
  }
}
