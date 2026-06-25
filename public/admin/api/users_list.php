<?php
require_once __DIR__ . '/../auth_logic.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';

header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $search = $_GET['search'] ?? '';

    $sql = "SELECT id, username, nickname, balance, qq_number, status, role FROM users WHERE is_robot = 0";
    $params = [];

    if ($search) {
        $sql .= " AND (username LIKE ? OR nickname LIKE ? OR id = ?)";
        $params = ["%$search%", "%$search%", is_numeric($search) ? (int)$search : -1];
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
