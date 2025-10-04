<?php
// auth/logout.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// セッションを完全に破棄
session_destroy();

// Cookieが使用されている場合は削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 必要なファイルを読み込み
try {
    require_once '../includes/functions.php';
    // ログインページにリダイレクト
    $login_url = getBasePath('auth/login.php');
    header('Location: ' . $login_url);
} catch (Exception $e) {
    // getBasePath関数が使えない場合は直接リダイレクト
    header('Location: login.php');
}
exit;
?>
