import React, { useState, useRef, useEffect } from 'react';
import { 
  Terminal as TerminalIcon, 
  Database, 
  Layers, 
  Cpu, 
  Play, 
  RotateCcw, 
  Check, 
  Sparkles, 
  HelpCircle 
} from 'lucide-react';

export default function DatabasePlayground({ users, lang }) {
  const isBangla = lang === 'bn';
  const [commandHistory, setCommandHistory] = useState([
    {
      cmd: "DB::table('users')->count()",
      output: `=> ${users.length}`
    },
    {
      cmd: "DB::table('users')->first()",
      output: `=> {\n    "id": 1,\n    "name": "${users[0]?.name || 'Tanvir Hasan'}",\n    "email": "${users[0]?.email || 'tanvir@example.com'}",\n    "role": "admin"\n}`
    },
    {
      cmd: "Cache::get('site_status')",
      output: `=> "online (latency: 0.04ms)"`
    }
  ]);
  const [inputVal, setInputVal] = useState('');
  const terminalEndRef = useRef(null);

  const quickCommands = [
    "DB::table('users')->count()",
    "DB::table('users')->first()",
    "DB::table('users')->where('status', 'active')->get()",
    "Cache::get('site_status')",
    "php cli.php routes",
    "php cli.php health",
    "php cli.php migrate",
    "help"
  ];

  useEffect(() => {
    terminalEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [commandHistory]);

  const handleRunCommand = (cmdToRun) => {
    const trimmed = (cmdToRun || inputVal).trim();
    if (!trimmed) return;

    let output = '';

    if (trimmed === 'clear') {
      setCommandHistory([]);
      setInputVal('');
      return;
    } else if (trimmed === 'help') {
      output = `NovaFlow Tinker Interactive REPL Commands:
  • DB::table('users')->count()                     - Count total users in table
  • DB::table('users')->first()                     - Fetch first user record
  • DB::table('users')->where('status', 'active')->get() - Active users
  • Cache::get('site_status')                       - Inspect cache engine
  • php cli.php routes                              - List registered HTTP routes
  • php cli.php health                              - Probe framework latency & memory
  • php cli.php migrate                             - Execute schema migrations
  • clear                                           - Clear terminal screen`;
    } else if (trimmed.includes("users')->count()")) {
      output = `=> ${users.length}`;
    } else if (trimmed.includes("users')->first()")) {
      const u = users[0] || { id: 1, name: "Tanvir Hasan", email: "tanvir@example.com" };
      output = `=> ${JSON.stringify(u, null, 4)}`;
    } else if (trimmed.includes("where('status', 'active')->get()")) {
      const activeUsers = users.filter(u => u.status === 'active');
      output = `=> ${JSON.stringify(activeUsers, null, 4)}`;
    } else if (trimmed.includes("Cache::get('site_status')")) {
      output = `=> "online (in-memory Map cache hit)"`;
    } else if (trimmed.includes("routes")) {
      output = `+--------+-------------------------+----------------------------------+
| METHOD | ROUTE                   | CONTROLLER / ACTION              |
+--------+-------------------------+----------------------------------+
| GET    | /                       | HomeController@index             |
| GET    | /inertia                | HomeController@inertiaDemo       |
| GET    | /login                  | AuthController@login             |
| POST   | /login                  | AuthController@postLogin         |
| GET    | /admin/dashboard        | AdminDashboardController@index   |
| GET    | /admin/users            | AdminUserController@index        |
| GET    | /admin/settings         | AdminSettingsController@index    |
| POST   | /api/v1/login           | Api\\V1\\AuthApiController@login |
| GET    | /api/v1/profile         | Api\\V1\\UserApiController@profile|
| GET    | /api/v1/users           | Api\\V1\\UserApiController@index |
+--------+-------------------------+----------------------------------+`;
    } else if (trimmed.includes("health")) {
      output = `NovaFlow System Health:
  Framework Version: NovaFlow 2.1.0 Enterprise
  PHP Engine: 8.2.12 (Node.js 22 runtime)
  Database: MySQL / SQLite (Connected, 0.08ms)
  Cache: Memory Map (Online, 100% Hit Ratio)
  Memory Usage: 4.12 MB Peak RAM
  Status: ALL SYSTEMS NOMINAL`;
    } else if (trimmed.includes("migrate")) {
      output = `Executing database migrations:
  Migrating: 2026_04_11_000000_create_jobs_table.php ... DONE (0.012s)
  Migrating: 2026_04_12_000000_create_rbac_tables.php ... DONE (0.018s)
  Migrating: 2026_04_15_000000_create_users_table.php ... DONE (0.010s)
Database migration completed successfully! (3 migrations)`;
    } else {
      output = `novaflow: command not recognized: "${trimmed}". Type "help" for a list of valid commands.`;
    }

    setCommandHistory(prev => [...prev, { cmd: trimmed, output }]);
    setInputVal('');
  };

  return (
    <div className={`max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 ${isBangla ? 'font-bangla' : ''}`}>
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
          <div className="flex items-center gap-2">
            <TerminalIcon className="w-6 h-6 text-rose-500" />
            <h1 className="text-2xl font-bold text-white tracking-tight">
              {isBangla ? 'ইন্টারেক্টিভ টিংকার শেল ও সিএলআই কনসোল' : 'Interactive Tinker REPL & CLI Console'}
            </h1>
          </div>
          <p className="text-xs text-slate-400 mt-1">
            {isBangla 
              ? 'টার্মিনাল ছাড়াই সরাসরি ব্রাউজার থেকে NovaFlow DB এবং ক্যাশ কোড এক্সিকিউট করুন' 
              : 'Direct in-browser execution of NovaFlow QueryBuilder, Cache, and CLI commands'}
          </p>
        </div>

        <button
          onClick={() => setCommandHistory([])}
          className="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer self-start sm:self-auto"
        >
          <RotateCcw className="w-3.5 h-3.5" />
          <span>Clear Screen</span>
        </button>
      </div>

      {/* Suggested Quick Commands */}
      <div className="space-y-1.5">
        <span className="text-[11px] font-mono uppercase tracking-wider text-slate-400 flex items-center gap-1">
          <Sparkles className="w-3 h-3 text-rose-400" />
          <span>Quick Commands (Click to run):</span>
        </span>
        <div className="flex flex-wrap gap-2">
          {quickCommands.map((qCmd, idx) => (
            <button
              key={idx}
              onClick={() => handleRunCommand(qCmd)}
              className="px-2.5 py-1 rounded-md bg-slate-900/90 hover:bg-rose-950/40 border border-slate-800 hover:border-rose-500/50 text-[11px] font-mono text-slate-300 hover:text-rose-300 transition cursor-pointer"
            >
              {qCmd}
            </button>
          ))}
        </div>
      </div>

      {/* Terminal Window */}
      <div className="rounded-2xl border border-slate-800 bg-[#070a12] shadow-2xl overflow-hidden font-mono text-xs">
        {/* Terminal Header */}
        <div className="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-3 h-3 rounded-full bg-red-500/80"></span>
            <span className="w-3 h-3 rounded-full bg-yellow-500/80"></span>
            <span className="w-3 h-3 rounded-full bg-emerald-500/80"></span>
            <span className="text-slate-400 text-xs ml-2 font-medium">novaflow-tinker — PHP 8.2 REPL</span>
          </div>
          <span className="text-[11px] text-slate-500">Node.js 22 Virtual Environment</span>
        </div>

        {/* Terminal Body */}
        <div className="p-5 space-y-4 max-h-[480px] overflow-y-auto">
          <div className="text-slate-500 text-[11px] pb-2 border-b border-slate-900">
            NovaFlow Interactive Tinker Shell v2.1.0 (Compiled in &lt; 0.05ms)<br />
            Type any PHP expression, Active Record query, or "help" for guidance.
          </div>

          {commandHistory.map((item, idx) => (
            <div key={idx} className="space-y-1">
              <div className="flex items-center gap-2 text-rose-400 font-semibold">
                <span className="text-slate-500">novaflow&gt;</span>
                <span>{item.cmd}</span>
              </div>
              <pre className="text-emerald-400 text-[11px] leading-relaxed whitespace-pre-wrap pl-6 overflow-x-auto">
                {item.output}
              </pre>
            </div>
          ))}

          {/* Active Input Line */}
          <form
            onSubmit={(e) => {
              e.preventDefault();
              handleRunCommand();
            }}
            className="flex items-center gap-2 text-rose-400 font-semibold pt-1"
          >
            <span className="text-slate-500">novaflow&gt;</span>
            <input
              type="text"
              autoFocus
              value={inputVal}
              onChange={e => setInputVal(e.target.value)}
              placeholder="Enter command, e.g. DB::table('users')->count()"
              className="flex-1 bg-transparent text-white placeholder-slate-600 focus:outline-none"
            />
          </form>
          <div ref={terminalEndRef} />
        </div>
      </div>
    </div>
  );
}
