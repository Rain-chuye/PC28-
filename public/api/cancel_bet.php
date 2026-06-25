<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    die;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$betId = $input['bet_id'] ?? 0;

if (!$betId) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    die;
}

try {
    $db = \App\Utils\DB::getInstance()->getConnection();
    $db->beginTransaction();

    // Check bet exists and belongs to user
    $stmt = $db->prepare("SELECT * FROM bets WHERE id = ? AND user_id = ? AND status = 0 FOR UPDATE");
    $stmt->execute([$betId, $userId]);
    $bet = $stmt->fetch();

    if (!$bet) {
        throw new Exception("订单不存在或已结算");
    }

    // Check if it's too late to cancel
    $latest = \App\Model\Lottery::getLatest();
    if ($latest) {
        $now = time();
        $nextDrawTs = strtotime($latest['next_draw_at']);
        // If next_draw_at is not available, we might need a different check or default to current issue logic
        // For simplicity, we check if the issue_no is the same as the latest draw issue_no
        // But usually, bets are for the NEXT issue.
        if ($bet['issue_no'] <= $latest['issue_no']) {
            throw new Exception("该期已开奖，无法撤单");
        }

        // Re-using the 20s lock logic from bet.php
        // However, we don't have a reliable countdown here without more context.
        // Let's assume 20s before next draw is the limit.
        if (($nextDrawTs - $now) <= 20) {
            throw new Exception("已封盘，无法撤单");
        }
    }

    // Refund
    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$bet['bet_amount'], $userId]);

    // Update turnover (reverse)
    $stmt = $db->prepare("UPDATE users SET daily_turnover = daily_turnover - ?, total_turnover = total_turnover - ? WHERE id = ?");
    $stmt->execute([$bet['bet_amount'], $bet['bet_amount'], $userId]);

    // Log refund
    $stmt = $db->prepare("INSERT INTO balance_logs (user_id, type, amount, balance_before, balance_after, description)
                           SELECT id, 'bonus', ?, balance - ?, balance, ? FROM users WHERE id = ?");
    $stmt->execute([$bet['bet_amount'], $bet['bet_amount'], "撤单退款: {$bet['play_type']} ({$bet['issue_no']} 期)", $userId]);

    // Delete or Update bet status to 'cancelled' (status 4)
    $stmt = $db->prepare("UPDATE bets SET status = 4 WHERE id = ?");
    $stmt->execute([$betId]);

    $db->commit();
    echo json_encode(['success' => true, 'message' => '撤单成功']);
} catch (Exception $e) {
    if($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
