<?php
// index.php - メインダッシュボード
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit;
}

$page_title = 'ダッシュボード - nihongonote';
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'includes/translation.php';

$user = $_SESSION['user'];
$user_progress = getUserProgress($user['id']);
$user_badges = getUserBadges($user['id']);
$badge_count = getUserBadgeCount($user['id']);

// 言語設定（URLパラメータを優先、その次にセッション言語、その次にユーザーの母語、デフォルトは日本語）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? $user['native_language'] ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'tl'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// 最近の進捗（上位5件）
$recent_progress = array_slice($user_progress, 0, 5);

// 完了したレッスン数を計算（L1_1,L1_2,L1_3すべて完了で1レッスン）
$lesson_progress = [];
foreach ($user_progress as $progress) {
    if ($progress['step'] === 'dekita') {
        $lesson_id = $progress['lesson_id'];
        if (!isset($lesson_progress[$lesson_id])) {
            $lesson_progress[$lesson_id] = [];
        }
        $lesson_progress[$lesson_id][] = $progress;
    }
}

// 各レッスンで3つのサブレッスンが完了しているかチェック
$completed_full_lessons = 0;
foreach ($lesson_progress as $lesson_id => $sections) {
    // L1のような形式の場合、L1_1, L1_2, L1_3の3つが完了している必要
    $section_ids = array_map(function($p) { return $p['lesson_id']; }, $sections);
    $base_lesson = preg_replace('/_\d+$/', '', $lesson_id);
    
    $expected_sections = [$base_lesson . '_1', $base_lesson . '_2', $base_lesson . '_3'];
    $completed_sections = array_intersect($expected_sections, $section_ids);
    
    if (count($completed_sections) >= 3) {
        $completed_full_lessons++;
    }
}

// 全レッスン数は20レッスン
$total_lessons = 20;
$progress_percentage = $total_lessons > 0 ? round(($completed_full_lessons / $total_lessons) * 100, 1) : 0;

// 翻訳するテキスト群
$texts_to_translate = [
    'lets_nihongo' => 'Let\'s nihongo!',
    'nobinobimeter' => 'のびのび<br>メーター',
    'completed_lessons' => '完了レッスン',
    'kotoba_time' => 'はなす<br>れんしゅう',
    'lesson_list' => 'レッスン一覧',
    'badge_collection' => 'バッジコレクション',
    'learning_record' => '学習記録',
    'dekita_mark' => 'できた<br>マーク',
    'no_badges_message' => 'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！',
    'no_progress_message' => '学習履歴がありません。最初のレッスンから始めましょう！',
    'step_miru' => 'みる',
    'step_yatte' => 'やってみる',
    'step_dekita' => 'できた',
    'about_you' => 'あなた<br>のこと',
    'profile_settings' => 'プロフィール設定',
    'game_learning' => 'あそんで<br>まなぶ',
    'game_description' => 'ゲームで楽しく学習',
    'about_school' => 'がっこう<br>のこと',
    'about_school_description' => '学校・生活コラム',
];

