import React, { useState } from 'react';
import { 
  BookOpen, 
  Terminal, 
  Database, 
  Layers, 
  Shield, 
  Cpu, 
  Copy, 
  Check, 
  ExternalLink 
} from 'lucide-react';

export default function Docs({ lang }) {
  const isBangla = lang === 'bn';
  const [activeSection, setActiveSection] = useState('getting-started');
  const [copiedId, setCopiedId] = useState(null);

  const copyToClipboard = (text, id) => {
    navigator.clipboard.writeText(text);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const sections = [
    { id: 'getting-started', label: isBangla ? '১. পরিচিতি ও সেটআপ' : '1. Getting Started' },
    { id: 'database', label: isBangla ? '২. ডাটাবেজ ও কুয়েরি বিল্ডার' : '2. Multi-DB QueryBuilder' },
    { id: 'inertia', label: isBangla ? '৩. Inertia.js + React 19' : '3. Inertia.js & React 19' },
    { id: 'storage-queue', label: isBangla ? '৪. স্টোরেজ ও কিউ সিস্টেম' : '4. Storage & Queues' },
    { id: 'cli', label: isBangla ? '৫. সিএলআই কোড জেনারেটর' : '5. CLI Scaffolder & Tinker' },
    { id: 'go-engine', label: isBangla ? '৬. Go / Fastify স্পিড ও সিকিউরিটি' : '6. Go Engine & Security' },
    { id: 'fastify-edition', label: isBangla ? '৭. Fastify (Node.js) এডিশন' : '7. Fastify (Node.js) Port' }
  ];

  return (
    <div className={`max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 ${isBangla ? 'font-bangla' : ''}`}>
      <div className="pb-6 border-b border-slate-800">
        <div className="flex items-center gap-2">
          <BookOpen className="w-6 h-6 text-rose-500" />
          <h1 className="text-2xl font-bold text-white tracking-tight">
            {isBangla ? 'NovaFlow ফ্রেমওয়ার্ক ডকুমেন্টেশন' : 'NovaFlow Framework Documentation'}
          </h1>
        </div>
        <p className="text-xs text-slate-400 mt-1">
          {isBangla 
            ? 'আর্কিটেকচার, মেথড রেফারেন্স এবং কোড স্নাইপেট গাইড' 
            : 'Complete architectural reference, syntax guides, and runtime examples'}
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-12 gap-8 mt-6">
        {/* Navigation Sidebar */}
        <div className="md:col-span-3 space-y-1">
          {sections.map(sec => (
            <button
              key={sec.id}
              onClick={() => setActiveSection(sec.id)}
              className={`w-full text-left px-3.5 py-2.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center justify-between ${
                activeSection === sec.id
                  ? 'bg-rose-600 text-white shadow-md shadow-rose-950/40'
                  : 'text-slate-400 hover:text-white hover:bg-slate-900'
              }`}
            >
              <span>{sec.label}</span>
            </button>
          ))}
        </div>

        {/* Content Body */}
        <div className="md:col-span-9 space-y-8 text-xs text-slate-300 leading-relaxed">
          {activeSection === 'getting-started' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">Getting Started with NovaFlow</h2>
                <p>
                  NovaFlow is a zero-bloat PHP MVC framework designed for maximum throughput, low memory footprint, and clean architectural separation. It decouples SQL query compilation from execution via dialect Grammars and supports modern SPAs via native Inertia.js protocol.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                <span className="font-mono text-xs font-bold text-rose-400 uppercase tracking-wider block">
                  Installation via Composer
                </span>
                <pre className="p-3 bg-slate-950 rounded-lg text-emerald-400 font-mono overflow-x-auto">
{`git clone https://github.com/atifsoftware/NovaFlow.git
cd NovaFlow
composer install
npm install && npm run build`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                <span className="font-mono text-xs font-bold text-cyan-400 uppercase tracking-wider block">
                  Environment Configuration (.env)
                </span>
                <pre className="p-3 bg-slate-950 rounded-lg text-amber-300 font-mono overflow-x-auto">
{`DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=novaflow_db
DB_USER=root
DB_PASS=secret
CACHE_DRIVER=file
QUEUE_CONNECTION=sync`}
                </pre>
              </div>
            </div>
          )}

          {activeSection === 'database' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">Multi-Database Fluent QueryBuilder</h2>
                <p>
                  NovaFlow’s QueryBuilder automatically uses the dialect Grammar for the active connection (MySQL, PostgreSQL, or SQLite) and generates safe parameterized prepared statements.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-rose-400">1. Select Queries & Where Clauses</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-emerald-400 font-mono overflow-x-auto">
{`use NovaFlow\\Core\\DB;

$users = DB::table('users')
    ->where('status', 'active')
    ->whereIn('role', ['admin', 'manager'])
    ->orderBy('id', 'DESC')
    ->limit(10)
    ->get();`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-orange-400">2. Atomic Transactions with Deadlock Retry</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-emerald-400 font-mono overflow-x-auto">
{`$order = DB::transaction(function() {
    DB::table('accounts')->where('id', 1)->decrement('balance', 500);
    DB::table('accounts')->where('id', 2)->increment('balance', 500);

    return DB::table('orders')->insertGetId([
        'user_id' => 1,
        'amount'  => 500,
        'status'  => 'completed'
    ]);
}, 3); // Retries up to 3 times on deadlocks`}
                </pre>
              </div>
            </div>
          )}

          {activeSection === 'inertia' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">Inertia.js + React 19 Frontend</h2>
                <p>
                  NovaFlow supports the Inertia.js protocol directly out of the box. Render full React components from your PHP controllers without writing any REST API endpoints!
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-indigo-400">PHP Controller:</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-sky-300 font-mono overflow-x-auto">
{`public function index(): void
{
    $this->inertia('Products/Index', [
        'products' => Product::where('status', 'active')->get(),
        'title'    => 'Our Products'
    ]);
}`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-pink-400">React Component (resources/js/Pages/Products/Index.jsx):</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-amber-200 font-mono overflow-x-auto">
{`export default function Index({ products, title }) {
    return (
        <div className="p-8">
            <h1 className="text-2xl font-bold">{title}</h1>
            <div className="grid grid-cols-3 gap-4 mt-6">
                {products.map(p => (
                    <div key={p.id} className="p-4 rounded-xl bg-slate-900">
                        <h3>{p.name}</h3>
                        <p>\${p.price}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}`}
                </pre>
              </div>
            </div>
          )}

          {activeSection === 'storage-queue' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">Storage Abstraction & Asynchronous Queues</h2>
                <p>
                  Manage files across Local disks and AWS S3 / Cloudflare R2 / MinIO with zero SDK bloat using native SigV4 signing. Execute background jobs asynchronously with multi-driver queues.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-emerald-400">Storage Drivers (Local & S3):</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-slate-200 font-mono overflow-x-auto">
{`use NovaFlow\\Core\\Storage;

// Local Disk
Storage::put('avatars/user_1.png', $fileContent);

// S3 Cloud Storage (Native SigV4, ~15 KB overhead)
Storage::disk('s3')->put('backups/2026.zip', $archive);
$url = Storage::disk('s3')->url('backups/2026.zip');`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-purple-400">Queue System (Sync, File, DB, Redis):</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-slate-200 font-mono overflow-x-auto">
{`use NovaFlow\\Core\\Queue;

Queue::push(new SendWelcomeEmailJob('user@example.com'));
Queue::push(new GenerateReportJob(), 'reports', 300); // 300s delay`}
                </pre>
              </div>
            </div>
          )}

          {activeSection === 'cli' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">CLI Scaffolder & Tinker REPL</h2>
                <p>
                  Generate models, migrations, seeders, and controllers with instant CLI commands, or drop into an interactive REPL session.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-rose-400">CLI Commands:</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-cyan-300 font-mono overflow-x-auto">
{`php cli.php tinker               # Start REPL
php cli.php migrate              # Run migrations
php cli.php db:seed              # Seed database
php cli.php make:model Product   # Scaffold model
php cli.php routes               # View registered routes
php cli.php health               # Check server health`}
                </pre>
              </div>
            </div>
          )}

          {activeSection === 'go-engine' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">Go / Fastify Persistent Engine & Security Hardening</h2>
                <p>
                  NovaFlow is pre-configured with persistent worker architectures (FrankenPHP & Spiral RoadRunner) to match the sub-millisecond execution speeds and 30,000+ RPS of Go and Fastify.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-cyan-400">1. FrankenPHP Go Persistent Worker (Caddyfile + worker.php)</span>
                <p className="text-xs text-slate-400">
                  Runs PHP scripts resident in persistent Go Goroutines. Zero file parsing overhead on each HTTP request:
                </p>
                <pre className="p-3 bg-slate-950 rounded-lg text-emerald-400 font-mono overflow-x-auto">
{`# Run with FrankenPHP binary
frankenphp run --config /app/Caddyfile

# Or via Docker
docker run -p 3000:3000 $(docker build -f Dockerfile.frankenphp -q .)`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-orange-400">2. Spiral RoadRunner (.rr.yaml)</span>
                <p className="text-xs text-slate-400">
                  Multiplexes requests through high-speed IPC pipes into 8 worker processes:
                </p>
                <pre className="p-3 bg-slate-950 rounded-lg text-amber-300 font-mono overflow-x-auto">
{`./rr serve -c .rr.yaml`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-emerald-400">3. Security Hardening Patches</span>
                <ul className="list-disc list-inside space-y-1 text-slate-300 pt-1">
                  <li><strong>QueryBuilder:</strong> Replaced unsafe <code>addslashes</code> with strict parameter sanitization in join/having clauses.</li>
                  <li><strong>SQL Grammar:</strong> Added regex injection filter blocking <code>;</code>, comments (<code>--</code>, <code>/*</code>), and unquoted identifiers.</li>
                  <li><strong>JWT Engine:</strong> Enforced cryptographically strong 256-bit session key generation, eliminating static dev key exploitation.</li>
                  <li><strong>Upload Protection:</strong> Removed dangerous archive types (<code>zip</code>) and enforced <code>SetHandler None</code> in <code>public/uploads/.htaccess</code>.</li>
                </ul>
              </div>
            </div>
          )}

          {activeSection === 'fastify-edition' && (
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-bold text-white mb-2">NovaFlow Fastify (Node.js 22 + TypeScript) Port</h2>
                <p>
                  A standalone Fastify implementation of NovaFlow located in <code>/novaflow-fastify</code> directory. It delivers native Node.js libuv event loop concurrency, handling over 75,000 requests per second.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-cyan-400">1. Fastify Directory Layout</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-emerald-400 font-mono text-[11px] overflow-x-auto">
{`novaflow-fastify/
├── src/
│   ├── core/
│   │   ├── grammar.ts       # MySQL, PostgreSQL, SQLite Grammars
│   │   ├── queryBuilder.ts  # Fluent QueryBuilder
│   │   └── store.ts         # High-Speed Memory Store & Cache
│   ├── routes/
│   │   ├── auth.ts          # /api/v1/auth (login, register)
│   │   ├── users.ts         # /api/v1/users (CRUD, profile)
│   │   └── health.ts        # /health & /benchmark
│   ├── app.ts               # Fastify factory, CORS & JWT
│   └── server.ts            # Server entry point (port 3001)
├── package.json
└── README.md`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-rose-400">2. How to Start Fastify Server</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-amber-300 font-mono text-[11px] overflow-x-auto">
{`cd novaflow-fastify
npx tsx src/server.ts

# Output:
# 🚀 NovaFlow Fastify Server running at http://0.0.0.0:3001
# ⚡ Performance: Up to 75,000+ req/sec on Node.js 22`}
                </pre>
              </div>

              <div className="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                <span className="font-mono font-bold text-emerald-400">3. Quick Test Endpoints</span>
                <pre className="p-3 bg-slate-950 rounded-lg text-sky-300 font-mono text-[11px] overflow-x-auto">
{`# 1. Health Probe
curl http://localhost:3001/health

# 2. Live In-Process Benchmark
curl http://localhost:3001/benchmark

# 3. User Login
curl -X POST http://localhost:3001/api/v1/auth/login \\
  -H "Content-Type: application/json" \\
  -d '{"email":"tanvir@example.com","password":"password123"}'`}
                </pre>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
