export const initialUsers = [
  {
    id: 1,
    name: "Tanvir Hasan",
    email: "tanvir@example.com",
    role: "admin",
    status: "active",
    avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=face",
    createdAt: "2026-01-15 10:24:00"
  },
  {
    id: 2,
    name: "Shohagh Mia",
    email: "shohaghInfo@gmail.com",
    role: "admin",
    status: "active",
    avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face",
    createdAt: "2026-02-01 14:12:00"
  },
  {
    id: 3,
    name: "Farhana Akter",
    email: "farhana@novaflow.test",
    role: "manager",
    status: "active",
    avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face",
    createdAt: "2026-03-10 09:30:00"
  },
  {
    id: 4,
    name: "Kamrul Islam",
    email: "kamrul@novaflow.test",
    role: "user",
    status: "suspended",
    avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face",
    createdAt: "2026-03-22 18:45:00"
  },
  {
    id: 5,
    name: "Nusrat Jahan",
    email: "nusrat@novaflow.test",
    role: "user",
    status: "active",
    avatar: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face",
    createdAt: "2026-04-05 11:15:00"
  }
];

export const translations = {
  en: {
    heroBadge: "Enterprise PHP 8.2 MVC + React 19 Inertia",
    heroTitle: "Speed. Precision. Scalability.",
    heroSubtitle: "NovaFlow is a ultra-fast, modern PHP MVC framework built with Multi-Database Grammars (MySQL, PostgreSQL, SQLite), Fluent QueryBuilder, Active Record ORM, and Bank-Grade Security.",
    startGuide: "Get Started",
    liveDemo: "Inertia.js SPA Demo",
    runBenchmark: "Run Live Benchmark",
    benchResults: "Benchmark Results (Grade A+)",
    queryCompiler: "Multi-DB SQL Grammar Compiler",
    queryCompilerDesc: "Switch database engines and see the compiled SQL dialect and quote conventions instantly.",
    tinkerConsole: "Interactive Tinker Shell (REPL)",
    tinkerDesc: "Test framework models, cache, and commands in real time directly from your browser.",
    keyFeatures: "Core Architectural Highlights",
    adminPanel: "Admin Dashboard",
    apiExplorer: "API Explorer",
    docs: "Documentation",
    features: [
      "Multi-Database Grammar Engine: Native MySQL, PostgreSQL, and SQLite support",
      "Sub-millisecond latency: < 0.05ms framework overhead & 595,000+ JSON req/sec",
      "Native Inertia.js Protocol v1/v2 integration with React 19 & Tailwind CSS v4",
      "Atomic Transactions with nested savepoint rollbacks & automatic deadlock retry",
      "Zero-SDK SigV4 AWS S3 / Cloudflare R2 / MinIO storage abstraction",
      "Multi-driver asynchronous background queue (Sync, File, DB, Redis)"
    ],
    langToggle: "বাংলা",
    login: "Log In",
    logout: "Log Out",
    welcome: "Welcome",
    quickStats: "Live Framework Health"
  },
  bn: {
    heroBadge: "এন্টারপ্রাইজ পিএইচপি ৮.২ এমভিসি + রিঅ্যাক্ট ১৯ ইনার্শিয়া",
    heroTitle: "গতি। নির্ভুলতা। আধুনিক আর্কিটেকচার।",
    heroSubtitle: "নোভাফ্লো (NovaFlow) একটি বিদ্যুৎগতির ও আধুনিক পিএইচপি এমভিসি ফ্রেমওয়ার্ক। এতে রয়েছে মাল্টি-ডাটাবেজ গ্রামার (MySQL, PostgreSQL, SQLite), ফ্লুয়েন্ট কুয়েরি বিল্ডার, অ্যাক্টিভ রেকর্ড ও ব্যাংক-গ্রেড সিকিউরিটি।",
    startGuide: "শুরু করুন",
    liveDemo: "ইনার্শিয়া এসপিএ ডেমো",
    runBenchmark: "লাইভ বেঞ্চমার্ক চালান",
    benchResults: "বেঞ্চমার্ক ফলাফল (গ্রেড A+)",
    queryCompiler: "মাল্টি-ডাটাবেজ এসকিউএল গ্রামার কম্পাইলার",
    queryCompilerDesc: "ডাটাবেজ ড্রাইভার পরিবর্তন করে রিয়েল-টাইমে ডায়ালেক্ট ও কোট সিনট্যাক্স দেখুন।",
    tinkerConsole: "ইন্টারেক্টিভ টিংকার শেল (REPL)",
    tinkerDesc: "ব্রাউজার থেকেই ফ্রেমওয়ার্ক মডেল, ক্যাশ ও কমান্ড সরাসরি টেস্ট করুন।",
    keyFeatures: "মূল আর্কিটেকচারাল সুবিধাসমূহ",
    adminPanel: "অ্যাডমিন প্যানেল",
    apiExplorer: "এপিআই এক্সপ্লোরার",
    docs: "ডকুমেন্টেশন",
    features: [
      "মাল্টি-ডাটাবেজ গ্রামার ইঞ্জিন: MySQL, PostgreSQL এবং SQLite-এর নিজস্ব ড্রাইভার",
      "বিদ্যুৎগতির পারফরমেন্স: < ০.০৫ms ওভারহেড ও প্রতি সেকেন্ডে প্রায় ৬,০০,০০০ JSON রেসপন্স",
      "Inertia.js প্রোটোকল দিয়ে সরাসরি PHP কন্ট্রোলার থেকে React 19 রেন্ডারিং",
      "ডাটাবেজ ট্রানজেকশন ও সেভপয়েন্ট: স্বয়ংক্রিয় rollback ও ডেডলক রিট্রাই সাপোর্ট",
      "কোনো ভারী SDK ছাড়া জিরো-ব্লোট SigV4 দিয়ে ক্লাউড S3 ও লোকাল স্টোরেজ ড্রাইভার",
      "মাল্টি-ড্রাইভার কিউ সিস্টেম (Sync, File, Database এবং Redis)"
    ],
    langToggle: "English",
    login: "লগইন",
    logout: "লগআউট",
    welcome: "স্বাগতম",
    quickStats: "লাইভ ফ্রেমওয়ার্ক স্ট্যাটাস"
  }
};
