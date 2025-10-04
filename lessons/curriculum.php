<?php
// lessons/curriculum.php - シンプル版（動作確認済み）
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ファイル読み込み
try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
    require_once '../includes/GeminiTranslator.php';
} catch (Exception $e) {
    die('ファイル読み込みエラー: ' . $e->getMessage());
}

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['user'];
$translator = new GeminiTranslator();

// 言語設定（他のページと統一）
$current_language = $_GET['lang'] ?? $_SESSION['language'] ?? $user['native_language'] ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'ko', 'vi', 'tl', 'ne', 'pt'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// 多言語テキスト定義
$texts = [
    'ja' => [
        'page_title' => 'このアプリでまなべること',
        'page_subtitle' => 'にほんごをたのしくまなびましょう！',
        'select_lesson' => 'レッスンを選んでください',
        'hover_description' => '右側のレッスンにカーソルを合わせると詳細が表示されます',
        'start_button' => 'スタート',
        'locked' => 'ロック中'
    ],
    'en' => [
        'page_title' => 'What You\'ll Learn in This App',
        'page_subtitle' => 'Let\'s Enjoy Learning Japanese!',
        'select_lesson' => 'Please select a lesson',
        'hover_description' => 'Hover over lessons on the right to see details',
        'start_button' => 'Start',
        'locked' => 'Locked'
    ],
    'zh' => [
        'page_title' => '通过此应用程序可学习的内容',
        'page_subtitle' => '快乐学习日文吧！',
        'select_lesson' => '请选择课程',
        'hover_description' => '将鼠标悬停在右侧课程上可查看详细信息',
        'start_button' => '开始',
        'locked' => '锁定'
    ],
    'tl' => [
        'page_title' => 'Ano ang Matututuhan Mo Dito',
        'page_subtitle' => 'Tara, Mag-enjoy sa Pag-aaral ng Nihongo!',
        'select_lesson' => 'Pumili ng Aralin',
        'hover_description' => 'I-hover ang mga aralin sa kanan para makita ang detalye',
        'start_button' => 'Simulan',
        'locked' => 'Nakakandado'
    ]
];

// レッスンタイトルの多言語対応
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

// 現在の言語のテキストを取得
$t = $texts[$current_language] ?? $texts['ja'];

// 性別に応じた色設定
$gender = $user['child_gender'] ?? 'boy';
$color_scheme = $gender === 'girl' ? 'pink' : 'blue';

// 進捗データの取得（エラーハンドリング付き）
try {
    $user_progress = getUserProgress($user['id']);
    $user_badges = getUserBadges($user['id']);
} catch (Exception $e) {
    $user_progress = [];
    $user_badges = [];
}

// 翻訳関数（一時的に無効化）
function translateIfNeeded($text, $targetLanguage = null, $translator = null) {
    // 未使用変数の警告を回避
    unset($targetLanguage, $translator);
    return $text; // 翻訳を無効にして高速化
}

// 進捗状況を整理
$progress_by_lesson = [];
$badges_by_lesson = [];

foreach ($user_progress as $progress) {
    $lesson_id = $progress['lesson_id'];
    if (!isset($progress_by_lesson[$lesson_id])) {
        $progress_by_lesson[$lesson_id] = [];
    }
    $progress_by_lesson[$lesson_id][] = $progress['step'];
}

foreach ($user_badges as $badge) {
    $lesson_id = $badge['lesson_id'];
    $badges_by_lesson[$lesson_id][] = $badge;
}

