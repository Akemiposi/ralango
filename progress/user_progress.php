<?php
// progress/user_progress.php - ユーザー用進捗管理
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/GeminiTranslator.php';

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

// 管理者からのアクセスかチェック
$viewing_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$is_admin_viewing = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin' && $viewing_user_id;

// セッションの管理者情報を保存
$admin_user = $_SESSION['user'];

if ($is_admin_viewing) {
    // 管理者が特定ユーザーを閲覧する場合
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$viewing_user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: ../admin/users.php');
        exit;
    }
} else {
    // 通常のユーザー自身のアクセス
    $user = $_SESSION['user'];
}

$translator = new GeminiTranslator();

// 現在の言語設定を取得（headerと同じロジック）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? ($user ? $user['native_language'] : 'ja') ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'ko', 'vi', 'tl', 'ne', 'pt'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// 翻訳配列
$translations = [
    'growth_meter' => [
        'ja' => 'のびのびメーター',
        'en' => 'Growth Meter',
        'zh' => '学习进步',
        'tl' => 'Pang-sukat ng Paglago'
    ],
    'encouragement_message' => [
        'ja' => '%sさん、きょうもがんばっているね！',
        'en' => '%s, you\'re doing great today too!',
        'zh' => '%s，今天也很努力哦！',
        'tl' => '%s, magaling ka ngayon din!'
    ],
    'lessons_finished' => [
        'ja' => 'おわったレッスン',
        'en' => 'Lessons Finished',
        'zh' => '结束的课程',
        'tl' => 'Natapos na mga Aralin'
    ],
    'lessons_finished_detail' => [
        'ja' => '%s%% おわった',
        'en' => '%s%%',
        'zh' => '%s%%',
        'tl' => '%s%%'
    ],
    'trophies_got' => [
        'ja' => 'もらったバッジ',
        'en' => 'Trophies You\'ve Got',
        'zh' => '获得的徽章',
        'tl' => 'Mga Tropeong Nakuha Mo'
    ],
    'trophies_detail' => [
        'ja' => 'ぜんぶ%dこのなか',
        'en' => 'Out of %d Total',
        'zh' => '总计%d个中',
        'tl' => 'Out of %d Total'
    ],
    'time_spent' => [
        'ja' => 'やったじかん（ふん）',
        'en' => 'Time Spent (Minutes)',
        'zh' => '学习时间（分钟）',
        'tl' => 'Oras na Ginugol (Minuto)'
    ],
    'time_spent_detail' => [
        'ja' => '%sじかん',
        'en' => '%s Minutes',
        'zh' => '%s小时',
        'tl' => '%s Minutes'
    ],
    'streak' => [
        'ja' => 'つづけたひにち',
        'en' => 'Streak',
        'zh' => '持续学习天数',
        'tl' => 'Sunod-sunod na Araw'
    ],
    'streak_detail' => [
        'ja' => 'ひかんつづけてる',
        'en' => '%d Days in a Row',
        'zh' => '持续%d天',
        'tl' => '%d Days in a Row'
    ],
    'how_many_days' => [
        'ja' => 'なんにちできたかな',
        'en' => 'How many days have you been learning?',
        'zh' => '你已经学习了多少天了？',
        'tl' => 'Kailan Mo Ito Magagawa?'
    ],
    'things_learned' => [
        'ja' => 'おぼえたところ',
        'en' => 'Things You\'ve Learned',
        'zh' => '已学习的内容',
        'tl' => 'Mga Natutunan Mo'
    ],
    'what_did_today' => [
        'ja' => 'きょうやったところ',
        'en' => 'What You Did Today',
        'zh' => '今天学习的内容',
        'tl' => 'Ginawa Mo Ngayon'
    ],
    'completed_status' => [
        'ja' => 'おわり',
        'en' => 'Completed',
        'zh' => '已完成',
        'tl' => 'Tapos'
    ],
    'continue_link' => [
        'ja' => 'つづきから',
        'en' => 'Continue',
        'zh' => '继续',
        'tl' => 'Magpatuloy'
    ],
    'start_link' => [
        'ja' => 'はじめる',
        'en' => 'Start',
        'zh' => '开始',
        'tl' => 'Magsimula'
    ],
    'lessons' => [
        'ja' => 'レッスン',
        'en' => 'Lessons',
        'zh' => '课程',
        'tl' => 'Mga Aralin'
    ],
    'weekly_study_count' => [
        'ja' => 'せんしゅうからのべんきょうかいすう',
        'en' => 'Study sessions this week',
        'zh' => '本周学习次数',
        'tl' => 'Mga sesyon ng pag-aaral ngayong linggo'
    ],
    'janken_game' => [
        'ja' => 'じゃんけんゲーム',
        'en' => 'Rock Paper Scissors',
        'zh' => '石头剪刀布',
        'tl' => 'Laro ng Janken (Bato-Bato-Pik)'
    ],
    'kana_card_game' => [
        'ja' => 'かなカードゲーム',
        'en' => 'Kana Card Game',
        'zh' => '假名卡片游戏',
        'tl' => 'Laro ng Kana Cards'
    ],
    'score_label' => [
        'ja' => 'てんすう:',
        'en' => 'Score:',
        'zh' => '得分:',
        'tl' => 'Points:'
    ],
    'score_unit' => [
        'ja' => 'てん',
        'en' => 'pts',
        'zh' => '分',
        'tl' => 'Points'
    ]
];

// 翻訳取得関数
function getTranslation($key, $language, $translations, $default = '') {
    return $translations[$key][$language] ?? $translations[$key]['ja'] ?? $default;
}

// 性別に応じた色設定
$gender = $user['child_gender'] ?? 'boy';
$color_scheme = $gender === 'girl' ? 'pink' : 'blue';

