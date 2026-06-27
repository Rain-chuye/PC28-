<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>赔率管理 - 东爷国际 PRO</title>
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
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-percentage"></i></div>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Odds Configuration</h1>
                <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest">Global Payout Rules</p>
            </div>
        </div>
        <button onclick="loadOdds()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main class="p-6 space-y-6">
        <div class="card-p p-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">Room Payout Multipliers</h3>
            <div id="odds-list" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-20 text-center col-span-full text-slate-200 font-black text-[10px] uppercase italic tracking-widest">Syncing Payouts...</div>
            </div>
        </div>
    </main>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/odds.php" class="sidebar-item active"><i class="fas fa-percentage"></i></a>
        <a href="/admin/pages/lottery_control.php" class="sidebar-item"><i class="fas fa-gamepad"></i></a>
    </nav>

    <script>
        async function loadOdds() {
            const res = await fetch('/api/lottery.php').then(r => r.json());
            const list = document.getElementById('odds-list');
            if(res.success) {
                const map = {'big':'大','small':'小','single':'单','double':'双','big_single':'大单','big_double':'大双','small_single':'小单','small_double':'小双','triple':'豹子','straight':'顺子','pair':'对子'};
                list.innerHTML = Object.keys(res.odds).map(k => `
                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 flex flex-col gap-6">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-slate-800 uppercase tracking-widest">${map[k] || k}</span>
                            <span class="text-[8px] font-black text-indigo-400 uppercase tracking-[0.2em]">${k}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="input-group mb-0">
                                <label>Standard (2.0)</label>
                                <input type="number" id="low-${k}" value="${res.odds[k].low}" class="form-input">
                            </div>
                            <div class="input-group mb-0">
                                <label>Premium (2.8)</label>
                                <input type="number" id="high-${k}" value="${res.odds[k].high}" class="form-input">
                            </div>
                        </div>
                        <button onclick="updateOdds('${k}')" class="w-full py-4 bg-white border border-slate-200 rounded-2xl text-[10px] font-black text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all uppercase tracking-widest">Update ${map[k] || k}</button>
                    </div>
                `).join('');
            }
        }

        async function updateOdds(key) {
            const res = await fetch('/admin/api/odds_update.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    play_type: key,
                    odds_low: document.getElementById('low-'+key).value,
                    odds_high: document.getElementById('high-'+key).value
                })
            }).then(r => r.json());
            if(res.success) alert(key.toUpperCase() + ' odds updated.');
        }
        loadOdds();
    </script>
</body>
</html>
