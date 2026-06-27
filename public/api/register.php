<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $nickname = trim($input['nickname'] ?? '');
        $password = $input['password'] ?? '';
        $qq = trim($input['qq'] ?? '');
        if (!$username || !$nickname || !$password) {
            echo json_encode(['success' => false, 'message' => '信息不完整']);
        } else {
            $db = \App\Utils\DB::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => '用户名已存在']);
            } else {
                $avatar = "/assets/avatars/" . rand(1, 4) . ".jpg";
                $stmt = $db->prepare("INSERT INTO users (username, nickname, password, qq_number, balance, status, role, avatar) VALUES (?, ?, ?, ?, 0, 1, 'user', ?)");
                $stmt->execute([$username, $nickname, $password, $qq, $avatar]);
                echo json_encode(['success' => true]);
            }
        }
    }
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