// 翻訳実行（日本語以外の場合のみ）
$translations = [];
if ($current_language !== 'ja') {
    if ($current_language === 'en') {
        // 英語の場合は専用の翻訳を使用
        $translations = [
            'lets_nihongo' => 'Let\'s nihongo!',
            'nobinobimeter' => 'Growth<br>Meter',
            'completed_lessons' => 'Completed Lessons',
            'kotoba_time' => 'Speaking<br>Practice',
            'lesson_list' => 'Lesson List',
            'badge_collection' => 'Badge Collection',
            'learning_record' => 'Learning Record',
            'dekita_mark' => 'Trophies',
            'no_badges_message' => 'No badges yet. Complete lessons to earn badges!',
            'no_progress_message' => 'No learning history. Start with your first lesson!',
            'step_miru' => 'Watch',
            'step_yatte' => 'Try',
            'step_dekita' => 'Done',
            'about_you' => 'About<br>You',
            'profile_settings' => 'Profile Settings',
            'game_learning' => "Play &<br>Learn",
            'game_description' => 'Learn through games',
            'about_school' => 'About<br>School',
            'about_school_description' => 'School & Life Column',
        ];
    } elseif ($current_language === 'tl') {
        // タガログ語の場合は専用の翻訳を使用
        $translations = [
            'lets_nihongo' => 'Tara nihongo!',
            'nobinobimeter' => 'Pang-sukat ng<br>Paglago',
            'completed_lessons' => 'Mga Natapos na Aralin',
            'kotoba_time' => 'Pagsasalita',
            'lesson_list' => 'Listahan ng Aralin',
            'badge_collection' => 'Koleksyon ng Badge',
            'learning_record' => 'Talaan ng Pag-aaral',
            'dekita_mark' => 'Mga Tropeo',
            'no_badges_message' => 'Wala pang mga badge. Tapusin ang mga aralin para makakuha ng mga badge!',
            'no_progress_message' => 'Walang kasaysayan ng pag-aaral. Magsimula sa unang aralin!',
            'step_miru' => 'Tingnan',
            'step_yatte' => 'Subukan',
            'step_dekita' => 'Tapos',
            'about_you' => 'Tungkol<br>sa Iyo',
            'profile_settings' => 'Mga Setting ng Profile',
            'game_learning' => 'Maglaro<br>Tayo!',
            'game_description' => 'Matuto sa pamamagitan ng mga laro',
            'about_school' => 'Tungkol sa<br>Paaralan Mo',
            'about_school_description' => 'Mga kwento tungkol sa paaralan at buhay'
        ];
    } elseif ($current_language === 'zh') {
        // 中国語の場合は専用の翻訳を使用
        $translations = [
            'lets_nihongo' => '一起学日语吧!',
            'nobinobimeter' => '我的步伐',
            'completed_lessons' => '已完成课程',
            'kotoba_time' => '口语练习',
            'lesson_list' => '课程列表',
            'badge_collection' => '徽章收藏',
            'learning_record' => '学习记录',
            'dekita_mark' => '成就印章',
            'no_badges_message' => '还没有徽章。完成课程来获得徽章吧！',
            'no_progress_message' => '没有学习记录。从第一课开始吧！',
            'step_miru' => '观看',
            'step_yatte' => '尝试',
            'step_dekita' => '完成',
            'about_you' => '关于你',
            'profile_settings' => '个人资料设置',
            'game_learning' => '游戏学习',
            'game_description' => '通过游戏学习',
            'about_school' => '关于学校',
            'about_school_description' => '学校和生活专栏'
        ];
    } else {
        $translations = translateMultipleTexts($texts_to_translate, $current_language, 'ja');
    }
} else {
    $translations = $texts_to_translate;
}
?>



