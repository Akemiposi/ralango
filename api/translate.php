<?php
// api/translate.php  
// Gemini API翻訳エンドポイント

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/GeminiTranslator.php';

// ログインチェック
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// リクエストボディを取得
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$text = $input['text'] ?? '';
$targetLanguage = $input['targetLanguage'] ?? '';
$sourceLanguage = $input['sourceLanguage'] ?? 'ja';
$context = $input['context'] ?? [];

// バリデーション
if (empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Text is required']);
    exit;
}

if (empty($targetLanguage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Target language is required']);
    exit;
}

if (strlen($text) > 2000) {
    http_response_code(400);
    echo json_encode(['error' => 'Text too long (max 2000 characters)']);
    exit;
}

// サポート言語チェック
$supportedLanguages = array_keys(SUPPORTED_LANGUAGES);
if (!in_array($targetLanguage, $supportedLanguages)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported target language']);
    exit;
}

// 不適切な文字をフィルタ
$text = strip_tags($text);

try {
    $translator = new GeminiTranslator();
    
    // APIが設定されているかチェック
    if (!$translator->isConfigured()) {
        // フォールバック翻訳を返す
        $fallbackTranslation = getFallbackTranslation($text, $targetLanguage);
        
        echo json_encode([
            'success' => true,
            'translation' => $fallbackTranslation,
            'original' => $text,
            'targetLanguage' => $targetLanguage,
            'sourceLanguage' => $sourceLanguage,
            'fallback' => true,
            'source' => 'fallback_dictionary'
        ]);
        exit;
    }
    
    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'translation' => $translation,
        'original' => $text,
        'targetLanguage' => $targetLanguage,
        'sourceLanguage' => $sourceLanguage,
        'fallback' => false,
        'source' => 'gemini_api'
    ]);
    
    // 使用統計を記録（オプション）
    try {
        $stmt = $pdo->prepare("
            INSERT INTO translation_usage_log 
            (user_id, original_text, translated_text, target_language, source_language, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['user']['id'],
            $text,
            $translation,
            $targetLanguage,
            $sourceLanguage
        ]);
    } catch (Exception $e) {
        // 統計記録の失敗は無視
        error_log("Translation usage log error: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Translation API Error: " . $e->getMessage());
    
    // エラーの場合もフォールバック翻訳を提供
    $fallbackTranslation = getFallbackTranslation($text, $targetLanguage);
    
    echo json_encode([
        'success' => true,
        'translation' => $fallbackTranslation,
        'original' => $text,
        'targetLanguage' => $targetLanguage,
        'sourceLanguage' => $sourceLanguage,
        'fallback' => true,
        'source' => 'fallback_dictionary',
        'error' => 'API temporarily unavailable'
    ]);
}

/**
 * フォールバック翻訳（簡易辞書ベース）
 */
function getFallbackTranslation($text, $targetLanguage) {
    // 基本的な単語・フレーズの翻訳辞書
    $dictionary = [
        'en' => [
            'せんせい、おはようございます。' => 'Good morning, teacher!',
            'おはよう。' => 'Good morning!',
            'せんせい' => 'teacher',
            'おはようございます' => 'good morning',
            'おはよう' => 'morning',
            'ありがとうございます' => 'thank you',
            'ありがとう' => 'thanks',
            'すみません' => 'excuse me',
            'こんにちは' => 'hello',
            'こんばんは' => 'good evening',
            'さようなら' => 'goodbye',
            'はじめまして' => 'nice to meet you',
            'わたし' => 'I',
            'あなた' => 'you',
            'なまえ' => 'name',
            'がっこう' => 'school',
            'べんきょう' => 'study'
        ],
        'zh' => [
            'せんせい、おはようございます。' => '老师，早上好！',
            'おはよう。' => '早上好！',
            'せんせい' => '老师',
            'おはようございます' => '早上好',
            'おはよう' => '早安',
            'ありがとうございます' => '谢谢',
            'ありがとう' => '谢谢',
            'すみません' => '不好意思',
            'こんにちは' => '你好',
            'こんばんは' => '晚上好',
            'さようなら' => '再见',
            'はじめまして' => '初次见面',
            'わたし' => '我',
            'あなた' => '你',
            'なまえ' => '名字',
            'がっこう' => '学校',
            'べんきょう' => '学习'
        ],
        'ko' => [
            'せんせい、おはようございます。' => '선생님, 안녕하세요!',
            'おはよう。' => '안녕하세요!',
            'せんせい' => '선생님',
            'おはようございます' => '안녕하세요',
            'おはよう' => '안녕',
            'ありがとうございます' => '감사합니다',
            'ありがとう' => '고마워',
            'すみません' => '죄송합니다',
            'こんにちは' => '안녕하세요',
            'こんばんは' => '안녕하세요',
            'さようなら' => '안녕히 가세요',
            'はじめまして' => '처음 뵙겠습니다',
            'わたし' => '저',
            'あなた' => '당신',
            'なまえ' => '이름',
            'がっこう' => '학교',
            'べんきょう' => '공부'
        ],
        'vi' => [
            'せんせい、おはようございます。' => 'Chào buổi sáng, thầy cô!',
            'おはよう。' => 'Chào buổi sáng!',
            'せんせい' => 'giáo viên',
            'おはようございます' => 'chào buổi sáng',
            'おはよう' => 'chào',
            'ありがとうございます' => 'cảm ơn',
            'ありがとう' => 'cảm ơn',
            'すみません' => 'xin lỗi',
            'こんにちは' => 'xin chào',
            'こんばんは' => 'chào buổi tối',
            'さようなら' => 'tạm biệt',
            'はじめまして' => 'rất vui được gặp',
            'わたし' => 'tôi',
            'あなた' => 'bạn',
            'なまえ' => 'tên',
            'がっこう' => 'trường học',
            'べんきょう' => 'học'
        ],
        'th' => [
            'せんせい、おはようございます。' => 'อรุณสวัสดิ์ครับ/ค่ะ คุณครู!',
            'おはよう。' => 'อรุณสวัสดิ์!',
            'せんせい' => 'ครู',
            'おはようございます' => 'อรุณสวัสดิ์',
            'おはよう' => 'สวัสดี',
            'ありがとうございます' => 'ขอบคุณ',
            'ありがとう' => 'ขอบคุณ',
            'すみません' => 'ขอโทษ',
            'こんにちは' => 'สวัสดี',
            'こんばんは' => 'สวัสดี',
            'さようなら' => 'ลาก่อน',
            'はじめまして' => 'ยินดีที่ได้รู้จัก',
            'わたし' => 'ฉัน',
            'あなた' => 'คุณ',
            'なまえ' => 'ชื่อ',
            'がっこう' => 'โรงเรียน',
            'べんきょう' => 'เรียน'
        ]
    ];
    
    // 完全一致検索
    if (isset($dictionary[$targetLanguage][$text])) {
        return $dictionary[$targetLanguage][$text];
    }
    
    // 部分一致検索
    foreach ($dictionary[$targetLanguage] as $japanese => $translation) {
        if (strpos($text, $japanese) !== false) {
            return $translation;
        }
    }
    
    // マッチしない場合はそのまま返す
    return $text;
}
?>