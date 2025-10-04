<?php
// config/database.php
// データベース接続設定

// 環境判定：XAMPP環境かXserver環境かを自動判定
$is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
             file_exists('/Applications/XAMPP')) || 
            (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);

if ($is_xampp) {
    // XAMPP環境設定
    $host = 'localhost';
    $dbname = 'nihongonote';
    $username = 'root';
    $password = '';
} else {
    // 本番環境設定（環境変数使用）
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'glassposi_nihongonote';
    $username = $_ENV['DB_USER'] ?? 'glassposi_akemi';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    // 環境変数が設定されていない場合のエラー
    if (empty($password) && !$is_xampp) {
        die('データベース設定エラー: 環境変数DB_PASSWORDが設定されていません。');
    }
}

try {
    // 本番環境用DSN
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
    // 開発環境では詳細エラーを表示、本番環境では隠す
    if (isset($_GET['debug']) || $is_xampp || php_sapi_name() === 'cli') {
        $error_msg = "データベース接続エラー: " . $e->getMessage() . "\n\n";
        
        if ($is_xampp) {
            $error_msg .= "XAMPP環境での確認事項:\n" .
                         "1. XAMPPのMySQLが起動しているか\n" .
                         "2. データベース 'nihongonote' が作成されているか\n" .
                         "3. phpMyAdminで接続確認\n" .
                         "4. 使用設定: host=localhost, user=root, password=(空), db=nihongonote\n";
        } else {
            $error_msg .= "本番環境での確認事項:\n" .
                         "1. Xserverのデータベースが起動しているか\n" .
                         "2. データベース 'glassposi_nihongonote' が存在するか\n" .
                         "3. ユーザー 'glassposi_akemi' の権限が正しいか\n";
        }
        
        die($error_msg);
    } else {
        die("データベース接続エラーが発生しました。管理者にお問い合わせください。");
    }
}
?>