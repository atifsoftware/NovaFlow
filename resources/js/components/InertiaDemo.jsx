import React from 'react';
import Welcome from '../Pages/Welcome';

export default function InertiaDemo({ user, settings, onNavigateBack }) {
  return (
    <div>
      <div className="bg-slate-900/90 border-b border-indigo-500/20 px-4 py-2 flex items-center justify-between text-xs">
        <div className="flex items-center gap-2 text-indigo-400">
          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Inertia.js Protocol Active — Controller rendered &quot;Pages/Welcome.jsx&quot;</span>
        </div>
        <button
          onClick={onNavigateBack}
          className="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1 rounded-lg border border-slate-700 transition cursor-pointer"
        >
          ← Return to Overview
        </button>
      </div>

      <Welcome
        title="NovaFlow PHP + Inertia.js 2.0"
        description="PHP MVC Controller communicates directly with React 19 without any REST API!"
        version="2.1.0"
        auth={{ user: user ? user.name : 'PHP Developer' }}
        appName={settings?.appName || 'NovaFlow PHP'}
        baseUrl=""
      />
    </div>
  );
}
