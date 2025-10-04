<?php
// lessons/badge_tree.php - バッジツリー表示
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ファイル読み込み
try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
} catch (Exception $e) {
    die('ファイル読み込みエラー: ' . $e->getMessage());
}

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['user'];

// 進捗データの取得（エラーハンドリング付き）
try {
    $user_badges = getUserBadges($user['id']);
    $user_progress = getUserProgress($user['id']);
} catch (Exception $e) {
    $user_badges = [];
    $user_progress = [];
}

$page_title = 'できたマーク - nihongonote';

// 言語設定（URLパラメータを優先、その次にセッション言語、その次にユーザーの母語、デフォルトは日本語）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? ($user ? $user['native_language'] : 'ja') ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'tl'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// 翻訳
$translations = [];
if ($current_language === 'en') {
    $translations = [
        'badge_collection_title' => 'Achievement Marks',
        'badge_collection_subtitle' => 'Take a Look at the Achievement Marks You Worked Hard to Get',
        'earned_badges' => 'Achievement Marks'
    ];
} elseif ($current_language === 'zh') {
    $translations = [
        'badge_collection_title' => '完成标记',
        'badge_collection_subtitle' => '看看你努力获得的完成标记吧',
        'earned_badges' => '完成标记'
    ];
} elseif ($current_language === 'tl') {
    $translations = [
        'badge_collection_title' => 'Mga Nakamit na Marka',
        'badge_collection_subtitle' => 'Tingnan ang mga Nakamit na Marka na Pinaghirapan Mo',
        'earned_badges' => 'Nakamit na Marka'
    ];
} else {
    $translations = [
        'badge_collection_title' => 'できたマーク',
        'badge_collection_subtitle' => 'あつめたできたマークをみてみよう！',
        'earned_badges' => 'もらったできたマークのかず'
    ];
}

require_once '../includes/header.php';

// バッジを整理
$badges_by_lesson = [];
foreach ($user_badges as $badge) {
    $lesson_id = $badge['lesson_id'];
    if (!isset($badges_by_lesson[$lesson_id])) {
        $badges_by_lesson[$lesson_id] = [];
    }
    $badges_by_lesson[$lesson_id][] = $badge;
}

// 進捗状況を整理
$progress_by_lesson = [];
foreach ($user_progress as $progress) {
    $lesson_id = $progress['lesson_id'];
    if (!isset($progress_by_lesson[$lesson_id])) {
        $progress_by_lesson[$lesson_id] = [];
    }
    $progress_by_lesson[$lesson_id][] = $progress['step'];
}

// 統計計算 - L1とL2は3つのサブレッスン、L3-L20は3ステップ
$total_possible_badges = 3 + 3 + (18 * 3); // L1: 3サブレッスン + L2: 3サブレッスン + L3-20: 18レッスン × 3ステップ
$earned_badges_count = count($user_badges);
$completion_percentage = round(($earned_badges_count / $total_possible_badges) * 100, 1);

