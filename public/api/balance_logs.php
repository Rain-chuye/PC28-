<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'all';
$filter = $_GET['filter'] ?? 'all';
$limit = (int)($_GET['limit'] ?? 50);

try {
    $sql = "SELECT id, type, amount, balance_after, description, created_at FROM balance_logs WHERE user_id = :uid";
    $params = [':uid' => $userId];

    if ($type !== 'all') {
        $sql .= " AND type = :type";
    }

    if ($filter === 'today') {
        $sql .= " AND DATE(created_at) = CURDATE()";
    } elseif ($filter === 'yesterday') {
        $sql .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    } elseif ($filter === 'week') {
        $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    }

    $sql .= " ORDER BY id DESC LIMIT :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    if ($type !== 'all') $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
