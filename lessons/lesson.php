<?php
// lessons/lesson.php - 個別レッスンページ（シンプル版）
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ファイル読み込み
try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
    require_once '../includes/logo.php';
} catch (Exception $e) {
    die('ファイル読み込みエラー: ' . $e->getMessage());
}

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

// レッスンID取得
$lesson_id = $_GET['id'] ?? 1;
$sub_lesson_id = $_GET['sub'] ?? 1;
$lesson_id = max(1, min(20, intval($lesson_id))); // 1-20の範囲に制限
$sub_lesson_id = max(1, min(3, intval($sub_lesson_id))); // 1-3の範囲に制限

$user = $_SESSION['user'];

// 言語設定（URLパラメータ > セッション > ユーザーの母語 > デフォルト日本語の優先順位）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? $user['native_language'] ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'ko', 'vi', 'tl', 'ne', 'pt', 'es', 'fr', 'de', 'it', 'ru', 'ar', 'hi', 'th'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// レッスンごとの挨拶翻訳辞書
$greeting_translations = [
    // レッスン1-1用（友達への朝の挨拶）
    'casual_morning' => [
        'ja' => 'おはよう！',
        'en' => 'Good morning!',
        'ko' => '안녕!',
        'zh' => '早上好！',
        'es' => '¡Buenos días!',
        'pt' => 'Bom dia!',
        'fr' => 'Bonjour!',
        'de' => 'Guten Morgen!',
        'it' => 'Buongiorno!',
        'ru' => 'Доброе утро!',
        'ar' => 'صباح الخير!',
        'hi' => 'सुप्रभात!',
        'th' => 'สวัสดีตอนเช้า!',
        'vi' => 'Chào buổi sáng!',
        'tl' => 'Magandang umaga!',
        'ne' => 'शुभ प्रभात!'
    ],
    // レッスン1-2用（先生への丁寧な朝の挨拶）
    'formal_morning' => [
        'ja' => 'せんせい、おはようございます。',
        'en' => 'Good morning, teacher.',
        'ko' => '선생님, 안녕하세요.',
        'zh' => '老师，早上好。',
        'es' => 'Buenos días, maestro/a.',
        'pt' => 'Bom dia, professor/a.',
        'fr' => 'Bonjour, professeur.',
        'de' => 'Guten Morgen, Lehrer/in.',
        'it' => 'Buongiorno, maestro/a.',
        'ru' => 'Доброе утро, учитель.',
        'ar' => 'صباح الخير، أستاذ/ة.',
        'hi' => 'नमस्ते, शिक्षक जी।',
        'th' => 'สวัสดีค่ะ/ครับ อาจารย์',
        'vi' => 'Chào cô/thầy.',
        'tl' => 'Magandang umaga po, guro.',
        'ne' => 'नमस्कार, गुरुजी।'
    ],
    // レッスン1-3用（お別れの挨拶）
    'goodbye' => [
        'ja' => 'さようなら。',
        'en' => 'Goodbye, teacher.',
        'ko' => '선생님, 안녕히 가세요.',
        'zh' => '老师，再见。',
        'es' => 'Adiós, maestro/a.',
        'pt' => 'Tchau, professor/a.',
        'fr' => 'Au revoir, professeur.',
        'de' => 'Auf Wiedersehen, Lehrer/in.',
        'it' => 'Arrivederci, maestro/a.',
        'ru' => 'До свидания, учитель.',
        'ar' => 'مع السلامة، أستاذ/ة.',
        'tl' => 'paalam po, guro!',
        'hi' => 'अलविदा, शिक्षक जी।',
        'th' => 'ลาก่อน อาจารย์',
        'vi' => 'Tạm biệt cô/thầy.',
        'ne' => 'नमस्कार, गुरुजी।'
    ],
    // レッスン2-1用（自己紹介）
    'self_introduction' => [
        'ja' => 'わたしは、{child_name}です。',
        'en' => 'I am {child_name}.',
        'zh' => '我叫{child_name}。',
        'ko' => '저는 {child_name}입니다.',
        'es' => 'Soy {child_name}.',
        'pt' => 'Eu sou {child_name}.',
        'fr' => 'Je suis {child_name}.',
        'de' => 'Ich bin {child_name}.',
        'it' => 'Sono {child_name}.',
        'ru' => 'Я {child_name}.',
        'ar' => 'أنا {child_name}.',
        'hi' => 'मैं {child_name} हूं।',
        'th' => 'ฉันชื่อ {child_name}',
        'vi' => 'Tôi là {child_name}.',
        'tl' => 'Ako si {child_name}.',
        'ne' => 'म {child_name} हुँ।'
    ]
];

// NEXTボタンの翻訳
$next_button_text = [
    'ja' => 'つぎへ',
    'en' => 'NEXT',
    'zh' => '下一步',
    'ko' => '다음',
    'vi' => 'Tiếp theo',
    'tl' => 'Susunod',
    'ne' => 'अर्को',
    'pt' => 'Próximo'
];

// 現在の言語のNEXTボタンテキストを取得
$current_next_text = $next_button_text[$current_language] ?? $next_button_text['ja'];

// パパママポイントの翻訳
$papa_mama_point_text = [
    'ja' => 'パパ・ママ<br>ポイント！',
    'en' => 'For<br>Parents',
    'zh' => '爸爸妈妈<br>要点',
    'ko' => '부모님<br>포인트',
    'vi' => 'Cho<br>Cha Mẹ',
    'tl' => 'Para sa<br>Magulang',
    'ne' => 'आमाबुबाको<br>लागि',
    'pt' => 'Para<br>Pais'
];

// 現在の言語のパパママポイントテキストを取得
$current_papa_mama_text = $papa_mama_point_text[$current_language] ?? $papa_mama_point_text['ja'];

// タブの翻訳
$tab_text = [
    'ja' => [
        'miru' => 'A みる',
        'yatte' => 'B やってみる', 
        'dekita' => 'C できた'
    ],
    'en' => [
        'miru' => 'A Watch',
        'yatte' => 'B Try',
        'dekita' => 'C Done'
    ],
    'zh' => [
        'miru' => 'A 观看',
        'yatte' => 'B 尝试',
        'dekita' => 'C 完成'
    ],
    'ko' => [
        'miru' => 'A 보기',
        'yatte' => 'B 해보기',
        'dekita' => 'C 완료'
    ],
    'vi' => [
        'miru' => 'A Xem',
        'yatte' => 'B Thử',
        'dekita' => 'C Hoàn thành'
    ],
    'tl' => [
        'miru' => 'A Tingnan',
        'yatte' => 'B Subukan',
        'dekita' => 'C Tapos'
    ],
    'ne' => [
        'miru' => 'A हेर्ने',
        'yatte' => 'B प्रयास गर्ने',
        'dekita' => 'C सम्पन्न'
    ],
    'pt' => [
        'miru' => 'A Ver',
        'yatte' => 'B Tentar',
        'dekita' => 'C Concluído'
    ]
];

// 現在の言語のタブテキストを取得
$current_tab_text = $tab_text[$current_language] ?? $tab_text['ja'];

// "You"の翻訳
$you_translations = [
    'ja' => 'あなた',
    'en' => 'You',
    'zh' => '你',
    'ko' => '당신',
    'vi' => 'Bạn',
    'tl' => 'Ikaw',
    'ne' => 'तपाईं',
    'pt' => 'Você'
];

// "You learned it!"と"You got a new badge!"の翻訳
$you_learned_text = [
    'ja' => 'おぼえました！',
    'en' => 'You learned it!',
    'zh' => '你学会了！',
    'ko' => '배웠습니다！',
    'vi' => 'Bạn đã học được!',
    'tl' => 'Natuto mo na!',
    'ne' => 'तपाईंले सिक्नुभयो!',
    'pt' => 'Você aprendeu!'
];

$you_got_badge_text = [
    'ja' => 'あたらしいバッジをもらいました！',
    'en' => 'You got a new badge!',
    'zh' => '你获得了新徽章！',
    'ko' => '새 배지를 받았습니다！',
    'vi' => 'Bạn nhận được huy hiệu mới!',
    'tl' => 'Nakakuha ka ng bagong badge!',
    'ne' => 'तपाईंले नयाँ बैज पाउनुभयो!',
    'pt' => 'Você ganhou um novo distintivo!'
];

// "Let's say it!"の翻訳
$lets_say_it_text = [
    'ja' => 'いってみましょう！',
    'en' => 'Let\'s say it!',
    'zh' => '我们来说吧！',
    'ko' => '말해봅시다！',
    'vi' => 'Hãy nói thử!',
    'tl' => 'Sabihin natin!',
    'ne' => 'भनौं!',
    'pt' => 'Vamos falar!'
];

// "Friend"の翻訳
$friend_translations = [
    'ja' => 'ともだち',
    'en' => 'Friend',
    'zh' => '朋友',
    'ko' => '친구',
    'vi' => 'Bạn bè',
    'tl' => 'Kaibigan',
    'ne' => 'साथी',
    'pt' => 'Amigo'
];

// "Teacher"の翻訳
$teacher_translations = [
    'ja' => 'せんせい',
    'en' => 'Teacher',
    'zh' => '老师',
    'ko' => '선생님',
    'vi' => 'Giáo viên',
    'tl' => 'Guro',
    'ne' => 'शिक्षक',
    'pt' => 'Professor'
];

// 現在の言語のテキストを取得
$current_you = $you_translations[$current_language] ?? $you_translations['ja'];
$current_you_learned = $you_learned_text[$current_language] ?? $you_learned_text['ja'];
$current_you_got_badge = $you_got_badge_text[$current_language] ?? $you_got_badge_text['ja'];
$current_lets_say_it = $lets_say_it_text[$current_language] ?? $lets_say_it_text['ja'];
$current_friend = $friend_translations[$current_language] ?? $friend_translations['ja'];
$current_teacher = $teacher_translations[$current_language] ?? $teacher_translations['ja'];

