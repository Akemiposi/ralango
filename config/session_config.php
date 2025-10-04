<?php
// config/session_config.php
// セッションセキュリティ設定

// セッション設定（セッション開始前に設定）
if (session_status() === PHP_SESSION_NONE) {
    // セッションCookieのセキュリティ設定
    ini_set('session.cookie_httponly', 1);      // JavaScriptからアクセス不可
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS時のみ送信
    ini_set('session.cookie_samesite', 'Strict'); // CSRF攻撃対策
    ini_set('session.use_only_cookies', 1);      // URLパラメータでのセッションID無効
    ini_set('session.cookie_lifetime', 0);       // ブラウザ終了時に削除
    
    // セッションタイムアウト設定（30分）
    ini_set('session.gc_maxlifetime', 1800);
    
    // セッション再生成間隔設定
    ini_set('session.cookie_path', '/');
    
    session_start();
    
    // セッションハイジャック対策：定期的にセッションIDを再生成
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5分ごと
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    }
    
    // セッションタイムアウトチェック
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > 1800)) { // 30分
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?>