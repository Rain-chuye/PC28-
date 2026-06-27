<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会员管理 - 东爷国际 PRO</title>
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
    <header class="bg-white border-b border-slate-200 p-6 flex flex-col gap-6 sticky top-0 z-50">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-users"></i></div>
                <div>
                    <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">User Management</h1>
                    <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest">Member Directory</p>
                </div>
            </div>
            <button onclick="loadUsers()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div class="bg-slate-50 border border-slate-100 rounded-2xl px-6 flex items-center">
            <i class="fas fa-search text-slate-200 text-xs"></i>
            <input type="text" id="user-search" class="w-full bg-transparent px-4 py-4 outline-none text-xs font-black text-slate-600" placeholder="Search by ID, Username, Nickname or QQ..." onkeyup="if(event.keyCode==13) loadUsers()">
        </div>
    </header>

    <main id="user-list-container" class="p-6 space-y-4">
        <div class="p-20 text-center text-slate-200 font-black text-[10px] uppercase italic tracking-[0.2em]">Loading members...</div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-[1000] bg-slate-900/60 backdrop-blur-sm hidden flex flex-col justify-end">
        <div class="bg-white rounded-t-[3rem] h-[90vh] flex flex-col shadow-2xl p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-10 shrink-0">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Edit Member</h3>
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Administrative Override</p>
                </div>
                <button onclick="closeModal()" class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>

            <div class="space-y-6 flex-1">
                <div class="input-group">
                    <label>Public Nickname</label>
                    <input type="text" id="edit-nick" class="form-input">
                </div>
                <div class="input-group">
                    <label>QQ Number</label>
                    <input type="text" id="edit-qq" class="form-input">
                </div>
                <div class="input-group">
                    <label>Account Balance (CNY)</label>
                    <input type="number" id="edit-bal" class="form-input" step="0.01">
                </div>
                <div class="input-group">
                    <label>Account Status</label>
                    <select id="edit-status" class="form-input">
                        <option value="active">Active (Normal)</option>
                        <option value="frozen">Frozen (No Access)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Reset Password (Leave blank to keep)</label>
                    <input type="text" id="edit-pass" class="form-input" placeholder="New strong password">
                </div>
            </div>

            <div class="pt-10 flex flex-col gap-4 shrink-0">
                <button id="saveBtn" onclick="saveUser()" class="w-full py-5 bg-blue-600 text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-blue-100">Apply Changes</button>
                <button onclick="deleteUser()" class="w-full py-5 bg-rose-50 text-rose-500 rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest border border-rose-100">Permanent Delete</button>
            </div>
        </div>
    </div>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item active"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/odds.php" class="sidebar-item"><i class="fas fa-percentage"></i></a>
        <a href="/admin/pages/lottery_control.php" class="sidebar-item"><i class="fas fa-gamepad"></i></a>
    </nav>

    <script>
        let currentUser = null;
        let userCache = {};

        async function loadUsers() {
            try {
                const s = document.getElementById('user-search').value;
                const res = await fetch('/admin/api/users_list.php?search=' + encodeURIComponent(s)).then(r => r.json());
                const container = document.getElementById('user-list-container');
                if(res.success) {
                    userCache = {};
                    res.data.forEach(u => userCache[u.id] = u);
                    container.innerHTML = res.data.map(u => `
                        <div class="card-p p-6 flex justify-between items-center ${u.status === 'frozen' ? 'opacity-40 grayscale' : ''}">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center font-black text-xs">${u.username[0].toUpperCase()}</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-black text-slate-800">${u.nickname || u.username}</p>
                                        ${u.status === 'frozen' ? '<span class="px-2 py-0.5 bg-rose-500 text-white text-[7px] font-black rounded uppercase tracking-widest">Frozen</span>' : ''}
                                    </div>
                                    <p class="text-[9px] text-slate-300 font-bold uppercase mt-1 tracking-widest">ID: ${u.id} | ${u.username}</p>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-6">
                                <div>
                                    <p class="text-xs font-black text-blue-600">¥${parseFloat(u.balance).toLocaleString()}</p>
                                    <p class="text-[8px] text-slate-300 font-black uppercase mt-1">Balance</p>
                                </div>
                                <button onclick="openEdit(${u.id})" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 flex items-center justify-center border border-slate-100"><i class="fas fa-cog"></i></button>
                            </div>
                        </div>
                    `).join('');
                } else container.innerHTML = `<p class="p-20 text-center font-black text-xs text-rose-400 uppercase tracking-widest">Fetch Failed: ${res.message}</p>`;
            } catch (e) {}
        }

        function openEdit(id) {
            currentUser = userCache[id];
            document.getElementById('edit-nick').value = currentUser.nickname || '';
            document.getElementById('edit-qq').value = currentUser.qq_number || '';
            document.getElementById('edit-bal').value = currentUser.balance;
            document.getElementById('edit-status').value = currentUser.status || 'active';
            document.getElementById('edit-pass').value = '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() { document.getElementById('editModal').classList.add('hidden'); }

        async function saveUser() {
            const btn = document.getElementById('saveBtn'); btn.disabled = true;
            const payload = {
                user_id: currentUser.id,
                nickname: document.getElementById('edit-nick').value.trim(),
                qq: document.getElementById('edit-qq').value.trim(),
                balance: document.getElementById('edit-bal').value,
                status: document.getElementById('edit-status').value,
                password: document.getElementById('edit-pass').value.trim(),
                action: 'update'
            };
            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            if(res.success) { closeModal(); loadUsers(); } else alert(res.message);
            btn.disabled = false;
        }

        async function deleteUser() {
            if(!confirm('DANGER! Permanently delete this member? All records will be orphaned.')) return;
            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: currentUser.id, action: 'delete' })
            }).then(r => r.json());
            if(res.success) { closeModal(); loadUsers(); } else alert(res.message);
        }

        loadUsers();
    </script>
</body>
</html>