// tts-buttonの翻訳
$tts_button_text = [
    'ja' => '🔊 やってみる',
    'en' => '🔊 Try it',
    'zh' => '🔊 试一试',
    'ko' => '🔊 해보기',
    'vi' => '🔊 Thử nói',
    'tl' => '🔊 Subukan',
    'ne' => '🔊 प्रयास गर्नुहोस्',
    'pt' => '🔊 Experimente'
];

$current_tts_button = $tts_button_text[$current_language] ?? $tts_button_text['ja'];

// ポイントカードタイトルの翻訳
$point_title_text = [
    'ja' => 'のポイント：',
    'en' => ' Points:',
    'zh' => '要点：',
    'ko' => '포인트:',
    'vi' => ' điểm:',
    'tl' => ' Points:',
    'ne' => 'को बिन्दुहरू:',
    'pt' => ' Pontos:'
];

$current_point_title = $point_title_text[$current_language] ?? $point_title_text['ja'];

// 完了メッセージの翻訳
$completion_message_text = [
    'ja' => 'を完了しました！',
    'en' => ' completed!',
    'zh' => '完成了！',
    'ko' => '를 완료했습니다！',
    'vi' => ' đã hoàn thành!',
    'tl' => ' tapos na!',
    'ne' => ' सम्पन्न भयो!',
    'pt' => ' concluído!'
];

$lesson_prefix_text = [
    'ja' => 'レッスン',
    'en' => 'Lesson',
    'zh' => '课程',
    'ko' => '레슨',
    'vi' => 'Bài học',
    'tl' => 'Aralin',
    'ne' => 'पाठ',
    'pt' => 'Lição'
];

$mastered_message_text = [
    'ja' => 'をマスターしました',
    'en' => ' mastered',
    'zh' => '已掌握',
    'ko' => '를 마스터했습니다',
    'vi' => ' đã thành thạo',
    'tl' => ' na-master',
    'ne' => ' दक्ष भयो',
    'pt' => ' dominado'
];

// レッスンタイトルの翻訳（「おはようございます」など）
$lesson_content_translations = [
    'ja' => [
        'おはよう' => 'おはよう',
        'あなたのなまえは？' => 'あなたのなまえは？',
        'おはようございます' => 'おはようございます'
    ],
    'en' => [
        'おはよう' => '"Ohayou"',
        'あなたのなまえは？' => '"What\'s your name?"',
        'おはようございます' => '"Ohayou Gozaimasu"'
    ],
    'zh' => [
        'おはよう' => '"Ohayou"',
        'あなたのなまえは？' => '"你叫什么名字？"',
        'おはようございます' => '"Ohayou Gozaimasu"'
    ],
    'ko' => [
        'おはよう' => '"안녕"',
        'あなたのなまえは？' => '"이름이 뭐예요?"',
        'おはようございます' => '"안녕하세요"'
    ],
    'vi' => [
        'おはよう' => '"Chào buổi sáng"',
        'あなたのなまえは？' => '"Tên bạn là gì?"',
        'おはようございます' => '"Chào buổi sáng"'
    ],
    'tl' => [
        'おはよう' => '"Magandang umaga!"',
        'あなたのなまえは？' => '"Ano ang pangalan mo?"',
        'おはようございます' => '"Magandang umaga po."'
    ],
    'ne' => [
        'おはよう' => '"शुभ प्रभात"',
        'あなたのなまえは？' => '"तपाईंको नाम के हो?"',
        'おはようございます' => '"नमस्कार"'
    ],
    'pt' => [
        'おはよう' => '"Bom dia"',
        'あなたのなまえは？' => '"Qual é o seu nome?"',
        'おはようございます' => '"Bom dia"'
    ]
];

$current_completion = $completion_message_text[$current_language] ?? $completion_message_text['ja'];
$current_mastered = $mastered_message_text[$current_language] ?? $mastered_message_text['ja'];
$current_lesson_content = $lesson_content_translations[$current_language] ?? $lesson_content_translations['ja'];
$current_lesson_prefix = $lesson_prefix_text[$current_language] ?? $lesson_prefix_text['ja'];

// 進行確認ダイアログの翻訳
$progress_confirm_text = [
    'ja' => 'にすすみますか？',
    'en' => '? Do you want to proceed?',
    'zh' => 'にすすみますか？',
    'ko' => '로 진행하시겠습니까?',
    'vi' => '? Bạn có muốn tiếp tục không?',
    'tl' => '? Gusto mo bang magpatuloy?',
    'ne' => 'मा जान चाहनुहुन्छ?',
    'pt' => '? Quer continuar?'
];

$current_progress_confirm = $progress_confirm_text[$current_language] ?? $progress_confirm_text['ja'];

// ボタンテキストの翻訳
$button_text = [
    'ja' => [
        'ok' => 'すすむ',
        'cancel' => 'やめる'
    ],
    'en' => [
        'ok' => 'Continue',
        'cancel' => 'Cancel'
    ],
    'zh' => [
        'ok' => '继续',
        'cancel' => '取消'
    ],
    'ko' => [
        'ok' => '계속',
        'cancel' => '취소'
    ],
    'vi' => [
        'ok' => 'Tiếp tục',
        'cancel' => 'Hủy'
    ],
    'tl' => [
        'ok' => 'Magpatuloy',
        'cancel' => 'Kanselahin'
    ],
    'ne' => [
        'ok' => 'जारी राख्ने',
        'cancel' => 'रद्द गर्ने'
    ],
    'pt' => [
        'ok' => 'Continuar',
        'cancel' => 'Cancelar'
    ]
];

$current_ok_text = $button_text[$current_language]['ok'] ?? $button_text['ja']['ok'];
$current_cancel_text = $button_text[$current_language]['cancel'] ?? $button_text['ja']['cancel'];

// ボタン翻訳関数
function getButtonText($key, $language) {
    $button_translations = [
        'menu_return' => [
            'ja' => 'メニューへ戻る',
            'en' => 'Back to Menu',
            'zh' => '返回菜单',
            'tl' => 'Bumalik sa Menu'
        ],
        'stop' => [
            'ja' => 'やめる',
            'en' => 'Logout',
            'zh' => '退出',
            'tl' => 'Logout'
        ]
    ];
    
    return $button_translations[$key][$language] ?? $button_translations[$key]['ja'] ?? $key;
}

// データベースからレッスンデータを取得
function getLessonData($pdo, $lesson_id, $sub_lesson_id, $child_name = '', $parent_name = '') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                lesson_number,
                step_number,
                step_type,
                subtitle,
                scene_description,
                scene_text_en,
                scene_text_zh,
                scene_text_tl,
                dialogue_data,
                papa_mama_point_ja,
                papa_mama_point_en,
                papa_mama_point_zh,
                video_url,
                video_filename,
                japanese_text,
                english_translation,
                chinese_translation,
                tagalog_translation,
                pronunciation,
                practice_romaji
            FROM lessons 
            WHERE lesson_number = ? AND sub_lesson_number = ?
            ORDER BY step_number
        ");
        $stmt->execute([$lesson_id, $sub_lesson_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            return null;
        }
        
        // 新しい構造に対応
        $lesson_data = [
            'title' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id,
            'subtitle' => $results[0]['subtitle'] ?? '',
            'lesson_number' => $lesson_id,
            'step_number' => $sub_lesson_id,
            'content' => []
        ];
        
        // step_typeごとにデータを整理
        foreach ($results as $row) {
            $step_type = $row['step_type'];
            
            // dialogue_dataがJSON形式の場合はデコードし、名前を置換
            $dialogue_data = [];
            if (!empty($row['dialogue_data'])) {
                $decoded = json_decode($row['dialogue_data'], true);
                if (is_array($decoded)) {
                    // 各dialogueエントリに名前置換を適用
                    foreach ($decoded as &$dialogue) {
                        if (isset($dialogue['japanese'])) {
                            $dialogue['japanese'] = replaceNames($dialogue['japanese'], $child_name, $parent_name);
                        }
                        if (isset($dialogue['translation'])) {
                            $dialogue['translation'] = replaceNames($dialogue['translation'], $child_name, $parent_name);
                        }
                    }
                    $dialogue_data = $decoded;
                }
            }
            
            $lesson_data['content'][$step_type] = [
                'japanese_text' => replaceNames($row['japanese_text'] ?? '', $child_name, $parent_name),
                'translation' => replaceNames($row['english_translation'] ?? '', $child_name, $parent_name),
                'chinese_translation' => replaceNames($row['chinese_translation'] ?? '', $child_name, $parent_name),
                'tagalog_translation' => replaceNames($row['tagalog_translation'] ?? '', $child_name, $parent_name),
                'scene_description' => replaceNames($row['scene_description'] ?? '', $child_name, $parent_name),
                'scene_text_en' => replaceNames($row['scene_text_en'] ?? '', $child_name, $parent_name),
                'scene_text_zh' => replaceNames($row['scene_text_zh'] ?? '', $child_name, $parent_name),
                'scene_text_tl' => replaceNames($row['scene_text_tl'] ?? '', $child_name, $parent_name),
                'dialogue_data' => $dialogue_data,
                'papa_mama_point_ja' => replaceNames($row['papa_mama_point_ja'] ?? '', $child_name, $parent_name),
                'papa_mama_point_en' => replaceNames($row['papa_mama_point_en'] ?? '', $child_name, $parent_name),
                'papa_mama_point_zh' => replaceNames($row['papa_mama_point_zh'] ?? '', $child_name, $parent_name),
                'papa_mama_point_tl' => replaceNames($row['papa_mama_point_tl'] ?? '', $child_name, $parent_name),
                'video_url' => $row['video_url'] ?? '',
                'video_filename' => $row['video_filename'] ?? '',
                'pronunciation' => !empty($row['pronunciation']) ? json_decode($row['pronunciation'], true) : [],
                'practice_romaji' => replaceNames($row['practice_romaji'] ?? '', $child_name, $parent_name)
            ];
        }
        
        return $lesson_data;
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        return null;
    }
}

