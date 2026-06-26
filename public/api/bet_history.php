<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

if (!isset(['user_id'])) {
    echo json_encode(['success' => false]);
    die;
}

 = ['issue'] ?? '';
 = ['filter'] ?? 'all';
 = ['user_id'];

try {
     = \App\Utils\DB::getInstance()->getConnection();
    if() {
         = ->prepare("SELECT SUM(win_amount) as total_win FROM bets WHERE user_id = ? AND issue_no = ? AND status = 1");
        ->execute([, ]);
         = ->fetchColumn();
        echo json_encode(['success' => true, 'total_win' => (float)( ?: 0)]);
    } else {
         = "SELECT * FROM bets WHERE user_id = ?";
         = [];

        if ( === 'today') {
             .= " AND DATE(created_at) = CURDATE()";
        } elseif ( === 'yesterday') {
             .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        } elseif ( === 'week') {
             .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }

         .= " ORDER BY id DESC LIMIT 100";
         = ->prepare();
        ->execute();
        echo json_encode(['success' => true, 'data' => ->fetchAll()]);
    }
} catch (Exception ) {
    echo json_encode(['success' => false, 'message' => ->getMessage()]);
}
