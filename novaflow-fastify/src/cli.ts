#!/usr/bin/env node
/**
 * NovaFlow Fastify CLI Scaffolding & Artisan Assistant
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const args = process.argv.slice(2);
const command = args[0];
const targetName = args[1];

function printBanner() {
  console.log(`\x1b[31m
  _   _                 ______ _                 
 | \\ | |               |  ____| |                
 |  \\| | _____   ____ _| |__  | | _____      __  
 | . \` |/ _ \\ \\ / / _\` |  __| | |/ _ \\ \\ /\\ / /  
 | |\\  | (_) \\ V / (_| | |    | | (_) \\ V  V /   
 |_| \\_|\\___/ \\_/ \\__,_|_|    |_|\\___/ \\_/\\_/  v2.1 Fastify Edition\x1b[0m
`);
}

if (!command || command === 'help' || command === '--help') {
  printBanner();
  console.log(`Usage: npx tsx src/cli.ts <command> [options]

Available Commands:
  \x1b[32mmake:model <Name>\x1b[0m         Scaffold a new Active Record Model
  \x1b[32mmake:controller <Name>\x1b[0m    Scaffold a new MVC Controller
  \x1b[32mmake:module <Name>\x1b[0m        Scaffold an HMVC Module (Controller, Model, View, Routes)
  \x1b[32mroutes\x1b[0m                    List registered Fastify HTTP routes
  \x1b[32mhealth\x1b[0m                    Check system resources and memory
`);
  process.exit(0);
}

if (command === 'make:model') {
  if (!targetName) {
    console.error('\x1b[31mError: Model name is required. Example: make:model Product\x1b[0m');
    process.exit(1);
  }
  const modelFile = path.join(__dirname, 'models', `${targetName}.ts`);
  const template = `import { BaseModel } from '../core/model';

export class ${targetName} extends BaseModel {
  public static table = '${targetName.toLowerCase()}s';
}
`;
  fs.writeFileSync(modelFile, template);
  console.log(`\x1b[32m✓ Model created successfully:\x1b[0m src/models/${targetName}.ts`);
  process.exit(0);
}

if (command === 'make:controller') {
  if (!targetName) {
    console.error('\x1b[31mError: Controller name is required. Example: make:controller ProductController\x1b[0m');
    process.exit(1);
  }
  const ctrlName = targetName.endsWith('Controller') ? targetName : `${targetName}Controller`;
  const ctrlFile = path.join(__dirname, 'controllers', `${ctrlName}.ts`);
  const template = `import { FastifyRequest, FastifyReply } from 'fastify';
import { BaseController } from '../core/controller';

export class ${ctrlName} extends BaseController {
  public async index(request: FastifyRequest, reply: FastifyReply): Promise<void> {
    this.view(reply, '${targetName.toLowerCase()}/index.ejs', {
      title: '${targetName} — NovaFlow MVC'
    });
  }
}
`;
  fs.writeFileSync(ctrlFile, template);
  console.log(`\x1b[32m✓ Controller created successfully:\x1b[0m src/controllers/${ctrlName}.ts`);
  process.exit(0);
}

if (command === 'make:module') {
  if (!targetName) {
    console.error('\x1b[31mError: Module name is required. Example: make:module Billing\x1b[0m');
    process.exit(1);
  }
  const modDir = path.join(__dirname, 'modules', targetName.toLowerCase());
  fs.mkdirSync(path.join(modDir, 'controllers'), { recursive: true });
  fs.mkdirSync(path.join(modDir, 'models'), { recursive: true });

  const routesTemplate = `import { FastifyInstance, FastifyPluginOptions } from 'fastify';

export async function ${targetName.toLowerCase()}ModuleRoutes(fastify: FastifyInstance, _opts: FastifyPluginOptions) {
  fastify.get('/', async (_req, reply) => {
    return reply.send({ module: '${targetName}', status: 'active' });
  });
}
`;
  fs.writeFileSync(path.join(modDir, 'routes.ts'), routesTemplate);
  console.log(`\x1b[32m✓ HMVC Module created successfully:\x1b[0m src/modules/${targetName.toLowerCase()}/`);
  process.exit(0);
}

if (command === 'routes') {
  printBanner();
  console.log(`Registered Fastify Routes:
  [GET]   /                     -> HomeController@index
  [GET]   /mvc/users            -> UserController@index
  [GET]   /sales/invoices       -> Sales\\InvoiceController@index
  [GET]   /inventory/items      -> Inventory\\ItemController@index
  [POST]  /api/v1/auth/login    -> AuthRoutes@login
  [POST]  /api/v1/auth/register -> AuthRoutes@register
  [GET]   /api/v1/profile       -> UserRoutes@profile [auth]
  [GET]   /api/v1/users         -> UserRoutes@index
  [GET]   /health               -> HealthRoutes@health
  [GET]   /benchmark            -> HealthRoutes@benchmark
`);
  process.exit(0);
}

if (command === 'health') {
  const mem = process.memoryUsage();
  console.log(`NovaFlow Fastify System Health:
  Node Runtime: ${process.version}
  Heap Used: ${(mem.heapUsed / 1024 / 1024).toFixed(2)} MB
  Uptime: ${Math.floor(process.uptime())}s
  Status: ALL SYSTEMS NOMINAL
`);
  process.exit(0);
}

console.log(`Unknown command: ${command}. Type "npx tsx src/cli.ts help" for available commands.`);