// ユーザー情報から名前を取得
$child_name = $user['child_name'] ?? '';
$parent_name = $user['parent_name'] ?? '';

$lesson_data = getLessonData($pdo, $lesson_id, $sub_lesson_id, $child_name, $parent_name);

// デフォルトレッスンデータ（データベースにデータがない場合）
if (!$lesson_data) {
    if ($lesson_id == 1 && $sub_lesson_id == 2) {
        // Lesson1_2用のデータ
        $lesson_data = [
            'title' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id,
            'lesson_number' => $lesson_id,
            'sub_lesson_number' => $sub_lesson_id,
            'content' => [
                'miru' => [
                    'japanese_text' => 'おはようございます。',
                    'translation' => 'Good morning.',
                    'scene_description' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'の学習場面です。',
                    'dialogue_data' => [
                        ['speaker' => 'Student', 'japanese' => 'せんせい、おはようございます。', 'translation' => 'Good morning, teacher.'],
                        ['speaker' => 'Teacher', 'japanese' => 'おはようございます。', 'translation' => 'Good morning.']
                    ]
                ],
                'yatte' => [
                    'japanese_text' => 'おはようございます。',
                    'translation' => 'Good morning.',
                    'pronunciation' => ['o', 'ha', 'yo', 'u', 'go', 'za', 'i', 'ma', 'su'],
                    'instruction_text' => 'この表現を練習してみましょう。',
                    'practice_romaji' => 'O ha yo u go za i ma su'
                ],
                'dekita' => [
                    'japanese_text' => 'おはようございます。',
                    'translation' => 'Good morning.',
                    'completion_message' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'のポイントを覚えましょう。'
                ]
            ]
        ];
    } elseif ($lesson_id == 1 && $sub_lesson_id == 3) {
        // Lesson1_3用のデータ
        $lesson_data = [
            'title' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id,
            'lesson_number' => $lesson_id,
            'sub_lesson_number' => $sub_lesson_id,
            'content' => [
                'miru' => [
                    'japanese_text' => 'さようなら。',
                    'translation' => 'Goodbye.',
                    'scene_description' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'の学習場面です。',
                    'dialogue_data' => [
                        ['speaker' => 'Student', 'japanese' => 'せんせい、さようなら。', 'translation' => 'Goodbye, teacher.'],
                        ['speaker' => 'Teacher', 'japanese' => 'さようなら。', 'translation' => 'Goodbye.']
                    ]
                ],
                'yatte' => [
                    'japanese_text' => 'せんせい、さようなら。',
                    'translation' => 'Goodbye, teacher.',
                    'pronunciation' => ['se', 'n', 'se', 'i', 'sa', 'yo', 'u', 'na', 'ra'],
                    'instruction_text' => 'この表現を練習してみましょう。',
                    'practice_romaji' => 'Se n se i, Sa yo u na ra'
                ],
                'dekita' => [
                    'japanese_text' => 'せんせい、さようなら。',
                    'translation' => 'Goodbye, teacher.',
                    'completion_message' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'のポイントを覚えましょう。'
                ]
            ]
        ];
    } else {
        // その他のレッスン用のデフォルトデータ
        $lesson_data = [
            'title' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id,
            'lesson_number' => $lesson_id,
            'sub_lesson_number' => $sub_lesson_id,
            'content' => [
                'miru' => [
                    'japanese_text' => 'こんにちは。',
                    'translation' => 'Hello.',
                    'scene_description' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'の学習場面です。',
                    'dialogue_data' => [
                        ['speaker' => 'Student', 'japanese' => 'こんにちは。', 'translation' => 'Hello.'],
                        ['speaker' => 'Teacher', 'japanese' => 'こんにちは。', 'translation' => 'Hello.']
                    ]
                ],
                'yatte' => [
                    'japanese_text' => 'こんにちは。',
                    'translation' => 'Hello.',
                    'pronunciation' => ['ko', 'n', 'ni', 'chi', 'wa'],
                    'instruction_text' => 'この表現を練習してみましょう。',
                    'practice_romaji' => 'Ko n ni chi wa'
                ],
                'dekita' => [
                    'japanese_text' => 'こんにちは。',
                    'translation' => 'Hello.',
                    'completion_message' => 'レッスン' . $lesson_id . '-' . $sub_lesson_id . 'のポイントを覚えましょう。'
                ]
            ]
        ];
    }
}
$page_title = 'LESSON' . $lesson_id . '_' . $sub_lesson_id . ' - ' . $lesson_data['title'] . ' - nihongonote';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- 共通カラーパレット（性別による色分けを無効化） -->
    <style>
    :root {
        --primary-color: #4ECDC4;
        --primary-light: #7ED7D1;
        --primary-dark: #3BB8B0;
        --accent-color: #FF6B35;
        --background: white;
        --card-background: white;
    }
    body {
        background: white !important;
    }
    </style>
</head>
<body data-lang="<?= $current_language ?>" data-current-lang="<?= $current_language ?>">

<!-- 上部背景画像は CSS で表示されるため、この img 要素は削除 -->

<!-- 言語切り替えタブ -->
<div class="language-tabs-global">
    <div class="language-tab <?= $current_language === 'ja' ? 'active' : '' ?>" data-lang="ja" onclick="switchLanguage('ja')">日本語</div>
    <div class="language-tab <?= $current_language === 'en' ? 'active' : '' ?>" data-lang="en" onclick="switchLanguage('en')">English</div>
    <div class="language-tab <?= $current_language === 'zh' ? 'active' : '' ?>" data-lang="zh" onclick="switchLanguage('zh')">中文</div>
    <div class="language-tab <?= $current_language === 'tl' ? 'active' : '' ?>" data-lang="tl" onclick="switchLanguage('tl')">Tagalog</div>
</div>

<!-- 中央ロゴ -->
<div class="top-logo-center" id="topLogo">
    <?php
    // 現在の言語に応じたロゴを表示
    $logo_files = [
        'en' => 'ralango_logo_en.png',
        'zh' => 'ralango_logo_zh.png', 
        'ja' => 'ralango_logo_jp.png'
    ];
    $logo_file = $logo_files[$current_language] ?? $logo_files['ja'];
    ?>
    <img src="../assets/images/<?= $logo_file ?>" alt="nihongonote" class="top-logo-image" id="topLogoImg">
</div>

<!-- 右上の子供の情報 -->
<div class="child-info-top">
    <div class="user-details-top">
        <div class="user-info-row">
            <span class="user-name">name: <?= h($user['child_name']) ?></span>
            <span class="user-lang">lang: <?= h($current_language) ?></span>
        </div>
        <div class="button-row">
            <a href="<?= getBasePath('index.php') ?>" class="menu-return-btn-small"><?= getButtonText('menu_return', $current_language) ?></a>
            <a href="<?= getBasePath('auth/logout.php') ?>" class="logout-button"><?= getButtonText('stop', $current_language) ?></a>
        </div>
    </div>
</div>

