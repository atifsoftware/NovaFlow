import React, { useState } from 'react';

export default function Welcome({ title, description, version, auth, appName, baseUrl }) {
  const [counter, setCounter] = useState(0);
  const [activeTab, setActiveTab] = useState('props');

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
      {/* Top Navbar */}
      <nav className="border-b border-slate-800 bg-slate-900/50 backdrop-blur sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-pink-500 flex items-center justify-center font-black text-white text-lg shadow-lg shadow-indigo-500/25">
              N
            </div>
            <div>
              <span className="font-bold text-lg tracking-tight bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                {appName || 'NovaFlow PHP'}
              </span>
              <span className="ml-2 text-[10px] font-mono px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                PHP 8.2 + React 19 + Inertia
              </span>
            </div>
          </div>

          <div className="flex items-center gap-4 text-sm">
            <span className="text-slate-400 text-xs flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              Logged in as: <strong className="text-slate-200">{auth?.user || 'Guest'}</strong>
            </span>
            <a
              href={baseUrl ? `${baseUrl}/` : "/"}
              className="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3.5 py-1.5 rounded-lg border border-slate-700 transition"
            >
              Main Site
            </a>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <main className="flex-1 max-w-5xl mx-auto px-4 py-12 w-full">
        <div className="text-center space-y-4 max-w-2xl mx-auto">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <span>⚛️ React 19 Hydrated Successfully</span>
            <span>•</span>
            <span>Zero REST API</span>
          </div>
          <h1 className="text-4xl sm:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-300 via-purple-200 to-pink-300 bg-clip-text text-transparent">
            {title}
          </h1>
          <p className="text-slate-400 text-base leading-relaxed">
            {description}
          </p>
        </div>

        {/* Live React State Counter (Proof of client-side React hydration) */}
        <div className="mt-10 p-6 rounded-2xl bg-gradient-to-b from-indigo-950/40 to-slate-900/60 border border-indigo-500/20 max-w-md mx-auto text-center shadow-xl shadow-indigo-950/50">
          <span className="text-xs font-mono uppercase tracking-widest text-indigo-400">
            Client-Side React State
          </span>
          <div className="text-5xl font-black text-white my-3 font-mono">
            {counter}
          </div>
          <p className="text-xs text-slate-400 mb-4">
            Click below to verify that this is a 100% active React component running inside NovaFlow PHP!
          </p>
          <div className="flex items-center justify-center gap-3">
            <button
              onClick={() => setCounter(c => c - 1)}
              className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold border border-slate-700 transition active:scale-95"
            >
              - Decrement
            </button>
            <button
              onClick={() => setCounter(0)}
              className="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white rounded-xl text-xs font-mono transition"
            >
              Reset
            </button>
            <button
              onClick={() => setCounter(c => c + 1)}
              className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/30 transition active:scale-95"
            >
              + Increment
            </button>
          </div>
        </div>

        {/* Tabs & Props Inspector */}
        <div className="mt-12">
          <div className="flex items-center justify-center gap-2 border-b border-slate-800 pb-3">
            <button
              onClick={() => setActiveTab('props')}
              className={`px-4 py-1.5 rounded-lg text-xs font-medium transition ${
                activeTab === 'props'
                  ? 'bg-indigo-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              Live Props from PHP Controller
            </button>
            <button
              onClick={() => setActiveTab('code')}
              className={`px-4 py-1.5 rounded-lg text-xs font-medium transition ${
                activeTab === 'code'
                  ? 'bg-indigo-600 text-white shadow'
                  : 'text-slate-400 hover:text-slate-200'
              }`}
            >
              How It Works (Code Comparison)
            </button>
          </div>

          <div className="mt-6">
            {activeTab === 'props' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="p-5 rounded-xl bg-slate-900/70 border border-slate-800">
                  <span className="text-xs font-mono text-indigo-400">title</span>
                  <p className="text-sm font-semibold text-slate-200 mt-1">{title}</p>
                  <span className="text-[10px] text-slate-500 font-mono mt-3 block">Type: string • Origin: PHP Controller</span>
                </div>

                <div className="p-5 rounded-xl bg-slate-900/70 border border-slate-800">
                  <span className="text-xs font-mono text-indigo-400">auth.user</span>
                  <p className="text-sm font-semibold text-slate-200 mt-1">{auth?.user}</p>
                  <span className="text-[10px] text-slate-500 font-mono mt-3 block">Type: object • Origin: Context / Shared Prop</span>
                </div>

                <div className="p-5 rounded-xl bg-slate-900/70 border border-slate-800">
                  <span className="text-xs font-mono text-indigo-400">version</span>
                  <p className="text-sm font-semibold text-slate-200 mt-1">{version}</p>
                  <span className="text-[10px] text-slate-500 font-mono mt-3 block">Type: string • Origin: NovaFlow Config</span>
                </div>

                <div className="p-5 rounded-xl bg-slate-900/70 border border-slate-800">
                  <span className="text-xs font-mono text-indigo-400">appName</span>
                  <p className="text-sm font-semibold text-slate-200 mt-1">{appName || 'NovaFlow'}</p>
                  <span className="text-[10px] text-slate-500 font-mono mt-3 block">Type: string • Origin: Global Inertia Share</span>
                </div>
              </div>
            )}

            {activeTab === 'code' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 font-mono text-xs">
                <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800">
                  <div className="text-indigo-400 font-bold mb-2 pb-2 border-b border-slate-800">
                    PHP Controller (Server)
                  </div>
                  <pre className="text-emerald-400 text-[11px] leading-relaxed overflow-x-auto">
{`public function inertiaDemo(): void {
    $this->inertia('Welcome', [
        'title' => 'NovaFlow PHP + Inertia.js',
        'auth'  => ['user' => 'PHP Developer']
    ]);
}`}
                  </pre>
                </div>

                <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800">
                  <div className="text-pink-400 font-bold mb-2 pb-2 border-b border-slate-800">
                    React Component (Client)
                  </div>
                  <pre className="text-sky-300 text-[11px] leading-relaxed overflow-x-auto">
{`export default function Welcome({ title, auth }) {
    return (
        <div>
            <h1>{title}</h1>
            <p>Welcome, {auth.user}</p>
        </div>
    );
}`}
                  </pre>
                </div>
              </div>
            )}
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="border-t border-slate-900 py-6 text-center text-xs text-slate-500">
        NovaFlow PHP Framework — Modern MVC with Native Inertia.js Protocol Support
      </footer>
    </div>
  );
}

