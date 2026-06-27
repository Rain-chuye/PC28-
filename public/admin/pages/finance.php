<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>财务管理 - 东爷国际 PRO</title>
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
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-money-check-alt"></i></div>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Finance Center</h1>
                <p class="text-[8px] font-black text-blue-500 uppercase tracking-widest">Rewards & Red Packets</p>
            </div>
        </div>
        <button onclick="location.href='/admin/pages/finance_list.php'" class="text-[9px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-4 py-2 rounded-xl">审核记录</button>
    </header>

    <main class="p-6 space-y-6">
        <div class="card-p p-10">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-10">Manual Red Packet Release</h3>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="input-group">
                        <label>Total Pool (¥)</label>
                        <input type="number" id="rp-amount" class="form-input" value="100">
                    </div>
                    <div class="input-group">
                        <label>Quantity</label>
                        <input type="number" id="rp-count" class="form-input" value="10">
                    </div>
                </div>
                <div class="input-group">
                    <label>Turnover Requirement (¥)</label>
                    <input type="number" id="rp-turnover" class="form-input" value="0">
                </div>
                <div class="input-group">
                    <label>Message / Greeting</label>
                    <input type="text" id="rp-msg" class="form-input" value="恭喜发财，大吉大利！">
                </div>
                <button onclick="sendRP()" class="w-full py-5 bg-blue-600 text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-blue-100 uppercase tracking-widest mt-4">Release Now</button>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-100 p-6 rounded-[2rem] flex items-start gap-4">
            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
            <p class="text-[10px] font-black text-blue-800 leading-relaxed uppercase tracking-wider">
                System will instantly push red packets to all online members in the bonus chat room.
            </p>
        </div>
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
        async function sendRP() {
            const res = await fetch('/admin/api/send_red_packet.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    amount: document.getElementById('rp-amount').value,
                    count: document.getElementById('rp-count').value,
                    turnover: document.getElementById('rp-turnover').value,
                    message: document.getElementById('rp-msg').value
                })
            }).then(r => r.json());
            if(res.success) alert('Red packet pool released successfully!');
            else alert(res.message);
        }
    </script>
</body>
</html>
