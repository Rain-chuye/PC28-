<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>账单审核 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: -apple-system, sans-serif; }
        .card-p { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .sidebar-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 1.25rem; transition: 0.2s; color: #94a3b8; font-weight: 900; font-size: 11px; text-transform: uppercase; }
        .sidebar-item.active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
        .proof-img { max-width: 100%; border-radius: 1.5rem; margin-top: 12px; border: 4px solid #f8fafc; cursor: zoom-in; }
    </style>
</head>
<body class="pb-32">
    <header class="bg-white border-b border-slate-200 p-6 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <button onclick="history.back()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-chevron-left"></i></button>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Finance Audit</h1>
                <p class="text-[8px] font-black text-blue-500 uppercase tracking-widest">Deposit & Withdrawal Queue</p>
            </div>
        </div>
        <button onclick="loadFinance()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main id="finance-container" class="p-6 space-y-6">
        <div class="p-20 text-center text-slate-200 font-black text-[10px] uppercase italic tracking-[0.2em]">Retrieving Queue...</div>
    </main>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item active"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/odds.php" class="sidebar-item"><i class="fas fa-percentage"></i></a>
        <a href="/admin/pages/lottery_control.php" class="sidebar-item"><i class="fas fa-gamepad"></i></a>
    </nav>

    <script>
        async function loadFinance() {
            try {
                const res = await fetch('/admin/api/finance_list_all.php').then(r => r.json());
                if(res.success) {
                    const container = document.getElementById('finance-container');
                    if(res.data.length === 0) {
                        container.innerHTML = `<div class="p-20 text-center text-slate-200 font-black text-[10px] uppercase italic tracking-widest">Queue is empty</div>`;
                        return;
                    }
                    container.innerHTML = res.data.map(f => {
                        const isDep = f.type === 'deposit';
                        const statusColor = f.status === 'pending' ? 'bg-amber-50 text-amber-500' : (f.status === 'approved' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500');
                        let proof = '';
                        if(f.proof_img) {
                            if(f.proof_img.startsWith('data:image')) proof = `<img src="${f.proof_img}" class="proof-img" onclick="window.open(this.src)">`;
                            else proof = `<div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 font-black text-[10px] text-slate-600 break-all">${f.proof_img}</div>`;
                        }

                        return `
                            <div class="card-p p-8">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest mb-1 ${isDep ? 'text-emerald-500' : 'text-rose-500'}">${isDep ? 'Incoming Deposit' : 'Withdrawal Request'}</p>
                                        <h3 class="text-2xl font-black text-slate-800">¥ ${parseFloat(f.amount).toLocaleString()}</h3>
                                    </div>
                                    <span class="px-4 py-1.5 rounded-xl text-[8px] font-black uppercase tracking-widest ${statusColor}">${f.status}</span>
                                </div>
                                <div class="space-y-3 mb-6">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Member: <span class="text-slate-800">${f.username} (ID: ${f.user_id})</span></p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date: <span class="text-slate-800">${f.created_at}</span></p>
                                    ${proof}
                                </div>
                                ${f.status === 'pending' ? `
                                    <div class="flex gap-4 pt-6 border-t border-slate-50">
                                        <button onclick="review(${f.id}, 'approved')" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-100">Approve</button>
                                        <button onclick="review(${f.id}, 'rejected')" class="flex-1 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest border border-slate-100">Reject</button>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('');
                }
            } catch (e) {}
        }

        async function review(id, status) {
            let reason = ''; if(status === 'rejected') reason = prompt('Reject reason:');
            const res = await fetch('/admin/api/finance_review.php', {
                method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: "id=" + id + "&status=" + status + "&reason=" + encodeURIComponent(reason || '')
            }).then(r => r.json());
            if(res.success) loadFinance(); else alert(res.message);
        }

        loadFinance();
    </script>
</body>
</html>
