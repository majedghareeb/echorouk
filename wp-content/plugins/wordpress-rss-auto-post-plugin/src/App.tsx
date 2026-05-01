import React, { useState, useEffect } from "react";
import { 
  Rss, 
  Settings, 
  RefreshCw, 
  CheckCircle2, 
  AlertCircle, 
  ExternalLink, 
  Image as ImageIcon, 
  Code,
  LayoutDashboard,
  FileText,
  Download
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";

interface RSSItem {
  title: string;
  link: string;
  pubDate: string;
  content: string;
  contentSnippet: string;
  guid: string;
  enclosure?: { url: string };
}

interface RSSFeed {
  title: string;
  items: RSSItem[];
}

export default function App() {
  const [feed, setFeed] = useState<RSSFeed | null>(null);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [syncResult, setSyncResult] = useState<{ message: string; count: number } | null>(null);
  const [activeTab, setActiveTab] = useState<"dashboard" | "code" | "settings">("dashboard");

  useEffect(() => {
    fetchFeed();
  }, []);

  const fetchFeed = async () => {
    try {
      setLoading(true);
      const response = await fetch("/api/rss");
      if (!response.ok) throw new Error("Failed to fetch feed");
      const data = await response.json();
      setFeed(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : "An error occurred");
    } finally {
      setLoading(false);
    }
  };

  const runSync = async () => {
    try {
      setSyncing(true);
      setSyncResult(null);
      const response = await fetch("/api/sync", { method: "POST" });
      if (!response.ok) throw new Error("Sync failed");
      const data = await response.json();
      setSyncResult(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Sync failed");
    } finally {
      setSyncing(false);
    }
  };

  const getThumbnail = (item: RSSItem) => {
    if (item.enclosure?.url) return item.enclosure.url;
    const match = item.content.match(/<img.+src=['"]([^'"]+)['"].*>/i);
    return match ? match[1] : null;
  };

  const Sidebar = () => (
    <div className="w-16 bg-black text-white min-h-screen flex flex-col items-center py-8 border-r border-zinc-200">
      <div className="mb-12">
        <Rss size={32} className="text-blue-600" />
      </div>
      <nav className="flex-1 flex flex-col gap-8">
        <button 
          onClick={() => setActiveTab("dashboard")}
          className={`p-3 transition-all ${activeTab === "dashboard" ? "bg-blue-600 text-white" : "text-zinc-500 hover:text-white"}`}
          title="Dashboard"
        >
          <LayoutDashboard size={24} />
        </button>
        <button 
          onClick={() => setActiveTab("code")}
          className={`p-3 transition-all ${activeTab === "code" ? "bg-blue-600 text-white" : "text-zinc-500 hover:text-white"}`}
          title="Source Code"
        >
          <Code size={24} />
        </button>
        <button 
          onClick={() => setActiveTab("settings")}
          className={`p-3 transition-all ${activeTab === "settings" ? "bg-blue-600 text-white" : "text-zinc-500 hover:text-white"}`}
          title="Settings"
        >
          <Settings size={24} />
        </button>
      </nav>
    </div>
  );

  const Header = () => (
    <header className="p-10 border-b border-zinc-200 bg-white flex justify-between items-end">
      <div>
        <p className="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-400 mb-2">WordPress Plugin v1.0.4</p>
        <h1 className="text-6xl md:text-8xl font-black leading-none tracking-tighter uppercase font-display">
          RSS<span className="text-blue-600">.</span>SYNC
        </h1>
      </div>
      <div className="text-right hidden sm:block">
        <p className="text-zinc-500 font-medium mb-1 uppercase tracking-widest text-[10px]">Engine Status</p>
        <div className="flex items-center gap-2 justify-end">
          <span className={`w-3 h-3 rounded-full ${syncing ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'}`}></span>
          <span className="font-mono font-bold uppercase text-lg">{syncing ? 'Syncing...' : 'Active'}</span>
        </div>
      </div>
    </header>
  );

  const Dashboard = () => (
    <div className="grid grid-cols-1 md:grid-cols-12 flex-1 overflow-hidden h-full">
      {/* Configuration Column */}
      <section className="md:col-span-5 p-10 bg-white border-r border-zinc-200 overflow-y-auto">
        <h2 className="text-3xl font-black uppercase tracking-tighter mb-8 border-b-4 border-black inline-block">Configuration</h2>
        
        <div className="space-y-8">
          <div className="group">
            <label className="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Primary Data Stream</label>
            <div className="flex border-b-2 border-zinc-300 py-2">
              <input 
                type="text" 
                value="https://www.echoroukonline.com/feed" 
                readOnly 
                className="w-full bg-transparent font-mono text-sm focus:outline-none"
              />
            </div>
          </div>

          <div className="flex gap-4">
            <div className="flex-1">
              <label className="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Media Handler</label>
              <div className="flex items-center gap-3 bg-zinc-100 p-4 rounded-sm border border-zinc-200">
                <ImageIcon size={16} className="text-blue-600" />
                <span className="text-[10px] font-bold uppercase tracking-tight">Download Images</span>
              </div>
            </div>
            <div className="flex-1">
              <label className="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Post Status</label>
              <div className="flex items-center gap-3 bg-zinc-100 p-4 rounded-sm border border-zinc-200">
                <FileText size={16} className="text-zinc-400" />
                <span className="text-[10px] font-bold uppercase tracking-tight">Auto-Publish</span>
              </div>
            </div>
          </div>

          <div className="pt-8">
            <button 
              onClick={runSync}
              disabled={syncing}
              className="w-full bg-black text-white hover:bg-blue-600 transition-all py-10 flex flex-col items-center justify-center gap-2 group disabled:bg-zinc-400 outline-none"
            >
              <RefreshCw size={32} className={syncing ? "animate-spin mb-2" : "mb-2"} />
              <span className="text-4xl font-black uppercase tracking-tighter leading-none">
                {syncing ? "Syncing..." : "Run Sync"}
              </span>
              <span className="text-[10px] font-medium uppercase tracking-[0.2em] opacity-60">Execute PHP Fetch Request</span>
            </button>
          </div>

          <AnimatePresence>
            {syncResult && (
              <motion.div 
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="p-6 bg-emerald-50 border-2 border-emerald-500"
              >
                <div className="flex items-center gap-3 mb-2">
                  <CheckCircle2 className="text-emerald-500" size={20} />
                  <span className="font-black uppercase text-xs tracking-wider text-emerald-700">Operation Success</span>
                </div>
                <p className="font-bold text-emerald-900">{syncResult.message}</p>
                <p className="text-[10px] font-mono text-emerald-600 mt-1 uppercase">Processed {syncResult.count} items</p>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* Footer Stats */}
        <div className="grid grid-cols-3 gap-1 mt-12 border-t border-zinc-200 pt-8">
          <div>
            <p className="text-[9px] font-black uppercase text-zinc-400 tracking-widest">Last Sync</p>
            <p className="font-mono font-bold text-xs">2026.04.28</p>
          </div>
          <div>
            <p className="text-[9px] font-black uppercase text-zinc-400 tracking-widest">Memory Use</p>
            <p className="font-mono font-bold text-xs">12.4 MB</p>
          </div>
          <div>
            <p className="text-[9px] font-black uppercase text-zinc-400 tracking-widest">PHP Load</p>
            <p className="font-mono font-bold text-xs">0.042 SEC</p>
          </div>
        </div>
      </section>

      {/* Activity Column */}
      <section className="md:col-span-7 p-10 bg-zinc-50 flex flex-col overflow-hidden">
        <div className="flex justify-between items-center mb-8">
          <h2 className="text-3xl font-black uppercase tracking-tighter font-display">Live Feed Log</h2>
          <div className="bg-white px-3 py-1 border border-zinc-200 text-[10px] font-black uppercase shadow-sm">
            Total: {feed?.items.length || 0} Ready
          </div>
        </div>

        <div className="flex-1 space-y-4 overflow-y-auto pr-2 custom-scrollbar">
          {loading ? (
            <div className="flex flex-col items-center justify-center h-full gap-4 grayscale opacity-50">
              <RefreshCw size={48} className="animate-spin text-zinc-900" />
              <p className="text-[10px] font-black uppercase tracking-widest">Initializing Log Connection...</p>
            </div>
          ) : (
            feed?.items.map((item, idx) => (
              <motion.div 
                key={idx}
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: idx * 0.05 }}
                className="bg-white p-6 border-l-8 border-emerald-500 shadow-sm flex items-center justify-between group hover:border-blue-600 transition-all"
              >
                <div className="flex gap-4 items-center min-w-0">
                  <div className="w-12 h-12 bg-zinc-100 rounded-sm overflow-hidden flex-shrink-0 flex items-center justify-center border border-zinc-200">
                    {getThumbnail(item) ? (
                      <img 
                        src={getThumbnail(item)!} 
                        alt="" 
                        className="w-full h-full object-cover"
                        referrerPolicy="no-referrer"
                      />
                    ) : (
                      <span className="text-[10px] font-black text-zinc-400 uppercase">IMG</span>
                    )}
                  </div>
                  <div className="min-w-0">
                    <h3 className="text-sm font-black truncate w-full max-w-[20rem] md:max-w-xs xl:max-w-md uppercase tracking-tight">
                      {item.title}
                    </h3>
                    <p className="text-[10px] font-mono text-zinc-400 uppercase tracking-widest mt-1">
                      Success • {new Date(item.pubDate).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} • status: ready
                    </p>
                  </div>
                </div>
                <div className="hidden lg:block text-right">
                  <span className="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 uppercase border border-emerald-100">
                    Synced
                  </span>
                </div>
              </motion.div>
            ))
          )}
        </div>
      </section>
    </div>
  );

  const CodeViewer = () => {
    const [phpCode, setPhpCode] = useState("");
    useEffect(() => {
      fetch("/wp-rss-importer.php")
        .then(res => res.text())
        .then(setPhpCode);
    }, []);

    return (
      <div className="p-10 space-y-6 bg-white flex-1 overflow-auto h-full">
        <div className="flex justify-between items-center pb-6 border-b-4 border-black">
          <div>
            <h2 className="text-4xl font-black uppercase tracking-tighter font-display">PHP Source</h2>
            <p className="text-[10px] font-black text-zinc-400 uppercase tracking-widest mt-2">Plugin Integrity Check</p>
          </div>
          <button className="flex items-center gap-2 px-6 py-3 bg-black text-white font-black uppercase text-xs tracking-widest hover:bg-blue-600 transition-colors">
            <Download size={16} />
            Fetch Code
          </button>
        </div>
        <div className="bg-zinc-900 p-8 shadow-2xl overflow-hidden group relative">
          <div className="absolute top-4 right-4 text-[10px] font-mono text-zinc-600 uppercase">wp-rss-importer.php</div>
          <pre className="text-sm font-mono text-zinc-300 whitespace-pre overflow-x-auto selection:bg-blue-600 selection:text-white pb-4">
            {phpCode || "// Loading PHP source..."}
          </pre>
        </div>
      </div>
    );
  };

  return (
    <div className="flex h-screen bg-zinc-50 font-sans text-zinc-900 selection:bg-black selection:text-white">
      <style>{`
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a1a1aa; }
      `}</style>
      <Sidebar />
      <div className="flex-1 flex flex-col overflow-hidden">
        <Header />
        <main className="flex-1 overflow-hidden">
          {activeTab === "dashboard" && <Dashboard />}
          {activeTab === "code" && <CodeViewer />}
          {activeTab === "settings" && (
            <div className="flex flex-col items-center justify-center p-20 text-center flex-1 h-full bg-white">
              <Settings size={120} className="text-zinc-100 mb-8" strokeWidth={4} />
              <h3 className="text-5xl font-black uppercase tracking-tighter mb-4 italic">Access Denied</h3>
              <p className="text-sm font-bold uppercase tracking-widest text-zinc-400 max-w-sm">
                Advanced kernel parameters are currently locked for this environment.
              </p>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

