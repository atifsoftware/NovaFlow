import { FastifyRequest, FastifyReply } from 'fastify';
import { BaseController } from '../../../core/controller';
import { Invoice } from '../models/Invoice';

export class InvoiceController extends BaseController {
  public async index(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    const invoices = Invoice.all();
    this.view(reply, 'sales/invoices.ejs', {
      title: 'Sales Invoices — NovaFlow HMVC',
      invoices
    });
  }

  public async apiIndex(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    const invoices = Invoice.all();
    this.json(reply, invoices);
  }
}