<div class="container">

    <div class="lesson-container">
        <div class="lesson-header">
            <div class="lesson-title">LESSON<?= $lesson_id ?>_<?= $sub_lesson_id ?></div>
            <?php 
            // 名前置換関数（既に上部で$child_name, $parent_nameは定義済み）
            function replaceNames($text, $child_name, $parent_name) {
                return str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $text);
            }
            
            // データベースからsubtitleを取得
            // デバッグ: subtitleの値を確認
            // echo "Debug: subtitle = '" . ($lesson_data['subtitle'] ?? 'NULL') . "'<br>";
            if (!empty($lesson_data['subtitle'])) {
                $japanese_title = str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $lesson_data['subtitle']);
            } else {
                $japanese_title = $lesson_data['title'];
            }

            // subtitle翻訳を取得
            $subtitle_translation = '';
            if ($current_language === 'en' && !empty($lesson_data['subtitle_en'])) {
                $subtitle_translation = str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $lesson_data['subtitle_en']);
            } elseif ($current_language === 'zh' && !empty($lesson_data['subtitle_zh'])) {
                $subtitle_translation = str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $lesson_data['subtitle_zh']);
            }
            ?>
            <div class="lesson-subtitle"><?= h($japanese_title) ?></div>
            <?php if ($subtitle_translation): ?>
            <div class="lesson-subtitle-en"><?= h($subtitle_translation) ?></div>
            <?php endif; ?>
        </div>

        <div class="lesson-tabs" data-active="1">
            <div class="tab active" onclick="showTab('miru')"><?= h($current_tab_text['miru']) ?></div>
            <div class="tab" onclick="showTab('yatte')"><?= h($current_tab_text['yatte']) ?></div>
            <div class="tab" onclick="showTab('dekita')"><?= h($current_tab_text['dekita']) ?></div>
        </div>

        <!-- A みる -->
        <div id="miruContent" class="lesson-content">
            <div class="split-layout">
                <div class="left-section">
                    <div class="video-section">
                        <?php if (!empty($lesson_data['content']['miru']['video_filename'])): ?>
                            <video id="lessonVideo" controls width="100%" style="max-width: 600px; height: auto; object-fit: fill;">
                                <?php $video_path = "../assets/videos/" . $lesson_data['content']['miru']['video_filename']; 
                                      $video_mtime = file_exists($video_path) ? filemtime($video_path) : time(); ?>
                                <source src="../assets/videos/<?= h($lesson_data['content']['miru']['video_filename']) ?>?v=<?= $video_mtime ?>" type="video/mp4">
                                お使いのブラウザは動画再生に対応していません。
                            </video>
                        <?php else: ?>
                            <div class="video-placeholder">
                                <div class="play-button" onclick="playVideo()"></div>
                            </div>
                        <?php endif; ?>
                        <div class="scene-description">
                            <?php
                            // データベースから場面説明を取得（多言語対応）
                            $child_name = $user['child_name'] ?? '';
                            $parent_name = $user['parent_name'] ?? '';
                            
                            // 日本語の場面説明を取得
                            $scene_text_jp = $lesson_data['content']['miru']['scene_description'] ?? '';
                            if ($scene_text_jp) {
                                $scene_text_jp = str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $scene_text_jp);
                            }
                            
                            // 翻訳の場面説明を取得
                            $scene_translation = '';
                            if ($current_language == 'en' && !empty($lesson_data['content']['miru']['scene_text_en'])) {
                                $scene_translation = $lesson_data['content']['miru']['scene_text_en'];
                            } elseif ($current_language == 'zh' && !empty($lesson_data['content']['miru']['scene_text_zh'])) {
                                $scene_translation = $lesson_data['content']['miru']['scene_text_zh'];
                            } elseif ($current_language == 'tl' && !empty($lesson_data['content']['miru']['scene_text_tl'])) {
                                $scene_translation = $lesson_data['content']['miru']['scene_text_tl'];
                            }
                            
                            // 翻訳にも名前の置換を適用
                            if ($scene_translation) {
                                $scene_translation = str_replace(['（子供の名前）', '（保護者の名前）'], [$child_name, $parent_name], $scene_translation);
                            }
                            
                            $user_lang = $current_language;
                            
                            ?>
                            <!-- 常に日本語を表示 -->
                            <div class="scene-text-jp"><?= h($scene_text_jp) ?></div>
                            <!-- 翻訳がある場合は追加で表示 -->
                            <?php if (!empty($scene_translation) && $current_language != 'ja'): ?>
                                <div class="scene-text-native"><?= h($scene_translation) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="right-section">
                    <div class="dialogue-section">
                    <?php foreach ($lesson_data['content']['miru']['dialogue_data'] ?? [] as $dialogue): 
                        // レッスンに応じた挨拶タイプを判定
                        if ($lesson_id == 1 && $sub_lesson_id == 1) {
                            $greeting_type = 'casual_morning';
                        } elseif ($lesson_id == 1 && $sub_lesson_id == 2) {
                            $greeting_type = 'formal_morning';
                        } elseif ($lesson_id == 1 && $sub_lesson_id == 3) {
                            $greeting_type = 'goodbye';
                        } elseif ($lesson_id == 2 && $sub_lesson_id == 1) {
                            $greeting_type = 'self_introduction';
                        } else {
                            $greeting_type = 'casual_morning';
                        }
                        
                        // 日本語の挨拶を表示するかどうか判定
                        if ($dialogue['speaker'] == 'You' || $dialogue['speaker'] == 'あなた') {
                            // L2-1の場合は特別な処理
                            if ($lesson_id == 2 && $sub_lesson_id == 1) {
                                $child_name = $user['child_name'] ?? '';
                                $japanese_text = "わたしは、{$child_name}です。";
                                if ($current_language == 'en') {
                                    $translated_greeting = "I am {$child_name}.";
                                } elseif ($current_language == 'zh') {
                                    $translated_greeting = "我叫{$child_name}。";
                                } else {
                                    $translated_greeting = "わたしは、{$child_name}です。";
                                }
                            } else {
                                // あなたの発言は元の日本語テキストを表示
                                $japanese_text = $dialogue['japanese'];
                                if ($current_language == 'zh' && isset($dialogue['translation_zh'])) {
                                    $translated_greeting = $dialogue['translation_zh'];
                                } elseif ($current_language == 'en' && isset($dialogue['translation'])) {
                                    $translated_greeting = $dialogue['translation'];
                                } else {
                                    $translated_greeting = $greeting_translations[$greeting_type][$current_language] ?? $dialogue['translation'];
                                }
                            }
                        } elseif ($dialogue['speaker'] == 'Friend' || $dialogue['speaker'] == 'ともだち') {
                            // L2-1の場合は特別な処理
                            if ($lesson_id == 2 && $sub_lesson_id == 1) {
                                $japanese_text = "わたしは、しょうたです。";
                                if ($current_language == 'en') {
                                    $translated_greeting = "I am Shota.";
                                } elseif ($current_language == 'zh') {
                                    $translated_greeting = "我叫 Shota。";
                                } else {
                                    $translated_greeting = "わたしは、しょうたです。";
                                }
                            } else {
                                // Friendの発言も言語別翻訳を適用
                                $japanese_text = $dialogue['japanese'];
                                if ($current_language == 'zh' && isset($dialogue['translation_zh'])) {
                                    $translated_greeting = $dialogue['translation_zh'];
                                } elseif ($current_language == 'en' && isset($dialogue['translation'])) {
                                    $translated_greeting = $dialogue['translation'];
                                } else {
                                    $translated_greeting = $greeting_translations[$greeting_type][$current_language] ?? $dialogue['translation'];
                                }
                            }
                        } else {
                            // Teacherの発言
                            if ($greeting_type == 'formal_morning') {
                                $japanese_text = 'おはようございます。';
                            } elseif ($greeting_type == 'goodbye') {
                                $japanese_text = 'さようなら。';
                            } else {
                                $japanese_text = $dialogue['japanese'];
                            }
                            
                            // Teacher用の翻訳
                            $teacher_translations = [
                                'formal_morning' => [
                                    'ja' => 'おはようございます。',
                                    'en' => 'Good morning.',
                                    'ko' => '안녕하세요.',
                                    'zh' => '早上好。',
                                    'es' => 'Buenos días.',
                                    'pt' => 'Bom dia.',
                                    'fr' => 'Bonjour.',
                                    'de' => 'Guten Morgen.',
                                    'it' => 'Buongiorno.',
                                    'ru' => 'Доброе утро.',
                                    'ar' => 'صباح الخير.',
                                    'hi' => 'सुप्रभात।',
                                    'th' => 'สวัสดี',
                                    'vi' => 'Chào em.',
                                    'tl' => 'Magandang umaga po.',
                                    'ne' => 'नमस्कार।'
                                ],
                                'goodbye' => [
                                    'ja' => 'さようなら。',
                                    'en' => 'Goodbye.',
                                    'ko' => '안녕히 가세요.',
                                    'zh' => '再见。',
                                    'es' => 'Adiós.',
                                    'pt' => 'Tchau.',
                                    'fr' => 'Au revoir.',
                                    'de' => 'Auf Wiedersehen.',
                                    'it' => 'Arrivederci.',
                                    'ru' => 'До свидания.',
                                    'ar' => 'مع السلامة.',
                                    'hi' => 'अलविदा।',
                                    'th' => 'ลาก่อน',
                                    'vi' => 'Tạm biệt.',
                                    'tl' => 'Paalam!',
                                    'ne' => 'नमस्कार।'
                                ]
                            ];
                            $translated_greeting = $teacher_translations[$greeting_type][$current_language] ?? $dialogue['translation'];
                        }
                    ?>
                        <div class="text-section">
                            <div class="text-label"><?= 
                                ($dialogue['speaker'] == 'You' || $dialogue['speaker'] == 'あなた') ? h($current_you) : 
                                (($dialogue['speaker'] == 'Friend' || $dialogue['speaker'] == 'ともだち') ? h($current_friend) : 
                                ($dialogue['speaker'] == 'Teacher' ? h($current_teacher) : h($dialogue['speaker']))) 
                            ?></div>
                            <div class="japanese-text"><?= h($japanese_text) ?></div>
                            <div class="translation-text"><?= h($translated_greeting) ?></div>
                        </div>
                    <?php endforeach; ?>
                        <button class="next-button" onclick="showTab('yatte')">
                            <?= h($current_next_text) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- B やってみる -->
        <div id="yatteContent" class="lesson-content hidden">
            <div class="split-layout">
                <div class="left-section">
                    <div class="video-section">
                        <?php if (!empty($lesson_data['content']['yatte']['video_filename'])): ?>
                            <video id="practiceVideo" controls width="100%" style="max-width: 600px; height: auto; object-fit: fill;">
                                <?php $video_path = "../assets/videos/" . $lesson_data['content']['yatte']['video_filename']; 
                                      $video_mtime = file_exists($video_path) ? filemtime($video_path) : time(); ?>
                                <source src="../assets/videos/<?= h($lesson_data['content']['yatte']['video_filename']) ?>?v=<?= $video_mtime ?>" type="video/mp4">
                                お使いのブラウザは動画再生に対応していません。
                            </video>
                        <?php else: ?>
                            <div class="video-placeholder">
                                <div class="play-button" onclick="playVideo()"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="right-section">
                    <div class="practice-section">
                    <div class="text-section">
                        <div class="text-label"><?= h($current_lets_say_it) ?></div>
                        <div class="pronunciation-guide">
                            <?php if (!empty($lesson_data['content']['yatte']['practice_romaji'])): ?>
                                <div style="font-size: 1.2em; color: #666; margin: 10px 0;">
                                    <?= h($lesson_data['content']['yatte']['practice_romaji']) ?>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($lesson_data['content']['yatte']['pronunciation'] ?? [] as $pronunciation): ?>
                                <span><?= h($pronunciation) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="japanese-text"><?= h($lesson_data['content']['yatte']['japanese_text'] ?? '') ?></div>
                        <div class="translation-text">
                            <?php
                            // やってみるセクションの翻訳（言語に応じて選択）
                            if ($current_language == 'zh' && !empty($lesson_data['content']['yatte']['chinese_translation'])) {
                                $yatte_translation = $lesson_data['content']['yatte']['chinese_translation'];
                            } elseif ($current_language == 'tl' && !empty($lesson_data['content']['yatte']['tagalog_translation'])) {
                                $yatte_translation = $lesson_data['content']['yatte']['tagalog_translation'];
                            } else {
                                $yatte_translation = $lesson_data['content']['yatte']['translation'] ?? '';
                            }
                            echo h($yatte_translation);
                            ?>
                        </div>
                        <button class="tts-button" onclick="playPracticeVideo()">
                            <?= h($current_tts_button) ?>
                        </button>
                    </div>
                        <button class="next-button" onclick="showTab('dekita')">
                            <?= h($current_next_text) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- C できた -->
        <div id="dekitaContent" class="lesson-content hidden">
            <div class="split-layout">
                <div class="left-section">
                    <div class="badge-container" style="text-align: center; margin: 50px auto;">
                        <?php 
                        // 新しいバッジパスパターンを使用
                        $badge_path = "../assets/images/badge/generated/badge_L{$lesson_id}_{$sub_lesson_id}.png";
                        ?>
                        <img src="<?= $badge_path ?>" 
                             alt="Badge L<?= $lesson_id ?>_<?= $sub_lesson_id ?>" 
                             class="badge-image animated-badge"
                             style="max-width: 200px; height: auto; box-shadow: 0 6px 25px rgba(0,0,0,0.4);"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="placeholder-badge" style="display: none; width: 200px; height: 200px; background: #ddd; justify-content: center; align-items: center; font-size: 1.5em; color: #999;">
                            画像なし
                        </div>
                        <div class="badge-ribbon" style="margin-top: 10px; font-size: 0.9em; color: #666;">Lesson<?= $lesson_id ?>_<?= $sub_lesson_id ?></div>
                    </div>
                </div>
                <div class="right-section">
                    <div class="text-section">
                        <div class="text-label" style="font-size: 1.5em; color: #4CAF50;"><?= h($current_you_learned) ?></div>
                        <?php if (isset($lesson_data['content']['dekita'])): ?>
                        <div class="japanese-text"><?= h($lesson_data['content']['dekita']['japanese_text'] ?? '') ?></div>
                        <div class="translation-text">
                            <?php
                            // できたセクションの翻訳（言語に応じて選択）
                            if ($current_language == 'zh' && !empty($lesson_data['content']['dekita']['chinese_translation'])) {
                                $dekita_translation = $lesson_data['content']['dekita']['chinese_translation'];
                            } elseif ($current_language == 'tl' && !empty($lesson_data['content']['dekita']['tagalog_translation'])) {
                                $dekita_translation = $lesson_data['content']['dekita']['tagalog_translation'];
                            } else {
                                $dekita_translation = $lesson_data['content']['dekita']['translation'] ?? '';
                            }
                            echo h($dekita_translation);
                            ?>
                        </div>
                        <?php endif; ?>
                        <button class="next-button" onclick="showBadgeModal()"><?= h($current_next_text) ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- パパ・ママポイントボタン（containerの上に配置） -->
    <button class="container-point-button" onclick="showLessonPoint()">
        <span class="point-icon">💡</span>
        <span class="point-text"><?= $current_papa_mama_text ?></span>
    </button>
