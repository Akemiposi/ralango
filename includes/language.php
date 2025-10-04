<?php
// 言語管理システム

class LanguageManager {
    private $language;
    private $texts = [];
    
    public function __construct($language = 'ja') {
        $this->language = $language;
    }
    
    public function setLanguage($language) {
        $this->language = $language;
        $_SESSION['language'] = $language;
    }
    
    public function getLanguage() {
        return $this->language;
    }
    
    // テキスト取得（翻訳未対応の場合は日本語を返す）
    public function getText($key, $japanese_text = '') {
        // まずセッションから言語を取得
        if (isset($_SESSION['language'])) {
            $this->language = $_SESSION['language'];
        }
        
        // ユーザーがログインしている場合、ユーザーの設定言語を優先
        if (isset($_SESSION['user']) && !isset($_SESSION['language_override'])) {
            // セッションから直接ユーザーの言語設定を取得
            if (isset($_SESSION['user']['preferred_language'])) {
                $this->language = $_SESSION['user']['preferred_language'];
            }
        }
        
        // 翻訳テキストを取得
        if ($this->language === 'ja' || empty($japanese_text)) {
            return $japanese_text;
        }
        
        // データベースから翻訳を取得
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT {$this->language} FROM translations WHERE text_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $translation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($translation && !empty($translation[$this->language])) {
                return $translation[$this->language];
            }
        } catch (Exception $e) {
            // エラーの場合は日本語テキストを返す
        }
        
        // 翻訳が見つからない場合は日本語テキストを返す
        return $japanese_text;
    }
    
    // 利用可能な言語リスト
    public function getAvailableLanguages() {
        return [
            'ja' => '日本語',
            'en' => 'English',
            'zh' => '中文',
            'tl' => 'Tagalog'
        ];
    }
    
    // 言語名を取得
    public function getLanguageName($language_code) {
        $languages = $this->getAvailableLanguages();
        return isset($languages[$language_code]) ? $languages[$language_code] : $language_code;
    }
}

// グローバル関数として使用可能にする
function t($key, $japanese_text = '') {
    global $lang;
    if (!isset($lang)) {
        $lang = new LanguageManager();
    }
    return $lang->getText($key, $japanese_text);
}

// 言語マネージャーのインスタンスを初期化
if (!isset($lang)) {
    $default_language = 'ja';
    if (isset($_SESSION['language'])) {
        $default_language = $_SESSION['language'];
    }
    $lang = new LanguageManager($default_language);
}
?>