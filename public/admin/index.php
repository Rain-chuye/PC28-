<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/../../src/Utils/DB.php';

$db = \App\Utils\DB::getInstance()->getConnection();

// Global Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_robot = 0")->fetchColumn();
$totalDeposit = $db->query("SELECT SUM(amount) FROM finance_requests WHERE type='deposit' AND status='approved'")->fetchColumn() ?: 0;
$totalWithdraw = $db->query("SELECT SUM(amount) FROM finance_requests WHERE type='withdraw' AND status='approved'")->fetchColumn() ?: 0;
$totalBets = $db->query("SELECT SUM(bet_amount) FROM bets")->fetchColumn() ?: 0;
$totalWins = $db->query("SELECT SUM(win_amount) FROM bets")->fetchColumn() ?: 0;
$totalRebates = $db->query("SELECT SUM(amount) FROM rebates")->fetchColumn() ?: 0;
$netProfit = $totalBets - $totalWins - $totalRebates;

$todayProfit = $db->query("SELECT SUM(bet_amount - win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayRebates = $db->query("SELECT SUM(amount) FROM rebates WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayNet = $todayProfit - $todayRebates;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理大盘 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: -apple-system, sans-serif; }
        .card-p { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .sidebar-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 1.25rem; transition: 0.2s; color: #94a3b8; font-weight: 900; font-size: 11px; text-transform: uppercase; }
        .sidebar-item.active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
    </style>
</head>
<body class="pb-24">
    <header class="bg-white border-b border-slate-200 p-6 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h1 class="text-sm font-black text-slate-800 uppercase tracking-widest">Admin Dashboard</h1>
                <p class="text-[8px] font-black text-blue-500 uppercase tracking-widest">System Control Center</p>
            </div>
        </div>
        <button onclick="location.href='/api/logout.php'" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400"><i class="fas fa-power-off"></i></button>
    </header>

    <main class="p-6 space-y-6">
        <!-- Highlights -->
        <div class="bg-blue-600 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-blue-100 relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-[10px] font-black opacity-60 uppercase mb-3 tracking-widest">Today's Net Revenue</p>
                <h2 class="text-4xl font-black mb-8">¥ <?= number_format($todayNet, 2) ?></h2>
                <div class="flex gap-6">
                    <div class="bg-white/10 px-4 py-2 rounded-xl">
                        <p class="text-[8px] opacity-60 font-black uppercase mb-1">今日下注</p>
                        <p class="text-sm font-black"><?= number_format($db->query("SELECT SUM(bet_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 0) ?></p>
                    </div>
                    <div class="bg-white/10 px-4 py-2 rounded-xl">
                        <p class="text-[8px] opacity-60 font-black uppercase mb-1">今日返奖</p>
                        <p class="text-sm font-black"><?= number_format($db->query("SELECT SUM(win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 0) ?></p>
                    </div>
                </div>
            </div>
            <i class="fas fa-chart-line absolute -right-6 -bottom-6 text-[15rem] text-white/5 rotate-12"></i>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="card-p p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Users</p>
                <p class="text-2xl font-black text-slate-800"><?= $totalUsers ?></p>
            </div>
            <div class="card-p p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Net Profit</p>
                <p class="text-2xl font-black text-blue-600">¥ <?= number_format($netProfit, 0) ?></p>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card-p p-8 space-y-6">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Financial Flow</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Total Deposit</span>
                    <span class="text-sm font-black text-emerald-500">¥ <?= number_format($totalDeposit, 2) ?></span>
                </div>
                <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Total Withdraw</span>
                    <span class="text-sm font-black text-rose-500">¥ <?= number_format($totalWithdraw, 2) ?></span>
                </div>
                <div class="flex justify-between items-center p-4">
                    <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Active Reserve</span>
                    <span class="text-lg font-black text-blue-600">¥ <?= number_format($totalDeposit - $totalWithdraw, 2) ?></span>
                </div>
            </div>
        </div>
    </main>

    <!-- Admin Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 pb-8 flex justify-around items-center z-50">
        <a href="/admin/index.php" class="sidebar-item active"><i class="fas fa-th-large"></i></a>
        <a href="/admin/pages/users.php" class="sidebar-item"><i class="fas fa-users"></i></a>
        <a href="/admin/pages/finance.php" class="sidebar-item"><i class="fas fa-money-check-alt"></i></a>
        <a href="/admin/pages/odds.php" class="sidebar-item"><i class="fas fa-percentage"></i></a>
        <a href="/admin/pages/lottery_control.php" class="sidebar-item"><i class="fas fa-gamepad"></i></a>
    </nav>
</body>
</html>
