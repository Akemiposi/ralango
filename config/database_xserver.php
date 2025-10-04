<?php
// config/database_xserver.php
// Xserver用データベース接続設定

// 環境変数から設定を取得（セキュリティ向上）
$host = $_ENV['XSERVER_DB_HOST'] ?? 'localhost';
$dbname = $_ENV['XSERVER_DB_NAME'] ?? '';
$username = $_ENV['XSERVER_DB_USER'] ?? '';
$password = $_ENV['XSERVER_DB_PASSWORD'] ?? '';

// 設定チェック
if (empty($dbname) || empty($username) || empty($password)) {
    die('データベース設定エラー: 環境変数が設定されていません。XSERVER_DB_HOST, XSERVER_DB_NAME, XSERVER_DB_USER, XSERVER_DB_PASSWORDを設定してください。');
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        
    $pdo = new PDO(
        $dsn, 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // グローバル変数として設定
    $GLOBALS['pdo'] = $pdo;
    
} catch(PDOException $e) {
    // 本番環境では詳細エラーを隠す
    if (isset($_GET['debug'])) {
        die("データベース接続エラー: " . $e->getMessage());
    } else {
        die("データベース接続エラーが発生しました。管理者にお問い合わせください。");
    }
}
?>