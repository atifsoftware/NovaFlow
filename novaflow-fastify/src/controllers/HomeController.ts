import { FastifyRequest, FastifyReply } from 'fastify';
import { BaseController } from '../core/controller';

export class HomeController extends BaseController {
  public async index(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    this.view(reply, 'home.ejs', {
      title: 'Home — NovaFlow Fastify MVC',
      heading: 'স্বাগতম NovaFlow Fastify MVC-তে',
      message: 'একটি সম্পূর্ণ মডার্ন Model-View-Controller (MVC) এবং Modular (HMVC) আর্কিটেকচার যা Fastify-এর গতি ও ফ্লেক্সিবিলিটি নিয়ে এসেছে।'
    });
  }
}