</div>

<!-- ポイントカード（全体共通） -->
<div id="lessonPointCard" class="lesson-point-card hidden">
    <div class="point-content">
        <h4>L<?= $lesson_id ?>-<?= $sub_lesson_id ?><?= h($current_point_title) ?></h4>
        <div class="point-text"></div>
    </div>
    <button class="close-point-btn" onclick="hideLessonPoint()">×</button>
</div>

<!-- バッジモーダル -->
<div id="badgeModal" class="badge-modal">
    <div class="badge-content">
        <div class="badge-title"><?= h($current_you_got_badge) ?></div>
        <div class="modal-badge-container" style="text-align: center;">
            <?php 
            // モーダル用の新しいバッジパスパターンを使用
            $modal_badge_path = "../assets/images/badge/generated/badge_L{$lesson_id}_{$sub_lesson_id}.png";
            ?>
            <img src="<?= $modal_badge_path ?>" 
                 alt="Badge L<?= $lesson_id ?>_<?= $sub_lesson_id ?>" 
                 class="badge-image modal-badge-animation"
                 style="max-width: 180px; height: auto; box-shadow: 0 6px 25px rgba(0,0,0,0.4);"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="placeholder-badge" style="display: none; width: 180px; height: 180px; background: #ddd; justify-content: center; align-items: center; font-size: 1.4em; color: #999;">
                画像なし
            </div>
            <div class="badge-ribbon" style="margin-top: 10px; font-size: 0.9em; color: #666;">Lesson<?= $lesson_id ?>_<?= $sub_lesson_id ?></div>
        </div>
        <div class="badge-message">
            <p><?= h($current_lesson_prefix) ?><?= $lesson_id ?>_<?= $sub_lesson_id ?><?= h($current_completion) ?></p>
            <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                <?= h($current_lesson_content[$lesson_data['title']] ?? $lesson_data['title']) ?><?= h($current_mastered) ?>
            </p>
        </div>
        <button class="next-button" onclick="closeBadgeModal()"><?= h($current_next_text) ?></button>
    </div>
</div>

<!-- カスタム確認モーダル -->
<div id="confirmModal" class="confirm-modal hidden">
    <div class="confirm-content">
        <div class="confirm-message" id="confirmMessage"></div>
        <div class="confirm-buttons">
            <button class="confirm-cancel-btn" id="confirmCancel"><?= h($current_cancel_text) ?></button>
            <button class="confirm-ok-btn" id="confirmOk"><?= h($current_ok_text) ?></button>
        </div>
    </div>
</div>

<script>
// シンプルなJavaScript機能
function showTab(tabName) {
    // すべてのタブを非アクティブに
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // すべてのコンテンツを非表示に
    document.querySelectorAll('.lesson-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // 対応するタブとコンテンツをアクティブに
    const contentMap = {
        'miru': 'miruContent',
        'yatte': 'yatteContent',
        'dekita': 'dekitaContent'
    };
    
    const tabIndex = ['miru', 'yatte', 'dekita'].indexOf(tabName);
    const tabs = document.querySelectorAll('.tab');
    if (tabs[tabIndex]) {
        tabs[tabIndex].classList.add('active');
    }
    
    const targetContent = document.getElementById(contentMap[tabName]);
    if (targetContent) {
        targetContent.classList.remove('hidden');
    }
}

function playVideo() {
    const currentTab = document.querySelector('.tab.active');
    let videoId;
    
    if (currentTab && currentTab.textContent.includes('みる')) {
        videoId = 'lessonVideo';
    } else if (currentTab && currentTab.textContent.includes('やってみる')) {
        videoId = 'practiceVideo';
    }
    
    const video = document.getElementById(videoId);
    if (video) {
        video.play();
    } else {
        alert('動画を再生します（デモ版）');
    }
}

function playTTS(text) {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'ja-JP';
        utterance.rate = 0.8;
        utterance.pitch = 1.1;
        speechSynthesis.speak(utterance);
    } else {
        alert('お使いのブラウザは音声機能に対応していません。');
    }
}

function playPracticeVideo() {
    const video = document.getElementById('practiceVideo');
    if (video) {
        video.play();
    } else {
        // 動画がない場合はTTSを再生
        const japaneseText = document.querySelector('#yatteContent .japanese-text');
        if (japaneseText) {
            playTTS(japaneseText.textContent);
        }
    }
}

function showBadgeModal() {
    // 進捗とバッジを保存
    saveProgress();
    
    const modal = document.getElementById('badgeModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// 進捗保存関数
function saveProgress() {
    const lessonId = <?= $lesson_id ?>;
    const subLessonId = <?= $sub_lesson_id ?>;
    const step = 'dekita';
    
    const data = {
        lesson_id: lessonId,
        sub_lesson_id: subLessonId,
        step: step
    };
    
    fetch('../api/save_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('Progress and badge saved successfully');
        } else {
            console.error('Failed to save progress:', result.error);
        }
    })
    .catch(error => {
        console.error('Error saving progress:', error);
    });
}

