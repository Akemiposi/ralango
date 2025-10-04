<?php
// config/api_config.example.php
// API設定ファイル（サンプル）
// 
// 使用方法：
// 1. このファイルを api_config.php にコピー
// 2. 実際のAPIキーに置き換える
// 3. 必要に応じて設定をカスタマイズ

// Google Text-to-Speech API設定
// Google Cloud Console で取得: https://console.cloud.google.com/
define('GOOGLE_TTS_API_KEY', $_ENV['GOOGLE_TTS_API_KEY'] ?? '');
define('GOOGLE_TTS_ENDPOINT', 'https://texttospeech.googleapis.com/v1/text:synthesize');

// Gemini API設定  
// Google AI Studio で取得: https://makersuite.google.com/app/apikey
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// 環境変数チェック
if (empty($_ENV['GOOGLE_TTS_API_KEY']) || empty($_ENV['GEMINI_API_KEY'])) {
    error_log('APIキー設定エラー: 環境変数GOOGLE_TTS_API_KEY, GEMINI_API_KEYが設定されていません。');
}

// サポート言語設定
define('SUPPORTED_LANGUAGES', [
    'en' => 'English',
    'zh' => '中文',
    'ko' => '한국어',
    'vi' => 'Tiếng Việt',
    'th' => 'ไทย',
    'es' => 'Español',
    'fr' => 'Français',
    'de' => 'Deutsch',
    'pt' => 'Português',
    'it' => 'Italiano'
]);

// Google TTS音声設定
define('TTS_VOICE_CONFIG', [
    'ja' => [
        'languageCode' => 'ja-JP',
        'name' => 'ja-JP-Neural2-B', // 自然な女性の声
        'ssmlGender' => 'FEMALE'
    ],
    'en' => [
        'languageCode' => 'en-US',
        'name' => 'en-US-Neural2-F',
        'ssmlGender' => 'FEMALE'
    ]
]);

// 音声ファイル保存設定
define('AUDIO_CACHE_DIR', __DIR__ . '/../assets/audio/cache/');
define('AUDIO_CACHE_URL', '/assets/audio/cache/');
define('AUDIO_CACHE_LIFETIME', 7 * 24 * 60 * 60); // 7日間

// API使用制限設定（1日あたり）
define('TTS_DAILY_LIMIT', 1000);        // TTS生成回数制限
define('TRANSLATION_DAILY_LIMIT', 500); // 翻訳回数制限

// キャッシュディレクトリが存在しない場合は作成
if (!file_exists(AUDIO_CACHE_DIR)) {
    mkdir(AUDIO_CACHE_DIR, 0755, true);
}