// curriculum.phpからL1-L3のレッスンデータを同期（サブタイトルのみ翻訳対応）
$curriculum_lessons = [
    1 => [
        'title' => 'おはよう', 
        'description' => [
            'ja' => '朝の基本的な挨拶を学びます',
            'en' => 'Learn basic morning greetings',
            'zh' => '学习早上的基本问候',
            'tl' => 'Matutuhan ang mga Bating Umaga'
        ],
        'sub_lessons' => [
            1 => [
                'title' => 'おはよう!',
                'description' => [
                    'ja' => '友だちへの朝のあいさつ',
                    'en' => 'Morning greeting to friends',
                    'zh' => '对朋友的早上问候',
                    'tl' => 'Pagbati sa mga Kaibigan sa Umaga'
                ]
            ],
            2 => [
                'title' => 'おはようございます。',
                'description' => [
                    'ja' => '先生への丁寧な朝のあいさつ',
                    'en' => 'Polite morning greeting to teachers',
                    'zh' => '对老师的礼貌早上问候',
                    'tl' => 'Magalang na Pagbati sa mga Guro sa Umaga'
                ]
            ],
            3 => [
                'title' => 'さようなら',
                'description' => [
                    'ja' => 'お別れのあいさつ',
                    'en' => 'Farewell greeting',
                    'zh' => '告别问候',
                    'tl' => 'Pagpapaalam'
                ]
            ]
        ]
    ],
    2 => [
        'title' => 'あなたのなまえは？', 
        'description' => [
            'ja' => '名前を伝えたり相手の名前を聞いたりできるようになります',
            'en' => 'Learn to tell your name and ask for others\' names',
            'zh' => '学会说出自己的名字和询问别人的名字',
            'tl' => 'Matutuhan ang Pagsasabi ng Pangalan at Pagtanong ng Pangalan ng Iba'
        ],
        'sub_lessons' => [
            1 => [
                'title' => 'わたしは、（子供の名前）です',
                'description' => [
                    'ja' => '自分の名前を伝える',
                    'en' => 'Tell your name',
                    'zh' => '说出自己的名字',
                    'tl' => 'Sabihin ang Inyong Pangalan'
                ]
            ],
            2 => [
                'title' => 'あなたのなまえはなんですか？',
                'description' => [
                    'ja' => '相手の名前をたずねる',
                    'en' => 'Ask for someone\'s name',
                    'zh' => '询问对方的名字',
                    'tl' => 'Tanungin ang Pangalan ng Iba'
                ]
            ],
            3 => [
                'title' => 'よろしくおねがいします',
                'description' => [
                    'ja' => '自己紹介の締めくくり',
                    'en' => 'Polite closing for introductions',
                    'zh' => '自我介绍的结束语',
                    'tl' => 'Magalang na Pagtatapos sa Pagpapakilala'
                ]
            ]
        ]
    ],
    3 => [
        'title' => '自己紹介', 
        'description' => [
            'ja' => '出身地、年齢、誕生日について話せるようになります',
            'en' => 'Learn to talk about your hometown, age, and birthday',
            'zh' => '学会谈论出生地、年龄和生日',
            'tl' => 'Matutuhan ang Pag-usapan ang Pinagmulan, Edad, at Kaarawan'
        ]
    ]
];

// レッスンデータ - curriculum.phpと同期（翻訳対応処理）
$lessons = [];
foreach ($curriculum_lessons as $id => $lesson) {
    $lessons[$id] = [
        'title' => $lesson['title'],
        'description' => is_array($lesson['description']) ? ($lesson['description'][$current_language] ?? $lesson['description']['ja']) : $lesson['description']
    ];
    
    if (isset($lesson['sub_lessons'])) {
        $lessons[$id]['sub_lessons'] = [];
        foreach ($lesson['sub_lessons'] as $sub_id => $sub_lesson) {
            $lessons[$id]['sub_lessons'][$sub_id] = [
                'title' => $sub_lesson['title'],
                'description' => is_array($sub_lesson['description']) ? ($sub_lesson['description'][$current_language] ?? $sub_lesson['description']['ja']) : $sub_lesson['description']
            ];
        }
    }
}

// 残りのレッスン（L4-L20）を追加
$lessons[4] = ['title' => '数字', 'description' => '1から10まで'];
$lessons[5] = ['title' => 'ひらがな', 'description' => 'ひらがなの読み方'];
$lessons[6] = ['title' => '時計', 'description' => '時間の読み方'];
$lessons[7] = ['title' => '学用品', 'description' => '学校で使う物'];
$lessons[8] = ['title' => '色', 'description' => '基本的な色'];
$lessons[9] = ['title' => '曜日', 'description' => '曜日の言い方'];
$lessons[10] = ['title' => '天気', 'description' => '天気について'];
$lessons[11] = ['title' => '学校生活1', 'description' => '学校での基本表現'];
$lessons[12] = ['title' => '学校生活2', 'description' => '学校生活の応用'];
$lessons[13] = ['title' => '買い物', 'description' => 'お店で使う表現'];
$lessons[14] = ['title' => '季節', 'description' => '四季について'];
$lessons[15] = ['title' => '食事', 'description' => '食べ物や食事'];
$lessons[16] = ['title' => '健康', 'description' => '体調や健康'];
$lessons[17] = ['title' => '地域', 'description' => '住んでいる場所'];
$lessons[18] = ['title' => '家族', 'description' => '家族の呼び方'];
$lessons[19] = ['title' => '趣味', 'description' => '好きなこと'];
$lessons[20] = ['title' => '総復習', 'description' => '全体の復習'];

function hasStep($lesson_id, $step, $progress_by_lesson) {
    $progress = $progress_by_lesson[$lesson_id] ?? [];
    return in_array($step, $progress);
}

