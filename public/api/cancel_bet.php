<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';
header('Content-Type: application/json');
if (!isset(['user_id'])) { echo json_encode(['success' => false, 'message' => '未登录']); die(); }
 = ['user_id'];
 = json_decode(file_get_contents('php://input'), true);
 = ['bet_id'] ?? 0;
if (!) { echo json_encode(['success' => false, 'message' => '参数错误']); die(); }
try {
     = \App\Utils\DB::getInstance()->getConnection();
    ->beginTransaction();
     = ->prepare('SELECT * FROM bets WHERE id = ? AND user_id = ? AND status = 0 FOR UPDATE');
    ->execute([, ]);
     = ->fetch();
    if (!) throw new Exception('订单不存在或已结算');
     = \App\Model\Lottery::getLatest();
    if () {
         = time();  = strtotime(['next_draw_at']);
        if (['issue_no'] <= ['issue_no']) throw new Exception('该期已开奖，无法撤单');
        if (( - ) <= 20) throw new Exception('已封盘，无法撤单');
    }
     = ->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
    ->execute([['bet_amount'], ]);
     = ->prepare('UPDATE users SET daily_turnover = daily_turnover - ?, total_turnover = total_turnover - ? WHERE id = ?');
    ->execute([['bet_amount'], ['bet_amount'], ]);
     = ->prepare("INSERT INTO balance_logs (user_id, type, amount, balance_before, balance_after, description) SELECT id, 'bonus', ?, balance - ?, balance, ? FROM users WHERE id = ?");
    ->execute([['bet_amount'], ['bet_amount'], '撤单退款: ' . ['play_type'], ]);
     = ->prepare('UPDATE bets SET status = 4 WHERE id = ?');
    ->execute([]);
    ->commit();
    echo json_encode(['success' => true, 'message' => '撤单成功']);
} catch (Exception ) { if(->inTransaction()) ->rollBack(); echo json_encode(['success' => false, 'message' => ->getMessage()]); }
