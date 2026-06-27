<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();

try {
    $action = $_GET['action'] ?? 'update';

    if ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) throw new Exception("Invalid payload");

        $sql = "UPDATE system_settings SET
                announcement = ?,
                agent_link_prefix = ?,
                custom_draw_interval = ?,
                chat_mute_all = ?,
                bot_auto_reply_enabled = ?,
                login_announcement = ?
                WHERE id = 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['announcement'],
            $data['agent_link_prefix'],
            $data['custom_draw_interval'],
            $data['chat_mute_all'],
            $data['bot_auto_reply_enabled'],
            $data['login_announcement']
        ]);
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    }
    elseif ($action === 'clear_chat') {
        $db->exec("TRUNCATE TABLE group_chat_messages");
        echo json_encode(['success' => true, 'message' => 'Chat history cleared']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