// 翻訳関数（一時的に無効化）
function translateIfNeeded($text, $targetLanguage, $translator) {
    return $text; // 翻訳を無効にして高速化
}

$page_title = translateIfNeeded('学習記録', $user['native_language'], $translator) . ' - nihongonote';

// header.phpが$userを上書きするので、一時的に保存
$target_user = $user;

require_once '../includes/header.php';

// $userを復元
$user = $target_user;

// adminユーザーの場合は全体統計を表示（ただし特定ユーザー閲覧時は除く）
$is_admin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

if ($is_admin && !$is_admin_viewing) {
    // 管理者が全体統計を見る場合
    $user_progress = getAllProgress();
    $user_badges = getAllBadges();
    $target_user_id = null; // 管理者の全体統計では個別ユーザーIDは不要
} else {
    // 通常ユーザーまたは管理者が特定ユーザーを閲覧する場合
    $target_user_id = $is_admin_viewing ? $viewing_user_id : $user['id'];
    $user_progress = getUserProgress($target_user_id);
    $user_badges = getUserBadges($target_user_id);
}

// 全体統計取得関数
function getAllProgress() {
    global $pdo;
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_progress ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getAllBadges() {
    global $pdo;
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM badges ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}


// curriculum.phpからのレッスンタイトル多言語対応
$lesson_titles = [
    'ja' => [
        1 => 'おはよう',
        2 => 'あなたのなまえは？',
        3 => '自己紹介'
    ],
    'en' => [
        1 => 'Good Morning',
        2 => 'What\'s Your Name?',
        3 => 'Self-Introduction'
    ],
    'zh' => [
        1 => '早上好',
        2 => '你叫什么名字？',
        3 => '自我介绍'
    ],
    'tl' => [
        1 => 'Magandang Umaga',
        2 => 'Ano ang Pangalan Mo?',
        3 => 'Sariling Pagpapakilala'
    ]
];

// curriculum.phpからの詳細レッスンデータ
$lessons = [
    1 => [
        'title' => 'おはよう', 
        'description' => '朝の基本的な挨拶を学びます',
        'sub_lessons' => [
            1 => [
                'title' => 'おはよう!',
                'description' => '友だちへの朝のあいさつ',
                'japanese' => 'おはよう！',
                'english' => 'Good morning!',
                'chinese' => '早！',
                'tagalog' => 'Magandang umaga!'
            ],
            2 => [
                'title' => 'おはようございます。',
                'description' => '先生への丁寧な朝のあいさつ', 
                'japanese' => 'おはようございます。',
                'english' => 'Good morning! (a polite greeting)',
                'chinese' => '早上好！',
                'tagalog' => 'Magandang umaga po.'
            ],
            3 => [
                'title' => 'さようなら',
                'description' => 'お別れのあいさつ',
                'japanese' => 'せんせい、さようなら', 
                'english' => 'Goodbye!',
                'chinese' => '再见！',
                'tagalog' => 'paalam po, guro!'
            ]
        ]
    ],
    2 => [
        'title' => 'あなたのなまえは？', 
        'description' => '名前を伝えたり相手の名前を聞いたりできるようになります',
        'sub_lessons' => [
            1 => [
                'title' => 'わたしは' . $user['child_name'] . 'です。',
                'description' => '自分の名前を紹介する',
                'japanese' => 'わたしは' . $user['child_name'] . 'です。',
                'english' => 'My name is ' . $user['child_name'] . '.',
                'chinese' => '我叫' . $user['child_name'] . '。',
                'tagalog' => 'Ako si ' . $user['child_name'] . '.'
            ],
            2 => [
                'title' => 'あなたのなまえはなんですか？',
                'description' => '相手の名前を聞く', 
                'japanese' => 'あなたのなまえはなんですか？',
                'english' => 'What\'s your name?',
                'chinese' => '你叫什么名字？',
                'tagalog' => 'Ano ang pangalan mo?'
            ],
            3 => [
                'title' => 'よろしくおねがいします。',
                'description' => '初めて会った人への挨拶',
                'japanese' => 'よろしくおねがいします。', 
                'english' => 'Nice to meet you!',
                'chinese' => '请多多关照！',
                'tagalog' => 'Ikinagagalak kong makilala ka.'
            ]
        ]
    ],
    3 => [
        'title' => '自己紹介', 
        'description' => '出身地、年齢、誕生日について話せるようになります',
        'sub_lessons' => [
            1 => [
                'title' => 'どこからきましたか？<br>わたしは〇〇からきました。',
                'description' => '出身地を聞いて答える',
                'japanese' => 'どこからきましたか？<br>わたしは〇〇からきました。',
                'english' => 'Where are you from?<br>I am from __（country name）__.',
                'chinese' => '你来自哪里？<br>我来自__（国名）__',
                'tagalog' => 'Saan ka galing?<br>Galing ako sa __（bansa）__.'
            ],
            2 => [
                'title' => 'なんさいですか？<br>わたしは〇〇さいです。',
                'description' => '年齢を聞いて答える', 
                'japanese' => 'なんさいですか？<br>わたしは〇〇さいです。',
                'english' => 'How old are you?<br>I am __（age）__ years old.',
                'chinese' => '你几岁？<br>我__（年龄）__岁',
                'tagalog' => 'Ilang taon ka na?<br>__（edad）__ taon na ako.'
            ],
            3 => [
                'title' => 'たんじょうびはいつですか？<br>わたしのたんじょうびは〇〇がつ〇〇にちです。',
                'description' => '誕生日を聞いて答える',
                'japanese' => 'たんじょうびはいつですか？<br>わたしのたんじょうびは〇〇がつ〇〇にちです。', 
                'english' => 'When is your birthday?<br>My birthday is __month____day__.',
                'chinese' => '你的生日是什么时候？<br>我的生日是__月__日'
            ]
        ]
    ]
    /*
    4 => [
        'title' => '数字', 
        'description' => '1から10までの数字を覚えます',
        'contents' => [
            'miru' => '1〜5',
            'yatte' => '6〜10',
            'dekita' => '数を数える'
        ]
    ],
    5 => [
        'title' => 'ひらがな', 
        'description' => 'ひらがなの読み方を学びます',
        'contents' => [
            'miru' => 'あ行',
            'yatte' => 'か行',
            'dekita' => 'さ行'
        ]
    ],
    6 => [
        'title' => '時計', 
        'description' => '時間の読み方を覚えます',
        'contents' => [
            'miru' => '○時',
            'yatte' => '○時半',
            'dekita' => '時間を聞く'
        ]
    ],
    7 => [
        'title' => '学用品', 
        'description' => '学校で使う物の名前を学びます',
        'contents' => [
            'miru' => 'えんぴつ・けしゴム',
            'yatte' => 'ノート・本',
            'dekita' => 'かばん・ふでばこ'
        ]
    ],
    8 => [
        'title' => '色', 
        'description' => '基本的な色の名前を学びます',
        'contents' => [
            'miru' => 'あか・あお・きいろ',
            'yatte' => 'みどり・しろ・くろ',
            'dekita' => '好きな色を言う'
        ]
    ],
    9 => [
        'title' => '曜日', 
        'description' => '曜日の言い方を学びます',
        'contents' => [
            'miru' => '月・火・水',
            'yatte' => '木・金・土・日',
            'dekita' => '今日は何曜日'
        ]
    ],
    10 => [
        'title' => '天気', 
        'description' => '天気について話します',
        'contents' => [
            'miru' => 'はれ・くもり',
            'yatte' => 'あめ・ゆき',
            'dekita' => '今日の天気'
        ]
    ],
    11 => [
        'title' => '学校生活1', 
        'description' => '学校での基本的な表現',
        'contents' => [
            'miru' => '教室',
            'yatte' => '授業',
            'dekita' => '休み時間'
        ]
    ],
    12 => [
        'title' => '学校生活2', 
        'description' => '学校生活の応用表現',
        'contents' => [
            'miru' => '給食',
            'yatte' => '掃除',
            'dekita' => '帰りの会'
        ]
    ],
    13 => [
        'title' => '買い物', 
        'description' => 'お店で使う表現を覚えます',
        'contents' => [
            'miru' => 'いらっしゃいませ',
            'yatte' => 'これください',
            'dekita' => 'ありがとうございました'
        ]
    ],
    14 => [
        'title' => '季節', 
        'description' => '四季について話します',
        'contents' => [
            'miru' => '春・夏',
            'yatte' => '秋・冬',
            'dekita' => '好きな季節'
        ]
    ],
    15 => [
        'title' => '食事', 
        'description' => '食べ物や食事について',
        'contents' => [
            'miru' => 'いただきます',
            'yatte' => 'おいしい',
            'dekita' => 'ごちそうさま'
        ]
    ],
    16 => [
        'title' => '健康', 
        'description' => '体調や健康について',
        'contents' => [
            'miru' => '元気',
            'yatte' => '疲れた',
            'dekita' => 'お大事に'
        ]
    ],
    17 => [
        'title' => '地域', 
        'description' => '住んでいる場所について',
        'contents' => [
            'miru' => '家',
            'yatte' => '学校',
            'dekita' => '公園'
        ]
    ],
    18 => [
        'title' => '家族', 
        'description' => '家族の呼び方を学びます',
        'contents' => [
            'miru' => 'お父さん・お母さん',
            'yatte' => 'お兄さん・お姉さん',
            'dekita' => '家族紹介'
        ]
    ],
    19 => [
        'title' => '趣味', 
        'description' => '好きなことについて話します',
        'contents' => [
            'miru' => '好き',
            'yatte' => '嫌い',
            'dekita' => '趣味を聞く'
        ]
    ],
    20 => [
        'title' => '総復習', 
        'description' => '全体の復習をします',
        'contents' => [
            'miru' => '復習1',
            'yatte' => '復習2',
            'dekita' => '総まとめ'
        ]
    ]
    */
];

// 統計データの計算
$total_sessions = count($user_progress);
$total_badges = ($target_user_id !== null) ? getUserBadgeCount($target_user_id) : count($user_badges);

// バッジベースの進捗計算
$completed_lessons = [];
$lessons_by_badge = [];

// バッジから進捗を計算
foreach ($user_badges as $badge) {
    $lesson_id = $badge['lesson_id'];
    // sub_lesson_idを直接使用
    $sub_lesson_id = $badge['sub_lesson_id'] ?? null;
    
    if (!isset($lessons_by_badge[$lesson_id])) {
        $lessons_by_badge[$lesson_id] = [];
    }
    
    // 有効な値のみ追加
    if ($sub_lesson_id !== null && $sub_lesson_id !== '' && $sub_lesson_id !== 0) {
        $lessons_by_badge[$lesson_id][] = $sub_lesson_id;
    }
}

// 完了したレッスンを計算（3つのバッジが揃ったレッスン）
foreach ($lessons_by_badge as $lesson_id => $badges) {
    $unique_badges = array_unique($badges);
    if (count($unique_badges) >= 3) {
        $completed_lessons[] = $lesson_id;
    }
}

// 次に進むべきサブレッスンを計算する関数
function getNextSubLesson($lesson_id, $badges_by_lesson) {
    $completed_sub_lessons = $badges_by_lesson[$lesson_id] ?? [];
    $completed_sub_lessons = array_unique($completed_sub_lessons);
    sort($completed_sub_lessons);
    
    // 1, 2, 3の順番で次に進むべきサブレッスンを決定
    for ($sub = 1; $sub <= 3; $sub++) {
        if (!in_array($sub, $completed_sub_lessons)) {
            return $sub;
        }
    }
    return 1; // 全て完了している場合は1を返す
}

$completed_count = count($completed_lessons);

// デバッグ用（本番では削除）
// echo '<pre>'; 
// echo 'User ID: ' . $user['id'] . "\n";
// echo 'User email: ' . $user['email'] . "\n";
// echo 'User role: ' . ($user['role'] ?? 'not set') . "\n";
// echo 'Is admin: ' . ($is_admin ? 'true' : 'false') . "\n";
// echo 'Total badges count: ' . count($user_badges) . "\n";

// echo "\n=== BADGES DETAIL ===\n";
// foreach ($user_badges as $badge) {
//     echo "Badge ID: " . $badge['id'] . ", Lesson: " . $badge['lesson_id'] . ", Sub: " . $badge['sub_lesson_id'] . "\n";
// }

// echo "\n=== LESSONS BY BADGE ===\n";
// foreach ($lessons_by_badge as $lesson_id => $badges) {
//     echo "Lesson $lesson_id: [" . implode(', ', $badges) . "] - Unique: [" . implode(', ', array_unique($badges)) . "] - Count: " . count(array_unique($badges)) . "\n";
// }

// echo "\n=== COMPLETED LESSONS ===\n";
// print_r($completed_lessons);
// echo 'Completed count: ' . $completed_count . "\n";
// echo '</pre>';

// 学習時間の計算（簡易版 - 1セッション約5分と仮定）
$total_study_time = $total_sessions * 5;

// 週間学習データ（過去7日間）
$weekly_progress = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $date_sessions = 0;
    
    foreach ($user_progress as $progress) {
        $progress_date = date('Y-m-d', strtotime($progress['created_at']));
        if ($progress_date === $date) {
            $date_sessions++;
        }
    }
    
    $weekly_progress[] = [
        'date' => $date,
        'day' => date('j', strtotime($date)),
        'sessions' => $date_sessions
    ];
}

