import { FastifyInstance, FastifyPluginOptions, FastifyRequest, FastifyReply } from 'fastify';
import { UserStore } from '../core/store';

export async function authRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  // Login Endpoint with JSON schema validation
  fastify.post('/login', {
    schema: {
      body: {
        type: 'object',
        required: ['email', 'password'],
        properties: {
          email: { type: 'string', format: 'email' },
          password: { type: 'string', minLength: 4 }
        }
      },
      response: {
        200: {
          type: 'object',
          properties: {
            status: { type: 'string' },
            token: { type: 'string' },
            token_type: { type: 'string' },
            expires_in: { type: 'number' },
            user: {
              type: 'object',
              properties: {
                id: { type: 'number' },
                name: { type: 'string' },
                email: { type: 'string' },
                role: { type: 'string' }
              }
            }
          }
        }
      }
    }
  }, async (request: FastifyRequest<{ Body: { email: string; password: string } }>, reply: FastifyReply) => {
    const { email, password } = request.body;
    const user = UserStore.findByEmail(email);

    if (!user || user.passwordHash !== password) {
      return reply.status(401).send({
        status: 'error',
        message: 'Invalid credentials provided'
      });
    }

    if (user.status !== 'active') {
      return reply.status(403).send({
        status: 'error',
        message: 'Your account has been suspended'
      });
    }

    // Sign JWT using Fastify JWT plugin
    const token = fastify.jwt.sign({
      sub: user.id,
      name: user.name,
      email: user.email,
      role: user.role
    });

    return reply.send({
      status: 'success',
      token,
      token_type: 'Bearer',
      expires_in: 86400,
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role
      }
    });
  });

  // Register Endpoint
  fastify.post('/register', {
    schema: {
      body: {
        type: 'object',
        required: ['name', 'email', 'password'],
        properties: {
          name: { type: 'string', minLength: 2 },
          email: { type: 'string', format: 'email' },
          password: { type: 'string', minLength: 6 }
        }
      }
    }
  }, async (request: FastifyRequest<{ Body: { name: string; email: string; password: string } }>, reply: FastifyReply) => {
    const { name, email, password } = request.body;
    const existing = UserStore.findByEmail(email);

    if (existing) {
      return reply.status(409).send({
        status: 'error',
        message: 'Email is already registered'
      });
    }

    const newUser = UserStore.create({
      name,
      email,
      passwordHash: password,
      role: 'user',
      status: 'active'
    });

    const token = fastify.jwt.sign({
      sub: newUser.id,
      name: newUser.name,
      email: newUser.email,
      role: newUser.role
    });

    return reply.status(201).send({
      status: 'success',
      message: 'Account created successfully',
      token,
      user: {
        id: newUser.id,
        name: newUser.name,
        email: newUser.email,
        role: newUser.role
      }
    });
  });
}
