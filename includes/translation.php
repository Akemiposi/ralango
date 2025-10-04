<?php
// includes/translation.php - 翻訳システム

// Gemini API設定
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent');

// 言語コードマッピング（翻訳専用）
function getTranslationLanguageName($code)
{
    $languages = [
        'ja' => '日本語',
        'en' => 'English',
        'zh' => '中文',
        'ko' => '한국어',
        'vi' => 'Tiếng Việt',
        'tl' => 'Filipino',
        'ne' => 'नेपाली',
        'pt' => 'Português'
    ];

    return $languages[$code] ?? 'English';
}

// 翻訳テキストのハッシュ生成
function generateTextHash($text)
{
    return hash('sha256', trim($text));
}

// キャッシュから翻訳を取得
function getCachedTranslation($text, $target_language, $source_language = 'ja')
{
    global $pdo;

    if (!$pdo) return null;

    try {
        $text_hash = generateTextHash($text);
        $stmt = $pdo->prepare("
            SELECT translated_text 
            FROM translation_cache 
            WHERE text_hash = ? 
            AND target_language = ? 
            AND source_language = ?
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$text_hash, $target_language, $source_language]);
        $result = $stmt->fetch();

        return $result ? $result['translated_text'] : null;
    } catch (Exception $e) {
        error_log("Translation cache error: " . $e->getMessage());
        return null;
    }
}

// 翻訳をキャッシュに保存
function saveCachedTranslation($original_text, $translated_text, $target_language, $source_language = 'ja')
{
    global $pdo;

    if (!$pdo) return false;

    try {
        $text_hash = generateTextHash($original_text);
        $stmt = $pdo->prepare("
            INSERT INTO translation_cache 
            (original_text, translated_text, source_language, target_language, text_hash)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$original_text, $translated_text, $source_language, $target_language, $text_hash]);

        return true;
    } catch (Exception $e) {
        error_log("Translation cache save error: " . $e->getMessage());
        return false;
    }
}

// Gemini APIで翻訳
function translateWithGemini($text, $target_language, $source_language = 'ja')
{
    $source_lang_name = getTranslationLanguageName($source_language);
    $target_lang_name = getTranslationLanguageName($target_language);

    $prompt = "Translate the following {$source_lang_name} text to {$target_lang_name}. 
    Only return the translated text, do not include any explanations or additional text.
    
    Text to translate: {$text}";

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'maxOutputTokens' => 1000
        ]
    ];

    $options = [
        'http' => [
            'header' => [
                "Content-Type: application/json",
                "x-goog-api-key: " . GEMINI_API_KEY
            ],
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 30
        ]
    ];

    try {
        $context = stream_context_create($options);
        $response = file_get_contents(GEMINI_API_URL, false, $context);

        // デバッグ情報を追加
        if (isset($_GET['debug'])) {
            error_log("Gemini API Response: " . ($response ?: 'FALSE'));
            if (!$response && isset($http_response_header)) {
                error_log("HTTP Response Headers: " . print_r($http_response_header, true));
            }
        }

        if ($response === false) {
            return null;
        }

        $result = json_decode($response, true);

        // デバッグ情報を追加
        if (isset($_GET['debug'])) {
            error_log("Gemini API Parsed Response: " . print_r($result, true));
        }

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }

        // エラーがある場合はログに記録
        if (isset($result['error'])) {
            error_log("Gemini API Error: " . print_r($result['error'], true));
        }

        return null;
    } catch (Exception $e) {
        error_log("Gemini translation error: " . $e->getMessage());
        return null;
    }
}

