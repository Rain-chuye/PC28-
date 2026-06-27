<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客服系统 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: -apple-system, sans-serif; }
        .card-p { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .sidebar-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 1.25rem; transition: 0.2s; color: #94a3b8; font-weight: 900; font-size: 11px; text-transform: uppercase; }
        .sidebar-item.active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
    </style>
</head>
<body class="pb-32">
    <header class="bg-white border-b border-slate-200 p-6 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-headset"></i></div>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Support Center</h1>
                <p class="text-[8px] font-black text-blue-500 uppercase tracking-widest">User Communications</p>
            </div>
        </div>
        <button onclick="loadUsers()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main id="user-list" class="p-6 space-y-4">
        <div class="p-20 text-center text-slate-200 font-black text-[10px] uppercase italic tracking-widest">Loading conversations...</div>
    </main>

    <!-- Individual Chat Modal -->
    <div id="chatModal" class="fixed inset-0 z-[1000] bg-slate-900/60 backdrop-blur-sm hidden flex flex-col justify-end">
        <div class="bg-white rounded-t-[3rem] h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 font-black text-xs" id="chat-user-icon">?</div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800" id="chat-user-name">Loading...</h3>
                        <p class="text-[9px] text-emerald-500 font-black uppercase tracking-widest">Live Chat Window</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="clearChat()" class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center border border-rose-100"><i class="fas fa-trash-alt"></i></button>
                    <button onclick="closeChat()" class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div id="chat-box" class="flex-1 overflow-y-auto p-8 space-y-6 bg-slate-50/50"></div>

            <div class="p-8 bg-white border-t border-slate-50 flex gap-4">
                <input type="text" id="chat-input" class="flex-1 bg-slate-50 border-none rounded-2xl px-6 font-bold text-sm outline-none" placeholder="Reply to user...">
                <button onclick="sendReply()" class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/chat_admin.php" class="sidebar-item active"><i class="fas fa-headset"></i></a>
    </nav>

    <script>
        let currentUid = null;
        let users = [];

        async function loadUsers() {
            const res = await fetch('/admin/api/users_list.php').then(r => r.json());
            if(res.success) {
                users = res.data;
                const container = document.getElementById('user-list');
                container.innerHTML = users.map(u => `
                    <div onclick="openChat(${u.id})" class="card-p p-6 flex justify-between items-center cursor-pointer active:scale-95 transition-all">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center font-black text-xs">${u.username[0].toUpperCase()}</div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800">${u.nickname || u.username}</h4>
                                <p class="text-[9px] text-slate-300 font-bold uppercase mt-1 tracking-widest">ID: ${u.id}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-200 text-xs"></i>
                    </div>
                `).join('');
            }
        }

        async function openChat(uid) {
            currentUid = uid;
            const u = users.find(u => u.id == uid);
            document.getElementById('chat-user-name').innerText = u.nickname || u.username;
            document.getElementById('chat-user-icon').innerText = u.username[0].toUpperCase();
            document.getElementById('chatModal').classList.remove('hidden');
            loadMessages();
        }

        function closeChat() { document.getElementById('chatModal').classList.add('hidden'); currentUid = null; }

        async function loadMessages() {
            if(!currentUid) return;
            const res = await fetch(`/api/chat.php?action=get_all_admin`).then(r => r.json());
            if(res.success) {
                const box = document.getElementById('chat-box');
                const filtered = res.data.filter(m => m.sender_id == currentUid || m.receiver_id == currentUid);
                box.innerHTML = filtered.map(m => `
                    <div class="flex ${m.sender_id == 0 ? 'justify-end' : ''}">
                        <div class="max-w-[85%] p-5 rounded-3xl ${m.sender_id == 0 ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-800 border border-slate-100 shadow-sm'}">
                            <p class="text-sm font-bold leading-relaxed">${m.message}</p>
                            <p class="text-[8px] mt-2 opacity-40 text-right uppercase">${m.created_at.split(' ')[1]}</p>
                        </div>
                    </div>
                `).join('');
                box.scrollTop = box.scrollHeight;
            }
        }

        async function sendReply() {
            const i = document.getElementById('chat-input'); const msg = i.value.trim(); if(!msg || !currentUid) return;
            const res = await fetch(`/api/chat.php?action=send_admin`, {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: currentUid, message: msg, type: 'text' })
            }).then(r => r.json());
            if(res.success) { i.value = ''; loadMessages(); }
        }

        async function clearChat() {
            if(!confirm('Clear conversation with this user?')) return;
            await fetch(`/api/chat.php?action=clear_private&user_id=${currentUid}`);
            loadMessages();
        }

        loadUsers();
        setInterval(loadMessages, 5000);
    </script>
</body>
</html>