function closeBadgeModal() {
    const modal = document.getElementById('badgeModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // 次のサブレッスンまたはレッスンに進む
    const currentLessonId = <?= $lesson_id ?>;
    const currentSubLessonId = <?= $sub_lesson_id ?>;
    
    const lessonPrefix = '<?= h($current_lesson_prefix) ?>';
    const progressConfirm = '<?= h($current_progress_confirm) ?>';
    
    // カスタム確認ダイアログ関数
    function showConfirmModal(message, onConfirm, onCancel) {
        const modal = document.getElementById('confirmModal');
        const messageEl = document.getElementById('confirmMessage');
        const okBtn = document.getElementById('confirmOk');
        const cancelBtn = document.getElementById('confirmCancel');
        
        messageEl.textContent = message;
        modal.classList.remove('hidden');
        
        // イベントリスナーをクリア
        okBtn.onclick = null;
        cancelBtn.onclick = null;
        
        okBtn.onclick = function() {
            modal.classList.add('hidden');
            onConfirm();
        };
        
        cancelBtn.onclick = function() {
            modal.classList.add('hidden');
            if (onCancel) onCancel();
        };
    }
    
    if (currentSubLessonId < 3) {
        // 同じレッスンの次のサブレッスンに進む
        const nextSubId = currentSubLessonId + 1;
        showConfirmModal(
            `${lessonPrefix}${currentLessonId}_${nextSubId}${progressConfirm}`,
            () => { window.location.href = `lesson.php?id=${currentLessonId}&sub=${nextSubId}`; },
            () => { window.location.href = 'curriculum.php'; }
        );
    } else if (currentLessonId < 20) {
        // 次のレッスンの最初のサブレッスンに進む
        const nextLessonId = currentLessonId + 1;
        showConfirmModal(
            `${lessonPrefix}${nextLessonId}_1${progressConfirm}`,
            () => { window.location.href = `lesson.php?id=${nextLessonId}&sub=1`; },
            () => { window.location.href = 'curriculum.php'; }
        );
    } else {
        alert('おめでとうございます！全てのレッスンを完了しました！');
        window.location.href = 'curriculum.php';
    }
}

// ESCキーでモーダルを閉じる
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeBadgeModal();
    }
});

// レッスンポイントの表示・非表示
function showLessonPoint() {
    const lessonId = <?= $lesson_id ?>;
    const subLessonId = <?= $sub_lesson_id ?>;
    const pointCard = document.getElementById('lessonPointCard');
    const pointText = pointCard.querySelector('.point-text');
    
    // DBから取得したパパママポイント
    const dbPapamaPoint = {
        'ja': <?= json_encode($lesson_data['content']['miru']['papa_mama_point_ja'] ?? '') ?>,
        'en': <?= json_encode($lesson_data['content']['miru']['papa_mama_point_en'] ?? '') ?>,
        'zh': <?= json_encode($lesson_data['content']['miru']['papa_mama_point_zh'] ?? '') ?>,
        'tl': <?= json_encode($lesson_data['content']['miru']['papa_mama_point_tl'] ?? '') ?>
    };
    
    // 各レッスンのポイントテキスト（多言語対応）
    const lessonPoints = {
        '1_1': {
            'ja': `朝、児童が登校するとき、友達に「おはよう！」（Good morning!）と言います。
では、このあいさつを練習してみましょう！
あなたが子どもの友達役になります。子どもが友達に会う様子をしたら、「おはよう！」と言います。
子どもが「おはよう！」と言ったら、友達役のあなたも「おはよう！」と答えます。`,
            'en': `When children arrive at school in the morning, they say "Ohayo!" (Good morning!) to friends.
So, let's practice this greeting!
You play the role of your child's friend and pretend you're meeting each other while saying, "Ohayo!"
When your child says "Ohayo!" you, as their friend, respond by saying "Ohayo!"`,
            'tl': `Kapag dumarating ang mga bata sa paaralan sa umaga, sinasabi nila ang "Ohayo!" (Magandang umaga!) sa kanilang mga kaibigan.
Kaya, magpraktis tayo ng pagbating ito!
Gumanap kayo bilang kaibigan ng inyong anak at magpanggap na nagkikita kayo habang sinasabi ang "Ohayo!"
Kapag sinabi ng inyong anak na "Ohayo!", kayo bilang kaibigan ay sasagot din ng "Ohayo!"`,
            'ko': `아침에 아이들이 등교할 때 친구에게 "안녕!"이라고 인사합니다.
이 인사를 연습해봅시다!
당신이 아이의 친구 역할을 해보세요. 아이가 당신을 만나면 "안녕!"이라고 말하세요.
아이가 "안녕!"이라고 하면, 친구 역할인 당신도 "안녕!"이라고 답하세요.`,
            'zh': `早晨，孩子上学时会对朋友说"早上好！（Ohayou）"。
那么，我们来练习这个问候吧！
你来扮演孩子的朋友。当孩子见到朋友时，就说"早上好！（Ohayou）"。
当孩子说"早上好！（Ohayou）"时，扮演朋友的你也要回应"早上好！（Ohayou）"。`,
            'vi': `Vào buổi sáng, khi trẻ em đi học, chúng nói "Chào buổi sáng!" với bạn bè.
Hãy luyện tập lời chào này!
Bạn đóng vai bạn của đứa trẻ. Khi đứa trẻ gặp bạn, hãy nói "Chào buổi sáng!"
Khi đứa trẻ nói "Chào buổi sáng!", bạn với vai trò là bạn cũng nên trả lời "Chào buổi sáng!"`,
            'es': `Por la mañana, cuando los niños van a la escuela, dicen "¡Buenos días!" a sus amigos.
¡Practiquemos este saludo!
Tú juegas el papel del amigo del niño. Cuando el niño te encuentre, di "¡Buenos días!"
Cuando el niño diga "¡Buenos días!", tú como amigo también debes responder "¡Buenos días!"`,
            'pt': `De manhã, quando as crianças vão para a escola, dizem "Bom dia!" aos amigos.
Vamos praticar essa saudação!
Você faz o papel do amigo da criança. Quando a criança te encontrar, diga "Bom dia!"
Quando a criança disser "Bom dia!", você como amigo também deve responder "Bom dia!"`,
            'tl': `Sa umaga, kapag pumapasok ang mga bata sa paaralan, sinasabi nila ang "Magandang umaga!" sa kanilang mga kaibigan.
Magsanay tayo ng pagbati na ito!
Ikaw ay maging kaibigan ng bata. Kapag nakita ka ng bata, sabihin ang "Magandang umaga!"
Kapag sinabi ng bata ang "Magandang umaga!", ikaw bilang kaibigan ay dapat ding sumagot ng "Magandang umaga!"`
        },
        '1_2': {
            'ja': `学校の校門の前では先生が生徒を待っています。
先生には「せんせい、おはようございます。」（Good morning, Teacher.）と言います。
「おはよう＋ございます」は、年上の人や目上の人に対して使う丁寧なあいさつです。
では、このあいさつを練習してみましょう！
あなたは先生役として、校門に立っている様子をします。子どもがあなたのところに来たら、「せんせい、おはようございます。」と言います。
先生役のあなたは、笑顔で「おはようございます。」と答えます。`,
            'en': `Teachers wait for students in front of the school gate.
The teacher is greeted with "Sensei, ohayogozaimasu." (Good morning, Teacher.)
"Ohayo" + "gozaimasu" is a polite greeting used for older or higher status people.
So, let's practice this greeting!
You pretend to be the teacher waiting at the school gate, and when your child approaches you, they'll say, "Sensei, ohayogozaimasu."
You, as the teacher, will reply with a smile, "Ohayogozaimasu."`,
            'tl': `Naghihintay ang mga guro sa harap ng gate ng paaralan.
Binabati ang guro ng, "Sensei, ohayou gozaimasu." (Magandang umaga, Ms. Sato.)
Ang "Ohayo" + "gozaimasu" ay isang magalang na pagbati na ginagamit para sa mas nakatatanda o mas mataas ang katayuan.

Kaya, magpraktis tayo ng pagbating ito!
Magpanggap ka na ikaw ang guro na naghihintay sa gate ng paaralan, at kapag lumapit ang iyong anak, sasabihin niya: "Sensei, ohayou gozaimasu."
Ikaw naman, bilang guro, ay sasagot nang may ngiti: "Ohayou gozaimasu."`,
            'ko': `학교 정문 앞에서 선생님이 학생들을 기다리고 있습니다.
선생님에게는 "선생님, 안녕하세요."라고 말합니다.
"오하요 고자이마스"는 연장자나 윗사람에게 사용하는 정중한 인사입니다.
이 인사를 연습해봅시다!
당신이 선생님 역할을 하며 교문에 서 있어보세요. 아이가 당신에게 오면 "선생님, 안녕하세요."라고 말합니다.
선생님 역할인 당신은 미소로 "안녕하세요."라고 답하세요.`,
            'zh': `在学校校门前，老师正等着儿童。
要对老师说"老师，早上好。"（Sensei Ohayougozaimasu）
"おはよう＋ございます"是用于年长者或上级人士的敬语问候。
那么，我们来练习这个问候吧！
你扮演老师的角色，站在校门口。当孩子走到你面前时，对你说"老师，早上好。（Sensei Ohayougozaimasu）"。
扮演老师的你，请微笑着回应："早上好。（Ohayougozaimasu）"。`,
            'vi': `Giáo viên đang đợi học sinh ở cổng trường.
Nói với giáo viên "Chào cô/thầy."
"Ohayou gozaimasu" là lời chào lịch sự dành cho người lớn tuổi và cấp trên.
Hãy luyện tập lời chào này!
Bạn đóng vai giáo viên, đứng ở cổng trường. Khi đứa trẻ đến gặp bạn, chúng nói "Chào cô/thầy."
Với vai trò giáo viên, bạn mỉm cười và trả lời "Chào em."`,
            'es': `Un maestro está esperando a los estudiantes en la puerta de la escuela.
Di al maestro "Buenos días, maestro."
"Ohayou gozaimasu" es un saludo cortés usado para personas mayores y superiores.
¡Practiquemos este saludo!
Tú haces el papel del maestro, parado en la puerta de la escuela. Cuando el niño venga a ti, dice "Buenos días, maestro."
Como maestro, sonríes y respondes "Buenos días."`,
            'pt': `Um professor está esperando os alunos no portão da escola.
Diga ao professor "Bom dia, professor."
"Ohayou gozaimasu" é uma saudação educada usada para pessoas mais velhas e superiores.
Vamos praticar essa saudação!
Você faz o papel do professor, parado no portão da escola. Quando a criança vier até você, diga "Bom dia, professor."
Como professor, você sorri e responde "Bom dia."`,
            'tl': `May guro na naghihintay sa mga estudyante sa gate ng paaralan.
Sabihin sa guro ang "Magandang umaga po, guro."
Ang "Ohayou gozaimasu" ay magalang na pagbati na ginagamit para sa matatanda at mga nakatataas.
Magsanay tayo ng pagbati na ito!
Ikaw ay maging guro, nakatayo sa gate ng paaralan. Kapag lumapit sa iyo ang bata, sasabihin niya ang "Magandang umaga po, guro."
Bilang guro, ngumiti ka at sumagot ng "Magandang umaga."`
        },
        '1_3': {
            'ja': `下校するとき、友達に「さようなら！」（Goodbye!）と言います。
「さようなら」は、一日の終わりのあいさつです。
では、このあいさつを練習してみましょう！
あなたが子どもの友達役になります。子どもが帰る様子をしたら、「さようなら！」と言います。
子どもが「さようなら！」と言ったら、友達役のあなたも「さようなら！」と答えます。`,
            'en': `When leaving school, "Sayounara!" (Goodbye!) is said to friends.
"Sayounara" is a polite thing to say at the end of the day.
So, let's practice this phrase!
You play the role of your child's friend. When your child looks like their leaving, say "Sayounara!"
When your child says, "Sayounara!" you, as their friend, reply with, "Sayounara!"`,
            'tl': `Kapag aalis sa paaralan, sinasabi ng mga bata ang "Sayonara!" (Paalam!) sa kanilang mga kaibigan.
Ang "Sayonara" ay isang magalang na paraan ng pagpapaalam sa pagtatapos ng araw.
Kaya, magpraktis tayo ng pagbating ito!
Gumanap kayo bilang kaibigan ng inyong anak. Kapag paalis na ang inyong anak, sabihin ninyo: "Sayonara!"
Kapag sinabi ng inyong anak na "Sayonara!", kayo bilang kaibigan ay sasagot din ng "Sayonara!"`,
            'ko': `하교할 때 친구에게 "안녕히 가세요!"라고 말합니다.
"사요나라"는 하루를 마무리하는 작별 인사입니다.
이 인사를 연습해봅시다!
당신이 아이의 친구 역할을 해보세요. 아이가 집에 갈 때 "안녕히 가세요!"라고 말하세요.
아이가 "안녕히 가세요!"라고 하면, 친구 역할인 당신도 "안녕히 가세요!"라고 답하세요.`,
            'zh': `放学时，要对朋友说"再见！"（Sayounara）。
"再见（Sayounara）"是一天结束时的。
那么，我们来练习这个问候语吧！
你扮演孩子的朋友。当孩子要放学时，你就说"再见！（Sayounara）"。
当孩子说"再见！（Sayounara）"时，扮演朋友的你也要回应"再见！（Sayounara）"。`,
            'vi': `Khi tan học, nói "Tạm biệt!" với bạn bè.
"Sayounara" là lời chào tạm biệt vào cuối ngày.
Hãy luyện tập lời chào này!
Bạn đóng vai bạn của đứa trẻ. Khi đứa trẻ sắp về nhà, hãy nói "Tạm biệt!"
Khi đứa trẻ nói "Tạm biệt!", bạn với vai trò là bạn cũng nên trả lời "Tạm biệt!"`,
            'es': `Al salir de la escuela, di "¡Adiós!" a los amigos.
"Sayounara" es un saludo de despedida al final del día.
¡Practiquemos este saludo!
Tú juegas el papel del amigo del niño. Cuando el niño esté a punto de irse, di "¡Adiós!"
Cuando el niño diga "¡Adiós!", tú como amigo también debes responder "¡Adiós!"`,
            'pt': `Ao sair da escola, diga "Tchau!" aos amigos.
"Sayounara" é uma saudação de despedida no final do dia.
Vamos praticar essa saudação!
Você faz o papel do amigo da criança. Quando a criança estiver prestes a ir embora, diga "Tchau!"
Quando a criança disser "Tchau!", você como amigo também deve responder "Tchau!"`,
            'tl': `Kapag umuuwi na mula sa paaralan, sabihin ang "Paalam!" sa mga kaibigan.
Ang "Sayounara" ay pagpapaalam sa katapusan ng araw.
Magsanay tayo ng pagbati na ito!
Ikaw ay maging kaibigan ng bata. Kapag aalis na ang bata, sabihin ang "Paalam!"
Kapag sinabi ng bata ang "Paalam!", ikaw bilang kaibigan ay dapat ding sumagot ng "Paalam!"`
        }
    };
    
    // 現在の言語を取得
    const currentLanguage = '<?= $current_language ?>';
    
    const pointKey = `${lessonId}_${subLessonId}`;
    const lessonData = lessonPoints[pointKey];
    
    let pointContent;
    
    // DBのパパママポイントを優先使用
    if (dbPapamaPoint[currentLanguage] && dbPapamaPoint[currentLanguage].trim() !== '') {
        pointContent = dbPapamaPoint[currentLanguage];
    } else if (dbPapamaPoint['ja'] && dbPapamaPoint['ja'].trim() !== '') {
        pointContent = dbPapamaPoint['ja'];
    } else if (lessonData && lessonData[currentLanguage]) {
        pointContent = lessonData[currentLanguage];
    } else if (lessonData && lessonData['ja']) {
        // フォールバック：日本語
        pointContent = lessonData['ja'];
    } else {
        // 最後のフォールバック
        pointContent = 'ポイント情報が見つかりません。';
    }
    
    pointText.innerHTML = pointContent.replace(/\n/g, '<br><br>');
    pointCard.classList.remove('hidden');
    pointCard.classList.add('slide-in');
}

