<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: -apple-system, sans-serif; }
        .card-p { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .sidebar-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 1.25rem; transition: 0.2s; color: #94a3b8; font-weight: 900; font-size: 11px; text-transform: uppercase; }
        .sidebar-item.active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
        .input-group label { display: block; font-size: 9px; font-weight: 900; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; margin-left: 8px; letter-spacing: 0.1em; }
        .form-input { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 20px; border-radius: 1rem; font-size: 13px; font-weight: 700; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #2563eb; background: white; }
    </style>
</head>
<body class="pb-32">
    <header class="bg-white border-b border-slate-200 p-6 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-cog"></i></div>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Global Settings</h1>
                <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest">System Engine</p>
            </div>
        </div>
        <button onclick="loadSettings()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main class="p-6 space-y-6">
        <div class="card-p p-8 space-y-6">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Announcements</h3>
            <div class="input-group">
                <label>Login Popup Announcement (Remotely Set)</label>
                <textarea id="login-announcement" class="form-input h-32" placeholder="Shown to users upon every login..."></textarea>
            </div>
            <div class="input-group">
                <label>Hall Scrolling Marquee</label>
                <input type="text" id="announcement" class="form-input" placeholder="Scrolling text in the game hall...">
            </div>
        </div>

        <div class="card-p p-8 space-y-6">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Operational Parameters</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-group">
                    <label>Agent Link Prefix</label>
                    <input type="text" id="agent-link" class="form-input">
                </div>
                <div class="input-group">
                    <label>Draw Interval (Seconds)</label>
                    <input type="number" id="draw-interval" class="form-input">
                </div>
            </div>
        </div>

        <div class="card-p p-8 space-y-4">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Interactive Controls</h3>
            <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl">
                <div>
                    <p class="text-xs font-black text-slate-800 uppercase">Mute All Members</p>
                    <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Restrict chat to admins only</p>
                </div>
                <input type="checkbox" id="mute-all" class="w-10 h-6">
            </div>
            <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl">
                <div>
                    <p class="text-xs font-black text-slate-800 uppercase">Robot Auto-Reply</p>
                    <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Global bot response engine</p>
                </div>
                <input type="checkbox" id="bot-reply" class="w-10 h-6">
            </div>
        </div>

        <div class="space-y-4 pt-4">
            <button onclick="saveSettings()" class="w-full py-5 bg-blue-600 text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-blue-100 uppercase tracking-widest">Commit Changes</button>
            <button onclick="clearChatHistory()" class="w-full py-5 bg-white border border-rose-100 text-rose-500 rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest">Purge All Chat History</button>
        </div>
    </main>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/settings.php" class="sidebar-item active"><i class="fas fa-cog"></i></a>
    </nav>

    <script>
        async function loadSettings() {
            const res = await fetch('/api/system_info.php?action=get_settings').then(r => r.json());
            if(res.success) {
                document.getElementById('login-announcement').value = res.data.login_announcement || '';
                document.getElementById('announcement').value = res.data.announcement || '';
                document.getElementById('agent-link').value = res.data.agent_link_prefix || '';
                document.getElementById('draw-interval').value = res.data.custom_draw_interval || 300;
                document.getElementById('mute-all').checked = res.data.chat_mute_all == '1';
                document.getElementById('bot-reply').checked = res.data.bot_auto_reply_enabled == '1';
            }
        }
        async function saveSettings() {
            const payload = {
                login_announcement: document.getElementById('login-announcement').value,
                announcement: document.getElementById('announcement').value,
                agent_link_prefix: document.getElementById('agent-link').value,
                custom_draw_interval: document.getElementById('draw-interval').value,
                chat_mute_all: document.getElementById('mute-all').checked ? '1' : '0',
                bot_auto_reply_enabled: document.getElementById('bot-reply').checked ? '1' : '0'
            };
            const res = await fetch('/admin/api/update_settings.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            alert(res.message);
        }
        async function clearChatHistory() {
            if(!confirm('ARE YOU SURE? This will permanently erase all communication logs.')) return;
            const res = await fetch('/admin/api/update_settings.php?action=clear_chat').then(r => r.json());
            alert(res.message);
        }
        loadSettings();
    </script>
</body>
</html>
