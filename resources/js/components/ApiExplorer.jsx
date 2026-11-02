import React, { useState } from 'react';
import { 
  Send, 
  Terminal, 
  Key, 
  CheckCircle, 
  Clock, 
  Copy, 
  Check, 
  Code2, 
  Layers 
} from 'lucide-react';

export default function ApiExplorer({ users, lang }) {
  const isBangla = lang === 'bn';
  const [selectedEndpointIndex, setSelectedEndpointIndex] = useState(0);
  const [authToken, setAuthToken] = useState('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsIm5hbWUiOiJUYW52aXIgSGFzYW4iLCJyb2xlIjoiYWRtaW4ifQ');
  const [isSending, setIsSending] = useState(false);
  const [responseOutput, setResponseOutput] = useState(null);
  const [copied, setCopied] = useState(false);

  // Custom request body state for editable test calls
  const [requestBody, setRequestBody] = useState(JSON.stringify({
    email: "tanvir@example.com",
    password: "password123"
  }, null, 2));

  const endpoints = [
    {
      method: "POST",
      path: "/api/v1/login",
      summary: "Authenticate User & Issue JWT Token",
      auth: "Public",
      throttle: "30 req / min",
      description: "Validates user credentials against database and returns a signed HS256 JWT bearer token.",
      defaultBody: JSON.stringify({
        email: "tanvir@example.com",
        password: "password123"
      }, null, 2),
      handler: (body, token) => {
        try {
          const parsed = JSON.parse(body || '{}');
          if (!parsed.email || !parsed.password) {
            return {
              status: 422,
              statusText: "Unprocessable Entity",
              data: {
                status: "error",
                message: "Validation failed",
                errors: {
                  email: ["The email field is required."],
                  password: ["The password field is required."]
                }
              }
            };
          }
          const matched = users.find(u => u.email.toLowerCase() === parsed.email.toLowerCase());
          return {
            status: 200,
            statusText: "OK",
            data: {
              status: "success",
              token: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImVtYWlsIjoidGFudmlyQGV4YW1wbGUuY29tIn0",
              token_type: "Bearer",
              expires_in: 3600,
              user: matched ? { id: matched.id, name: matched.name, email: matched.email, role: matched.role } : { id: 1, name: "Tanvir Hasan", email: parsed.email, role: "admin" }
            }
          };
        } catch {
          return { status: 400, statusText: "Bad Request", data: { error: "Invalid JSON payload" } };
        }
      }
    },
    {
      method: "GET",
      path: "/api/v1/profile",
      summary: "Retrieve Authenticated User Profile",
      auth: "Bearer JWT Required",
      throttle: "60 req / min",
      description: "Decodes the JWT token from the Authorization header and returns user metadata via UserResource transformer.",
      defaultBody: null,
      handler: (body, token) => {
        if (!token) {
          return {
            status: 401,
            statusText: "Unauthorized",
            data: { status: "error", message: "Missing or invalid Bearer token in Authorization header" }
          };
        }
        return {
          status: 200,
          statusText: "OK",
          data: {
            status: "success",
            data: {
              id: 1,
              name: "Tanvir Hasan",
              email: "tanvir@example.com",
              role: "admin",
              permissions: ["manage-users", "system-settings", "run-migrations"],
              last_login_at: new Date().toISOString()
            }
          }
        };
      }
    },
    {
      method: "GET",
      path: "/api/v1/users",
      summary: "List Users with Pagination & Resource Transformer",
      auth: "Bearer JWT Required",
      throttle: "60 req / min",
      description: "Returns paginated list of users sanitized by JsonResource (stripping sensitive columns like password_hash).",
      defaultBody: null,
      handler: (body, token) => {
        return {
          status: 200,
          statusText: "OK",
          data: {
            status: "success",
            data: users.map(u => ({ id: u.id, name: u.name, email: u.email, role: u.role, status: u.status })),
            meta: {
              current_page: 1,
              per_page: 10,
              total: users.length,
              last_page: 1
            },
            links: {
              first: "/api/v1/users?page=1",
              last: "/api/v1/users?page=1",
              prev: null,
              next: null
            }
          }
        };
      }
    },
    {
      method: "GET",
      path: "/api/v1/users/1",
      summary: "Get Single User Resource",
      auth: "Bearer JWT Required",
      throttle: "60 req / min",
      description: "Finds single user by ID. Returns 404 if user not found.",
      defaultBody: null,
      handler: (body, token) => {
        const u = users[0] || { id: 1, name: "Tanvir Hasan", email: "tanvir@example.com", role: "admin" };
        return {
          status: 200,
          statusText: "OK",
          data: {
            status: "success",
            data: {
              id: u.id,
              name: u.name,
              email: u.email,
              role: u.role,
              status: u.status,
              created_at: u.createdAt
            }
          }
        };
      }
    },
    {
      method: "GET",
      path: "/health",
      summary: "System Health & Latency Probe",
      auth: "Public",
      throttle: "None",
      description: "Checks database connectivity, cache driver status, and framework uptime.",
      defaultBody: null,
      handler: () => {
        return {
          status: 200,
          statusText: "OK",
          data: {
            status: "healthy",
            uptime_seconds: 48210,
            database: {
              connection: "mysql",
              connected: true,
              latency_ms: 0.12
            },
            cache: {
              driver: "memory",
              hits: 1420,
              misses: 12
            },
            memory_usage_mb: 4.12
          }
        };
      }
    }
  ];

  const currentEndpoint = endpoints[selectedEndpointIndex];

  const handleSelectEndpoint = (index) => {
    setSelectedEndpointIndex(index);
    setRequestBody(endpoints[index].defaultBody || '');
    setResponseOutput(null);
  };

  const handleSendRequest = () => {
    setIsSending(true);
    const start = performance.now();
    setTimeout(() => {
      const res = currentEndpoint.handler(requestBody, authToken);
      const latency = (performance.now() - start).toFixed(2);
      setResponseOutput({
        ...res,
        latency,
        headers: {
          'Content-Type': 'application/json',
          'X-Powered-By': 'NovaFlow PHP Framework v2.1.0',
          'X-RateLimit-Limit': '60',
          'X-RateLimit-Remaining': '58',
          'Date': new Date().toUTCString()
        }
      });
      setIsSending(false);
    }, 80);
  };

  const copyResponse = () => {
    if (!responseOutput) return;
    navigator.clipboard.writeText(JSON.stringify(responseOutput.data, null, 2));
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className={`max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 ${isBangla ? 'font-bangla' : ''}`}>
      <div className="pb-4 border-b border-slate-800">
        <div className="flex items-center gap-2">
          <Code2 className="w-6 h-6 text-indigo-400" />
          <h1 className="text-2xl font-bold text-white tracking-tight">
            {isBangla ? 'ইন্টারেক্টিভ এপিআই এক্সপ্লোরার ও সোয়াগার' : 'Interactive API Explorer & Swagger'}
          </h1>
        </div>
        <p className="text-xs text-slate-400 mt-1">
          {isBangla ? 'ব্রাউজার থেকেই সরাসরি NovaFlow API v1 এন্ডপয়েন্টগুলো টেস্ট করুন' : 'Test NovaFlow API v1 endpoints live with customizable payloads and bearer tokens'}
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Endpoint Catalog Sidebar */}
        <div className="lg:col-span-4 space-y-2">
          <span className="text-[11px] font-mono uppercase tracking-wider text-slate-400 block mb-2">
            Available Endpoints
          </span>
          {endpoints.map((ep, idx) => (
            <button
              key={idx}
              onClick={() => handleSelectEndpoint(idx)}
              className={`w-full text-left p-3 rounded-xl border transition cursor-pointer flex items-center justify-between ${
                selectedEndpointIndex === idx
                  ? 'bg-slate-900 border-indigo-500/50 shadow-lg shadow-indigo-950/30'
                  : 'bg-slate-950/50 hover:bg-slate-900 border-slate-800/80 text-slate-400 hover:text-slate-200'
              }`}
            >
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <span className={`px-2 py-0.5 rounded text-[10px] font-bold font-mono ${
                    ep.method === 'GET'
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                      : 'bg-blue-500/10 text-blue-400 border border-blue-500/30'
                  }`}>
                    {ep.method}
                  </span>
                  <span className="font-mono text-xs font-semibold text-white">
                    {ep.path}
                  </span>
                </div>
                <div className="text-[11px] text-slate-400 truncate max-w-[220px]">
                  {ep.summary}
                </div>
              </div>
            </button>
          ))}
        </div>

        {/* Request & Response Runner Panel */}
        <div className="lg:col-span-8 space-y-4">
          <div className="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            {/* Header with method, path, and Send button */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-800">
              <div className="flex items-center gap-2">
                <span className={`px-2.5 py-1 rounded-md text-xs font-bold font-mono ${
                  currentEndpoint.method === 'GET'
                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                    : 'bg-blue-500/10 text-blue-400 border border-blue-500/30'
                }`}>
                  {currentEndpoint.method}
                </span>
                <span className="text-sm font-bold font-mono text-white">
                  {currentEndpoint.path}
                </span>
              </div>

              <button
                onClick={handleSendRequest}
                disabled={isSending}
                className="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-950/60 transition cursor-pointer"
              >
                <Send className="w-3.5 h-3.5" />
                <span>{isSending ? 'Sending...' : 'Send Request'}</span>
              </button>
            </div>

            <p className="text-xs text-slate-300">
              {currentEndpoint.description}
            </p>

            {/* Auth Token Header Config */}
            {currentEndpoint.auth !== 'Public' && (
              <div className="space-y-1">
                <label className="text-[11px] font-mono text-slate-400 flex items-center gap-1.5">
                  <Key className="w-3 h-3 text-indigo-400" />
                  <span>Authorization: Bearer &lt;token&gt;</span>
                </label>
                <input
                  type="text"
                  value={authToken}
                  onChange={e => setAuthToken(e.target.value)}
                  className="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 focus:outline-none focus:border-indigo-500"
                />
              </div>
            )}

            {/* Request Body (for POST) */}
            {currentEndpoint.method === 'POST' && (
              <div className="space-y-1">
                <label className="text-[11px] font-mono text-slate-400 block">
                  Request JSON Body:
                </label>
                <textarea
                  rows={4}
                  value={requestBody}
                  onChange={e => setRequestBody(e.target.value)}
                  className="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-emerald-400 focus:outline-none focus:border-indigo-500"
                />
              </div>
            )}
          </div>

          {/* Response Output Panel */}
          {responseOutput && (
            <div className="p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-slate-800 text-xs font-mono">
                <div className="flex items-center gap-3">
                  <span className={`px-2.5 py-0.5 rounded-full font-bold ${
                    responseOutput.status >= 200 && responseOutput.status < 300
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                      : 'bg-red-500/10 text-red-400 border border-red-500/30'
                  }`}>
                    {responseOutput.status} {responseOutput.statusText}
                  </span>
                  <span className="text-slate-400 flex items-center gap-1">
                    <Clock className="w-3 h-3 text-slate-500" />
                    <span>{responseOutput.latency} ms</span>
                  </span>
                </div>

                <button
                  onClick={copyResponse}
                  className="flex items-center gap-1 text-[11px] text-slate-400 hover:text-white transition cursor-pointer"
                >
                  {copied ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                  <span>{copied ? 'Copied' : 'Copy JSON'}</span>
                </button>
              </div>

              {/* Response Headers toggle or preview */}
              <div className="text-[11px] font-mono text-slate-500 space-y-0.5 pb-2 border-b border-slate-900">
                {Object.entries(responseOutput.headers).map(([k, v]) => (
                  <div key={k} className="flex gap-2">
                    <span className="text-slate-400">{k}:</span>
                    <span>{v}</span>
                  </div>
                ))}
              </div>

              {/* Formatted JSON output */}
              <pre className="text-xs font-mono text-sky-300 leading-relaxed overflow-x-auto max-h-72">
                {JSON.stringify(responseOutput.data, null, 2)}
              </pre>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
