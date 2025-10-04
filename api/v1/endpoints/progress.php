<?php
// api/v1/endpoints/progress.php
// 進捗関連API

switch ($method) {
    case 'GET':
        handleGetProgress($routes);
        break;
        
    case 'POST':
        handlePostProgress($routes);
        break;
        
    default:
        apiError('Method not allowed', 405);
}

function handleGetProgress($routes) {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        apiError('Missing user_id parameter', 400);
    }
    
    try {
        $progress = getUserProgress($user_id);
        $badges = getUserBadges($user_id);
        
        apiSuccess([
            'progress' => $progress,
            'badges' => $badges,
            'total_badges' => count($badges)
        ]);
        
    } catch (Exception $e) {
        apiError('Failed to get progress', 500);
    }
}

function handlePostProgress($routes) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id'], $input['lesson_id'], $input['step'])) {
        apiError('Missing required parameters', 400);
    }
    
    try {
        // 進捗を保存
        $result = saveUserProgress(
            $input['user_id'],
            $input['lesson_id'],
            $input['step']
        );
        
        apiSuccess(['saved' => $result], 'Progress saved successfully');
        
    } catch (Exception $e) {
        apiError('Failed to save progress', 500);
    }
}
?>