// L1の詳細なサブレッスン構造に対応
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
                'tagalog' => 'Magandang umaga po, guro.'
            ],
            3 => [
                'title' => 'さようなら',
                'description' => 'お別れのあいさつ',
                'japanese' => 'せんせい、さようなら', 
                'english' => 'Goodbye!',
                'chinese' => '再见！',
                'tagalog' => 'Paalam po, guro.'
            ]
        ],
        'steps' => [
            1 => 'おはよう！',
            2 => 'おはようございます。',
            3 => 'せんせい、さようなら'
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
        ],
        'steps' => [
            1 => 'わたしは' . $user['child_name'] . 'です。',
            2 => 'あなたのなまえはなんですか？',
            3 => 'よろしくおねがいします。'
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
                'chinese' => '你的生日是什么时候？<br>我的生日是__月__日',
                'tagalog' => 'Kailan ang inyong kaarawan?<br>Ang aking kaarawan ay __buwan__ __araw__.'
            ]
        ],
        'steps' => [
            1 => 'どこからきましたか？<br>わたしは〇〇からきました。',
            2 => 'なんさいですか？<br>わたしは〇〇さいです。',
            3 => 'たんじょうびはいつですか？<br>わたしのたんじょうびは〇〇がつ〇〇にちです。'
        ]
    ],
    /*
    4 => [
        'title' => '数字', 
        'description' => '1から10までの数字と数の数え方を覚えます',
        'steps' => [
            1 => '1〜10（いち〜じゅう）',
            2 => '〇ほんです（3ほんです）',
            3 => '〇がつ〇にちです'
        ]
    ],
    5 => [
        'title' => 'ひらがな', 
        'description' => 'ひらがなの読み方を学び、文字と音を結び付けます',
        'steps' => [
            1 => 'あ・い・う・え・お',
            2 => 'あ',
            3 => 'あめ／あさひ'
        ]
    ],
    6 => [
        'title' => '時計', 
        'description' => '時刻の読み方と時間に関する会話を学びます',
        'steps' => [
            1 => '〇じです',
            2 => 'なんじですか？／〇じです',
            3 => 'はい！'
        ]
    ],
    7 => [
        'title' => '学用品', 
        'description' => '身近な学用品の名前を覚えます',
        'steps' => [
            1 => 'これはえんぴつです',
            2 => 'これはなんですか？',
            3 => 'これは〇〇です'
        ]
    ],
    8 => [
        'title' => '色', 
        'description' => '基本的な色の名前を覚えて識別できるようになります',
        'steps' => [
            1 => 'あかです',
            2 => 'これはなんいろですか？',
            3 => 'これは〇いろです'
        ]
    ],
    9 => [
        'title' => '曜日', 
        'description' => '曜日の言い方を学び、日付と組み合わせて使えるようになります',
        'steps' => [
            1 => '月ようびです',
            2 => 'きょうはなんようびですか？',
            3 => 'きょうは〇ようびです'
        ]
    ],
    10 => [
        'title' => '天気', 
        'description' => '天気について話し、日常会話で使えるようになります',
        'steps' => [
            1 => 'はれです',
            2 => 'きょうのてんきはなんですか？',
            3 => 'きょうは〇〇です'
        ]
    ],
    11 => [
        'title' => '学校生活（あいさつ）', 
        'description' => '学校でのあいさつと返事を学びます',
        'steps' => [
            1 => 'はい！',
            2 => 'おはようございます',
            3 => 'よろしくおねがいします'
        ]
    ],
    12 => [
        'title' => '学校生活（感謝）', 
        'description' => '授業の終わりや下校時の挨拶を学びます',
        'steps' => [
            1 => 'ありがとうございました',
            2 => 'さようなら',
            3 => 'またあした！'
        ]
    ],
    13 => [
        'title' => '買い物', 
        'description' => 'お店で使う基本的な表現を覚えます',
        'steps' => [
            1 => 'これください',
            2 => 'いくらですか？',
            3 => 'ありがとうございます'
        ]
    ],
    14 => [
        'title' => '季節・行事', 
        'description' => '季節の行事や記念日の挨拶を学びます',
        'steps' => [
            1 => 'あけましておめでとう',
            2 => 'たんじょうびおめでとう！',
            3 => 'おめでとう！'
        ]
    ],
    15 => [
        'title' => '食事', 
        'description' => '食事の前後の挨拶とお願いの仕方を学びます',
        'steps' => [
            1 => 'いただきます',
            2 => 'ごちそうさまでした',
            3 => 'おかわりください'
        ]
    ],
    16 => [
        'title' => '天気（詳細）', 
        'description' => '詳しい天気の表現と会話を学びます',
        'steps' => [
            1 => 'きょうはあめです',
            2 => 'きょうのてんきはなんですか？',
            3 => 'きょうはくもりです'
        ]
    ],
    17 => [
        'title' => '健康・気持ち', 
        'description' => '体調や気持ちを表現できるようになります',
        'steps' => [
            1 => 'きもちがわるいです',
            2 => 'げんきです！',
            3 => 'つかれました'
        ]
    ],
    18 => [
        'title' => '地域・場所', 
        'description' => '場所をたずねたり教えたりできるようになります',
        'steps' => [
            1 => 'トイレはどこですか？',
            2 => 'ここです',
            3 => 'そこです'
        ]
    ],
    19 => [
        'title' => '家族（両親・兄姉）', 
        'description' => '家族を紹介できるようになります',
        'steps' => [
            1 => 'これは' . $user['child_name'] . 'のおとうさんです',
            2 => 'これは' . $user['child_name'] . 'のおかあさんです',
            3 => 'これは' . $user['child_name'] . 'のおにいさんです'
        ]
    ],
    20 => [
        'title' => '家族（姉・祖父母）', 
        'description' => '家族を紹介できるようになります',
        'steps' => [
            1 => 'これは' . $user['child_name'] . 'のおねえさんです',
            2 => 'これは' . $user['child_name'] . 'のおじいさんです',
            3 => 'これは' . $user['child_name'] . 'のおばあさんです'
        ]
    ]
    */
];