<div class="dashboard-container">


    <div class="dashboard-grid">
        <!-- 1. のびのびメーター -->
        <div class="dashboard-box progress-box" onclick="location.href='progress/user_progress.php'">
            <div class="box-icon progress-icon">
                <img src="assets/images/icons/meter.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['nobinobimeter'] ?></h3>
        </div>

        <!-- 2. ことばのじかん -->
        <div class="dashboard-box lesson-box" onclick="location.href='lessons/curriculum.php'">
            <div class="box-icon lesson-icon">
                <img src="assets/images/icons/hanasu.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['kotoba_time'] ?></h3>
        </div>

        <!-- 3. できたマーク -->
        <div class="dashboard-box badge-box" onclick="location.href='lessons/badge_tree.php'">
            <div class="box-icon badge-icon">
                <img src="assets/images/icons/badge.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['dekita_mark'] ?></h3>
        </div>

        <!-- 4. あなたのこと -->
        <div class="dashboard-box user-box" onclick="location.href='account/profile.php'">
            <div class="box-icon user-icon">
                <img src="assets/images/icons/profile.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['about_you'] ?></h3>
        </div>

        <!-- 5. ゲームで学ぶ -->
        <div class="dashboard-box game-box" onclick="location.href='games/index.php'">
            <div class="box-icon game-icon">
                <img src="assets/images/icons/game.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['game_learning'] ?></h3>
        </div>
        
        <!-- 6. がっこうのこと -->
        <div class="dashboard-box school-box" onclick="location.href='about_school/index.php'">
            <div class="box-icon school-icon">
                <img src="assets/images/icons/school.png" alt="">
            </div>
            <h3 class="box-title"><?= $translations['about_school'] ?></h3>
        </div>
    </div>

        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <!-- 管理者ページボタン（管理者のみ表示） -->
        <div class="admin-section">
            <button class="admin-button" onclick="location.href='admin/index.php'">
                📊 管理者ページ
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// URL パラメータから言語を取得して適用
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang') || '<?= $current_language ?>';
    
    // ダッシュボードを明示的に表示
    const dashboardContainer = document.querySelector('.dashboard-container');
    const dashboardGrid = document.querySelector('.dashboard-grid');
    const dashboardBoxes = document.querySelectorAll('.dashboard-box');
    
    if (dashboardContainer) {
        dashboardContainer.style.display = 'block';
        dashboardContainer.style.visibility = 'visible';
        dashboardContainer.style.opacity = '1';
    }
    
    if (dashboardGrid) {
        dashboardGrid.style.display = 'grid';
        dashboardGrid.style.visibility = 'visible';
        dashboardGrid.style.opacity = '1';
    }
    
    dashboardBoxes.forEach(box => {
        box.style.display = 'flex';
        box.style.visibility = 'visible';
        box.style.opacity = '1';
    });
    
    if (lang) {
        // ボディに言語クラスを適用
        document.body.className = document.body.className.replace(/\blang-\w+\b/g, '');
        document.body.classList.add('lang-' + lang);
        
        // ダッシュボードコンテナにも適用
        if (dashboardContainer) {
            dashboardContainer.className = dashboardContainer.className.replace(/\blang-\w+\b/g, '');
            dashboardContainer.classList.add('lang-' + lang);
        }
    }
});
</script>

<style>

/* ユーザー詳細の既存スタイルをリセット */
.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 25px;
}

.user-info-row {
    display: flex;
    align-items: center;
    gap: 20px;
}

.logout-row {
    display: flex;
    justify-content: flex-end;
}

/* 統一フォント設定 - 全言語対応 */
* {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, 
                "Noto Sans CJK JP", "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, 
                "Noto Sans", "Liberation Sans", sans-serif !important;
    font-feature-settings: "kern" 1;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* 背景画像設定 */
body {
    position: relative;
    background-image: url('assets/images/bg_top.png'), url('assets/images/bg_bottom.png');
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
}

/* 底部背景 */
.bottom-background {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100vw;
    height: 200px;
    background-image: url('assets/images/bg_bottom.png');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    background-position: center bottom;
    z-index: -1;
    pointer-events: none;
    opacity: 0.7;
}

/* 左右の風船配置 */
.balloon-left {
    position: fixed;
    left: 2%;
    top: 50%;
    transform: translateY(-50%);
    width: auto;
    height: 40vh;
    z-index: 2;
    pointer-events: none;
    opacity: 0.7;
}

.balloon-right {
    position: fixed;
    right: 2%;
    top: 50%;
    transform: translateY(-50%);
    width: auto;
    height: 40vh;
    z-index: 2;
    pointer-events: none;
    opacity: 0.7;
}

/* 特定言語の微調整のみ */
.lang-ne *, .lang-ne {
    font-family: -apple-system, BlinkMacSystemFont, "Noto Sans Devanagari", "Mangal", 
                "Segoe UI", Arial, sans-serif !important;
}

/* 中文フォント統一 */
.lang-zh *, .lang-zh, .lang-zh .box-title, .lang-zh .dashboard-box {
    font-family: 'PingFang SC', 'Microsoft YaHei', 'SimHei', sans-serif !important;
}

/* 中国語の時はタイトルを1行にする */
.lang-zh .box-title {
    white-space: nowrap !important;
    bottom: -20px !important;
}


.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    position: relative;
    top: 0;
    z-index: 10;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.welcome-section {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: var(--primary-color);
    color: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px hsla(var(--base-hue), 40%, 70%, 0.3);
}

.welcome-title {
    font-size: 2.5em;
    margin-bottom: 10px;
    font-weight: 700;
}

.welcome-subtitle {
    font-size: 1.2em;
    opacity: 0.9;
}

.dashboard-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 10px;
    max-width: 1200px;
    margin: 0 auto 10px;
    visibility: visible !important;
    opacity: 1 !important;
}

