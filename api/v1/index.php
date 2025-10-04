<?php
// api/v1/index.php
// API エントリーポイント

header('Content-Type: application/json; charset=utf-8');

// CORS対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// エラーハンドリング
function apiError($message, $code = 400, $details = null) {
    http_response_code($code);
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ];
    if ($details) {
        $response['details'] = $details;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function apiSuccess($data = null, $message = null) {
    $response = [
        'success' => true,
        'timestamp' => date('c')
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    if ($message) {
        $response['message'] = $message;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 設定ファイル読み込み（エラーハンドリング強化）
    if (file_exists('../../config/database.php')) {
        require_once '../../config/database.php';
    }
    
    // functions.phpは一時的にコメントアウト（重複定義エラー回避）
    if (file_exists('../../includes/functions.php')) {
        require_once '../../includes/functions.php';
    }
    
    // リクエスト解析
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // API パスを取得
    $parsed_url = parse_url($request_uri, PHP_URL_PATH);
    // 環境判定
    $is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
                 file_exists('/Applications/XAMPP')) || 
                (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
    
    if ($is_xampp) {
        $api_path = str_replace('/nihongonote/api/v1/', '', $parsed_url);
    } else {
        $api_path = str_replace('/api/v1/', '', $parsed_url);
    }
    $method = $_SERVER['REQUEST_METHOD'];
    
    // ルーティング
    $routes = explode('/', trim($api_path, '/'));
    $endpoint = $routes[0] ?? 'health';
    
    switch ($endpoint) {
        case 'lessons':
            require_once 'endpoints/lessons.php';
            break;
            
        case 'progress':
            require_once 'endpoints/progress.php';
            break;
            
        case 'badges':
            require_once 'endpoints/badges.php';
            break;
            
        case 'auth':
            require_once 'endpoints/auth.php';
            break;
            
        case 'health':
            // ヘルスチェックエンドポイント
            apiSuccess([
                'status' => 'ok',
                'version' => '1.0.0',
                'server_time' => date('c')
            ], 'API is running');
            break;
            
        default:
            apiError('Endpoint not found', 404);
    }
    
} catch (Exception $e) {
    apiError('Internal server error', 500, [
        'error_code' => $e->getCode(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>