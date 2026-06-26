<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset(['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    die;
}

 = \App\Utils\DB::getInstance()->getConnection();
 = ['user_id'];
 = ['type'] ?? 'all';
 = ['filter'] ?? 'all';
 = (int)(['limit'] ?? 50);

try {
     = "SELECT id, type, amount, balance_after, description, created_at FROM balance_logs WHERE user_id = :uid";
     = [':uid' => ];

    if ( !== 'all') {
         .= " AND type = :type";
    }

    if ( === 'today') {
         .= " AND DATE(created_at) = CURDATE()";
    } elseif ( === 'yesterday') {
         .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    } elseif ( === 'week') {
         .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    }

     .= " ORDER BY id DESC LIMIT :limit";

     = ->prepare();
    ->bindValue(':uid', , PDO::PARAM_INT);
    if ( !== 'all') ->bindValue(':type', , PDO::PARAM_STR);
    ->bindValue(':limit', , PDO::PARAM_INT);

    ->execute();
     = ->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => ]);
} catch (Exception ) {
    echo json_encode(['success' => false, 'message' => ->getMessage()]);
}