// ステータス判定関数
function getLessonStatus($lesson_id, $progress_by_lesson, $badges_by_lesson) {
    $progress = $progress_by_lesson[$lesson_id] ?? [];
    $badges = $badges_by_lesson[$lesson_id] ?? [];
    
    if (in_array('dekita', $progress) && !empty($badges)) {
        return 'completed';
    } elseif (!empty($progress)) {
        return 'in-progress';
    } else {
        return 'not-started';
    }
}

function getCompletedSteps($lesson_id, $progress_by_lesson) {
    $progress = $progress_by_lesson[$lesson_id] ?? [];
    $steps = ['miru', 'yatte', 'dekita'];
    $completed = 0;
    
    foreach ($steps as $step) {
        if (in_array($step, $progress)) {
            $completed++;
        } else {
            break;
        }
    }
    
    return $completed;
}

$page_title = translateIfNeeded('レッスン一覧', $user['native_language'], $translator) . ' - nihongonote';
require_once '../includes/header.php';
?>

<div class="curriculum-container <?= $color_scheme ?>-theme">

    <div class="curriculum-header">
        <h1 class="curriculum-title"><?= h($t['page_title']) ?></h1>
        <p class="curriculum-subtitle">
            <?= h($t['page_subtitle']) ?>
        </p>
    </div>

    <!-- 左側プレビューエリアと右側グリッドのレイアウト -->
    <div class="lessons-layout">
        <!-- 左側: プレビューエリア -->
        <div class="lesson-preview" id="lessonPreview">
            <div class="preview-placeholder">
                <h2><?= h($t['select_lesson']) ?></h2>
                <p><?= h($t['hover_description']) ?></p>
            </div>
        </div>

        <!-- 右側: レッスングリッド (4x5) -->
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson_id => $lesson): ?>
                <?php
                $status = getLessonStatus($lesson_id, $progress_by_lesson, $badges_by_lesson);
                $is_available = $lesson_id == 1 || getLessonStatus($lesson_id - 1, $progress_by_lesson, $badges_by_lesson) === 'completed';
                ?>
                
                <?php 
                $translated_title = $lesson_titles[$current_language][$lesson_id] ?? $lesson['title'];
                ?>
                <div class="lesson-card-simple <?= $status ?> <?= !$is_available ? 'locked' : '' ?>" 
                     data-lesson-id="<?= $lesson_id ?>" 
                     data-lesson-title="<?= h($translated_title) ?>"
                     data-lesson-description="<?= h($lesson['description']) ?>"
                     data-lesson-available="<?= $is_available ? 'true' : 'false' ?>"
                     style="--lesson-index: <?= $lesson_id - 1 ?>; --gender: <?= $user['child_gender'] == 'male' ? '1' : '0' ?>;"
                     <?php if (isset($lesson['sub_lessons'])): ?>
                         data-sub-lessons="<?= htmlspecialchars(json_encode($lesson['sub_lessons'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                     <?php endif; ?>>
                    
                    <div class="lesson-number">L<?= $lesson_id ?></div>
                    <div class="lesson-title"><?= h($translated_title) ?></div>
                    <div class="lesson-action"><?= h($t['start_button']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const lessonCards = document.querySelectorAll('.lesson-card-simple');
    const lessonPreview = document.getElementById('lessonPreview');
    
    function showPreview(card) {
        const lessonId = card.dataset.lessonId;
        const title = card.dataset.lessonTitle;
        const description = card.dataset.lessonDescription;
        const isAvailable = card.dataset.lessonAvailable === 'true';
        let subLessons = null;
        try {
            subLessons = card.dataset.subLessons ? JSON.parse(card.dataset.subLessons) : null;
            console.log(`Lesson ${lessonId} preview:`, { title, description, subLessons });
        } catch (e) {
            console.error('JSON parse error:', e, card.dataset.subLessons);
            subLessons = null;
        }
        
        let previewContent = `
            <div class="preview-content">
                <div class="preview-header">
                    <h2>Lesson${lessonId} ${title}</h2>
                </div>
        `;
        
        if (subLessons) {
            previewContent += '<div class="preview-sub-lessons">';
            Object.keys(subLessons).forEach(subId => {
                const subLesson = subLessons[subId];
                previewContent += `
                    <div class="preview-sub-item">
                        <span class="sub-number">${subId}</span>
                        <div class="sub-title-container">
                            <div class="sub-title" data-lang="japanese">${subLesson.japanese || subLesson.title}</div>
                            <div class="sub-title" data-lang="english" style="display:none;">
                                <div class="japanese-subtitle">${subLesson.japanese || subLesson.title}</div>
                                <div class="main-title">${subLesson.english || subLesson.title}</div>
                            </div>
                            <div class="sub-title" data-lang="chinese" style="display:none;">
                                <div class="japanese-subtitle">${subLesson.japanese || subLesson.title}</div>
                                <div class="main-title">${subLesson.chinese || subLesson.title}</div>
                            </div>
                            <div class="sub-title" data-lang="tagalog" style="display:none;">
                                <div class="japanese-subtitle">${subLesson.japanese || subLesson.title}</div>
                                <div class="main-title">${subLesson.tagalog || subLesson.title}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            previewContent += '</div>';
        }
        
        previewContent += `
                <div class="preview-action">
                    <button class="preview-start-btn ${!isAvailable ? 'disabled' : ''}" 
                            ${!isAvailable ? 'disabled' : ''} 
                            onclick="${isAvailable ? `window.location.href='lesson.php?id=${lessonId}&sub=1'` : ''}">
                        ${!isAvailable ? '<?= h($t["locked"]) ?>' : '<?= h($t["start_button"]) ?>'}
                    </button>
                </div>
            </div>
        `;
        
        lessonPreview.innerHTML = previewContent;
        
        // 現在の言語に基づいて初期表示を設定
        const currentLang = '<?= $current_language ?>';
        let displayLang = 'japanese'; // デフォルト
        if (currentLang === 'en') {
            displayLang = 'english';
        } else if (currentLang === 'zh') {
            displayLang = 'chinese';
        } else if (currentLang === 'tl') {
            displayLang = 'tagalog';
        }
        
        // 表示言語を設定
        const subTitles = lessonPreview.querySelectorAll('.sub-title');
        subTitles.forEach(title => {
            if (title.dataset.lang === displayLang) {
                title.style.display = 'block';
            } else {
                title.style.display = 'none';
            }
        });
        
        // グローバル言語切り替えとの連動用関数
        window.updatePreviewLanguage = function(globalLang) {
            let previewLang = 'japanese';
            if (globalLang === 'en') {
                previewLang = 'english';
            } else if (globalLang === 'zh') {
                previewLang = 'chinese';
            } else if (globalLang === 'tl') {
                previewLang = 'tagalog';
            }
            
            const subTitles = lessonPreview.querySelectorAll('.sub-title');
            subTitles.forEach(title => {
                if (title.dataset.lang === previewLang) {
                    title.style.display = 'block';
                } else {
                    title.style.display = 'none';
                }
            });
        };
    }
    
    lessonCards.forEach(card => {
        // デスクトップ: ホバー時にプレビュー表示
        card.addEventListener('mouseenter', function() {
            if (window.innerWidth > 1024) {
                showPreview(this);
            }
        });
        
        // クリック処理
        card.addEventListener('click', function(e) {
            const lessonId = this.dataset.lessonId;
            const isAvailable = this.dataset.lessonAvailable === 'true';
            
            // タブレット/モバイルの場合: 初回タップでプレビュー表示のみ
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                showPreview(this);
                return;
            }
            
            // デスクトップの場合: 直接レッスンへ移動
            if (isAvailable) {
                window.location.href = `lesson.php?id=${lessonId}&sub=1`;
            }
        });
        
    });
});

// グローバル言語切り替え関数を拡張してプレビューも更新
function switchLanguage(lang) {
    // アクティブなタブを更新
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
    
    // body要素に現在の言語を設定
    document.body.setAttribute('data-current-lang', lang);
    
    // プレビューエリアの言語も更新
    if (window.updatePreviewLanguage) {
        window.updatePreviewLanguage(lang);
    }
    
    // セッションに言語設定を保存（非同期）
    fetch('../api/set_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            language: lang
        })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            // 成功した場合、ページをリロードして翻訳を反映
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('lang', lang);
            window.location.search = urlParams.toString();
        }
    }).catch(error => {
        // エラーが発生してもページをリロード
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('lang', lang);
        window.location.search = urlParams.toString();
    });
}
</script>

<style>
/* 背景画像設定 */
body {
    background-image: url('../assets/images/bg_top.png'), url('../assets/images/bg_bottom.png');
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
}

.curriculum-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.curriculum-header {
    text-align: center;
    margin-bottom: 30px;
}

.curriculum-title {
    font-size: 2.5em;
    color: hsl(210, 45%, 60%);
    margin-bottom: 10px;
    margin-top: 10px;
}

.curriculum-subtitle {
    color: #666;
    font-size: 1.1em;
}

.lessons-layout {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

/* 左側プレビューエリア */
.lesson-preview {
    flex: 1;
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 600px;
    position: sticky;
    top: 20px;
}

.preview-placeholder {
    text-align: center;
    color: #999;
}

.preview-placeholder h2 {
    color: #ccc;
    margin-bottom: 10px;
}

.preview-content {
    width: 100%;
    text-align: center;
}

.preview-header h2 {
    color: hsl(210, 45%, 60%);
    font-size: 2.2em;
    margin-bottom: 15px;
}


.preview-sub-lessons {
    margin: 25px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
}

.preview-sub-item {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 15px;
    margin: 10px 0;
    padding: 12px 20px;
    background: white;
    border-radius: 10px;
    font-size: 1.1em;
    min-height: 60px;
}

.sub-number {
    background: hsl(210, 40%, 75%);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
    margin-top: 5px;
}

.sub-title-container {
    flex: 0 0 auto;
    max-width: 400px;
    width: 400px;
}

.sub-title {
    color: #333;
    font-weight: 500;
    text-align: left;
    display: inline;
}

.preview-language-tabs {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 15px 0;
}

.preview-lang-btn {
    background: #f0f0f0;
    border: 2px solid #ddd;
    border-radius: 20px;
    padding: 8px 16px;
    cursor: pointer;
    font-size: 0.9em;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #666;
}

.preview-lang-btn:hover {
    background: #e0e0e0;
    border-color: #ccc;
}

.preview-lang-btn.active {
    background: hsl(210, 45%, 60%);
    border-color: hsl(210, 45%, 60%);
    color: white;
}

.preview-start-btn {
    background: hsl(210, 40%, 70%);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 30px;
    font-size: 1.2em;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px hsla(210, 40%, 70%, 0.3);
}

.preview-start-btn:hover:not(.disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px hsla(210, 40%, 70%, 0.4);
}

.preview-start-btn.disabled {
    background: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}

/* 右側レッスングリッド */
.lessons-grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 170px;
    gap: 20px;
    max-width: 600px;
    padding-right: 10px;
}

.lesson-card-simple {
    background: white;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 150px;
    color: #444;
}

.lesson-card-simple:hover:not(.locked) {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    background: hsl(
        calc(var(--gender) * 180 + (1 - var(--gender)) * 10), 
        50%, 
        70%
    );
}

.lesson-card-simple:hover:not(.locked) .lesson-title {
    color: white;
}

.lesson-card-simple:hover:not(.locked) .lesson-action {
    color: white;
}

.lesson-card-simple:hover:not(.locked) .lesson-number {
    background: white;
    color: hsl(
        calc(var(--gender) * 180 + (1 - var(--gender)) * 10), 
        55%, 
        50%
    );
}

.lesson-card-simple.completed {
    background: hsl(
        calc(var(--gender) * 150 + (1 - var(--gender)) * 20), 
        30%, 
        90%
    );
    border: 2px solid hsl(
        calc(var(--gender) * 150 + (1 - var(--gender)) * 20), 
        40%, 
        65%
    );
}

.lesson-card-simple.in-progress {
    background: hsl(
        calc(var(--gender) * 45 + (1 - var(--gender)) * 35), 
        35%, 
        88%
    );
    border: 2px solid hsl(
        calc(var(--gender) * 45 + (1 - var(--gender)) * 35), 
        45%, 
        70%
    );
}

.lesson-card-simple.locked {
    background: #f8f8f8;
    color: #bbb;
    cursor: not-allowed;
}

/* 男の子用ブルーテーマ */
.blue-theme .lesson-card-simple {
    background: linear-gradient(135deg, #f3f8ff, #e8f2ff);
    border: 2px solid #e3f2fd;
}

.blue-theme .lesson-card-simple:hover:not(.locked) {
    background: linear-gradient(135deg, #4a90e2, #42a5f5);
    box-shadow: 0 8px 30px rgba(74, 144, 226, 0.3);
}

.blue-theme .lesson-card-simple.completed {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 2px solid #4a90e2;
}

.blue-theme .lesson-card-simple.in-progress {
    background: linear-gradient(135deg, #e8f4fd, #d1ecf1);
    border: 2px solid #42a5f5;
}

.blue-theme .lesson-number {
    background: #4a90e2;
    color: white;
}

.blue-theme .lesson-card-simple:hover:not(.locked) .lesson-number {
    background: white;
    color: #4a90e2;
}

.blue-theme .lesson-title {
    color: #357abd;
}

/* 女の子用ピンクテーマ */
.pink-theme .lesson-card-simple {
    background: linear-gradient(135deg, #fff0f5, #ffe4ec);
    border: 2px solid #fce4ec;
}

.pink-theme .lesson-card-simple:hover:not(.locked) {
    background: linear-gradient(135deg, #e91e63, #f48fb1);
    box-shadow: 0 8px 30px rgba(233, 30, 99, 0.3);
}

.pink-theme .lesson-card-simple.completed {
    background: linear-gradient(135deg, #fce4ec, #f8bbd9);
    border: 2px solid #e91e63;
}

.pink-theme .lesson-card-simple.in-progress {
    background: linear-gradient(135deg, #ffeef3, #ffcdd2);
    border: 2px solid #f48fb1;
}

.pink-theme .lesson-number {
    background: #e91e63;
    color: white;
}

.pink-theme .lesson-card-simple:hover:not(.locked) .lesson-number {
    background: white;
    color: #e91e63;
}

.pink-theme .lesson-title {
    color: #c2185b;
}

.lesson-number {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 8px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    background: hsl(
        calc(var(--gender) * (180 + var(--lesson-index) * 4) + (1 - var(--gender)) * (10 + var(--lesson-index) * 2)), 
        40%, 
        85%
    );
    color: #666;
    transition: all 0.3s ease;
}

.lesson-title {
    font-size: 1.1em;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.3;
    color: hsl(
        calc(var(--gender) * 180 + (1 - var(--gender)) * 10), 
        55%, 
        50%
    );
    transition: all 0.3s ease;
}

.lesson-action {
    font-size: 0.9em;
    opacity: 0.8;
    margin-top: auto;
}

/* Preview area subtitle styles */
.sub-title-container .sub-title {
    line-height: 1.4;
}

.sub-title .main-title {
    font-weight: normal;
    margin-top: 4px;
    color: #333;
}

.sub-title .japanese-subtitle {
    font-size: 0.85em;
    color: #666;
    margin-bottom: 4px;
    font-weight: bold;
}

/* レスポンシブ対応 */
@media (max-width: 1024px) {
    .lessons-layout {
        flex-direction: column;
    }
    
    .lesson-preview {
        min-height: 300px;
        order: 2;
    }
    
    .lessons-grid {
        order: 1;
        max-width: none;
        grid-template-columns: repeat(4, 1fr);
    }
    
    .lesson-card-simple {
        height: 120px;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .lessons-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .lesson-card-simple {
        height: 100px;
    }
}
</style>

<script src="../assets/js/main.js"></script>

<?php require_once '../includes/footer.php'; ?>
