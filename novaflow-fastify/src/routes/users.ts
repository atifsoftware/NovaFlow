import { FastifyInstance, FastifyPluginOptions, FastifyRequest, FastifyReply } from 'fastify';
import { UserStore } from '../core/store';

export async function userRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  // GET /api/v1/profile (JWT Authenticated)
  fastify.get('/profile', {
    preHandler: [fastify.authenticate]
  }, async (request: FastifyRequest, reply: FastifyReply) => {
    const userPayload = request.user as { sub: number; email: string; name: string; role: string };
    const user = UserStore.findById(userPayload.sub);

    if (!user) {
      return reply.status(404).send({ status: 'error', message: 'User not found' });
    }

    return reply.send({
      status: 'success',
      data: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        status: user.status,
        createdAt: user.createdAt
      }
    });
  });

  // GET /api/v1/users (List with search & pagination)
  fastify.get('/users', async (request: FastifyRequest<{ Querystring: { search?: string; page?: string; limit?: string } }>, reply: FastifyReply) => {
    const { search, page = '1', limit = '10' } = request.query;
    let users = UserStore.all();

    if (search) {
      const q = search.toLowerCase();
      users = users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
    }

    const pageNum = parseInt(page, 10) || 1;
    const limitNum = parseInt(limit, 10) || 10;
    const start = (pageNum - 1) * limitNum;
    const paginated = users.slice(start, start + limitNum);

    return reply.send({
      status: 'success',
      data: paginated.map(u => ({
        id: u.id,
        name: u.name,
        email: u.email,
        role: u.role,
        status: u.status,
        createdAt: u.createdAt
      })),
      meta: {
        current_page: pageNum,
        per_page: limitNum,
        total: users.length,
        last_page: Math.ceil(users.length / limitNum) || 1
      }
    });
  });

  // GET /api/v1/users/:id
  fastify.get('/users/:id', async (request: FastifyRequest<{ Params: { id: string } }>, reply: FastifyReply) => {
    const id = parseInt(request.params.id, 10);
    const user = UserStore.findById(id);

    if (!user) {
      return reply.status(404).send({ status: 'error', message: 'User not found' });
    }

    return reply.send({
      status: 'success',
      data: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        status: user.status,
        createdAt: user.createdAt
      }
    });
  });

  // POST /api/v1/users (Create new user)
  fastify.post('/users', {
    schema: {
      body: {
        type: 'object',
        required: ['name', 'email'],
        properties: {
          name: { type: 'string', minLength: 2 },
          email: { type: 'string', format: 'email' },
          role: { type: 'string', enum: ['admin', 'manager', 'user'] },
          status: { type: 'string', enum: ['active', 'suspended'] }
        }
      }
    }
  }, async (request: FastifyRequest<{ Body: { name: string; email: string; role?: 'admin' | 'manager' | 'user'; status?: 'active' | 'suspended' } }>, reply: FastifyReply) => {
    const { name, email, role = 'user', status = 'active' } = request.body;

    const existing = UserStore.findByEmail(email);
    if (existing) {
      return reply.status(409).send({ status: 'error', message: 'User with this email already exists' });
    }

    const created = UserStore.create({
      name,
      email,
      passwordHash: 'password123',
      role,
      status
    });

    return reply.status(201).send({
      status: 'success',
      data: created
    });
  });

  // PUT /api/v1/users/:id (Update user)
  fastify.put('/users/:id', async (request: FastifyRequest<{ Params: { id: string }; Body: { name?: string; email?: string; role?: 'admin' | 'manager' | 'user'; status?: 'active' | 'suspended' } }>, reply: FastifyReply) => {
    const id = parseInt(request.params.id, 10);
    const updated = UserStore.update(id, request.body);

    if (!updated) {
      return reply.status(404).send({ status: 'error', message: 'User not found' });
    }

    return reply.send({
      status: 'success',
      data: updated
    });
  });

  // DELETE /api/v1/users/:id
  fastify.delete('/users/:id', async (request: FastifyRequest<{ Params: { id: string } }>, reply: FastifyReply) => {
    const id = parseInt(request.params.id, 10);
    const deleted = UserStore.delete(id);

    if (!deleted) {
      return reply.status(404).send({ status: 'error', message: 'User not found' });
    }

    return reply.send({
      status: 'success',
      message: 'User deleted successfully'
    });
  });

  // PATCH /api/v1/users/:id/status (Toggle status)
  fastify.patch('/users/:id/status', async (request: FastifyRequest<{ Params: { id: string } }>, reply: FastifyReply) => {
    const id = parseInt(request.params.id, 10);
    const toggled = UserStore.toggleStatus(id);

    if (!toggled) {
      return reply.status(404).send({ status: 'error', message: 'User not found' });
    }

    return reply.send({
      status: 'success',
      data: toggled
    });
  });
}