.dashboard-box {
    background: transparent;
    padding: 0;
    margin: 0;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 300px;
    max-height: 350px;
    display: flex !important;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    visibility: visible !important;
    opacity: 1 !important;
}

.box-icon {
    width: 300px;
    height: 300px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.box-icon img {
    width: 336px;
    height: 336px;
    object-fit: contain;
}

.dashboard-box:hover .box-icon {
    transform: translateY(-5px) scale(1.05);
    filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.8)) drop-shadow(0 0 30px rgba(255, 255, 255, 0.4));
}

.dashboard-box:hover .box-title {
    transform: translateX(-50%) translateY(-5px) scale(1.05);
    filter: drop-shadow(0 0 20px rgba(255, 255, 255, 1)) drop-shadow(0 0 40px rgba(255, 255, 255, 0.8)) drop-shadow(0 0 60px rgba(255, 255, 255, 0.6));
}

.box-title {
    font-size: 3.2em;
    margin: 0;
    font-weight: 600;
    text-align: center;
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    background: transparent;
    padding: 10px 20px;
    border-radius: 0;
    box-shadow: none;
    z-index: 2;
    white-space: normal;
    min-width: 300px;
    max-width: 500px;
    width: 450px;
    overflow: visible;
    text-overflow: initial;
    color: white;
    line-height: 1.2;
    transition: all 0.3s ease;
}


/* Dashboard box specific styles */

.progress-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -50px;
    text-shadow: 0 0 0 #FF6B35, 1px 0 0 #FF6B35, -1px 0 0 #FF6B35, 0 1px 0 #FF6B35, 0 -1px 0 #FF6B35,
                 1px 1px 0 #FF6B35, -1px -1px 0 #FF6B35, 1px -1px 0 #FF6B35, -1px 1px 0 #FF6B35,
                 2px 0 0 #FF6B35, -2px 0 0 #FF6B35, 0 2px 0 #FF6B35, 0 -2px 0 #FF6B35,
                 2px 1px 0 #FF6B35, -2px -1px 0 #FF6B35, 2px -1px 0 #FF6B35, -2px 1px 0 #FF6B35,
                 1px 2px 0 #FF6B35, -1px -2px 0 #FF6B35, 1px -2px 0 #FF6B35, -1px 2px 0 #FF6B35,
                 2px 2px 0 #FF6B35, -2px -2px 0 #FF6B35, 2px -2px 0 #FF6B35, -2px 2px 0 #FF6B35,
                 3px 0 0 #FF6B35, -3px 0 0 #FF6B35, 0 3px 0 #FF6B35, 0 -3px 0 #FF6B35,
                 3px 1px 0 #FF6B35, -3px -1px 0 #FF6B35, 3px -1px 0 #FF6B35, -3px 1px 0 #FF6B35,
                 1px 3px 0 #FF6B35, -1px -3px 0 #FF6B35, 1px -3px 0 #FF6B35, -1px 3px 0 #FF6B35,
                 3px 2px 0 #FF6B35, -3px -2px 0 #FF6B35, 3px -2px 0 #FF6B35, -3px 2px 0 #FF6B35,
                 2px 3px 0 #FF6B35, -2px -3px 0 #FF6B35, 2px -3px 0 #FF6B35, -2px 3px 0 #FF6B35,
                 3px 3px 0 #FF6B35, -3px -3px 0 #FF6B35, 3px -3px 0 #FF6B35, -3px 3px 0 #FF6B35;
}