function hideLessonPoint() {
    const pointCard = document.getElementById('lessonPointCard');
    pointCard.classList.remove('slide-in');
    pointCard.classList.add('slide-out');
    
    setTimeout(() => {
        pointCard.classList.add('hidden');
        pointCard.classList.remove('slide-out');
    }, 300);
}

// 言語切り替え機能（グローバルタブ用）
function switchLanguage(lang) {
    // アクティブなタブを更新
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
    
    // body要素に現在の言語を設定
    document.body.setAttribute('data-current-lang', lang);
    
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

// スクロール時ロゴ制御
window.addEventListener('scroll', function() {
    const logo = document.getElementById('topLogo');
    const logoImg = document.getElementById('topLogoImg');
    
    if (window.scrollY > 100) {
        logo.classList.add('scrolled');
        logoImg.classList.add('scrolled');
    } else {
        logo.classList.remove('scrolled');
        logoImg.classList.remove('scrolled');
    }
});

// 言語変更機能（既存の下位互換）
function changeLanguage(selectedLang) {
    switchLanguage(selectedLang);
}
</script>

<style>
.lesson-subtitle-en {
    font-size: 1.2em;
    color: #999;
    font-style: normal;
    margin-bottom: 20px;
}

.badge-message {
    margin: 20px 0;
    text-align: center;
}

.badge-message p:first-child {
    font-size: 1.1em;
    font-weight: 600;
    color: #4CAF50;
}

/* バッジアニメーション */
.animated-badge {
    animation: badgeAppear 0.8s ease-out;
}

@keyframes badgeAppear {
    0% {
        transform: scale(0) rotate(180deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(90deg);
        opacity: 0.8;
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.modal-badge-animation {
    animation: modalBadgeAppear 1s ease-out;
}

@keyframes modalBadgeAppear {
    0% {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
    }
    20% {
        transform: scale(0.3) rotate(-90deg);
        opacity: 0.3;
    }
    60% {
        transform: scale(1.3) rotate(10deg);
        opacity: 0.9;
    }
    80% {
        transform: scale(0.9) rotate(-5deg);
        opacity: 1;
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.badge-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

/* バッジ画像を四角いままで表示 */
.badge-image {
    border-radius: 0 !important;
}

/* 黄色い円を完全に削除 */
.animated-badge,
.modal-badge-animation {
    background: none !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
}

.animated-badge::before,
.animated-badge::after,
.modal-badge-animation::before,
.modal-badge-animation::after {
    display: none !important;
}

.placeholder-badge {
    opacity: 0.6;
    border: 2px dashed #ccc;
}

/* ロゴアイコンを透明バックグラウンドに */
.logo-icon {
    background: transparent !important;
}

/* 言語セレクタのスタイル */
.language-selector {
    margin-left: 20px;
}

.language-selector select {
    background: var(--card-background);
    border: 1px solid var(--primary-light);
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.9em;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
}

.language-selector select:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px hsla(var(--base-hue), 40%, 70%, 0.2);
}

/* 動画の透明バック表示と幅統一 */
#lessonVideo,
#practiceVideo {
    width: 100% !important;
    max-width: 600px !important;
    height: auto !important;
    object-fit: cover !important;
    background: transparent !important;
    border-radius: 8px;
}

/* 全動画要素の幅統一 */
.video-section video,
.video-area video {
    width: 100% !important;
    max-width: 600px !important;
    height: auto !important;
    object-fit: cover !important;
    object-position: center !important;
    background: transparent !important;
}

/* 動画コンテナも透明バック */
.video-section,
.video-area {
    background: transparent !important;
}

/* 動画エリアの基本スタイル */
.video-area {
    margin: 0;
    padding: 0;
}

.video-area video,
.video-area .video-placeholder {
    margin: 0;
    padding: 0;
    background: transparent;
}

/* 動画の黒バック完全除去 */
video {
    background-color: transparent !important;
    background: transparent !important;
}

/* ボタングループのスタイル */
.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin: 20px 0;
}

/* 統一ボタンスタイル */
.button-group .tts-button,
.button-group .point-button {
    background: linear-gradient(45deg, #4CAF50, #45a049) !important;
    color: white !important;
    border: none !important;
    padding: 12px 25px !important;
    border-radius: 25px !important;
    font-size: 0.9em !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    margin: 0 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(76,175,80,0.3) !important;
    min-width: 140px !important;
    justify-content: center !important;
}

.button-group .tts-button:hover,
.button-group .point-button:hover {
    background: linear-gradient(45deg, #45a049, #388e3c) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(76,175,80,0.4) !important;
}

.point-button .point-text {
    text-align: center;
    line-height: 1.2;
    color: white;
    text-shadow: none;
}

.point-button:hover {
    background: linear-gradient(45deg, #45a049, #388e3c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76,175,80,0.4);
}

.point-icon {
    font-size: 1.2em;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* ポイントカードのスタイル */
.lesson-point-card {
    position: fixed;
    top: 50%;
    right: -400px;
    transform: translateY(-50%);
    width: 380px;
    max-width: 90vw;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.lesson-point-card.slide-in {
    right: 20px;
}

.lesson-point-card.slide-out {
    right: -400px;
}

.lesson-point-card.hidden {
    display: none;
}

.point-content {
    padding: 25px;
}

.point-content h4 {
    color: #4CAF50;
    font-size: 1.3em;
    margin-bottom: 15px;
    border-bottom: 2px solid #e8f5e9;
    padding-bottom: 10px;
}

.point-text {
    color: #333;
    line-height: 1.6;
    font-size: 0.95em;
    font-family: 'PingFang SC', 'Microsoft YaHei', 'SimSun', 'Hiragino Sans GB', 'WenQuanYi Micro Hei', sans-serif;
}

.close-point-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 1.8em;
    color: #999;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-point-btn:hover {
    color: #f44336;
    transform: scale(1.1);
}

/* 言語別フォント統一 */
body[data-lang="zh"] * {
    font-family: 'PingFang SC', 'Microsoft YaHei', 'SimSun', 'Hiragino Sans GB', 'WenQuanYi Micro Hei', sans-serif !important;
}

body[data-lang="ko"] * {
    font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', 'Nanum Gothic', 'Dotum', sans-serif !important;
}

body[data-lang="ja"] * {
    font-family: 'Hiragino Sans', 'Yu Gothic', 'Meiryo', 'MS PGothic', sans-serif !important;
}

body[data-lang="vi"] * {
    font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif !important;
}

body[data-lang="tl"] * {
    font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif !important;
}

/* 固定パパ・ママポイントボタン */
.floating-point-button {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(45deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 15px 20px;
    border-radius: 50px;
    font-size: 0.9em;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 999;
    box-shadow: 0 8px 25px rgba(76,175,80,0.3), 0 15px 35px rgba(76,175,80,0.2);
    transition: all 0.3s ease;
    animation: float-button 3s ease-in-out infinite;
}

.floating-point-button:hover {
    background: linear-gradient(45deg, #45a049, #388e3c);
    transform: translateY(-8px) scale(1.1);
    box-shadow: 0 12px 35px rgba(76,175,80,0.5), 0 20px 50px rgba(76,175,80,0.3);
    animation: none; /* ホバー時は浮遊アニメーションを止める */
}

.floating-point-button .point-icon {
    font-size: 1.3em;
    animation: bounce-icon 2s ease-in-out infinite;
}

.floating-point-button .point-text {
    text-align: center;
    line-height: 1.2;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* 浮遊エフェクト */
@keyframes float-button {
    0%, 100% { 
        transform: translateY(0px);
        box-shadow: 0 8px 25px rgba(76,175,80,0.3), 0 15px 35px rgba(76,175,80,0.2);
    }
    50% { 
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(76,175,80,0.4), 0 25px 50px rgba(76,175,80,0.25);
    }
}

/* アイコンバウンス */
@keyframes bounce-icon {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    25% { transform: translateY(-2px) rotate(-5deg); }
    75% { transform: translateY(-1px) rotate(5deg); }
}


/* レスポンシブ対応 */
@media (max-width: 900px) {
    header {
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }
    
    .main-nav {
        order: 2;
    }
    
    .user-info {
        order: 3;
        position: static;
        margin: 0;
    }
    
    .floating-point-button {
        bottom: 20px;
        right: 20px;
        padding: 12px 16px;
        font-size: 0.8em;
    }
    
    .button-group {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    
    .lesson-point-card {
        width: calc(100vw - 20px);
        right: -100vw;
        top: 10px;
        transform: none;
        height: calc(100vh - 20px);
        overflow-y: auto;
    }
    
    .lesson-point-card.slide-in {
        right: 10px;
    }
    
    .lesson-point-card.slide-out {
        right: -100vw;
    }
}

/* NEXTボタンの性別対応色 */
.next-button {
    background: linear-gradient(45deg, var(--primary-color), var(--primary-dark)) !important;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 25px;
    font-size: 1.1em;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px auto;
    transition: all 0.3s ease;
}

.next-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px hsla(var(--base-hue), 40%, 70%, 0.4);
}

.next-button::after {
    content: '→';
    font-size: 1.2em;
}

/* みるタブのレイアウトスタイル */
.miru-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

/* やってみるタブのレイアウトスタイル - みるタブと同じ構造 */
.yatte-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.video-section {
    flex: 1;
    min-width: 300px;
}

.dialogue-section {
    flex: 1;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    min-height: 400px;
}

.practice-section {
    flex: 1;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    min-height: 400px;
}

/* 動画とダイアログ/練習の頭を揃える - すべて同じ上端位置に配置 */
.video-section video,
.video-section .video-placeholder {
    margin-top: 0;
    vertical-align: top;
}

.dialogue-section .text-section:first-child,
.practice-section .text-section:first-child {
    margin-top: 0;
}

/* やってみるの動画位置をみるタブと完全に統一 */
.yatte-layout .video-section {
    align-self: flex-start;
}

.miru-layout .video-section {
    align-self: flex-start;
}

/* シーン説明のマージン調整 */
.scene-description {
    margin-top: 15px !important;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.scene-description {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.scene-text-jp {
    font-size: 1.0em;
    color: #333;
    font-weight: 600;
    margin-bottom: 8px;
}

.scene-text-native {
    font-size: 0.9em;
    color: #666;
}

@media (max-width: 768px) {
    .miru-layout,
    .yatte-layout {
        flex-direction: column;
        gap: 15px;
    }
    
    .video-section,
    .dialogue-section,
    .practice-section {
        min-width: auto;
        width: 100%;
    }
}

/* Split Layout for A/B/C sections */
.split-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    min-height: 500px;
    width: 100%;
}

.left-section,
.right-section {
    flex: 1;
    width: 50%;
    min-width: 0; /* Allow flex items to shrink */
}

.left-section {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.right-section {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 20px;
}

/* Container Point Button */
.container-point-button {
    position: absolute;
    bottom: 110px;
    right: 130px;
    background: linear-gradient(45deg, #4CAF50, #45a049);
    color: white;
    border: none;
    border-radius: 50%;
    width: 140px;
    height: 140px;
    font-size: 1.1em;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(76,175,80,0.3), 0 15px 35px rgba(76,175,80,0.2);
    transition: all 0.3s ease;
    z-index: 100;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    animation: float-button 3s ease-in-out infinite;
}

.container-point-button:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(76,175,80,0.4), 0 25px 50px rgba(76,175,80,0.3);
}

.container-point-button .point-icon {
    font-size: 2.2em;
    animation: bounce-icon 2s ease-in-out infinite;
}

.container-point-button .point-text {
    line-height: 1.1;
    text-align: center;
    color: white;
    font-size: 1.2em;
    font-weight: bold;
}

/* 日本語選択時のパパママポイントボタンの文字サイズ */
[data-current-lang="ja"] .container-point-button .point-text {
    font-size: 1.0em;
}

/* 日本語選択時は翻訳テキストを非表示 */
[data-current-lang="ja"] .translation-text,
[data-current-lang="ja"] .scene-translation,
[data-current-lang="ja"] .scene-text-native {
    display: none;
}

/* カスタム確認モーダル */
.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.confirm-modal.hidden {
    display: none;
}

.confirm-content {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.confirm-message {
    font-size: 1.2em;
    color: #333;
    margin-bottom: 25px;
    line-height: 1.4;
}

.confirm-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.confirm-cancel-btn,
.confirm-ok-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
}

.confirm-cancel-btn {
    background: #f44336;
    color: white;
}

.confirm-cancel-btn:hover {
    background: #d32f2f;
    transform: translateY(-2px);
}

.confirm-ok-btn {
    background: #4CAF50;
    color: white;
}

.confirm-ok-btn:hover {
    background: #45a049;
    transform: translateY(-2px);
}

/* Mobile responsive for split layout */
@media (max-width: 900px) {
    .split-layout {
        flex-direction: column;
        gap: 20px;
    }
    
    .left-section,
    .right-section {
        flex: none;
        width: 100%;
    }
    
    .container-point-button {
        position: fixed;
        top: auto;
        bottom: 20px;
        right: 20px;
        width: 120px;
        height: 120px;
        font-size: 1em;
    }
}

.container {
    background: transparent !important;
}

/* Override body background for lesson page - single background only */
body {
    background: var(--background) !important;
    background-image: url('../assets/images/bg_top.png'), url('../assets/images/bg_bottom.png') !important;
    background-position: center top, center bottom !important;
    background-repeat: no-repeat, no-repeat !important;
    background-size: 100% auto, 100% auto !important;
}

/* Completely disable any overlapping background elements */
*::before {
    background-image: none !important;
}

/* Make nav background same as other pages */
.main-nav {
    background: transparent !important;
}
</style>

</body>
</html>