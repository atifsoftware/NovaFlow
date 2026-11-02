import React, { useState } from 'react';
import { 
  Users, 
  Settings, 
  Activity, 
  Server, 
  Plus, 
  Trash2, 
  Edit3, 
  CheckCircle, 
  XCircle, 
  Search, 
  Save, 
  ShieldAlert, 
  Database, 
  HardDrive 
} from 'lucide-react';

export default function AdminDashboard({ 
  users, 
  onAddUser, 
  onEditUser, 
  onDeleteUser, 
  onToggleStatus,
  settings,
  onUpdateSettings,
  lang 
}) {
  const isBangla = lang === 'bn';
  const [activeTab, setActiveTab] = useState('overview'); // 'overview' | 'users' | 'settings'
  const [searchQuery, setSearchQuery] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);
  const [editingUser, setEditingUser] = useState(null);

  // New user form state
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    role: 'user',
    status: 'active'
  });

  // Settings form state
  const [localSettings, setLocalSettings] = useState({ ...settings });
  const [settingsSaved, setSettingsSaved] = useState(false);

  const filteredUsers = users.filter(u => 
    u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    u.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
    u.role.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleCreateSubmit = (e) => {
    e.preventDefault();
    if (!newUser.name || !newUser.email) return;
    onAddUser({
      ...newUser,
      id: Date.now(),
      createdAt: new Date().toISOString().slice(0, 19).replace('T', ' ')
    });
    setNewUser({ name: '', email: '', role: 'user', status: 'active' });
    setShowAddModal(false);
  };

  const handleEditSubmit = (e) => {
    e.preventDefault();
    if (!editingUser) return;
    onEditUser(editingUser);
    setEditingUser(null);
  };

  const handleSaveSettings = (e) => {
    e.preventDefault();
    onUpdateSettings(localSettings);
    setSettingsSaved(true);
    setTimeout(() => setSettingsSaved(false), 2500);
  };

  return (
    <div className={`max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 ${isBangla ? 'font-bangla' : ''}`}>
      {/* Admin Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
          <div className="flex items-center gap-2">
            <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h1 className="text-2xl font-bold text-white tracking-tight">
              {isBangla ? 'NovaFlow কন্ট্রোল সেন্টার' : 'NovaFlow Admin Control Center'}
            </h1>
          </div>
          <p className="text-xs text-slate-400 mt-1">
            {isBangla ? 'ইউজার ম্যানেজমেন্ট, ফ্রেমওয়ার্ক কনফিগারেশন ও সিস্টেম ওভারভিউ' : 'User access control, framework configurations, and runtime monitoring'}
          </p>
        </div>

        {/* Admin Navigation Tabs */}
        <div className="inline-flex rounded-xl bg-slate-900 p-1 border border-slate-800">
          <button
            onClick={() => setActiveTab('overview')}
            className={`px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${
              activeTab === 'overview'
                ? 'bg-rose-600 text-white shadow'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Activity className="w-3.5 h-3.5" />
            <span>{isBangla ? 'ওভারভিউ' : 'Overview'}</span>
          </button>

          <button
            onClick={() => setActiveTab('users')}
            className={`px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${
              activeTab === 'users'
                ? 'bg-rose-600 text-white shadow'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Users className="w-3.5 h-3.5" />
            <span>{isBangla ? 'ইউজার্স' : 'Users'}</span>
            <span className="ml-1 text-[10px] px-1.5 py-0.2 bg-slate-800 text-slate-300 rounded-full">
              {users.length}
            </span>
          </button>

          <button
            onClick={() => setActiveTab('settings')}
            className={`px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${
              activeTab === 'settings'
                ? 'bg-rose-600 text-white shadow'
                : 'text-slate-400 hover:text-white'
            }`}
          >
            <Settings className="w-3.5 h-3.5" />
            <span>{isBangla ? 'সেটিংস' : 'Settings'}</span>
          </button>
        </div>
      </div>

      {/* OVERVIEW TAB */}
      {activeTab === 'overview' && (
        <div className="space-y-6">
          {/* KPI Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="p-5 rounded-2xl bg-gradient-to-br from-emerald-950/40 to-slate-900 border border-emerald-500/20">
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-xs font-medium text-emerald-400 uppercase tracking-wider">
                    {isBangla ? 'মোট ইউজার' : 'Total Users'}
                  </span>
                  <div className="text-3xl font-extrabold text-white mt-1">{users.length}</div>
                </div>
                <Users className="w-8 h-8 text-emerald-400 opacity-80" />
              </div>
              <span className="text-[11px] text-slate-400 mt-2 block">
                {users.filter(u => u.status === 'active').length} {isBangla ? 'সক্রিয় একাউন্ট' : 'active accounts'}
              </span>
            </div>

            <div className="p-5 rounded-2xl bg-gradient-to-br from-blue-950/40 to-slate-900 border border-blue-500/20">
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-xs font-medium text-blue-400 uppercase tracking-wider">
                    {isBangla ? 'সার্ভার পোর্ট' : 'Server Port'}
                  </span>
                  <div className="text-3xl font-extrabold text-white mt-1">3000</div>
                </div>
                <Server className="w-8 h-8 text-blue-400 opacity-80" />
              </div>
              <span className="text-[11px] text-slate-400 mt-2 block">
                Host: 0.0.0.0 (AI Studio Ready)
              </span>
            </div>

            <div className="p-5 rounded-2xl bg-gradient-to-br from-orange-950/40 to-slate-900 border border-orange-500/20">
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-xs font-medium text-orange-400 uppercase tracking-wider">
                    {isBangla ? 'সক্রিয় ডাটাবেজ' : 'Active DB'}
                  </span>
                  <div className="text-3xl font-extrabold text-white mt-1 uppercase">
                    {settings.dbEngine || 'mysql'}
                  </div>
                </div>
                <Database className="w-8 h-8 text-orange-400 opacity-80" />
              </div>
              <span className="text-[11px] text-slate-400 mt-2 block">
                Dialect: Prepared Statements
              </span>
            </div>

            <div className="p-5 rounded-2xl bg-gradient-to-br from-purple-950/40 to-slate-900 border border-purple-500/20">
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-xs font-medium text-purple-400 uppercase tracking-wider">
                    {isBangla ? 'ক্যাশিং ইঞ্জিন' : 'Cache Driver'}
                  </span>
                  <div className="text-3xl font-extrabold text-white mt-1 uppercase">
                    {settings.cacheDriver || 'memory'}
                  </div>
                </div>
                <HardDrive className="w-8 h-8 text-purple-400 opacity-80" />
              </div>
              <span className="text-[11px] text-slate-400 mt-2 block">
                Peak RAM: ~4.0 MB
              </span>
            </div>
          </div>

          {/* Quick Actions & System Info */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
              <h3 className="text-base font-bold text-white">
                {isBangla ? 'স্বাগতম NovaFlow অ্যাডমিন প্যানেলে!' : 'Welcome to NovaFlow Admin Control!'}
              </h3>
              <p className="text-xs text-slate-400 leading-relaxed">
                {isBangla 
                  ? 'আপনার ফ্রেমওয়ার্ক এখন সফলভাবে AI Studio ওয়েব পরিবেশে মাইগ্রেট হয়েছে। এখানে আপনি সরাসরি ইউজারদের অনুমোদন, সেটিংস কনফিগারেশন এবং পারফরমেন্স মনিটর করতে পারেন।'
                  : 'Your framework is running live in the AI Studio web container. You have full access to real-time user management, active config settings, and query monitoring.'}
              </p>
              <div className="flex flex-wrap gap-3 pt-2">
                <button
                  onClick={() => setActiveTab('users')}
                  className="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer"
                >
                  <Users className="w-3.5 h-3.5" />
                  <span>{isBangla ? 'ইউজার তালিকা পরিচালনা করুন' : 'Manage Users'}</span>
                </button>
                <button
                  onClick={() => setActiveTab('settings')}
                  className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2 cursor-pointer"
                >
                  <Settings className="w-3.5 h-3.5" />
                  <span>{isBangla ? 'সাইট সেটিংস কনফিগার করুন' : 'System Settings'}</span>
                </button>
              </div>
            </div>

            <div className="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-3">
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">
                {isBangla ? 'সিস্টেম স্ট্যাটাস' : 'System Information'}
              </h4>
              <div className="text-xs space-y-2 text-slate-300 font-mono">
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-500">Framework:</span>
                  <span>NovaFlow v2.1.0</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-500">Node Runtime:</span>
                  <span>v22 (Vite 6)</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-500">Frontend SPA:</span>
                  <span>React 19 + Inertia</span>
                </div>
                <div className="flex justify-between py-1">
                  <span className="text-slate-500">Security:</span>
                  <span className="text-emerald-400 font-semibold">RBAC + CSRF Ready</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* USERS TAB (CRUD) */}
      {activeTab === 'users' && (
        <div className="space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div className="relative flex-1 max-w-sm">
              <Search className="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                placeholder={isBangla ? 'নাম, ইমেইল বা রোল খুঁজুন...' : 'Search users by name, email, role...'}
                className="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition"
              />
            </div>

            <button
              onClick={() => setShowAddModal(true)}
              className="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-rose-950/60 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>{isBangla ? 'নতুন ইউজার যোগ করুন' : 'Add New User'}</span>
            </button>
          </div>

          {/* User Table */}
          <div className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-800 bg-slate-900/90 text-slate-400 uppercase tracking-wider font-mono text-[11px]">
                    <th className="py-3 px-4">User</th>
                    <th className="py-3 px-4">Role</th>
                    <th className="py-3 px-4">Status</th>
                    <th className="py-3 px-4">Created</th>
                    <th className="py-3 px-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60 text-slate-300">
                  {filteredUsers.length === 0 ? (
                    <tr>
                      <td colSpan="5" className="py-8 text-center text-slate-500">
                        {isBangla ? 'কোনো ইউজার পাওয়া যায়নি।' : 'No users match your search query.'}
                      </td>
                    </tr>
                  ) : (
                    filteredUsers.map(user => (
                      <tr key={user.id} className="hover:bg-slate-800/40 transition">
                        <td className="py-3.5 px-4">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-slate-800 overflow-hidden flex items-center justify-center font-bold text-rose-400 text-xs border border-slate-700">
                              {user.name.charAt(0)}
                            </div>
                            <div>
                              <div className="font-semibold text-white">{user.name}</div>
                              <div className="text-[11px] text-slate-400 font-mono">{user.email}</div>
                            </div>
                          </div>
                        </td>
                        <td className="py-3.5 px-4">
                          <span className={`inline-flex px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold uppercase ${
                            user.role === 'admin'
                              ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                              : user.role === 'manager'
                              ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30'
                              : 'bg-slate-800 text-slate-300'
                          }`}>
                            {user.role}
                          </span>
                        </td>
                        <td className="py-3.5 px-4">
                          <button
                            onClick={() => onToggleStatus(user.id)}
                            className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium transition cursor-pointer ${
                              user.status === 'active'
                                ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20'
                                : 'bg-red-500/10 text-red-400 hover:bg-red-500/20'
                            }`}
                            title="Click to toggle status"
                          >
                            <span className={`w-1.5 h-1.5 rounded-full ${
                              user.status === 'active' ? 'bg-emerald-400' : 'bg-red-400'
                            }`}></span>
                            <span className="capitalize">{user.status}</span>
                          </button>
                        </td>
                        <td className="py-3.5 px-4 font-mono text-[11px] text-slate-400">
                          {user.createdAt}
                        </td>
                        <td className="py-3.5 px-4 text-right space-x-2">
                          <button
                            onClick={() => setEditingUser(user)}
                            className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
                            title="Edit User"
                          >
                            <Edit3 className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => onDeleteUser(user.id)}
                            className="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-950 text-slate-300 hover:text-rose-400 transition cursor-pointer"
                            title="Delete User"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* SETTINGS TAB */}
      {activeTab === 'settings' && (
        <form onSubmit={handleSaveSettings} className="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-6 max-w-2xl">
          <div className="pb-4 border-b border-slate-800">
            <h3 className="text-base font-bold text-white">
              {isBangla ? 'ফ্রেমওয়ার্ক ও অ্যাপ্লিকেশন সেটিংস' : 'Framework & Application Settings'}
            </h3>
            <p className="text-xs text-slate-400 mt-1">
              {isBangla ? 'config/config.php এবং config/database.php কনফিগারেশন' : 'Persisted framework variables and environment configuration'}
            </p>
          </div>

          {settingsSaved && (
            <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
              <CheckCircle className="w-4 h-4" />
              <span>{isBangla ? 'সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে!' : 'Settings updated successfully!'}</span>
            </div>
          )}

          <div className="space-y-4 text-xs">
            <div>
              <label className="block text-slate-300 font-semibold mb-1">
                {isBangla ? 'অ্যাপ্লিকেশন নাম (APP_NAME)' : 'Application Name (APP_NAME)'}
              </label>
              <input
                type="text"
                value={localSettings.appName || ''}
                onChange={e => setLocalSettings({ ...localSettings, appName: e.target.value })}
                className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 transition"
              />
            </div>

            <div>
              <label className="block text-slate-300 font-semibold mb-1">
                {isBangla ? 'এডমিন ইমেইল' : 'Admin Email Address'}
              </label>
              <input
                type="email"
                value={localSettings.adminEmail || ''}
                onChange={e => setLocalSettings({ ...localSettings, adminEmail: e.target.value })}
                className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 transition"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-slate-300 font-semibold mb-1">
                  {isBangla ? 'ডিফল্ট ডাটাবেজ ড্রাইভার' : 'Default Database Connection'}
                </label>
                <select
                  value={localSettings.dbEngine || 'mysql'}
                  onChange={e => setLocalSettings({ ...localSettings, dbEngine: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 transition"
                >
                  <option value="mysql">MySQL / MariaDB</option>
                  <option value="pgsql">PostgreSQL</option>
                  <option value="sqlite">SQLite (:memory:)</option>
                </select>
              </div>

              <div>
                <label className="block text-slate-300 font-semibold mb-1">
                  {isBangla ? 'ক্যাশিং ড্রাইভার' : 'Cache Driver'}
                </label>
                <select
                  value={localSettings.cacheDriver || 'file'}
                  onChange={e => setLocalSettings({ ...localSettings, cacheDriver: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 transition"
                >
                  <option value="file">File (storage/cache)</option>
                  <option value="memory">In-Memory Map</option>
                  <option value="redis">Redis Cache</option>
                </select>
              </div>
            </div>

            <div className="pt-2">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={localSettings.maintenanceMode || false}
                  onChange={e => setLocalSettings({ ...localSettings, maintenanceMode: e.target.checked })}
                  className="rounded border-slate-700 text-rose-600 focus:ring-rose-500 bg-slate-950"
                />
                <span className="text-slate-300 font-medium">
                  {isBangla ? 'মেইনটেনেন্স মোড অন করুন' : 'Enable Maintenance Mode'}
                </span>
              </label>
            </div>
          </div>

          <button
            type="submit"
            className="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-rose-950/60 transition cursor-pointer"
          >
            <Save className="w-3.5 h-3.5" />
            <span>{isBangla ? 'সেটিংস সেভ করুন' : 'Save Settings'}</span>
          </button>
        </form>
      )}

      {/* ADD USER MODAL */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">
                {isBangla ? 'নতুন ইউজার তৈরি করুন' : 'Create New User'}
              </h3>
              <button
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-white"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-semibold mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  value={newUser.name}
                  onChange={e => setNewUser({ ...newUser, name: e.target.value })}
                  placeholder="e.g. Asif Mahmud"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div>
                <label className="block text-slate-300 font-semibold mb-1">Email Address</label>
                <input
                  type="email"
                  required
                  value={newUser.email}
                  onChange={e => setNewUser({ ...newUser, email: e.target.value })}
                  placeholder="asif@novaflow.test"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-semibold mb-1">Role</label>
                  <select
                    value={newUser.role}
                    onChange={e => setNewUser({ ...newUser, role: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                  >
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>

                <div>
                  <label className="block text-slate-300 font-semibold mb-1">Status</label>
                  <select
                    value={newUser.status}
                    onChange={e => setNewUser({ ...newUser, status: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                  >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                  </select>
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-lg shadow-rose-950/60 cursor-pointer"
                >
                  Save User
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* EDIT USER MODAL */}
      {editingUser && (
        <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Edit User: {editingUser.name}</h3>
              <button
                onClick={() => setEditingUser(null)}
                className="text-slate-400 hover:text-white"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleEditSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-semibold mb-1">Full Name</label>
                <input
                  type="text"
                  required
                  value={editingUser.name}
                  onChange={e => setEditingUser({ ...editingUser, name: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div>
                <label className="block text-slate-300 font-semibold mb-1">Email</label>
                <input
                  type="email"
                  required
                  value={editingUser.email}
                  onChange={e => setEditingUser({ ...editingUser, email: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-semibold mb-1">Role</label>
                  <select
                    value={editingUser.role}
                    onChange={e => setEditingUser({ ...editingUser, role: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                  >
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>

                <div>
                  <label className="block text-slate-300 font-semibold mb-1">Status</label>
                  <select
                    value={editingUser.status}
                    onChange={e => setEditingUser({ ...editingUser, status: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500"
                  >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                  </select>
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setEditingUser(null)}
                  className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-lg shadow-rose-950/60 cursor-pointer"
                >
                  Update User
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
