<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');
 = ['token'] ?? ['token'] ?? '';
if ( !== 'admin_lua_secret_123') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); die(); }
 = ['action'] ?? '';
 = \App\Utils\DB::getInstance()->getConnection();
try {
    if ( === 'user_list') {
         = ['search'] ?? '';
         = 'SELECT id, username, nickname, balance, status FROM users WHERE is_robot = 0';
         = [];
        if () {  .= ' AND (username LIKE ? OR nickname LIKE ? OR id = ?)';  = ["%%", "%%", ]; }
         = ->prepare(); ->execute();
        echo json_encode(['success' => true, 'data' => ->fetchAll()]);
    } elseif ( === 'adjust_balance') {
         = ['user_id'];  = (float)['amount'];
        ->beginTransaction();
         = ->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
        ->execute([, ]);
         = ->prepare("INSERT INTO balance_logs (user_id, type, amount, balance_before, balance_after, description) SELECT id, 'bonus', ?, balance - ?, balance, 'Lua Admin Adjust' FROM users WHERE id = ?");
        ->execute([, , ]);
        ->commit();
        echo json_encode(['success' => true]);
    } elseif ( === 'set_status') {
         = ['user_id'];  = ['status'];
         = ->prepare('UPDATE users SET status = ? WHERE id = ?');
        ->execute([, ]);
        echo json_encode(['success' => true]);
    } elseif ( === 'get_bets') {
         = ->query('SELECT * FROM bets ORDER BY id DESC LIMIT 50');
        echo json_encode(['success' => true, 'data' => ->fetchAll()]);
    } else { echo json_encode(['success' => false, 'message' => 'Invalid action']); }
} catch (Exception ) { if(->inTransaction()) ->rollBack(); echo json_encode(['success' => false, 'message' => ->getMessage()]); }
