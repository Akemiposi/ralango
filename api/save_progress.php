<?php
// api/save_progress.php
// 進捗保存API

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
$sub_lesson_id = $input['sub_lesson_id'] ?? null;
$step = $input['step'] ?? null;

// バリデーション
if (!$lesson_id || !$step) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$lesson_id = (int)$lesson_id;
$allowed_steps = ['miru', 'yatte', 'dekita'];

if (!in_array($step, $allowed_steps)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid step']);
    exit;
}

if ($lesson_id < 1 || $lesson_id > 20) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid lesson ID']);
    exit;
}

try {
    // 進捗を記録（lesson_idとsub_lesson_idを分けて記録）
    $result = recordProgressWithSubLesson($user['id'], $lesson_id, $sub_lesson_id, $step);
    
    if ($result['success']) {
        // 完了時にバッジも付与
        if ($step === 'dekita') {
            if ($sub_lesson_id) {
                // サブレッスンバッジ（L1, L2など）
                earnBadge($user['id'], $lesson_id, 'completion', $sub_lesson_id);
            } else {
                // 通常レッスンのバッジ
                earnBadge($user['id'], $lesson_id, 'completion');
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Progress saved successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to save progress',
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