// 静的翻訳辞書（Gemini APIのフォールバック用）
function getStaticTranslation($text, $target_language)
{
    $translations = [
        'en' => [
            'こんにちは、' => 'Hello, ',
            'さん！' => '!',
            '今日も一緒に日本語を学びましょう' => 'Let\'s learn Japanese together today',
            '学習進捗' => 'Learning Progress',
            '完了レッスン' => 'Completed Lessons',
            '獲得バッジ' => 'Earned Badges',
            '学習を始める' => 'Start Learning',
            'レッスン一覧' => 'Lesson List',
            'バッジコレクション' => 'Badge Collection',
            '学習記録' => 'Learning Records',
            '最新のバッジ' => 'Latest Badges',
            '最近の学習' => 'Recent Learning',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => 'No badges yet. Complete lessons to earn badges!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => 'No learning history. Let\'s start with the first lesson!',
            'みる' => 'Watch',
            'やってみる' => 'Try it',
            'できた' => 'Done',
            'アカウント管理' => 'Account Management'
        ],
        'zh' => [
            'こんにちは、' => '你好，',
            'さん！' => '！',
            '今日も一緒に日本語を学びましょう' => '今天也一起学日语吧',
            '学習進捗' => '学习进度',
            '完了レッスン' => '已完成课程',
            '獲得バッジ' => '获得徽章',
            '学習を始める' => '开始学习',
            'レッスン一覧' => '课程列表',
            'バッジコレクション' => '徽章收藏',
            '学習記録' => '学习记录',
            '最新のバッジ' => '最新徽章',
            '最近の学習' => '最近学习',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => '还没有徽章。 完成课程来获得徽章吧！',
            '学習履歴がありません。最初のレッスンから始めましょう！' => '没有学习记录。 从第一课开始吧！',
            'みる' => '观看',
            'やってみる' => '试试看',
            'できた' => '完成了',
            'アカウント管理' => '账户管理'
        ],
        'ko' => [
            'こんにちは、' => '안녕하세요, ',
            'さん！' => '님!',
            '今日も一緒に日本語を学びましょう' => '오늘도 함께 일본어를 배워봅시다',
            '学習進捗' => '학습 진도',
            '完了レッスン' => '완료된 수업',
            '獲得バッジ' => '획득한 배지',
            '学習を始める' => '학습 시작하기',
            'レッスン一覧' => '수업 목록',
            'バッジコレクション' => '배지 컬렉션',
            '学習記録' => '학습 기록',
            '最新のバッジ' => '최신 배지',
            '最近の学習' => '최근 학습',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => '아직 배지가 없습니다. 수업을 완료해서 배지를 획득해보세요!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => '학습 기록이 없습니다. 첫 번째 수업부터 시작해봅시다!',
            'みる' => '보기',
            'やってみる' => '해보기',
            'できた' => '완료'
        ],
        'vi' => [
            'こんにちは、' => 'Xin chào, ',
            'さん！' => '!',
            '今日も一緒に日本語を学びましょう' => 'Hôm nay cũng hãy cùng học tiếng Nhật nhé',
            '学習進捗' => 'Tiến độ học tập',
            '完了レッスン' => 'Bài học đã hoàn thành',
            '獲得バッジ' => 'Huy hiệu đã đạt',
            '学習を始める' => 'Bắt đầu học',
            'レッスン一覧' => 'Danh sách bài học',
            'バッジコレクション' => 'Bộ sưu tập huy hiệu',
            '学習記録' => 'Hồ sơ học tập',
            '最新のバッジ' => 'Huy hiệu mới nhất',
            '最近の学習' => 'Học tập gần đây',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => 'Chưa có huy hiệu nào. Hãy hoàn thành bài học để nhận huy hiệu!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => 'Chưa có lịch sử học tập. Hãy bắt đầu từ bài học đầu tiên!',
            'みる' => 'Xem',
            'やってみる' => 'Thử làm',
            'できた' => 'Đã xong'
        ],
        'tl' => [
            'こんにちは、' => 'Kumusta, ',
            'さん！' => '!',
            '今日も一緒に日本語を学びましょう' => 'Matuto tayo ng Japanese ngayong araw din',
            '学習進捗' => 'Progress sa Pag-aaral',
            '完了レッスン' => 'Natapos na mga Aralin',
            '獲得バッジ' => 'Nakamit na mga Badge',
            '学習を始める' => 'Simulan ang Pag-aaral',
            'レッスン一覧' => 'Listahan ng mga Aralin',
            'バッジコレクション' => 'Koleksyon ng mga Badge',
            '学習記録' => 'Talaan ng Pag-aaral',
            '最新のバッジ' => 'Pinakabagong mga Badge',
            '最近の学習' => 'Kamakailang Pag-aaral',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => 'Wala pang mga badge. Kumpletuhin ang mga aralin para makakuha ng badge!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => 'Walang kasaysayan ng pag-aaral. Simulan natin sa unang aralin!',
            'みる' => 'Panoorin',
            'やってみる' => 'Subukan',
            'できた' => 'Tapos na'
        ],
        'ne' => [
            'こんにちは、' => 'नमस्कार, ',
            'さん！' => '!',
            '今日も一緒に日本語を学びましょう' => 'आज पनि सँगै जापानी भाषा सिकौं',
            '学習進捗' => 'सिकाइको प्रगति',
            '完了レッスン' => 'पूरा भएका पाठहरू',
            '獲得バッジ' => 'प्राप्त बिजेसहरू',
            '学習を始める' => 'सिकाइ सुरु गर्नुहोस्',
            'レッスン一覧' => 'पाठहरूको सूची',
            'バッジコレクション' => 'बिजेस संग्रह',
            '学習記録' => 'सिकाइको रेकर्ड',
            '最新のバッジ' => 'नवीनतम बिजेसहरू',
            '最近の学習' => 'भर्खरको सिकाइ',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => 'अझै कुनै बिजेस छैन। पाठहरू पूरा गरेर बिजेसहरू प्राप्त गरौं!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => 'सिकाइको इतिहास छैन। पहिलो पाठबाट सुरु गरौं!',
            'みる' => 'हेर्नुहोस्',
            'やってみる' => 'प्रयास गर्नुहोस्',
            'できた' => 'सकियो'
        ],
        'pt' => [
            'こんにちは、' => 'Olá, ',
            'さん！' => '!',
            '今日も一緒に日本語を学びましょう' => 'Vamos aprender japonês juntos hoje também',
            '学習進捗' => 'Progresso dos Estudos',
            '完了レッスン' => 'Lições Completadas',
            '獲得バッジ' => 'Emblemas Conquistados',
            '学習を始める' => 'Começar a Estudar',
            'レッスン一覧' => 'Lista de Lições',
            'バッジコレクション' => 'Coleção de Emblemas',
            '学習記録' => 'Registro de Estudos',
            '最新のバッジ' => 'Emblemas Mais Recentes',
            '最近の学習' => 'Estudos Recentes',
            'まだバッジがありません。レッスンを完了してバッジを獲得しましょう！' => 'Ainda não há emblemas. Complete as lições para conquistar emblemas!',
            '学習履歴がありません。最初のレッスンから始めましょう！' => 'Não há histórico de estudos. Vamos começar pela primeira lição!',
            'みる' => 'Assistir',
            'やってみる' => 'Vamos tentar',
            'できた' => 'Conseguiu'
        ]
    ];

    return $translations[$target_language][$text] ?? null;
}