// 完了したサブレッスンを時系列順に取得
$recent_activities = [];
foreach ($user_badges as $badge) {
    $lesson_id = $badge['lesson_id'];
    $sub_lesson_id = $badge['sub_lesson_id'] ?? null;
    
    // sub_lesson_idが有効な場合のみ追加
    if ($sub_lesson_id !== null && $sub_lesson_id !== '' && $sub_lesson_id !== 0) {
        // ステップ形式に変換
        $step_mapping = [1 => 'sub_lesson_1', 2 => 'sub_lesson_2', 3 => 'sub_lesson_3'];
        $step = $step_mapping[$sub_lesson_id] ?? 'sub_lesson_1';
        
        $recent_activities[] = [
            'lesson_id' => $lesson_id,
            'step' => $step,
            'created_at' => $badge['created_at']
        ];
    }
}

// 作成日時で降順ソート（新しいものから）
usort($recent_activities, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// 上位10件に制限
$recent_activities = array_slice($recent_activities, 0, 10);

// 学習ストリーク計算
$streak = 0;
$current_date = date('Y-m-d');
$check_date = $current_date;

while (true) {
    $has_activity = false;
    foreach ($user_progress as $progress) {
        $progress_date = date('Y-m-d', strtotime($progress['created_at']));
        if ($progress_date === $check_date) {
            $has_activity = true;
            break;
        }
    }
    
    if ($has_activity) {
        $streak++;
        $check_date = date('Y-m-d', strtotime($check_date . ' -1 day'));
    } else {
        break;
    }
    
    // 最大30日まで
    if ($streak >= 30) break;
}

$progress_percentage = round(($completed_count / 20) * 100, 1);
?>

<div class="progress-container <?= $color_scheme ?>-theme">
    <div class="progress-header">
        <?php if ($is_admin_viewing): ?>
            <div class="admin-nav">
                <a href="../admin/users.php" class="btn btn-back">← ユーザー管理に戻る</a>
            </div>
        <?php endif; ?>
        <h1 class="progress-title"><?= h(getTranslation('growth_meter', $current_language, $translations)) ?></h1>
        <p class="progress-subtitle">
            <?php if ($is_admin_viewing): ?>
                <?= h($user['child_name']) ?>さんの学習記録
            <?php elseif ($is_admin && !$is_admin_viewing): ?>
                <?= $current_language === 'ja' ? '全体の学習統計' : ($current_language === 'en' ? 'Overall Learning Statistics' : '整体学习统计') ?>
            <?php else: ?>
                <?= sprintf(h(getTranslation('encouragement_message', $current_language, $translations)), h($user['child_name'])) ?>
            <?php endif; ?>
        </p>
    </div>

    <!-- 統計サマリー -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><img src="../assets/images/icons/owatta.png" alt="おわったレッスン" class="icon-img"></div>
            <div class="stat-content">
                <div class="stat-number"><?= $completed_count ?></div>
                <div class="stat-label"><?= h(getTranslation('lessons_finished', $current_language, $translations)) ?></div>
                <div class="stat-detail"><?= sprintf(h(getTranslation('lessons_finished_detail', $current_language, $translations)), $progress_percentage) ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><img src="../assets/images/icons/morattabadge.png" alt="もらったバッジ" class="icon-img"></div>
            <div class="stat-content">
                <div class="stat-number"><?= $total_badges ?></div>
                <div class="stat-label"><?= h(getTranslation('trophies_got', $current_language, $translations)) ?></div>
                <div class="stat-detail"><?= sprintf(h(getTranslation('trophies_detail', $current_language, $translations)), 20 * 3) ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><img src="../assets/images/icons/yattajikan.png" alt="やったじかん" class="icon-img"></div>
            <div class="stat-content">
                <div class="stat-number"><?= $total_study_time ?></div>
                <div class="stat-label"><?= h(getTranslation('time_spent', $current_language, $translations)) ?></div>
                <div class="stat-detail"><?= sprintf(h(getTranslation('time_spent_detail', $current_language, $translations)), round($total_study_time / 60, 1)) ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><img src="../assets/images/icons/tuzuketa.png" alt="つづけたひにち" class="icon-img"></div>
            <div class="stat-content">
                <div class="stat-number"><?= $streak ?></div>
                <div class="stat-label"><?= h(getTranslation('streak', $current_language, $translations)) ?></div>
                <div class="stat-detail"><?= $current_language === 'ja' ? $streak . 'にちつづけている' : sprintf(h(getTranslation('streak_detail', $current_language, $translations)), $streak) ?></div>
            </div>
        </div>
    </div>

    <!-- 週間アクティビティ -->
    <div class="section-card">
        <h2 class="section-title"><img src="../assets/images/icons/nannichi.png" alt="なんにちできたかな" class="section-icon"> <?= h(getTranslation('how_many_days', $current_language, $translations)) ?></h2>
        <div class="weekly-chart">
            <?php foreach ($weekly_progress as $day): ?>
                <div class="day-column">
                    <div class="day-label"><?= date('n/j', strtotime($day['date'])) ?></div>
                    <div class="activity-circle">
                        <span class="activity-number"><?= $day['sessions'] ?></span>
                    </div>
                    <div class="lesson-label"><?= h(getTranslation('lessons', $current_language, $translations)) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chart-legend">
            <span><?= h(getTranslation('weekly_study_count', $current_language, $translations)) ?></span>
        </div>
    </div>

    <div class="content-grid">
        <!-- レッスン進捗 -->
        <div class="section-card">
            <h2 class="section-title"><img src="../assets/images/icons/oboeta.png" alt="おぼえたところ" class="section-icon"> <?= h(getTranslation('things_learned', $current_language, $translations)) ?></h2>
            <div class="lesson-progress-list">
                <?php foreach ($lessons as $lesson_id => $lesson): ?>
                    <?php
                    // バッジベースで進捗を計算
                    $lesson_badges = $lessons_by_badge[$lesson_id] ?? [];
                    $completed_sub_lessons = array_unique($lesson_badges);
                    $completed_steps = count($completed_sub_lessons);
                    
                    $lesson_percentage = ($completed_steps / 3) * 100;
                    $status = $completed_steps == 3 ? 'completed' : ($completed_steps > 0 ? 'in-progress' : 'not-started');
                    
                    // 次に進むべきサブレッスンを取得
                    $next_sub_lesson = getNextSubLesson($lesson_id, $lessons_by_badge);
                    ?>
                    
                    <div class="lesson-progress-item <?= $status ?>">
                        <div class="lesson-info">
                            <div class="lesson-number">L<?= $lesson_id ?></div>
                            <div class="lesson-details">
<?php 
                                $translated_title = $lesson_titles[$current_language][$lesson_id] ?? $lesson['title'];
                                ?>
                                <div class="lesson-name"><?= h($translated_title) ?></div>
                                <div class="lesson-desc"><?= h($lesson['title']) ?></div>
                                <div class="progress-section">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $lesson_percentage ?>%"></div>
                                    </div>
                                    <div class="step-indicators-row">
                                        <div class="step-indicators">
                                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                                <div class="step-segment <?= in_array($i, $completed_sub_lessons) ? 'completed' : '' ?>">
                                                    <?= $i ?>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="progress-text"><?= $completed_steps === 0 ? '0/3' : ($completed_steps === 1 ? '1/3' : ($completed_steps === 2 ? '2/3' : '3/3')) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="lesson-actions">
                            <?php if ($completed_steps === 3): ?>
                                <span class="status-badge completed">✅ <?= h(getTranslation('completed_status', $current_language, $translations)) ?></span>
                            <?php elseif ($completed_steps > 0): ?>
                                <a href="../lessons/lesson.php?id=<?= $lesson_id ?>&sub=<?= $next_sub_lesson ?>" class="continue-link"><?= h(getTranslation('continue_link', $current_language, $translations)) ?></a>
                            <?php elseif ($lesson_id === 1 || in_array($lesson_id - 1, $completed_lessons)): ?>
                                <a href="../lessons/lesson.php?id=<?= $lesson_id ?>&sub=1" class="start-link"><?= h(getTranslation('start_link', $current_language, $translations)) ?></a>
                            <?php else: ?>
                                <span class="status-badge locked">🔒</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 最近の活動 -->
        <div class="section-card">
            <h2 class="section-title"><img src="../assets/images/icons/kyouyatta.png" alt="きょうやったところ" class="section-icon"> <?= h(getTranslation('what_did_today', $current_language, $translations)) ?></h2>
            <div class="activity-list" id="activity-list">
                <!-- ゲームの得点表示 - 1行で2つ並べる -->
                <div class="game-activities-row">
                    <div class="activity-item game-activity half-width">
                        <div class="activity-icon">🎮</div>
                        <div class="activity-content">
                            <div class="activity-title"><?= h(getTranslation('janken_game', $current_language, $translations)) ?></div>
                            <div class="activity-score">
                                <span class="score-label"><?= h(getTranslation('score_label', $current_language, $translations)) ?></span>
                                <span id="janken_display_score" class="score-value">0</span>
                                <span class="score-unit"><?= h(getTranslation('score_unit', $current_language, $translations)) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="activity-item game-activity half-width">
                        <div class="activity-icon">🎯</div>
                        <div class="activity-content">
                            <div class="activity-title"><?= h(getTranslation('kana_card_game', $current_language, $translations)) ?></div>
                            <div class="activity-score">
                                <span class="score-label"><?= h(getTranslation('score_label', $current_language, $translations)) ?></span>
                                <span id="kanacard_display_score" class="score-value">0</span>
                                <span class="score-unit"><?= h(getTranslation('score_unit', $current_language, $translations)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <?php
                        $lesson_data = $lessons[$activity['lesson_id']] ?? [];
                        $lesson_title = $lesson_titles[$current_language][$activity['lesson_id']] ?? $lesson_data['title'] ?? "レッスン{$activity['lesson_id']}";
                        
                        // ステップからサブレッスンIDを推定
                        $step_to_sub_id = [
                            'miru' => 1,
                            'yatte' => 2, 
                            'dekita' => 3,
                            'sub_lesson_1' => 1,
                            'sub_lesson_2' => 2,
                            'sub_lesson_3' => 3
                        ];
                        $sub_lesson_id = $step_to_sub_id[$activity['step']] ?? 1;
                        
                        // サブレッスンの詳細情報を取得
                        $sub_lesson = $lesson_data['sub_lessons'][$sub_lesson_id] ?? null;
                        $sub_lesson_title = '';
                        
                        if ($sub_lesson) {
                            if ($current_language === 'en') {
                                $sub_lesson_title = $sub_lesson['english'] ?? $sub_lesson['japanese'];
                            } elseif ($current_language === 'zh') {
                                $sub_lesson_title = $sub_lesson['chinese'] ?? $sub_lesson['japanese']; 
                            } elseif ($current_language === 'tl') {
                                $sub_lesson_title = $sub_lesson['tagalog'] ?? $sub_lesson['japanese'];
                            } else {
                                $sub_lesson_title = $sub_lesson['japanese'];
                            }
                        } else {
                            // フォールバック：古い形式のcontentsを使用
                            $sub_lesson_title = $lesson_data['contents'][$activity['step']] ?? '';
                        }
                        
                        // アイコンをレッスン番号に変更
                        $step_icon = "L{$activity['lesson_id']}-{$sub_lesson_id}";
                        ?>
                        
                        <div class="activity-item">
                            <div class="activity-icon"><?= $step_icon ?></div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    L<?= $activity['lesson_id'] ?>-<?= $sub_lesson_id ?> <?= h($lesson_title) ?>
                                </div>
                                <?php if (!empty($sub_lesson_title)): ?>
                                <div class="activity-subtitle">
                                    <?= h($sub_lesson_title) ?>
                                </div>
                                <?php endif; ?>
                                <div class="activity-time">
                                    <?= date('n月j日 H:i', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (empty($recent_activities)): ?>
                <div class="empty-lesson-state">
                    <div class="empty-icon">📚</div>
                    <?php if ($current_language === 'tl'): ?>
                        <p>Wala pang pag-aaral na nagawa.<br>Simulan ang unang aralin!</p>
                        <a href="../lessons/curriculum.php" class="btn btn-primary">Simulan ang mga Aralin</a>
                    <?php elseif ($current_language === 'en'): ?>
                        <p>No lessons completed yet.<br>Let's start with the first lesson!</p>
                        <a href="../lessons/curriculum.php" class="btn btn-primary">Start Lessons</a>
                    <?php elseif ($current_language === 'zh'): ?>
                        <p>还没有完成任何课程。<br>让我们从第一课开始吧！</p>
                        <a href="../lessons/curriculum.php" class="btn btn-primary">开始课程</a>
                    <?php else: ?>
                        <p>まだべんきょうしたことがないです。<br>さいしょのレッスンからはじめてみよう！</p>
                        <a href="../lessons/curriculum.php" class="btn btn-primary">レッスンをはじめる</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
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

/* ベーステーマ */
.progress-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    position: relative;
}

/* 男の子用ブルーテーマ */
.blue-theme {
    --theme-primary: #4a90e2;
    --theme-primary-dark: #357abd;
    --theme-primary-light: #e3f2fd;
    --theme-accent: #42a5f5;
    --theme-highlight: #1976d2;
}

.blue-theme .stat-card.highlight {
    background: linear-gradient(135deg, #4a90e2, #42a5f5);
}

.blue-theme .activity-bar {
    background: #4a90e2;
}

.blue-theme .activity-count {
    background: #42a5f5;
}

.blue-theme .start-link, .blue-theme .continue-link {
    background: #4a90e2;
}

.blue-theme .start-link:hover, .blue-theme .continue-link:hover {
    background: #357abd;
}

.blue-theme .activity-item.game-activity {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-left: 5px solid #4a90e2;
}

.blue-theme .lesson-progress-item.completed {
    border-color: #4a90e2;
}

.blue-theme .lesson-progress-item.in-progress {
    border-color: #42a5f5;
}

.blue-theme .lesson-number {
    background: #4a90e2;
}

.blue-theme .progress-fill {
    background: linear-gradient(90deg, #4a90e2, #42a5f5);
}

.blue-theme .score-value, .blue-theme .activity-score .score-value {
    color: #4a90e2;
}

.blue-theme .step-dot.completed {
    background: #4a90e2;
    color: white;
}

/* 女の子用ピンクテーマ */
.pink-theme {
    --theme-primary: #e91e63 !important;
    --theme-primary-dark: #c2185b !important;
    --theme-primary-light: #fce4ec !important;
    --theme-accent: #f48fb1 !important;
    --theme-highlight: #ad1457 !important;
    --primary-color: #e91e63 !important;
    --primary-dark: #c2185b !important;
    --primary-light: #fce4ec !important;
    --accent-color: #f48fb1 !important;
    --secondary-color: #f48fb1 !important;
    --card-background: white !important;
    --background: white !important;
}

.pink-theme .stat-card.highlight {
    background: linear-gradient(135deg, #e91e63, #f48fb1);
}

.pink-theme .activity-bar {
    background: #e91e63;
}

.pink-theme .activity-count {
    background: #f48fb1;
}

.pink-theme .start-link, .pink-theme .continue-link {
    background: #e91e63;
}

.pink-theme .start-link:hover, .pink-theme .continue-link:hover {
    background: #c2185b;
}

.pink-theme .activity-item.game-activity {
    background: linear-gradient(135deg, #fce4ec, #f8bbd9);
    border-left: 5px solid #e91e63;
}

.pink-theme .lesson-progress-item.completed {
    border-color: #e91e63;
}

.pink-theme .lesson-progress-item.in-progress {
    border-color: #f48fb1;
}

.pink-theme .lesson-number {
    background: #e91e63;
}

.pink-theme .progress-fill {
    background: linear-gradient(90deg, #e91e63, #f48fb1);
}

.pink-theme .score-value, .pink-theme .activity-score .score-value {
    color: #e91e63;
}

.pink-theme .step-dot.completed {
    background: #e91e63;
    color: white;
}

/* ピンクテーマの追加強制スタイル */
.pink-theme .progress-title {
    color: #c2185b !important;
}

.pink-theme .section-title {
    color: #c2185b !important;
    border-bottom-color: #fce4ec !important;
}

.pink-theme .stat-number {
    color: #c2185b !important;
}

.pink-theme .activity-title {
    color: #c2185b !important;
}

.pink-theme .lesson-name {
    color: #c2185b !important;
}

.pink-theme .day-label {
    color: #c2185b !important;
}

.pink-theme .progress-text {
    color: #c2185b !important;
}

.pink-theme .section-card {
    border-color: #fce4ec !important;
}

.pink-theme .stat-card {
    border-color: #fce4ec !important;
}

/* レッスンボックスの背景色 - ブルーテーマ */
.blue-theme .lesson-info .lesson-details .lesson-name {
    color: #357abd;
}

.blue-theme .lesson-progress-item {
    background: linear-gradient(135deg, #f3f8ff, #e8f2ff);
}

.blue-theme .lesson-progress-item.completed {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-color: #4a90e2;
}

.blue-theme .lesson-progress-item.in-progress {
    background: linear-gradient(135deg, #e8f4fd, #d1ecf1);
    border-color: #42a5f5;
}

/* レッスンボックスの背景色 - ピンクテーマ */
.pink-theme .lesson-info .lesson-details .lesson-name {
    color: #c2185b;
}

.pink-theme .lesson-progress-item {
    background: linear-gradient(135deg, #fff0f5, #ffe4ec);
}

.pink-theme .lesson-progress-item.completed {
    background: linear-gradient(135deg, #fce4ec, #f8bbd9);
    border-color: #e91e63;
}

.pink-theme .lesson-progress-item.in-progress {
    background: linear-gradient(135deg, #ffeef3, #ffcdd2);
    border-color: #f48fb1;
}

.progress-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    position: relative;
}

.progress-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 20px;
    color: var(--primary-dark);
}

.progress-title {
    font-size: 3em;
    color: var(--primary-dark);
    margin-bottom: 15px;
    font-weight: 700;
}

.progress-subtitle {
    font-size: 1.3em;
    color: #666;
    margin-bottom: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    border: 3px solid var(--primary-light);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

.stat-card.highlight {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
}

.stat-icon {
    font-size: 3.5em;
    opacity: 0.8;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}

.section-icon {
    width: 45px;
    height: 45px;
    object-fit: contain;
    margin-right: 10px;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: var(--primary-dark);
    line-height: 1;
    margin-bottom: 5px;
}

.stat-card.highlight .stat-number {
    color: white;
}

.stat-label {
    font-size: 1.1em;
    color: #666;
    margin-bottom: 5px;
    font-weight: 600;
}

.stat-card.highlight .stat-label {
    color: rgba(255, 255, 255, 0.9);
}

.stat-detail {
    font-size: 0.9em;
    color: #999;
}

.stat-card.highlight .stat-detail {
    color: rgba(255, 255, 255, 0.7);
}

.section-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    margin-bottom: 30px;
    border: 2px solid var(--primary-light);
}

.section-title {
    font-size: 1.8em;
    color: var(--primary-dark);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 3px solid var(--primary-light);
    padding-bottom: 15px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.weekly-chart {
    display: flex;
    justify-content: space-around;
    align-items: center;
    height: 150px;
    margin: 20px 0;
    background: var(--background);
    padding: 20px;
    border-radius: 15px;
}

.day-column {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 5px;
}

.blue-theme .activity-circle {
    background: #1976d2; /* スカイブルー（男の子） */
}

.pink-theme .activity-circle {
    background: #e91e63; /* メインピンク（女の子） */
}

.activity-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.activity-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.activity-number {
    color: white;
    font-weight: bold;
    font-size: 1.2em;
}

.lesson-label {
    font-size: 0.8em;
    color: #666;
    margin-top: 2px;
}

.activity-bar {
    width: 100%;
    max-width: 40px;
    background: var(--primary-color);
    border-radius: 8px 8px 0 0;
    position: relative;
    min-height: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.activity-count {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--accent-color);
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.day-label {
    margin-top: 10px;
    font-weight: 600;
    color: var(--primary-dark);
}

.chart-legend {
    text-align: center;
    margin-top: 15px;
    color: #666;
    font-size: 0.9em;
}

.lesson-progress-list {
    display: grid;
    gap: 15px;
}

.lesson-progress-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: var(--background);
    border-radius: 15px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
}

.progress-section {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.step-indicators-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.step-indicators {
    display: flex;
    width: 200px;
    height: 32px;
    border-radius: 16px;
    overflow: hidden;
    background: #f0f0f0;
    flex-shrink: 0;
}

.step-segment {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    transition: all 0.3s ease;
    position: relative;
}

/* 統一スタイル：完了したステップのみ濃い色 */
.step-segment {
    background: var(--primary-light); /* 薄い色（未完了） */
}

.step-segment.completed {
    background: var(--primary-color); /* はじめるボタンと同じ色（完了） */
    color: white;
}

.progress-text {
    font-weight: bold;
    color: var(--primary-dark);
    font-size: 0.9em;
    min-width: 40px;
    text-align: center;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    transition: width 0.3s ease;
    border-radius: 4px;
}

.lesson-actions {
    min-width: 100px;
    display: flex;
    justify-content: center;
}

.lesson-progress-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.lesson-progress-item.completed {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, var(--background), var(--card-background));
}

.lesson-progress-item.in-progress {
    border-color: var(--accent-color);
    background: linear-gradient(135deg, var(--background), var(--card-background));
}

.lesson-info {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.lesson-number {
    background: var(--primary-color);
    color: white;
    padding: 10px 15px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 1.1em;
    min-width: 50px;
    text-align: center;
}

.lesson-details h4 {
    margin: 0 0 8px 0;
    color: var(--primary-dark);
    font-size: 1.2em;
}

.lesson-details p {
    margin: 0;
    color: #666;
    font-size: 0.95em;
}

.lesson-progress-bar {
    flex: 1;
    height: 12px;
    background: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    margin: 0 15px;
}

.lesson-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    transition: width 0.3s ease;
    border-radius: 6px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.completed {
    background: var(--primary-color);
    color: white;
}

.status-badge.in-progress {
    background: var(--accent-color);
    color: white;
}

.status-badge.locked {
    background: #ccc;
    color: #666;
}

.start-link, .continue-link {
    background: var(--primary-color);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: bold;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    min-width: 80px;
    text-align: center;
    display: inline-block;
}

.start-link:hover, .continue-link:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.status-badge.locked {
    background: #ccc;
    color: #666;
    min-width: 80px;
    text-align: center;
    display: inline-block;
}

.activity-list {
    display: grid;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: var(--background);
    border-radius: 12px;
    border-left: 5px solid var(--accent-color);
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.activity-icon {
    font-size: 0.8em;
    font-weight: bold;
    background: white;
    color: var(--primary-dark);
    border: 2px solid var(--primary-light);
    padding: 8px 10px;
    border-radius: 15px;
    min-width: 50px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 3px;
    font-size: 1.1em;
}

.activity-subtitle {
    font-size: 0.95em;
    color: #555;
    margin-bottom: 5px;
    line-height: 1.3;
    font-weight: 500;
}

.activity-time {
    font-size: 0.9em;
    color: #666;
    opacity: 0.8;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .progress-container {
        padding: 15px;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .progress-title {
        font-size: 2em;
    }
    
    .weekly-chart {
        height: 120px;
    }
}

/* レッスン得点表示 */
.lesson-score {
    background: rgba(108, 117, 125, 0.1);
    padding: 8px 12px;
    border-radius: 10px;
    margin: 10px 0;
    border: 2px solid #e9ecef;
    min-width: 120px;
    text-align: center;
}

/* ゲーム活動行 */
.game-activities-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

/* サブレッスン詳細表示スタイル */
.sub-lessons-detail {
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
}

.sub-lesson-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 8px 0;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sub-lesson-item.completed {
    background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
    border-left: 3px solid #28a745;
}

.sub-number {
    background: var(--secondary-color);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9em;
    flex-shrink: 0;
}

.sub-lesson-item.completed .sub-number {
    background: #28a745;
}

.sub-content {
    flex: 1;
}

.sub-text {
    font-size: 0.95em;
    color: #333;
    line-height: 1.4;
}

.sub-text-japanese {
    font-size: 0.8em;
    color: #666;
    margin-bottom: 3px;
    font-weight: 500;
}

.sub-text-main {
    font-size: 0.95em;
    color: #333;
    line-height: 1.4;
    font-weight: normal;
}

/* ゲーム活動の得点表示 */
.activity-item.game-activity {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-left: 5px solid var(--primary-color);
}

.activity-item.game-activity.half-width {
    flex: 1;
    margin-bottom: 0;
}

.activity-score {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
}

.activity-score .score-label {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9em;
}

.activity-score .score-value {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.3em;
}

.activity-score .score-unit {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9em;
}

.lesson-score .score-label {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.8em;
}

.lesson-score .score-value {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.2em;
    margin: 0 3px;
}

.lesson-score .score-unit {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.8em;
}

/* 管理者ナビゲーション */
.admin-nav {
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #545b62;
    transform: translateY(-1px);
}

.btn-back {
    background: #6c757d;
}

.btn-back:hover {
    background: #545b62;
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .lesson-progress-item {
        flex-direction: column;
        text-align: center;
    }
    
    .lesson-progress-bar {
        order: 3;
        margin: 15px 0 0 0;
        width: 100%;
    }
}
</style>

<script>
// レッスンの得点をローカルストレージから読み込んで表示
function loadLessonScores() {
    for (let i = 1; i <= 20; i++) {
        const lessonScore = localStorage.getItem(`lesson_${i}_score`) || '0';
        const lessonElement = document.getElementById(`lesson_${i}_score`);
        
        if (lessonElement) {
            lessonElement.textContent = lessonScore;
        }
    }
}

// ゲームの得点をローカルストレージから読み込んで表示
function loadGameScores() {
    const jankenScore = localStorage.getItem('janken_score') || '0';
    const kanacardScore = localStorage.getItem('kanacard_score') || '0';
    
    const jankenElement = document.getElementById('janken_display_score');
    const kanacardElement = document.getElementById('kanacard_display_score');
    
    if (jankenElement) {
        jankenElement.textContent = jankenScore;
    }
    
    if (kanacardElement) {
        kanacardElement.textContent = kanacardScore;
    }
}

// ページ読み込み時に得点を表示
document.addEventListener('DOMContentLoaded', function() {
    loadLessonScores();
    loadGameScores();
    
    // 定期的に得点を更新（他のタブで学習/ゲームした場合に対応）
    setInterval(function() {
        loadLessonScores();
        loadGameScores();
    }, 5000);
});
</script>

<?php require_once '../includes/footer.php'; ?>


