import { FastifyRequest, FastifyReply } from 'fastify';
import { BaseController } from '../core/controller';
import { User } from '../models/User';

export class UserController extends BaseController {
  public async index(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    const users = User.all();
    this.view(reply, 'users/index.ejs', {
      title: 'Users List — NovaFlow MVC',
      users
    });
  }

  public async show(request: FastifyRequest<{ Params: { id: string } }>, reply: FastifyReply): Promise<void> {
    const id = parseInt(request.params.id, 10);
    const user = User.find(id);

    if (!user) {
      return this.json(reply, { error: 'User not found' }, 404);
    }

    return this.json(reply, user);
  }
}
