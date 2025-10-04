<?php
// includes/GeminiTranslator.php
// Gemini API翻訳クラス

require_once __DIR__ . '/../config/api_config.php';

class GeminiTranslator {
    private $apiKey;
    private $endpoint;
    private $cache = [];
    
    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->endpoint = GEMINI_ENDPOINT;
    }
    
    /**
     * テキストを翻訳
     * @param string $text 翻訳するテキスト
     * @param string $targetLanguage 翻訳先言語コード
     * @param string $sourceLanguage 翻訳元言語コード（デフォルト：日本語）
     * @param array $context 翻訳のコンテキスト情報
     * @return string|false 翻訳結果、失敗時はfalse
     */
    public function translate($text, $targetLanguage, $sourceLanguage = 'ja', $context = []) {
        // キャッシュキーを生成
        $cacheKey = md5($text . $targetLanguage . $sourceLanguage . serialize($context));
        
        // キャッシュから取得を試す
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // データベースキャッシュから取得を試す
        $cachedTranslation = $this->getCachedTranslation($cacheKey);
        if ($cachedTranslation) {
            $this->cache[$cacheKey] = $cachedTranslation;
            return $cachedTranslation;
        }
        
        // 言語名を取得
        $targetLanguageName = $this->getLanguageName($targetLanguage);
        $sourceLanguageName = $this->getLanguageName($sourceLanguage);
        
        // プロンプトを構築
        $prompt = $this->buildTranslationPrompt($text, $targetLanguageName, $sourceLanguageName, $context);
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1, // 一貫性を重視
                'topK' => 1,
                'topP' => 0.1,
                'maxOutputTokens' => 200
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Gemini API cURL Error: " . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log("Gemini API HTTP Error: " . $httpCode . " - " . $response);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            error_log("Gemini API Error: No translation in response");
            return false;
        }
        
        $translation = trim($data['candidates'][0]['content']['parts'][0]['text']);
        
        // キャッシュに保存
        $this->cache[$cacheKey] = $translation;
        $this->saveCachedTranslation($cacheKey, $text, $translation, $targetLanguage, $sourceLanguage);
        
        return $translation;
    }
    
    /**
     * 翻訳プロンプトを構築
     */
    private function buildTranslationPrompt($text, $targetLanguage, $sourceLanguage, $context) {
        $contextInfo = '';
        
        if (!empty($context['lesson_type'])) {
            $contextInfo .= "これは日本語学習アプリの{$context['lesson_type']}レッスンの内容です。";
        }
        
        if (!empty($context['target_audience'])) {
            $contextInfo .= "対象は{$context['target_audience']}です。";
        }
        
        if (!empty($context['tone'])) {
            $contextInfo .= "翻訳のトーンは{$context['tone']}でお願いします。";
        }
        
        $prompt = "以下の{$sourceLanguage}のテキストを{$targetLanguage}に翻訳してください。{$contextInfo}

翻訳ルール：
1. 自然で読みやすい翻訳にしてください
2. 日本語学習者にとって理解しやすい表現を心がけてください
3. 文化的なニュアンスも考慮してください
4. 翻訳結果のみを返答してください（説明や追加文は不要）

翻訳対象テキスト：
{$text}";

        return $prompt;
    }
    
    /**
     * 言語コードから言語名を取得
     */
    private function getLanguageName($languageCode) {
        $languageNames = [
            'ja' => '日本語',
            'en' => '英語',
            'zh' => '中国語',
            'ko' => '韓国語', 
            'vi' => 'ベトナム語',
            'th' => 'タイ語',
            'es' => 'スペイン語',
            'fr' => 'フランス語',
            'de' => 'ドイツ語',
            'pt' => 'ポルトガル語',
            'it' => 'イタリア語'
        ];
        
        return $languageNames[$languageCode] ?? $languageCode;
    }
    
    /**
     * キャッシュされた翻訳を取得
     */
    private function getCachedTranslation($cacheKey) {
        global $pdo;
        
        if (!$pdo) return null;
        
        try {
            $stmt = $pdo->prepare("
                SELECT translation 
                FROM translation_cache 
                WHERE cache_key = ? AND created_at > ?
            ");
            $stmt->execute([
                $cacheKey, 
                date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60) // 30日間有効
            ]);
            
            $result = $stmt->fetch();
            return $result ? $result['translation'] : null;
            
        } catch (Exception $e) {
            error_log("Translation cache fetch error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 翻訳をキャッシュに保存
     */
    private function saveCachedTranslation($cacheKey, $originalText, $translation, $targetLang, $sourceLang) {
        global $pdo;
        
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO translation_cache 
                (cache_key, original_text, translation, target_language, source_language, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                translation = VALUES(translation),
                created_at = NOW()
            ");
            
            $stmt->execute([
                $cacheKey,
                $originalText,
                $translation, 
                $targetLang,
                $sourceLang
            ]);
            
        } catch (Exception $e) {
            error_log("Translation cache save error: " . $e->getMessage());
        }
    }
    
    /**
     * 複数のテキストを一括翻訳
     * @param array $texts 翻訳するテキストの配列
     * @param string $targetLanguage 翻訳先言語
     * @param string $sourceLanguage 翻訳元言語
     * @return array 翻訳結果の配列
     */
    public function translateBatch($texts, $targetLanguage, $sourceLanguage = 'ja') {
        $results = [];
        
        foreach ($texts as $key => $text) {
            $translation = $this->translate($text, $targetLanguage, $sourceLanguage);
            $results[$key] = $translation !== false ? $translation : $text;
            
            // API制限を考慮して少し待機
            usleep(100000); // 0.1秒
        }
        
        return $results;
    }
    
    /**
     * APIキーの検証
     */
    public function isConfigured() {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY_HERE';
    }
    
    /**
     * キャッシュクリーンアップ
     */
    public function cleanupCache() {
        global $pdo;
        
        if (!$pdo) return;
        
        try {
            $stmt = $pdo->prepare("
                DELETE FROM translation_cache 
                WHERE created_at < ?
            ");
            $stmt->execute([
                date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60) // 30日前より古いものを削除
            ]);
            
            $deletedCount = $stmt->rowCount();
            if ($deletedCount > 0) {
                error_log("Cleaned up {$deletedCount} old translation cache entries");
            }
            
        } catch (Exception $e) {
            error_log("Translation cache cleanup error: " . $e->getMessage());
        }
    }
}
?>