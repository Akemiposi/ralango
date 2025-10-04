<?php
// includes/logo.php - サイトロゴコンポーネント

function renderSiteLogo($lesson_id = null, $sub_lesson_id = null) {
    $title = $lesson_id && $sub_lesson_id ? "LESSON{$lesson_id}_{$sub_lesson_id}" : "nihongonote";
    $subtitle = $lesson_id && $sub_lesson_id ? "nihongonote" : "日本語学習アプリ";
    
    // 現在の言語を取得
    $current_lang = $_SESSION['language'] ?? 'ja';
    
    // 言語別ロゴの選択
    $logo_files = [
        'en' => 'ralango_logo_en.png',
        'zh' => 'ralango_logo_zh.png', 
        'ja' => 'ralango_logo_jp.png'
    ];
    
    $logo_file = $logo_files[$current_lang] ?? $logo_files['ja']; // デフォルトは日本語
    $logo_path = $lesson_id ? "../assets/images/{$logo_file}" : "assets/images/{$logo_file}";
    
    return '
    <div class="logo">
        <img src="' . $logo_path . '" alt="nihongonote" class="logo-image">
        <div class="logo-text">
            <div class="title">' . htmlspecialchars($title) . '</div>
            <div class="subtitle">' . htmlspecialchars($subtitle) . '</div>
        </div>
    </div>';
}
?>