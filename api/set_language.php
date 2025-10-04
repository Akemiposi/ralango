<?php
// api/set_language.php - 言語設定をセッションとDBに保存
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

header('Content-Type: application/json');

// ログインチェック
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// POSTデータを取得
$data = json_decode(file_get_contents('php://input'), true);
$language = $data['language'] ?? null;

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'tl'];

if (!$language || !in_array($language, $supported_languages)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid language']);
    exit;
}

try {
    // セッションに言語設定を保存
    $_SESSION['language'] = $language;
    $_SESSION['language_override'] = true;
    
    // ユーザーのpreferred_languageをDBに更新
    $user_id = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
    $stmt->execute([$language, $user_id]);
    
    // セッションのユーザー情報も更新
    $_SESSION['user']['preferred_language'] = $language;
    
    echo json_encode(['success' => true, 'language' => $language]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>