.lesson-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -50px;
    text-shadow: 0 0 0 #4ECDC4, 1px 0 0 #4ECDC4, -1px 0 0 #4ECDC4, 0 1px 0 #4ECDC4, 0 -1px 0 #4ECDC4,
                 1px 1px 0 #4ECDC4, -1px -1px 0 #4ECDC4, 1px -1px 0 #4ECDC4, -1px 1px 0 #4ECDC4,
                 2px 0 0 #4ECDC4, -2px 0 0 #4ECDC4, 0 2px 0 #4ECDC4, 0 -2px 0 #4ECDC4,
                 2px 1px 0 #4ECDC4, -2px -1px 0 #4ECDC4, 2px -1px 0 #4ECDC4, -2px 1px 0 #4ECDC4,
                 1px 2px 0 #4ECDC4, -1px -2px 0 #4ECDC4, 1px -2px 0 #4ECDC4, -1px 2px 0 #4ECDC4,
                 2px 2px 0 #4ECDC4, -2px -2px 0 #4ECDC4, 2px -2px 0 #4ECDC4, -2px 2px 0 #4ECDC4,
                 3px 0 0 #4ECDC4, -3px 0 0 #4ECDC4, 0 3px 0 #4ECDC4, 0 -3px 0 #4ECDC4,
                 3px 1px 0 #4ECDC4, -3px -1px 0 #4ECDC4, 3px -1px 0 #4ECDC4, -3px 1px 0 #4ECDC4,
                 1px 3px 0 #4ECDC4, -1px -3px 0 #4ECDC4, 1px -3px 0 #4ECDC4, -1px 3px 0 #4ECDC4,
                 3px 2px 0 #4ECDC4, -3px -2px 0 #4ECDC4, 3px -2px 0 #4ECDC4, -3px 2px 0 #4ECDC4,
                 2px 3px 0 #4ECDC4, -2px -3px 0 #4ECDC4, 2px -3px 0 #4ECDC4, -2px 3px 0 #4ECDC4,
                 3px 3px 0 #4ECDC4, -3px -3px 0 #4ECDC4, 3px -3px 0 #4ECDC4, -3px 3px 0 #4ECDC4;
}

.badge-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -50px;
    text-shadow: 0 0 0 #E67E22, 1px 0 0 #E67E22, -1px 0 0 #E67E22, 0 1px 0 #E67E22, 0 -1px 0 #E67E22,
                 1px 1px 0 #E67E22, -1px -1px 0 #E67E22, 1px -1px 0 #E67E22, -1px 1px 0 #E67E22,
                 2px 0 0 #E67E22, -2px 0 0 #E67E22, 0 2px 0 #E67E22, 0 -2px 0 #E67E22,
                 2px 1px 0 #E67E22, -2px -1px 0 #E67E22, 2px -1px 0 #E67E22, -2px 1px 0 #E67E22,
                 1px 2px 0 #E67E22, -1px -2px 0 #E67E22, 1px -2px 0 #E67E22, -1px 2px 0 #E67E22,
                 2px 2px 0 #E67E22, -2px -2px 0 #E67E22, 2px -2px 0 #E67E22, -2px 2px 0 #E67E22,
                 3px 0 0 #E67E22, -3px 0 0 #E67E22, 0 3px 0 #E67E22, 0 -3px 0 #E67E22,
                 3px 1px 0 #E67E22, -3px -1px 0 #E67E22, 3px -1px 0 #E67E22, -3px 1px 0 #E67E22,
                 1px 3px 0 #E67E22, -1px -3px 0 #E67E22, 1px -3px 0 #E67E22, -1px 3px 0 #E67E22,
                 3px 2px 0 #E67E22, -3px -2px 0 #E67E22, 3px -2px 0 #E67E22, -3px 2px 0 #E67E22,
                 2px 3px 0 #E67E22, -2px -3px 0 #E67E22, 2px -3px 0 #E67E22, -2px 3px 0 #E67E22,
                 3px 3px 0 #E67E22, -3px -3px 0 #E67E22, 3px -3px 0 #E67E22, -3px 3px 0 #E67E22;
}

