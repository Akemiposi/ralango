<?php
// progress/user_list.php - ユーザー情報一覧
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'ユーザー情報一覧 - nihongonote';

requireAdmin(); // 管理者のみアクセス可能
require_once '../includes/header.php';

// 全ユーザーの詳細データを取得
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(DISTINCT CASE WHEN up.step = 'dekita' THEN up.lesson_id END) as completed_lessons,
               COUNT(b.id) as total_badges,
               MAX(up.created_at) as last_activity,
               COUNT(DISTINCT DATE(up.created_at)) as active_days,
               COUNT(up.id) as total_progress_entries,
               COALESCE(gs.avg_score, 0) as avg_game_score,
               COALESCE(gs.best_score, 0) as best_game_score,
               COALESCE(gs.total_games, 0) as total_games,
               MIN(up.created_at) as first_activity
        FROM users u 
        LEFT JOIN user_progress up ON u.id = up.user_id
        LEFT JOIN badges b ON u.id = b.user_id
        LEFT JOIN (
            SELECT user_id, 
                   AVG(score) as avg_score, 
                   MAX(score) as best_score,
                   COUNT(*) as total_games
            FROM game_scores 
            GROUP BY user_id
        ) gs ON u.id = gs.user_id
        WHERE u.role = 'user'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $error_message = "データの取得に失敗しました: " . $e->getMessage();
}

// 各ユーザーの学習時間を計算
foreach ($users as &$user) {
    $user['estimated_study_time'] = round($user['total_progress_entries'] * 5); // 分単位
    $user['study_hours'] = floor($user['estimated_study_time'] / 60);
    $user['study_minutes'] = $user['estimated_study_time'] % 60;
    $user['completion_rate'] = round(($user['completed_lessons'] / 20) * 100, 1);
}
unset($user);

// 言語コードから言語名への変換
$language_names = [
    'ja' => '日本語',
    'en' => 'English',
    'zh' => '中文',
    'ko' => '한국어',
    'vi' => 'Tiếng Việt',
    'tl' => 'Filipino',
    'ne' => 'नेपाली',
    'pt' => 'Português'
];
?>

