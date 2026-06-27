<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $action = $_GET['action'] ?? 'get';

    if ($action === 'get_settings') {
        $stmt = $db->query("SELECT * FROM system_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $settings]);
    } else {
        $stmt = $db->query("SELECT * FROM system_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $settings]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
