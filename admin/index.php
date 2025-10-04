<?php
// admin/index.php - 管理者ダッシュボード
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインチェック
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';

    $page_title = '管理者ダッシュボード - nihongonote';
    require_once '../includes/header.php';
} catch (Exception $e) {
    die("エラー: " . $e->getMessage());
}

// 統計情報を取得
try {
    $total_lessons = $pdo->query("SELECT COUNT(*) FROM lessons_master WHERE is_active = 1")->fetchColumn();
    $total_sub_lessons = $pdo->query("SELECT COUNT(*) FROM sub_lessons WHERE is_active = 1")->fetchColumn();
    $total_translations = $pdo->query("SELECT COUNT(*) FROM translations")->fetchColumn();
    $recent_uploads = $pdo->query("SELECT * FROM upload_history ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $total_lessons = $total_sub_lessons = $total_translations = 0;
    $recent_uploads = [];
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>管理者ダッシュボード</h1>
        <p>レッスン、動画、翻訳データの管理</p>
    </div>

    <!-- 統計カード -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total_lessons ?></div>
            <div class="stat-label">総レッスン数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_sub_lessons ?></div>
            <div class="stat-label">サブレッスン数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_translations ?></div>
            <div class="stat-label">翻訳データ数</div>
        </div>
    </div>

    <!-- 管理メニュー -->
    <div class="admin-menu-grid">
        <!-- <div class="admin-menu-card" onclick="location.href='lessons.php'">
            <h3>レッスン管理</h3>
            <p>レッスンの作成・編集・削除</p>
        </div>

        <div class="admin-menu-card" onclick="location.href='translations.php'">
            <h3>翻訳管理</h3>
            <p>Excelファイルから翻訳データをアップロード</p>
        </div>

        <div class="admin-menu-card" onclick="location.href='videos.php'">
            <h3>動画管理</h3>
            <p>レッスン動画のアップロード・管理</p>
        </div> -->

        <div class="admin-menu-card" onclick="location.href='badge_generator.php'">
            <h3>バッジ管理</h3>
            <p>バッジデザインとテキストの管理</p>
        </div>

        <div class="admin-menu-card" onclick="location.href='users.php'">
            <h3>ユーザー管理</h3>
            <p>登録ユーザーの管理</p>
        </div>

        <!-- <div class="admin-menu-card" onclick="location.href='reports.php'">
            <h3>レポート</h3>
            <p>学習進捗とアクセス統計</p>
        </div> -->
    </div>

    <!-- 最近のアップロード履歴 -->
    <div class="recent-uploads">
        <h3>最近のアップロード</h3>
        <div class="upload-list">
            <?php if (empty($recent_uploads)): ?>
                <p>アップロード履歴がありません</p>
            <?php else: ?>
                <?php foreach ($recent_uploads as $upload): ?>
                    <div class="upload-item">
                        <div class="upload-info">
                            <strong><?= h($upload['original_filename']) ?></strong>
                            <span class="upload-type"><?= h($upload['file_type']) ?></span>
                            <span class="upload-status status-<?= $upload['upload_status'] ?>"><?= h($upload['upload_status']) ?></span>
                        </div>
                        <div class="upload-date"><?= date('Y-m-d H:i', strtotime($upload['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
body {
    background-image: url('../assets/images/bg_top.png'), url('../assets/images/bg_bottom.png');
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
    background-attachment: fixed, fixed;
    min-height: 100vh;
}

.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    text-align: center;
    margin-bottom: 40px;
}

.admin-header h1 {
    color: #333;
    font-size: 2.5em;
    margin-bottom: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 3em;
    font-weight: bold;
    color: #4CAF50;
    margin-bottom: 10px;
}

.stat-label {
    color: #666;
    font-weight: 500;
}

.admin-menu-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.admin-menu-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.admin-menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.admin-menu-card h3 {
    color: #333;
    margin: 0 0 10px 0;
}

.admin-menu-card p {
    color: #666;
    font-size: 0.9em;
}

.recent-uploads {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.upload-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.upload-item:last-child {
    border-bottom: none;
}

.upload-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.upload-type {
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
}

.upload-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-error {
    background: #f8d7da;
    color: #721c24;
}

.status-processing {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .stats-grid,
    .admin-menu-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>