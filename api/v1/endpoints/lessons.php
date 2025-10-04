<?php
// api/v1/endpoints/lessons.php
// レッスン関連API

switch ($method) {
    case 'GET':
        handleGetLessons($routes);
        break;
        
    case 'POST':
        handlePostLessons($routes);
        break;
        
    default:
        apiError('Method not allowed', 405);
}

function handleGetLessons($routes) {
    $lesson_id = $routes[1] ?? null;
    $sub_id = $routes[2] ?? null;
    
    if ($lesson_id && $sub_id) {
        // 特定のレッスン取得
        $lesson_data = getLessonData($lesson_id, $sub_id);
        if ($lesson_data) {
            apiSuccess($lesson_data);
        } else {
            apiError('Lesson not found', 404);
        }
    } elseif ($lesson_id) {
        // レッスン一覧取得（サブレッスン含む）
        $lessons = getLessonList($lesson_id);
        apiSuccess($lessons);
    } else {
        // 全レッスン一覧取得
        $all_lessons = getAllLessons();
        apiSuccess($all_lessons);
    }
}

function handlePostLessons($routes) {
    $action = $routes[1] ?? null;
    
    switch ($action) {
        case 'complete':
            handleLessonComplete();
            break;
            
        default:
            apiError('Invalid action', 400);
    }
}

function handleLessonComplete() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['lesson_id'], $input['sub_id'], $input['user_id'])) {
        apiError('Missing required parameters', 400);
    }
    
    try {
        // レッスン完了処理
        $result = markLessonComplete(
            $input['user_id'],
            $input['lesson_id'],
            $input['sub_id']
        );
        
        apiSuccess(['completed' => $result], 'Lesson marked as complete');
        
    } catch (Exception $e) {
        apiError('Failed to complete lesson', 500);
    }
}

function getAllLessons() {
    // レッスン一覧を返す（簡単な例）
    return [
        ['id' => 1, 'title' => 'あいさつ', 'sub_lessons' => 3],
        ['id' => 2, 'title' => 'わたしのこと', 'sub_lessons' => 3],
        // ... 他のレッスン
    ];
}

function getLessonData($lesson_id, $sub_id) {
    // 実際のデータベースからレッスンデータを取得
    // 現在はサンプルデータを返す
    if ($lesson_id == 1 && in_array($sub_id, [1, 2, 3])) {
        return [
            'lesson_id' => $lesson_id,
            'sub_id' => $sub_id,
            'title' => 'あいさつ ' . $sub_id,
            'content' => [
                'miru' => ['video_url' => 'sample.mp4'],
                'yatte' => ['practice_text' => 'おはよう'],
                'dekita' => ['badge' => 'badge_1_' . $sub_id . '.png']
            ]
        ];
    }
    return null;
}
?>