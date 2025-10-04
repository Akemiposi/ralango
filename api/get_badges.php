<?php
// api/get_badges.php
// バッジ取得API

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

// ログインチェック
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];

// GETメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = $_GET['user_id'] ?? $user['id'];
$user_id = (int)$user_id;

// 他のユーザーのバッジは管理者のみ閲覧可能
if ($user_id !== (int)$user['id'] && $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    $badges = getUserBadges($user_id);
    $badge_count = getUserBadgeCount($user_id);
    
    // バッジデータを整理
    $organized_badges = [];
    foreach ($badges as $badge) {
        $lesson_id = $badge['lesson_id'];
        if (!isset($organized_badges[$lesson_id])) {
            $organized_badges[$lesson_id] = [];
        }
        $organized_badges[$lesson_id][] = [
            'id' => $badge['id'],
            'type' => $badge['badge_type'],
            'earned_at' => $badge['created_at'],
            'image_path' => getBadgeImagePath($lesson_id, 1)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'badges' => $organized_badges,
        'total_count' => $badge_count,
        'user_id' => $user_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'details' => $e->getMessage()
    ]);
}
?>
