import React, { useState } from 'react';
import { 
  Globe, 
  Terminal, 
  Database, 
  Layers, 
  Lock, 
  LogOut, 
  LogIn, 
  BookOpen, 
  Code2, 
  ShieldCheck, 
  Cpu, 
  Activity,
  Menu,
  X,
  Download
} from 'lucide-react';
import { initialUsers, translations } from './data/mockData';
import Showcase from './components/Showcase';
import InertiaDemo from './components/InertiaDemo';
import AdminDashboard from './components/AdminDashboard';
import ApiExplorer from './components/ApiExplorer';
import DatabasePlayground from './components/DatabasePlayground';
import Docs from './components/Docs';

export default function App() {
  const [lang, setLang] = useState('en'); // 'en' | 'bn'
  const [activeTab, setActiveTab] = useState('showcase'); // 'showcase' | 'inertia' | 'admin' | 'api-explorer' | 'db-repl' | 'docs'
  const [users, setUsers] = useState(initialUsers);
  const [currentUser, setCurrentUser] = useState(initialUsers[0]); // Logged in as Tanvir Hasan
  const [showLoginModal, setShowLoginModal] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Framework Settings State
  const [settings, setSettings] = useState({
    appName: 'NovaFlow',
    adminEmail: 'admin@novaflow.test',
    dbEngine: 'mysql',
    cacheDriver: 'memory',
    maintenanceMode: false
  });

  const t = translations[lang];

  // User CRUD Handlers
  const handleAddUser = (user) => {
    setUsers(prev => [user, ...prev]);
  };

  const handleEditUser = (updatedUser) => {
    setUsers(prev => prev.map(u => u.id === updatedUser.id ? updatedUser : u));
  };

  const handleDeleteUser = (userId) => {
    setUsers(prev => prev.filter(u => u.id !== userId));
  };

  const handleToggleStatus = (userId) => {
    setUsers(prev => prev.map(u => {
      if (u.id === userId) {
        return {
          ...u,
          status: u.status === 'active' ? 'suspended' : 'active'
        };
      }
      return u;
    }));
  };

  const handleLoginSubmit = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const email = formData.get('email');
    const matched = users.find(u => u.email.toLowerCase() === email.toLowerCase());
    setCurrentUser(matched || {
      id: Date.now(),
      name: email.split('@')[0],
      email: email,
      role: 'admin',
      status: 'active'
    });
    setShowLoginModal(false);
  };

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-rose-500 selection:text-white">
      {/* Top Main Navigation Bar */}
      <header className="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-md border-b border-slate-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          {/* Brand */}
          <div 
            onClick={() => setActiveTab('showcase')}
            className="flex items-center gap-2.5 cursor-pointer group"
          >
            <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-600 via-rose-500 to-orange-400 flex items-center justify-center font-black text-white text-lg shadow-lg shadow-rose-600/30 group-hover:scale-105 transition">
              N
            </div>
            <div>
              <div className="flex items-center gap-1.5">
                <span className="font-extrabold text-lg tracking-tight text-white">
                  Nova<span className="text-rose-500">Flow</span>
                </span>
                <span className="text-[10px] font-mono px-1.5 py-0.2 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">
                  v2.1
                </span>
              </div>
              <span className="text-[10px] text-slate-500 hidden sm:block">PHP MVC + React 19</span>
            </div>
          </div>

          {/* Desktop Navigation Links */}
          <nav className="hidden lg:flex items-center gap-1">
            <button
              onClick={() => setActiveTab('showcase')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                activeTab === 'showcase'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              {lang === 'bn' ? 'হোম' : 'Overview'}
            </button>

            <button
              onClick={() => setActiveTab('inertia')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 ${
                activeTab === 'inertia'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              <span>⚛️</span>
              <span>Inertia.js React</span>
            </button>

            <button
              onClick={() => setActiveTab('admin')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                activeTab === 'admin'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              {t.adminPanel}
            </button>

            <button
              onClick={() => setActiveTab('api-explorer')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                activeTab === 'api-explorer'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              {t.apiExplorer}
            </button>

            <button
              onClick={() => setActiveTab('db-repl')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 ${
                activeTab === 'db-repl'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              <Terminal className="w-3.5 h-3.5" />
              <span>Tinker REPL</span>
            </button>

            <button
              onClick={() => setActiveTab('docs')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                activeTab === 'docs'
                  ? 'bg-rose-600/10 text-rose-400 border border-rose-500/30'
                  : 'text-slate-300 hover:text-white hover:bg-slate-900'
              }`}
            >
              {t.docs}
            </button>
          </nav>

          {/* Right Header Actions: Download, Language Switcher & User Profile */}
          <div className="flex items-center gap-2.5">
            {/* Direct Download Fastify ZIP */}
            <a
              href="/novaflow-fastify.zip"
              download="novaflow-fastify.zip"
              className="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5 transition cursor-pointer"
              title="Download Fastify Edition ZIP"
            >
              <Download className="w-3.5 h-3.5 text-emerald-400" />
              <span className="hidden sm:inline">Download</span>
              <span>Fastify (.zip)</span>
            </a>

            {/* Language Switcher */}
            <button
              onClick={() => setLang(l => l === 'en' ? 'bn' : 'en')}
              className="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-medium text-slate-300 hover:text-white flex items-center gap-1.5 transition cursor-pointer"
              title="Toggle Language"
            >
              <Globe className="w-3.5 h-3.5 text-rose-400" />
              <span>{t.langToggle}</span>
            </button>

            {/* Auth Button */}
            {currentUser ? (
              <div className="flex items-center gap-2">
                <div 
                  onClick={() => setActiveTab('admin')}
                  className="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-900/60 border border-slate-800 text-xs cursor-pointer hover:border-slate-700 transition"
                >
                  <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
                  <span className="text-slate-200 font-medium truncate max-w-[100px]">{currentUser.name}</span>
                </div>
                <button
                  onClick={() => setCurrentUser(null)}
                  className="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 transition cursor-pointer"
                  title="Log Out"
                >
                  <LogOut className="w-3.5 h-3.5" />
                </button>
              </div>
            ) : (
              <button
                onClick={() => setShowLoginModal(true)}
                className="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer"
              >
                <LogIn className="w-3.5 h-3.5" />
                <span>{t.login}</span>
              </button>
            )}

            {/* Mobile menu toggle */}
            <button
              onClick={() => setMobileMenuOpen(prev => !prev)}
              className="lg:hidden p-1.5 rounded-lg bg-slate-900 text-slate-300 hover:text-white border border-slate-800"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Dropdown Menu */}
        {mobileMenuOpen && (
          <div className="lg:hidden px-4 pt-2 pb-4 bg-slate-950 border-b border-slate-900 space-y-1">
            <button
              onClick={() => { setActiveTab('showcase'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              {lang === 'bn' ? 'হোম ওভারভিউ' : 'Overview'}
            </button>
            <button
              onClick={() => { setActiveTab('inertia'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              Inertia.js React SPA
            </button>
            <button
              onClick={() => { setActiveTab('admin'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              {t.adminPanel}
            </button>
            <button
              onClick={() => { setActiveTab('api-explorer'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              {t.apiExplorer}
            </button>
            <button
              onClick={() => { setActiveTab('db-repl'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              Tinker REPL
            </button>
            <button
              onClick={() => { setActiveTab('docs'); setMobileMenuOpen(false); }}
              className="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-900"
            >
              {t.docs}
            </button>
          </div>
        )}
      </header>

      {/* Main View Container */}
      <main className="flex-1">
        {activeTab === 'showcase' && (
          <Showcase 
            lang={lang} 
            onNavigate={(tab) => setActiveTab(tab)} 
          />
        )}

        {activeTab === 'inertia' && (
          <InertiaDemo 
            user={currentUser} 
            settings={settings}
            onNavigateBack={() => setActiveTab('showcase')}
          />
        )}

        {activeTab === 'admin' && (
          <AdminDashboard
            users={users}
            onAddUser={handleAddUser}
            onEditUser={handleEditUser}
            onDeleteUser={handleDeleteUser}
            onToggleStatus={handleToggleStatus}
            settings={settings}
            onUpdateSettings={setSettings}
            lang={lang}
          />
        )}

        {activeTab === 'api-explorer' && (
          <ApiExplorer 
            users={users} 
            lang={lang} 
          />
        )}

        {activeTab === 'db-repl' && (
          <DatabasePlayground 
            users={users} 
            lang={lang} 
          />
        )}

        {activeTab === 'docs' && (
          <Docs 
            lang={lang} 
          />
        )}
      </main>

      {/* Global Footer */}
      <footer className="border-t border-slate-900 py-8 px-4 text-center text-xs text-slate-500 bg-slate-950/60">
        <div className="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <span className="font-bold text-slate-400">NovaFlow PHP Framework</span>
            <span>•</span>
            <span>v2.1.0 Enterprise</span>
          </div>

          <div className="flex items-center gap-4 text-slate-400 text-[11px]">
            <button onClick={() => setActiveTab('docs')} className="hover:text-white transition cursor-pointer">
              Docs
            </button>
            <button onClick={() => setActiveTab('api-explorer')} className="hover:text-white transition cursor-pointer">
              API Explorer
            </button>
            <button onClick={() => setActiveTab('db-repl')} className="hover:text-white transition cursor-pointer">
              Tinker
            </button>
            <button onClick={() => setActiveTab('inertia')} className="hover:text-white transition cursor-pointer">
              Inertia.js
            </button>
          </div>

          <div>
            Open Source under MIT License
          </div>
        </div>
      </footer>

      {/* Login Modal */}
      {showLoginModal && (
        <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <div className="flex items-center gap-2">
                <Lock className="w-4 h-4 text-rose-500" />
                <h3 className="text-base font-bold text-white">Log in to NovaFlow</h3>
              </div>
              <button 
                onClick={() => setShowLoginModal(false)}
                className="text-slate-400 hover:text-white cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleLoginSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-semibold mb-1">Email Address</label>
                <input
                  type="email"
                  name="email"
                  required
                  defaultValue="tanvir@example.com"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div>
                <label className="block text-slate-300 font-semibold mb-1">Password</label>
                <input
                  type="password"
                  name="password"
                  required
                  defaultValue="password123"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div className="text-[11px] text-slate-400 bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                Demo Accounts: <br />
                • Admin: <span className="text-rose-400">tanvir@example.com</span><br />
                • Manager: <span className="text-cyan-400">farhana@novaflow.test</span>
              </div>

              <button
                type="submit"
                className="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition shadow-lg shadow-rose-950/60 cursor-pointer"
              >
                Log In
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