.user-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -60px;
    text-shadow: 0 0 0 #FF69B4, 1px 0 0 #FF69B4, -1px 0 0 #FF69B4, 0 1px 0 #FF69B4, 0 -1px 0 #FF69B4,
                 1px 1px 0 #FF69B4, -1px -1px 0 #FF69B4, 1px -1px 0 #FF69B4, -1px 1px 0 #FF69B4,
                 2px 0 0 #FF69B4, -2px 0 0 #FF69B4, 0 2px 0 #FF69B4, 0 -2px 0 #FF69B4,
                 2px 1px 0 #FF69B4, -2px -1px 0 #FF69B4, 2px -1px 0 #FF69B4, -2px 1px 0 #FF69B4,
                 1px 2px 0 #FF69B4, -1px -2px 0 #FF69B4, 1px -2px 0 #FF69B4, -1px 2px 0 #FF69B4,
                 2px 2px 0 #FF69B4, -2px -2px 0 #FF69B4, 2px -2px 0 #FF69B4, -2px 2px 0 #FF69B4,
                 3px 0 0 #FF69B4, -3px 0 0 #FF69B4, 0 3px 0 #FF69B4, 0 -3px 0 #FF69B4,
                 3px 1px 0 #FF69B4, -3px -1px 0 #FF69B4, 3px -1px 0 #FF69B4, -3px 1px 0 #FF69B4,
                 1px 3px 0 #FF69B4, -1px -3px 0 #FF69B4, 1px -3px 0 #FF69B4, -1px 3px 0 #FF69B4,
                 3px 2px 0 #FF69B4, -3px -2px 0 #FF69B4, 3px -2px 0 #FF69B4, -3px 2px 0 #FF69B4,
                 2px 3px 0 #FF69B4, -2px -3px 0 #FF69B4, 2px -3px 0 #FF69B4, -2px 3px 0 #FF69B4,
                 3px 3px 0 #FF69B4, -3px -3px 0 #FF69B4, 3px -3px 0 #FF69B4, -3px 3px 0 #FF69B4;
}

.school-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -60px;
    text-shadow: 0 0 0 #9B59B6, 1px 0 0 #9B59B6, -1px 0 0 #9B59B6, 0 1px 0 #9B59B6, 0 -1px 0 #9B59B6,
                 1px 1px 0 #9B59B6, -1px -1px 0 #9B59B6, 1px -1px 0 #9B59B6, -1px 1px 0 #9B59B6,
                 2px 0 0 #9B59B6, -2px 0 0 #9B59B6, 0 2px 0 #9B59B6, 0 -2px 0 #9B59B6,
                 2px 1px 0 #9B59B6, -2px -1px 0 #9B59B6, 2px -1px 0 #9B59B6, -2px 1px 0 #9B59B6,
                 1px 2px 0 #9B59B6, -1px -2px 0 #9B59B6, 1px -2px 0 #9B59B6, -1px 2px 0 #9B59B6,
                 2px 2px 0 #9B59B6, -2px -2px 0 #9B59B6, 2px -2px 0 #9B59B6, -2px 2px 0 #9B59B6,
                 3px 0 0 #9B59B6, -3px 0 0 #9B59B6, 0 3px 0 #9B59B6, 0 -3px 0 #9B59B6,
                 3px 1px 0 #9B59B6, -3px -1px 0 #9B59B6, 3px -1px 0 #9B59B6, -3px 1px 0 #9B59B6,
                 1px 3px 0 #9B59B6, -1px -3px 0 #9B59B6, 1px -3px 0 #9B59B6, -1px 3px 0 #9B59B6,
                 3px 2px 0 #9B59B6, -3px -2px 0 #9B59B6, 3px -2px 0 #9B59B6, -3px 2px 0 #9B59B6,
                 2px 3px 0 #9B59B6, -2px -3px 0 #9B59B6, 2px -3px 0 #9B59B6, -2px 3px 0 #9B59B6,
                 3px 3px 0 #9B59B6, -3px -3px 0 #9B59B6, 3px -3px 0 #9B59B6, -3px 3px 0 #9B59B6;
}