function getCompletedSubLessons($lesson_id, $user_badges) {
    // L1とL2の場合、獲得したサブレッスンバッジ数を返す
    if ($lesson_id == 1 || $lesson_id == 2) {
        $count = 0;
        foreach ($user_badges as $badge) {
            if ($badge['lesson_id'] == $lesson_id && $badge['sub_lesson_id'] !== null) {
                $count++;
            }
        }
        return $count;
    }
    return 0;
}

?>

<div class="badge-tree-container">
    <div class="badge-tree-header">
        <h1 class="badge-tree-title"><?= h($translations['badge_collection_title']) ?></h1>
        <p class="badge-tree-subtitle">
            <?= h($translations['badge_collection_subtitle']) ?>
        </p>
        
        <div class="collection-stats">
            <div class="stat-card">
                <div class="stat-number"><?= $earned_badges_count ?></div>
                <div class="stat-label"><?= h($translations['earned_badges']) ?></div>
            </div>
        </div>
    </div>

    <div class="badge-grid">
        <?php for ($lesson_id = 1; $lesson_id <= 20; $lesson_id++): ?>
            <?php 
            $lesson = $lessons[$lesson_id] ?? ['title' => "レッスン{$lesson_id}", 'description' => ''];
            $lesson_badges = $badges_by_lesson[$lesson_id] ?? [];
            $lesson_progress = $progress_by_lesson[$lesson_id] ?? [];
            ?>
            
            <div class="lesson-badge-group">
                <div class="lesson-info">
                    <h3 class="lesson-number">L<?= $lesson_id ?></h3>
                    <div class="lesson-details">
                        <div class="lesson-name"><?= h($lesson['title']) ?></div>
                        <div class="lesson-desc"><?= h($lesson['description']) ?></div>
                    </div>
                </div>

                <div class="badge-row">
                    <?php if (($lesson_id == 1 || $lesson_id == 2) && isset($lesson['sub_lessons'])): ?>
                        <!-- L1とL2のサブレッスンバッジ -->
                        <?php foreach ($lesson['sub_lessons'] as $sub_id => $sub_lesson): ?>
                            <?php 
                            // サブレッスンバッジがあるかチェック
                            $has_badge = false;
                            foreach ($user_badges as $badge) {
                                if ($badge['lesson_id'] == $lesson_id && $badge['sub_lesson_id'] == $sub_id) {
                                    $has_badge = true;
                                    break;
                                }
                            }
                            // 新しいパターンのバッジパス
                            $badge_path = "../assets/images/badge/generated/badge_L{$lesson_id}_{$sub_id}.png";
                            ?>
                            
                            <div class="badge-slot <?= $has_badge ? 'earned' : 'locked' ?>">
                                <?php if ($has_badge): ?>
                                    <div class="badge-earned" onclick="showBadgeDetail(<?= $lesson_id ?>, <?= $sub_id ?>)">
                                        <img src="<?= $badge_path ?>" alt="Lesson <?= $lesson_id ?>_<?= $sub_id ?> Badge"
                                             class="badge-image"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="badge-fallback" style="display: none;">
                                            <div class="badge-number"><?= $lesson_id ?>-<?= $sub_id ?></div>
                                        </div>
                                        <div class="badge-step-name"><?= h($sub_lesson['title']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="badge-locked">
                                        <div class="lock-icon">🔒</div>
                                        <div class="badge-step-name"><?= h($sub_lesson['title']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- 通常レッスンの3ステップバッジ -->
                        <?php 
                        $steps = ['miru', 'yatte', 'dekita'];
                        $step_names = ['みる', 'やってみる', 'できた'];
                        ?>
                        
                        <?php for ($step = 1; $step <= 3; $step++): ?>
                            <?php 
                            $step_key = $steps[$step - 1];
                            $has_badge = hasStep($lesson_id, $step_key, $progress_by_lesson);
                            // 新しいパターンのバッジパス
                            $badge_path = "../assets/images/badge/generated/badge_L{$lesson_id}_{$step}.png";
                            ?>
                            
                            <div class="badge-slot <?= $has_badge ? 'earned' : 'locked' ?>">
                                <?php if ($has_badge): ?>
                                    <div class="badge-earned" onclick="showBadgeDetail(<?= $lesson_id ?>, <?= $step ?>)">
                                        <img src="<?= $badge_path ?>" alt="Lesson <?= $lesson_id ?>-<?= $step ?> Badge"
                                             class="badge-image"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="badge-fallback" style="display: none;">
                                            <div class="badge-number"><?= $lesson_id ?>-<?= $step ?></div>
                                        </div>
                                        <div class="badge-step-name"><?= $step_names[$step - 1] ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="badge-locked">
                                        <div class="lock-icon">🔒</div>
                                        <div class="badge-step-name"><?= $step_names[$step - 1] ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>

                <div class="lesson-progress-mini">
                    <div class="progress-mini-bar">
                        <?php 
                        if (($lesson_id == 1 || $lesson_id == 2) && isset($lesson['sub_lessons'])) {
                            // L1とL2の場合はサブレッスンの完了数
                            $completed_steps = getCompletedSubLessons($lesson_id, $user_badges);
                            $total_steps = 3;
                        } else {
                            // 通常レッスンの場合は3ステップの完了数
                            $completed_steps = 0;
                            $steps = ['miru', 'yatte', 'dekita'];
                            foreach ($steps as $step_key) {
                                if (hasStep($lesson_id, $step_key, $progress_by_lesson)) {
                                    $completed_steps++;
                                } else {
                                    break; // ステップは順番に完了する必要がある
                                }
                            }
                            $total_steps = 3;
                        }
                        $lesson_percentage = ($completed_steps / $total_steps) * 100;
                        ?>
                        <div class="progress-mini-fill" style="width: <?= $lesson_percentage ?>%"></div>
                    </div>
                    <div class="progress-mini-text"><?= $completed_steps ?>/<?= $total_steps ?></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<!-- バッジ詳細モーダル -->
<div id="badgeDetailModal" class="badge-detail-modal">
    <div class="badge-detail-content">
        <div class="modal-header">
            <h2 class="modal-title">バッジ詳細</h2>
            <button class="modal-close" onclick="closeBadgeDetail()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="badge-large">
                <img id="badgeDetailImage" src="" alt="" class="badge-detail-image">
            </div>
            <div class="badge-info-detail">
                <h3 id="badgeDetailTitle">Lesson X-X</h3>
                <p id="badgeDetailDescription">説明</p>
                <div class="badge-stats">
                    <div class="stat">
                        <span class="stat-label">獲得日:</span>
                        <span id="badgeDetailDate">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeBadgeDetail()">閉じる</button>
        </div>
    </div>
</div>

<style>
/* 背景画像設定 */
body {
    background-image: url('../assets/images/bg_top.png'), url('../assets/images/bg_bottom.png');
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
}

.badge-tree-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.badge-tree-header {
    text-align: center;
    margin-bottom: 40px;
}

.badge-tree-title {
    font-size: 3em;
    color: var(--accent-color);
    margin-bottom: 15px;
    font-weight: 700;
}

.badge-tree-subtitle {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 30px;
}

.collection-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    min-width: 120px;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: var(--accent-color);
    line-height: 1;
}

.stat-label {
    color: #666;
    font-size: 0.9em;
    margin-top: 5px;
}

.progress-overview {
    max-width: 400px;
    margin: 0 auto;
}

.progress-bar-large {
    height: 12px;
    background: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill-large {
    height: 100%;
    background: var(--primary-color);
    border-radius: 6px;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    color: #666;
    font-size: 0.9em;
}

.badge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.lesson-badge-group {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.lesson-badge-group:hover {
    transform: translateY(-3px);
}

.lesson-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.lesson-number {
    background: var(--primary-color);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    font-weight: bold;
}

.lesson-details {
    flex: 1;
}

.lesson-name {
    font-size: 1.3em;
    font-weight: bold;
    color: #333;
    margin-bottom: 3px;
}

.lesson-desc {
    color: #666;
    font-size: 0.9em;
}

.badge-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 15px;
}

.badge-slot {
    flex: 1;
    text-align: center;
}

.badge-earned,
.badge-locked {
    position: relative;
    transition: all 0.3s ease;
}

.badge-earned {
    cursor: pointer;
    animation: badgeGlow 2s ease-in-out infinite alternate;
}

.badge-earned:hover {
    transform: scale(1.1);
}

.badge-image {
    max-width: 80px;
    height: auto;
    filter: drop-shadow(0 0 10px rgba(255,215,0,0.6)) drop-shadow(0 0 20px rgba(255,215,0,0.3));
}

.badge-fallback {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 8px 25px rgba(255,215,0,0.4);
}

.badge-number {
    color: white;
    font-weight: bold;
    font-size: 1.1em;
}

.badge-locked {
    opacity: 0.4;
}

.lock-icon {
    width: 80px;
    height: 80px;
    border: 3px dashed #ccc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2em;
    color: #ccc;
    margin: 0 auto;
}

.badge-step-name {
    margin-top: 8px;
    font-size: 0.8em;
    color: #666;
    font-weight: 500;
}

.lesson-progress-mini {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-mini-bar {
    flex: 1;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-mini-fill {
    height: 100%;
    background: var(--primary-color);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.progress-mini-text {
    font-size: 0.8em;
    color: #666;
    font-weight: 500;
    min-width: 30px;
}

/* バッジ詳細モーダル */
.badge-detail-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.badge-detail-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}

.modal-title {
    color: var(--accent-color);
    font-size: 1.5em;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 2em;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 25px;
    text-align: center;
}

.badge-large {
    margin-bottom: 20px;
}

.badge-detail-image {
    max-width: 120px;
    height: auto;
    filter: drop-shadow(0 0 15px rgba(255,215,0,0.8)) drop-shadow(0 0 30px rgba(255,215,0,0.4));
}

.badge-info-detail h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.3em;
}

.badge-stats {
    margin-top: 20px;
    text-align: left;
}

.stat {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #eee;
    text-align: right;
}

@keyframes badgeGlow {
    0% { filter: drop-shadow(0 0 10px rgba(255,215,0,0.6)) drop-shadow(0 0 20px rgba(255,215,0,0.3)); }
    100% { filter: drop-shadow(0 0 15px rgba(255,215,0,0.8)) drop-shadow(0 0 30px rgba(255,215,0,0.5)); }
}

/* レスポンシブ */
@media (max-width: 768px) {
    .badge-grid {
        grid-template-columns: 1fr;
    }
    
    .collection-stats {
        gap: 15px;
    }
    
    .stat-card {
        min-width: 100px;
        padding: 15px;
    }
    
    .stat-number {
        font-size: 2em;
    }
    
    .badge-row {
        gap: 10px;
    }
    
    .badge-image {
        max-width: 60px;
        height: auto;
    }
    
    .lock-icon {
        width: 60px;
        height: 60px;
    }
    
    .badge-fallback {
        width: 60px;
        height: 60px;
    }
}
</style>

<script>
function showBadgeDetail(lessonId, step) {
    const modal = document.getElementById('badgeDetailModal');
    const image = document.getElementById('badgeDetailImage');
    const title = document.getElementById('badgeDetailTitle');
    const description = document.getElementById('badgeDetailDescription');
    
    // バッジ情報を設定
    let imagePath, titleText, descText;
    
    if (lessonId == 1) {
        // L1のサブレッスンバッジ
        imagePath = `../assets/images/badge/generated/badge_L1_${step}.png`;
        titleText = `レッスン1-${step}`;
        
        const subLessonTitles = ['', 'おはよう', 'おはようございます', 'さようなら'];
        const subLessonDescs = ['', '友だちへの朝のあいさつを完了しました', '先生への丁寧な朝のあいさつを完了しました', 'お別れのあいさつを完了しました'];
        descText = subLessonDescs[step];
    } else {
        // 通常レッスンバッジ
        imagePath = `../assets/images/badge/generated/badge_L${lessonId}_${step}.png`;
        titleText = `レッスン${lessonId}-${step}`;
        
        const stepNames = ['', 'みる', 'やってみる', 'できた'];
        const descriptions = ['', '動画を見ました', '発音練習をしました', 'レッスンを完了しました'];
        descText = descriptions[step];
    }
    
    image.src = imagePath;
    image.alt = titleText + ' バッジ';
    title.textContent = titleText;
    description.textContent = descText;
    
    modal.style.display = 'flex';
}

function closeBadgeDetail() {
    const modal = document.getElementById('badgeDetailModal');
    modal.style.display = 'none';
}

// モーダル外クリックで閉じる
document.getElementById('badgeDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBadgeDetail();
    }
});

// ESCキーで閉じる
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBadgeDetail();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>