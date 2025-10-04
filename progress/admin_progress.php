<?php
// progress/admin_progress.php - 管理者用進捗管理
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = '管理者ダッシュボード - nihongonote';

requireAdmin(); // 管理者のみアクセス可能
require_once '../includes/header.php';

// 全ユーザーのデータを取得
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
               COALESCE(gs.total_games, 0) as total_games
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
        ORDER BY last_activity DESC
    ");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
}

// 統計データ
$total_users = count($users);
$active_users_30d = 0;
$total_completed_lessons = 0;
$total_badges_earned = 0;

$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));

foreach ($users as &$user) {
    if ($user['last_activity'] && $user['last_activity'] > $thirty_days_ago) {
        $active_users_30d++;
    }
    $total_completed_lessons += $user['completed_lessons'];
    $total_badges_earned += $user['total_badges'];
    
    // 学習時間の推定計算（進捗エントリー数 × 平均5分）
    $user['estimated_study_time'] = round($user['total_progress_entries'] * 5); // 分単位
    $user['study_hours'] = floor($user['estimated_study_time'] / 60);
    $user['study_minutes'] = $user['estimated_study_time'] % 60;
}
unset($user); // 参照を解除

// レッスン別統計
try {
    $stmt = $pdo->query("
        SELECT lesson_id, 
               COUNT(DISTINCT user_id) as users_started,
               SUM(CASE WHEN step = 'dekita' THEN 1 ELSE 0 END) as users_completed
        FROM user_progress 
        GROUP BY lesson_id 
        ORDER BY lesson_id
    ");
    $lesson_stats = $stmt->fetchAll();
} catch (Exception $e) {
    $lesson_stats = [];
}

// 最近の活動
try {
    $stmt = $pdo->query("
        SELECT up.*, u.parent_name, u.child_name
        FROM user_progress up
        JOIN users u ON up.user_id = u.id
        ORDER BY up.created_at DESC
        LIMIT 20
    ");
    $recent_activities = $stmt->fetchAll();
} catch (Exception $e) {
    $recent_activities = [];
}

// レッスン名マップ
$lessons = [
    1 => 'あいさつ', 2 => '自己紹介', 3 => '自己紹介＋数字', 4 => '数字', 5 => 'ひらがな',
    6 => '時計', 7 => '学用品', 8 => '色', 9 => '曜日', 10 => '天気',
    11 => '学校生活1', 12 => '学校生活2', 13 => '買い物', 14 => '季節', 15 => '食事',
    16 => '健康', 17 => '地域', 18 => '家族', 19 => '趣味', 20 => '総復習'
];
?>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">管理者ダッシュボード</h1>
        <p class="admin-subtitle">
            nihongonoteの利用状況と学習進捗を管理できます
        </p>
    </div>

    <!-- 全体統計 -->
    <div class="overview-stats">
        <div class="overview-card">
            <div class="overview-icon"></div>
            <div class="overview-content">
                <div class="overview-number"><?= $total_users ?></div>
                <div class="overview-label">総ユーザー数</div>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="overview-icon"></div>
            <div class="overview-content">
                <div class="overview-number"><?= $active_users_30d ?></div>
                <div class="overview-label">アクティブユーザー<br><small>過去30日</small></div>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="overview-icon"></div>
            <div class="overview-content">
                <div class="overview-number"><?= $total_completed_lessons ?></div>
                <div class="overview-label">完了レッスン数<br><small>累計</small></div>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="overview-icon"></div>
            <div class="overview-content">
                <div class="overview-number"><?= $total_badges_earned ?></div>
                <div class="overview-label">獲得バッジ数<br><small>累計</small></div>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <!-- ユーザー一覧 -->
        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">ユーザー管理</h2>
                <div class="section-actions">
                    <a href="user_list.php" class="btn btn-primary">
                        ユーザー情報一覧
                    </a>
                    <button class="btn btn-secondary" onclick="exportUserData()">
                        データエクスポート
                    </button>
                </div>
            </div>
            
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ユーザー情報</th>
                            <th>学習進捗</th>
                            <th>使用日数</th>
                            <th>学習時間</th>
                            <th>ゲームスコア</th>
                            <th>バッジ</th>
                            <th>最終活動</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $completion_rate = round(($user['completed_lessons'] / 20) * 100, 1);
                            $is_active = $user['last_activity'] && $user['last_activity'] > $thirty_days_ago;
                            ?>
                            <tr class="user-row <?= $is_active ? 'active' : 'inactive' ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-admin">
                                            <?= strtoupper(substr($user['parent_name'], 0, 1)) ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?= h($user['parent_name']) ?></div>
                                            <div class="child-name">子: <?= h($user['child_name']) ?></div>
                                            <div class="user-meta">
                                                <?= h($user['email']) ?> | 
                                                <?= strtoupper($user['native_language']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-info">
                                        <div class="progress-number">
                                            <?= $user['completed_lessons'] ?>/20
                                        </div>
                                        <div class="progress-bar-small">
                                            <div class="progress-fill-small" style="width: <?= $completion_rate ?>%"></div>
                                        </div>
                                        <div class="progress-percent"><?= $completion_rate ?>%</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="usage-info">
                                        <div class="usage-days"><?= isset($user['active_days']) ? $user['active_days'] : '0' ?>日</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="time-info">
                                        <div class="study-time">
                                            <?= isset($user['study_hours']) ? $user['study_hours'] : '0' ?>時間<?= isset($user['study_minutes']) ? $user['study_minutes'] : '0' ?>分
                                        </div>
                                        <div class="time-detail"><?= isset($user['estimated_study_time']) ? $user['estimated_study_time'] : '0' ?>分</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="game-score-info">
                                        <?php if (isset($user['total_games']) && $user['total_games'] > 0): ?>
                                            <div class="avg-score">平均: <?= round($user['avg_game_score'], 1) ?>点</div>
                                            <div class="best-score">最高: <?= round($user['best_game_score'], 1) ?>点</div>
                                            <div class="game-count"><?= $user['total_games'] ?>回</div>
                                        <?php else: ?>
                                            <div class="no-games">未プレイ</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge-count">
                                        <span class="badge-number"><?= $user['total_badges'] ?></span>
                                        <span class="badge-max">/60</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="last-activity">
                                        <?php if ($user['last_activity']): ?>
                                            <?= date('n/j H:i', strtotime($user['last_activity'])) ?>
                                        <?php else: ?>
                                            <span class="no-activity">未活動</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-small btn-primary" 
                                                onclick="viewUserDetails(<?= $user['id'] ?>)">
                                            詳細
                                        </button>
                                        <button class="btn-small btn-secondary" 
                                                onclick="resetUserProgress(<?= $user['id'] ?>)">
                                            リセット
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- レッスン統計 -->
        <div class="admin-section">
            <h2 class="section-title">レッスン別統計</h2>
            
            <div class="lesson-stats-grid">
                <?php foreach ($lesson_stats as $stat): ?>
                    <?php 
                    $lesson_title = $lessons[$stat['lesson_id']] ?? "レッスン{$stat['lesson_id']}";
                    $completion_rate = $stat['users_started'] > 0 ? 
                        round(($stat['users_completed'] / $stat['users_started']) * 100, 1) : 0;
                    ?>
                    
                    <div class="lesson-stat-card">
                        <div class="lesson-stat-header">
                            <div class="lesson-number-admin">L<?= $stat['lesson_id'] ?></div>
                            <div class="lesson-name-admin"><?= h($lesson_title) ?></div>
                        </div>
                        
                        <div class="lesson-stat-body">
                            <div class="stat-row">
                                <span>開始者:</span>
                                <span><?= $stat['users_started'] ?>人</span>
                            </div>
                            <div class="stat-row">
                                <span>完了者:</span>
                                <span><?= $stat['users_completed'] ?>人</span>
                            </div>
                            <div class="completion-rate">
                                完了率: <?= $completion_rate ?>%
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 最近の活動 -->
        <div class="admin-section">
            <h2 class="section-title">最近の学習活動</h2>
            
            <div class="recent-activities-admin">
                <?php foreach ($recent_activities as $activity): ?>
                    <?php
                    $lesson_title = $lessons[$activity['lesson_id']] ?? "レッスン{$activity['lesson_id']}";
                    $step_names = [
                        'miru' => 'みる',
                        'yatte' => 'やってみる',
                        'dekita' => 'できた'
                    ];
                    $step_name = $step_names[$activity['step']] ?? $activity['step'];
                    $step_icons = [
                        'miru' => '',
                        'yatte' => '',
                        'dekita' => ''
                    ];
                    $step_icon = $step_icons[$activity['step']] ?? '';
                    ?>
                    
                    <div class="activity-item-admin">
                        <div class="activity-icon-admin"><?= $step_icon ?></div>
                        <div class="activity-content-admin">
                            <div class="activity-user">
                                <?= h($activity['child_name']) ?> (<?= h($activity['parent_name']) ?>)
                            </div>
                            <div class="activity-lesson">
                                L<?= $activity['lesson_id'] ?> <?= h($lesson_title) ?> - <?= h($step_name) ?>
                            </div>
                            <div class="activity-time-admin">
                                <?= date('n月j日 H:i', strtotime($activity['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ユーザー詳細モーダル -->
<div id="userDetailModal" class="admin-modal">
    <div class="admin-modal-content">
        <div class="modal-header">
            <h2 class="modal-title">ユーザー詳細</h2>
            <button class="modal-close" onclick="closeUserDetails()">&times;</button>
        </div>
        <div class="modal-body" id="userDetailContent">
            <!-- ユーザー詳細がここに動的に読み込まれます -->
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    text-align: center;
    margin-bottom: 40px;
}

.admin-title {
    font-size: 3em;
    color: #4CAF50;
    margin-bottom: 15px;
    font-weight: 700;
}

.admin-subtitle {
    font-size: 1.2em;
    color: #666;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.overview-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.overview-icon {
    font-size: 3em;
}

.overview-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #4CAF50;
    line-height: 1;
}

.overview-label {
    color: #666;
    margin-top: 5px;
}

.admin-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 1.5em;
    color: #4CAF50;
    font-weight: 600;
    margin: 0;
}

.section-actions {
    display: flex;
    gap: 10px;
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

.users-table-container {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.users-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.users-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.user-row.active {
    background: rgba(76,175,80,0.05);
}

.user-row.inactive {
    opacity: 0.7;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar-admin {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.user-name {
    font-weight: 600;
    color: #333;
}

.child-name {
    font-size: 0.9em;
    color: #666;
    margin-top: 2px;
}

.user-meta {
    font-size: 0.8em;
    color: #999;
    margin-top: 2px;
}

.progress-info {
    text-align: center;
}

.progress-number {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.progress-bar-small {
    width: 80px;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
    margin: 5px auto;
}

.progress-fill-small {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    border-radius: 3px;
}

.progress-percent {
    font-size: 0.8em;
    color: #666;
}

.usage-info {
    text-align: center;
}

.usage-days {
    font-weight: 600;
    color: #2196F3;
    font-size: 1.1em;
}

.time-info {
    text-align: center;
}

.study-time {
    font-weight: 600;
    color: #4CAF50;
    font-size: 0.9em;
    margin-bottom: 2px;
}

.time-detail {
    font-size: 0.7em;
    color: #999;
}

.game-score-info {
    text-align: center;
    font-size: 0.8em;
}

.avg-score {
    font-weight: 600;
    color: #FF5722;
    margin-bottom: 1px;
}

.best-score {
    color: #FF9800;
    margin-bottom: 1px;
}

.game-count {
    color: #666;
    font-size: 0.7em;
}

.no-games {
    color: #999;
    font-style: italic;
}

.badge-count {
    text-align: center;
}

.badge-number {
    font-size: 1.2em;
    font-weight: bold;
    color: #ff9800;
}

.badge-max {
    color: #666;
    font-size: 0.9em;
}

.last-activity {
    font-size: 0.9em;
    color: #666;
}

.no-activity {
    color: #999;
    font-style: italic;
}

.user-actions {
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.8em;
    border-radius: 15px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-small:hover {
    transform: translateY(-1px);
}

.lesson-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.lesson-stat-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid #4CAF50;
}

.lesson-stat-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.lesson-number-admin {
    background: #4CAF50;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8em;
    font-weight: bold;
}

.lesson-name-admin {
    font-weight: 600;
    color: #333;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.9em;
}

.completion-rate {
    margin-top: 10px;
    font-weight: 600;
    color: #4CAF50;
    text-align: center;
}

.recent-activities-admin {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item-admin {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 8px;
}

.activity-icon-admin {
    font-size: 1.5em;
}

.activity-user {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.activity-lesson {
    color: #666;
    margin-bottom: 2px;
}

.activity-time-admin {
    font-size: 0.8em;
    color: #999;
}

.admin-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
}

.admin-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

/* レスポンシブ */
@media (max-width: 768px) {
    .overview-stats {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .users-table-container {
        font-size: 0.8em;
    }
    
    .user-actions {
        flex-direction: column;
    }
    
    .lesson-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function viewUserDetails(userId) {
    // ユーザー詳細を取得して表示
    fetch(`../api/get_user_details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('userDetailModal');
                const content = document.getElementById('userDetailContent');
                
                // ユーザー詳細HTMLを構築
                content.innerHTML = `
                    <div class="user-detail-info">
                        <h3>${data.user.parent_name} - ${data.user.child_name}</h3>
                        <p>Email: ${data.user.email}</p>
                        <p>言語: ${data.user.native_language.toUpperCase()}</p>
                        <p>登録日: ${new Date(data.user.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="user-progress-detail">
                        <h4>学習進捗詳細</h4>
                        <!-- 詳細な進捗データを表示 -->
                    </div>
                `;
                
                modal.style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            NihongonoteApp.utils.showNotification('ユーザー詳細の取得に失敗しました', 'error');
        });
}

function closeUserDetails() {
    document.getElementById('userDetailModal').style.display = 'none';
}

function resetUserProgress(userId) {
    if (confirm('このユーザーの進捗をリセットしますか？この操作は取り消せません。')) {
        // 進捗リセットAPI呼び出し
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
                NihongonoteApp.utils.showNotification('進捗をリセットしました', 'success');
                location.reload();
            } else {
                NihongonoteApp.utils.showNotification('リセットに失敗しました', 'error');
            }
        });
    }
}

function exportUserData() {
    // ユーザーデータのCSVエクスポート
    window.location.href = '../api/export_users.php';
}

// モーダル外クリックで閉じる
document.getElementById('userDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUserDetails();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>