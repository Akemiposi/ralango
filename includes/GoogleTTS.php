<?php
// includes/GoogleTTS.php
// Google Text-to-Speech API クラス

require_once __DIR__ . '/../config/api_config.php';

class GoogleTTS {
    private $apiKey;
    private $endpoint;
    
    public function __construct() {
        $this->apiKey = GOOGLE_TTS_API_KEY;
        $this->endpoint = GOOGLE_TTS_ENDPOINT;
    }
    
    /**
     * テキストを音声に変換
     * @param string $text 変換するテキスト
     * @param string $languageCode 言語コード（ja-JP等）
     * @param string $voiceName 音声名
     * @return array|false 成功時は音声データ、失敗時はfalse
     */
    public function synthesize($text, $languageCode = 'ja-JP', $voiceName = null) {
        // 音声設定を取得
        $voiceConfig = TTS_VOICE_CONFIG['ja'];
        if ($voiceName) {
            $voiceConfig['name'] = $voiceName;
        }
        
        $payload = [
            'input' => [
                'text' => $text
            ],
            'voice' => [
                'languageCode' => $voiceConfig['languageCode'],
                'name' => $voiceConfig['name'],
                'ssmlGender' => $voiceConfig['ssmlGender']
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
                'speakingRate' => 0.9, // 少しゆっくり
                'pitch' => 0.0,
                'volumeGainDb' => 0.0
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
            error_log("Google TTS cURL Error: " . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log("Google TTS HTTP Error: " . $httpCode . " - " . $response);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['audioContent'])) {
            error_log("Google TTS API Error: No audio content in response");
            return false;
        }
        
        return [
            'audioContent' => $data['audioContent'],
            'mimeType' => 'audio/mpeg'
        ];
    }
    
    /**
     * 音声を生成してファイルに保存
     * @param string $text テキスト
     * @param string $languageCode 言語コード
     * @return string|false 音声ファイルのURL、失敗時はfalse
     */
    public function generateAudioFile($text, $languageCode = 'ja-JP') {
        // キャッシュファイル名を生成（テキストのハッシュベース）
        $filename = md5($text . $languageCode) . '.mp3';
        $filepath = AUDIO_CACHE_DIR . $filename;
        $fileurl = AUDIO_CACHE_URL . $filename;
        
        // キャッシュファイルが存在し、有効期限内の場合は既存ファイルを使用
        if (file_exists($filepath) && 
            (time() - filemtime($filepath)) < AUDIO_CACHE_LIFETIME) {
            return $fileurl;
        }
        
        // 音声を生成
        $audioData = $this->synthesize($text, $languageCode);
        if (!$audioData) {
            return false;
        }
        
        // Base64デコードして保存
        $audioContent = base64_decode($audioData['audioContent']);
        if (file_put_contents($filepath, $audioContent) === false) {
            error_log("Failed to save audio file: " . $filepath);
            return false;
        }
        
        return $fileurl;
    }
    
    /**
     * キャッシュクリーンアップ
     * 古い音声ファイルを削除
     */
    public function cleanupCache() {
        if (!is_dir(AUDIO_CACHE_DIR)) {
            return;
        }
        
        $files = glob(AUDIO_CACHE_DIR . '*.mp3');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if ((time() - filemtime($file)) > AUDIO_CACHE_LIFETIME) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        if ($deletedCount > 0) {
            error_log("Cleaned up {$deletedCount} old audio cache files");
        }
    }
    
    /**
     * APIキーの検証
     * @return bool APIキーが設定されているかチェック
     */
    public function isConfigured() {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_GOOGLE_TTS_API_KEY_HERE';
    }
    
    /**
     * 利用可能な音声リストを取得
     * @return array 音声リスト
     */
    public function getAvailableVoices($languageCode = 'ja-JP') {
        $endpoint = 'https://texttospeech.googleapis.com/v1/voices';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return [];
        }
        
        $data = json_decode($response, true);
        if (!isset($data['voices'])) {
            return [];
        }
        
        // 指定言語の音声のみフィルタ
        $voices = array_filter($data['voices'], function($voice) use ($languageCode) {
            return isset($voice['languageCodes']) && 
                   in_array($languageCode, $voice['languageCodes']);
        });
        
        return $voices;
    }
}
?>