<div class="user-list-container">
    <div class="user-list-header">
        <div class="header-content">
            <h1 class="page-title">👥 ユーザー情報一覧</h1>
            <p class="page-subtitle">登録されているユーザーの詳細情報を確認できます</p>
        </div>
        <div class="header-actions">
            <a href="admin_progress.php" class="btn btn-secondary">
                ⬅️ 管理者ダッシュボードに戻る
            </a>
            <button class="btn btn-primary" onclick="exportUserData()">
                📊 CSV出力
            </button>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <p><?= h($error_message) ?></p>
        </div>
    <?php endif; ?>

    <div class="user-stats-summary">
        <div class="stat-item">
            <div class="stat-number"><?= count($users) ?></div>
            <div class="stat-label">総ユーザー数</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?= count(array_filter($users, function($u) { return $u['last_activity'] && $u['last_activity'] > date('Y-m-d H:i:s', strtotime('-30 days')); })) ?></div>
            <div class="stat-label">30日以内の<br>アクティブユーザー</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?= array_sum(array_column($users, 'completed_lessons')) ?></div>
            <div class="stat-label">総完了レッスン数</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?= array_sum(array_column($users, 'total_badges')) ?></div>
            <div class="stat-label">総獲得バッジ数</div>
        </div>
    </div>

    <div class="user-cards-grid">
        <?php foreach ($users as $user): ?>
            <?php 
            $is_active = $user['last_activity'] && $user['last_activity'] > date('Y-m-d H:i:s', strtotime('-30 days'));
            $language_name = $language_names[$user['native_language']] ?? $user['native_language'];
            ?>
            <div class="user-card <?= $is_active ? 'active' : 'inactive' ?>">
                <div class="user-card-header">
                    <div class="user-avatar-large">
                        <?= strtoupper(substr($user['parent_name'], 0, 1)) ?>
                    </div>
                    <div class="user-basic-info">
                        <h3 class="parent-name">保護者: <?= h($user['parent_name']) ?></h3>
                        <h4 class="child-name">生徒: <?= h($user['child_name']) ?> (<?= h($user['child_gender']) ?>)</h4>
                        <p class="user-email"><?= h($user['email']) ?></p>
                        <p class="user-language"><?= h($language_name) ?></p>
                    </div>
                    <div class="user-status">
                        <?php if ($is_active): ?>
                            <span class="status-badge active">🟢 アクティブ</span>
                        <?php else: ?>
                            <span class="status-badge inactive">🔴 非アクティブ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="user-card-body">
                    <div class="progress-section">
                        <h4>📚 学習進捗</h4>
                        <div class="progress-details">
                            <div class="progress-item">
                                <span class="progress-label">完了レッスン:</span>
                                <span class="progress-value"><?= $user['completed_lessons'] ?>/20 (<?= $user['completion_rate'] ?>%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $user['completion_rate'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-content">
                                <div class="stat-title">使用日数</div>
                                <div class="stat-value"><?= $user['active_days'] ?>日</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">⏱️</div>
                            <div class="stat-content">
                                <div class="stat-title">学習時間</div>
                                <div class="stat-value"><?= $user['study_hours'] ?>h <?= $user['study_minutes'] ?>m</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">🏆</div>
                            <div class="stat-content">
                                <div class="stat-title">バッジ数</div>
                                <div class="stat-value"><?= $user['total_badges'] ?>/60</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon">🎮</div>
                            <div class="stat-content">
                                <div class="stat-title">ゲーム</div>
                                <?php if ($user['total_games'] > 0): ?>
                                    <div class="stat-value"><?= round($user['avg_game_score'], 1) ?>点</div>
                                    <div class="stat-detail"><?= $user['total_games'] ?>回プレイ</div>
                                <?php else: ?>
                                    <div class="stat-value">未プレイ</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="activity-info">
                        <div class="activity-item">
                            <strong>登録日:</strong> <?= date('Y年m月d日', strtotime($user['created_at'])) ?>
                        </div>
                        <?php if ($user['first_activity']): ?>
                        <div class="activity-item">
                            <strong>学習開始:</strong> <?= date('Y年m月d日', strtotime($user['first_activity'])) ?>
                        </div>
                        <?php endif; ?>
                        <div class="activity-item">
                            <strong>最終活動:</strong> 
                            <?php if ($user['last_activity']): ?>
                                <?= date('Y年m月d日 H:i', strtotime($user['last_activity'])) ?>
                            <?php else: ?>
                                未活動
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="user-card-footer">
                    <button class="btn btn-outline-primary" onclick="viewUserProgress(<?= $user['id'] ?>)">
                        📊 詳細進捗
                    </button>
                    <button class="btn btn-outline-secondary" onclick="resetUserProgress(<?= $user['id'] ?>)">
                        🔄 進捗リセット
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.user-list-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.user-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.page-title {
    font-size: 2.5rem;
    color: #4CAF50;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.page-subtitle {
    color: #666;
    margin: 0;
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(76,175,80,0.3);
}

.btn-secondary {
    background: #2196F3;
    color: white;
}

.btn-secondary:hover {
    background: #1976D2;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(33,150,243,0.3);
}

.user-stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-item {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 3rem;
    font-weight: bold;
    color: #4CAF50;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.user-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.user-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    padding: 25px;
    transition: all 0.3s ease;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.15);
}

.user-card.active {
    border-left: 5px solid #4CAF50;
}

.user-card.inactive {
    border-left: 5px solid #f44336;
    opacity: 0.8;
}

.user-card-header {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.user-avatar-large {
    width: 60px;
    height: 60px;
    background: linear-gradient(45deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
}

.user-basic-info {
    flex: 1;
}

.parent-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 5px 0;
}

.child-name {
    font-size: 1.1rem;
    font-weight: 500;
    color: #333;
    margin: 5px 0;
}

.user-email, .user-language {
    margin: 3px 0;
    color: #666;
    font-size: 0.9rem;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(76,175,80,0.1);
    color: #4CAF50;
}

.status-badge.inactive {
    background: rgba(244,67,54,0.1);
    color: #f44336;
}

.progress-section {
    margin-bottom: 20px;
}

.progress-section h4 {
    color: #333;
    margin: 0 0 10px 0;
    font-size: 1rem;
}

.progress-details {
    margin-bottom: 10px;
}

.progress-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.progress-label {
    color: #666;
}

.progress-value {
    font-weight: 600;
    color: #333;
}

.progress-bar {
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    border-radius: 4px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon {
    font-size: 1.5rem;
}

.stat-title {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 2px;
}

.stat-value {
    font-weight: 600;
    color: #333;
}

.stat-detail {
    font-size: 0.7rem;
    color: #999;
}

.activity-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.activity-item {
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: #666;
}

.user-card-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-outline-primary {
    background: transparent;
    border: 2px solid #4CAF50;
    color: #4CAF50;
    padding: 8px 16px;
    font-size: 0.9rem;
}

.btn-outline-primary:hover {
    background: #4CAF50;
    color: white;
}

.btn-outline-secondary {
    background: transparent;
    border: 2px solid #2196F3;
    color: #2196F3;
    padding: 8px 16px;
    font-size: 0.9rem;
}

.btn-outline-secondary:hover {
    background: #2196F3;
    color: white;
}

.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* レスポンシブ */
@media (max-width: 768px) {
    .user-list-header {
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .user-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .user-card-footer {
        justify-content: stretch;
    }
    
    .user-card-footer .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>

<script>
function viewUserProgress(userId) {
    // ユーザー詳細進捗を表示
    alert('ユーザーID ' + userId + ' の詳細進捗機能は準備中です');
}

function resetUserProgress(userId) {
    if (confirm('このユーザーの進捗をリセットしますか？この操作は取り消せません。')) {
        fetch('../api/reset_user_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('進捗をリセットしました');
                location.reload();
            } else {
                alert('リセットに失敗しました');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('エラーが発生しました');
        });
    }
}

function exportUserData() {
    // CSVエクスポート
    window.location.href = '../api/export_users.php';
}
</script>

<?php require_once '../includes/footer.php'; ?>