.game-box .box-title {
    color: white;
    font-weight: 600;
    bottom: -60px;
    text-shadow: 0 0 0 #2ecc71, 1px 0 0 #2ecc71, -1px 0 0 #2ecc71, 0 1px 0 #2ecc71, 0 -1px 0 #2ecc71,
                 1px 1px 0 #2ecc71, -1px -1px 0 #2ecc71, 1px -1px 0 #2ecc71, -1px 1px 0 #2ecc71,
                 2px 0 0 #2ecc71, -2px 0 0 #2ecc71, 0 2px 0 #2ecc71, 0 -2px 0 #2ecc71,
                 2px 1px 0 #2ecc71, -2px -1px 0 #2ecc71, 2px -1px 0 #2ecc71, -2px 1px 0 #2ecc71,
                 1px 2px 0 #2ecc71, -1px -2px 0 #2ecc71, 1px -2px 0 #2ecc71, -1px 2px 0 #2ecc71,
                 2px 2px 0 #2ecc71, -2px -2px 0 #2ecc71, 2px -2px 0 #2ecc71, -2px 2px 0 #2ecc71,
                 3px 0 0 #2ecc71, -3px 0 0 #2ecc71, 0 3px 0 #2ecc71, 0 -3px 0 #2ecc71,
                 3px 1px 0 #2ecc71, -3px -1px 0 #2ecc71, 3px -1px 0 #2ecc71, -3px 1px 0 #2ecc71,
                 1px 3px 0 #2ecc71, -1px -3px 0 #2ecc71, 1px -3px 0 #2ecc71, -1px 3px 0 #2ecc71,
                 3px 2px 0 #2ecc71, -3px -2px 0 #2ecc71, 3px -2px 0 #2ecc71, -3px 2px 0 #2ecc71,
                 2px 3px 0 #2ecc71, -2px -3px 0 #2ecc71, 2px -3px 0 #2ecc71, -2px 3px 0 #2ecc71,
                 3px 3px 0 #2ecc71, -3px -3px 0 #2ecc71, 3px -3px 0 #2ecc71, -3px 3px 0 #2ecc71;
}

.admin-section {
    margin-top: 120px;
    text-align: center;
}

.admin-button {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    display: inline-block;
    text-decoration: none;
}

.admin-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
    background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
}

/* レスポンシブ */
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-title {
        font-size: 2em;
    }
    
    .progress-stats {
        flex-direction: column;
        gap: 15px;
    }
}
/* ログアウトリンクのスタイル */
.logout-container {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
}

.logout-link {
    color: #666;
    text-decoration: none;
    font-size: 0.9em;
    padding: 8px 16px;
    border-radius: 20px;
    background: var(--card-background);
    border: 1px solid #ddd;
    transition: all 0.3s ease;
}

.logout-link:hover {
    background: #f5f5f5;
    color: var(--primary-dark);
    border-color: var(--primary-color);
}

/* メニューへボタンのスタイル */
.menu-button-container {
    position: absolute;
    top: 20px;
    right: 180px;
    z-index: 10;
}

.menu-button {
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 12px 24px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.menu-button:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* Fix double bg_bottom.png - disable lower positioned background */
.bottom-background {
    display: none !important;
}
</style>

<!-- 底部背景 -->
<div class="bottom-background"></div>

<!-- 左右の風船 -->
<!-- <img src="assets/images/baroon_left.png" alt="" class="balloon-left"> -->
<!-- <img src="assets/images/baroon_right.png" alt="" class="balloon-right"> -->

<?php require_once 'includes/footer.php'; ?>