// メイン翻訳関数
function translateText($text, $target_language, $source_language = 'ja')
{
    // デバッグログ
    if (isset($_GET['debug'])) {
        error_log("Translation Debug: $text | $source_language -> $target_language");
    }

    // 同じ言語の場合はそのまま返す
    if ($source_language === $target_language) {
        return $text;
    }

    // キャッシュから確認
    $cached = getCachedTranslation($text, $target_language, $source_language);
    if ($cached !== null) {
        if (isset($_GET['debug'])) {
            error_log("Translation Debug: Using cached result: $cached");
        }
        return $cached;
    }

    // 静的翻訳辞書から確認
    $static_translation = getStaticTranslation($text, $target_language);
    if ($static_translation !== null) {
        // 静的翻訳をキャッシュに保存
        saveCachedTranslation($text, $static_translation, $target_language, $source_language);
        if (isset($_GET['debug'])) {
            error_log("Translation Debug: Using static translation: $static_translation");
        }
        return $static_translation;
    }

    // Gemini APIで翻訳
    $translated = translateWithGemini($text, $target_language, $source_language);

    if ($translated !== null) {
        // キャッシュに保存
        saveCachedTranslation($text, $translated, $target_language, $source_language);
        if (isset($_GET['debug'])) {
            error_log("Translation Debug: New translation: $translated");
        }
        return $translated;
    }

    // 翻訳失敗時は原文を返す
    if (isset($_GET['debug'])) {
        error_log("Translation Debug: Translation failed, returning original text");
    }
    return $text;
}

// 複数のテキストを一括翻訳
function translateMultipleTexts($texts, $target_language, $source_language = 'ja')
{
    $translations = [];

    foreach ($texts as $key => $text) {
        $translations[$key] = translateText($text, $target_language, $source_language);
    }

    return $translations;
}

// HTML内のテキストを翻訳（data-translate属性用）
function getTranslationForTemplate($key, $target_language, $default_text = '')
{
    if (empty($default_text)) {
        return $key; // キーをそのまま返す
    }

    return translateText($default_text, $target_language);
}
