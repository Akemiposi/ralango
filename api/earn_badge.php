<?php
// api/earn_badge.php
// バッジ獲得API

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// リクエストボディを取得
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$lesson_id = $input['lesson_id'] ?? null;
$badge_type = $input['badge_type'] ?? 'completion';

// バリデーション
if (!$lesson_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing lesson_id']);
    exit;
}

$lesson_id = (int)$lesson_id;
$allowed_badge_types = ['completion', 'perfect', 'speed', 'practice'];

if (!in_array($badge_type, $allowed_badge_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid badge_type']);
    exit;
}

if ($lesson_id < 1 || $lesson_id > 20) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid lesson ID']);
    exit;
}

try {
    $result = earnBadge($user['id'], $lesson_id, $badge_type);
    
    if ($result['success']) {
        $response = [
            'success' => true,
            'message' => $result['message'],
            'badge' => [
                'lesson_id' => $lesson_id,
                'badge_type' => $badge_type,
                'image_path' => getBadgeImagePath($lesson_id, 1),
                'earned_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        // 特別なバッジの場合は追加情報
        if ($result['message'] === 'Already earned') {
            $response['already_earned'] = true;
        }
        
        echo json_encode($response);
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to earn badge',
            'details' => $result['error'] ?? 'Unknown error'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'details' => $e->getMessage()
    ]);
}
?>