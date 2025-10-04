<?php
// admin/users.php - ユーザー管理画面
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

    $page_title = 'ユーザー管理 - nihongonote';
    require_once '../includes/header.php';
} catch (Exception $e) {
    die("エラー: " . $e->getMessage());
}

// 進捗詳細を表示するユーザーID
$view_user_id = isset($_GET['view']) ? intval($_GET['view']) : null;

// ユーザー一覧取得
try {
    $users = $pdo->query("
        SELECT 
            u.*,
            COUNT(DISTINCT CONCAT(up.lesson_id, '-', up.step)) as completed_steps,
            COUNT(DISTINCT up.lesson_id) as completed_lessons,
            COUNT(DISTINCT b.id) as total_badges,
            COALESCE(SUM(gs.score), 0) as total_game_score,
            MAX(up.created_at) as last_activity
        FROM users u
        LEFT JOIN user_progress up ON u.id = up.user_id
        LEFT JOIN badges b ON u.id = b.user_id
        LEFT JOIN game_scores gs ON u.id = gs.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ")->fetchAll();

    // 特定ユーザーの詳細進捗取得
    $user_progress_detail = null;
    if ($view_user_id) {
        // ユーザー基本情報
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$view_user_id]);
        $user_detail = $stmt->fetch();
        
        if ($user_detail) {
            // レッスン進捗取得
            $stmt = $pdo->prepare("
                SELECT 
                    lm.id as lesson_id,
                    lm.lesson_number,
                    lm.title as lesson_title,
                    sl.id as sub_lesson_id,
                    sl.title as sub_lesson_title,
                    COUNT(DISTINCT up.step) as completed_steps,
                    COUNT(DISTINCT b.id) as earned_badges,
                    MAX(up.created_at) as last_completed,
                    GROUP_CONCAT(DISTINCT up.step ORDER BY up.step) as completed_step_list
                FROM lessons_master lm
                LEFT JOIN sub_lessons sl ON lm.id = sl.lesson_id AND sl.is_active = 1
                LEFT JOIN user_progress up ON sl.id = up.lesson_id AND up.user_id = ?
                LEFT JOIN badges b ON sl.id = b.lesson_id AND b.user_id = ?
                WHERE lm.is_active = 1
                GROUP BY lm.id, sl.id
                ORDER BY lm.lesson_number, sl.id
            ");
            $stmt->execute([$view_user_id, $view_user_id]);
            $user_progress_detail = $stmt->fetchAll();
            
            // ゲームスコア取得
            $stmt = $pdo->prepare("
                SELECT 
                    game_name,
                    SUM(score) as total_score,
                    COUNT(*) as play_count,
                    MAX(level_reached) as max_level,
                    MAX(created_at) as last_played
                FROM game_scores 
                WHERE user_id = ?
                GROUP BY game_name
                ORDER BY total_score DESC
            ");
            $stmt->execute([$view_user_id]);
            $game_scores = $stmt->fetchAll();
        }
    }

    // ユーザーをロール別に分離
    $admins = array_filter($users, function($u) { return $u['role'] === 'admin'; });
    $regular_users = array_filter($users, function($u) { return $u['role'] === 'user'; });
    
    $total_users = count($regular_users); // role='user'のみカウント
    $active_users = 0;
    $total_progress = 0;
    $total_game_points = 0;
    
    foreach ($regular_users as $user) { // role='user'のみで統計計算
        if ($user['last_activity'] && strtotime($user['last_activity']) > strtotime('-30 days')) {
            $active_users++;
        }
        $total_progress += $user['completed_lessons'];
        $total_game_points += $user['total_game_score'];
    }
    
} catch (Exception $e) {
    $users = [];
    $admins = [];
    $regular_users = [];
    $total_users = $active_users = $total_progress = $total_game_points = 0;
}

// ユーザー削除処理
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    try {
        $pdo->beginTransaction();
        
        // 関連データを削除
        $pdo->prepare("DELETE FROM user_progress WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM badges WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM game_scores WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        
        $pdo->commit();
        
        echo "<script>alert('ユーザーを削除しました。'); location.reload();</script>";
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<script>alert('削除に失敗しました: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// 履歴リセット処理
if (isset($_POST['reset_progress']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    try {
        $pdo->beginTransaction();
        
        // 学習進捗とバッジ、ゲームスコアをリセット
        $pdo->prepare("DELETE FROM user_progress WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM badges WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM game_scores WHERE user_id = ?")->execute([$user_id]);
        
        $pdo->commit();
        
        echo "<script>alert('学習履歴をリセットしました。'); location.reload();</script>";
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<script>alert('リセットに失敗しました: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>ユーザー管理</h1>
        <p>登録ユーザーの管理と進捗確認</p>
        <a href="index.php" class="btn btn-back">管理者画面に戻る</a>
    </div>

    <!-- 統計情報 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total_users ?></div>
            <div class="stat-label">総ユーザー数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $active_users ?></div>
            <div class="stat-label">アクティブユーザー</div>
            <div class="stat-note">(30日以内にアクセス)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $total_progress ?></div>
            <div class="stat-label">総完了レッスン</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($total_game_points) ?></div>
            <div class="stat-label">総ゲームポイント</div>
        </div>
    </div>


    <!-- 一般ユーザー一覧 -->
    <div class="users-section user-section-table">
        <h2>👥 ユーザー一覧 (<?= count($regular_users) ?>名)</h2>
        
        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>親の名前</th>
                        <th>子供の名前</th>
                        <th>性別</th>
                        <th>メール</th>
                        <th>母語</th>
                        <th>登録日</th>
                        <th>完了レッスン</th>
                        <th>バッジ数</th>
                        <th>ゲームスコア</th>
                        <th>最終アクティビティ</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($regular_users)): ?>
                        <tr>
                            <td colspan="12" class="no-data">ユーザーが登録されていません</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($regular_users as $user): ?>
                            <tr>
                                <td><?= h($user['id']) ?></td>
                                <td><?= h($user['parent_name']) ?></td>
                                <td><a href="../progress/user_progress.php?user_id=<?= $user['id'] ?>" class="name-link"><?= h($user['child_name']) ?></a></td>
                                <td>
                                    <span class="gender-badge gender-<?= $user['child_gender'] ?>">
                                        <?= $user['child_gender'] == 'boy' ? '男の子' : '女の子' ?>
                                    </span>
                                </td>
                                <td><?= h($user['email']) ?></td>
                                <td><?= h(getLanguageName($user['native_language'])) ?></td>
                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                <td class="progress-cell"><?= $user['completed_lessons'] ?></td>
                                <td class="badge-cell"><?= $user['total_badges'] ?></td>
                                <td class="score-cell"><?= number_format($user['total_game_score']) ?></td>
                                <td>
                                    <?php if ($user['last_activity']): ?>
                                        <?= date('Y-m-d', strtotime($user['last_activity'])) ?>
                                        <?php 
                                        $days_ago = floor((time() - strtotime($user['last_activity'])) / (60 * 60 * 24));
                                        if ($days_ago <= 7): ?>
                                            <span class="activity-recent">最近</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="activity-none">なし</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('この生徒の学習履歴をリセットしますか？（進捗・バッジ・ゲームスコアが削除されます）');">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="reset_progress" class="btn btn-warning btn-small">リセット</button>
                                    </form>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('このユーザーを削除しますか？関連データも全て削除されます。');">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-small">削除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 管理者一覧（アコーディオン） -->
    <div class="users-section admin-section-accordion">
        <div class="accordion-header" onclick="toggleAdminAccordion()">
            <h2>🔧 管理者一覧 (<?= count($admins) ?>名)</h2>
            <span class="accordion-icon">▼</span>
        </div>
        
        <div class="accordion-content" id="adminAccordion" style="display: none;">
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>親の名前</th>
                            <th>子供の名前</th>
                            <th>性別</th>
                            <th>メール</th>
                            <th>母語</th>
                            <th>登録日</th>
                            <th>最終アクティビティ</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="9" class="no-data">管理者が登録されていません</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $user): ?>
                                <tr>
                                    <td><?= h($user['id']) ?></td>
                                    <td><?= h($user['parent_name']) ?></td>
                                    <td><?= h($user['child_name']) ?></td>
                                    <td>
                                        <span class="gender-badge gender-<?= $user['child_gender'] ?>">
                                            <?= $user['child_gender'] == 'boy' ? '男の子' : '女の子' ?>
                                        </span>
                                    </td>
                                    <td><?= h($user['email']) ?></td>
                                    <td><?= h(getLanguageName($user['native_language'])) ?></td>
                                    <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['last_activity']): ?>
                                            <?= date('Y-m-d', strtotime($user['last_activity'])) ?>
                                            <?php 
                                            $days_ago = floor((time() - strtotime($user['last_activity'])) / (60 * 60 * 24));
                                            if ($days_ago <= 7): ?>
                                                <span class="activity-recent">最近</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="activity-none">なし</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('このユーザーを削除しますか？関連データも全て削除されます。');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-small">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ユーザー進捗詳細 -->
    <?php if ($view_user_id && isset($user_detail) && $user_detail): ?>
        <div class="progress-detail-section">
            <div class="progress-header">
                <h2><?= h($user_detail['child_name']) ?>さんの学習進捗</h2>
                <div class="progress-info">
                    <span class="info-item">親: <?= h($user_detail['parent_name']) ?></span>
                    <span class="info-item">性別: <?= $user_detail['child_gender'] == 'boy' ? '男の子' : '女の子' ?></span>
                    <span class="info-item">母語: <?= h(getLanguageName($user_detail['native_language'])) ?></span>
                    <span class="info-item">登録日: <?= date('Y-m-d', strtotime($user_detail['created_at'])) ?></span>
                </div>
                <a href="?" class="btn btn-secondary">一覧に戻る</a>
            </div>

            <!-- ゲームスコア表示 -->
            <?php if (!empty($game_scores)): ?>
                <div class="game-scores-section">
                    <h3>ゲームスコア</h3>
                    <div class="game-scores-grid">
                        <?php foreach ($game_scores as $game): ?>
                            <div class="game-score-card">
                                <h4><?= h($game['game_name']) ?></h4>
                                <div class="score-details">
                                    <div class="score-item">
                                        <span class="score-label">総スコア:</span>
                                        <span class="score-value"><?= number_format($game['total_score']) ?>pt</span>
                                    </div>
                                    <div class="score-item">
                                        <span class="score-label">プレイ回数:</span>
                                        <span class="score-value"><?= $game['play_count'] ?>回</span>
                                    </div>
                                    <div class="score-item">
                                        <span class="score-label">最高レベル:</span>
                                        <span class="score-value">Lv.<?= $game['max_level'] ?></span>
                                    </div>
                                    <div class="score-item">
                                        <span class="score-label">最終プレイ:</span>
                                        <span class="score-value"><?= date('Y-m-d', strtotime($game['last_played'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="progress-grid">
                <?php if (empty($user_progress_detail)): ?>
                    <p class="no-progress">学習進捗がありません</p>
                <?php else: ?>
                    <?php 
                    $current_lesson = null;
                    foreach ($user_progress_detail as $progress): 
                        if ($current_lesson != $progress['lesson_number']):
                            if ($current_lesson !== null): echo '</div></div>'; endif;
                            $current_lesson = $progress['lesson_number'];
                    ?>
                        <div class="lesson-group">
                            <h3 class="lesson-title">レッスン<?= $progress['lesson_number'] ?>: <?= h($progress['lesson_title']) ?></h3>
                            <div class="sub-lessons">
                    <?php endif; ?>
                    
                    <?php if ($progress['sub_lesson_title']): ?>
                    <div class="sub-lesson-card <?= $progress['completed_steps'] > 0 ? 'completed' : 'not-started' ?>">
                        <div class="sub-lesson-header">
                            <h4><?= h($progress['sub_lesson_title']) ?></h4>
                            <div class="progress-badges">
                                <?php if ($progress['completed_steps'] > 0): ?>
                                    <span class="progress-badge completed">完了</span>
                                <?php else: ?>
                                    <span class="progress-badge not-started">未開始</span>
                                <?php endif; ?>
                                
                                <?php if ($progress['earned_badges'] > 0): ?>
                                    <span class="badge-count">バッジ <?= $progress['earned_badges'] ?>個</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="sub-lesson-details">
                            <div class="detail-item">
                                <span class="detail-label">完了ステップ:</span>
                                <span class="detail-value"><?= $progress['completed_steps'] ?>/3</span>
                            </div>
                            
                            <?php if ($progress['last_completed']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">最終完了:</span>
                                    <span class="detail-value"><?= date('Y-m-d H:i', strtotime($progress['last_completed'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="step-progress">
                            <?php 
                            $completed_steps_array = explode(',', $progress['completed_step_list']);
                            for ($step = 1; $step <= 3; $step++): ?>
                                <div class="step-indicator <?= in_array($step, $completed_steps_array) ? 'completed' : 'pending' ?>">
                                    <?= $step ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php endforeach; ?>
                    <?php if ($current_lesson !== null): ?>
                        </div></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.admin-header h1 {
    color: #333;
    font-size: 2.5em;
    margin: 0;
}

.admin-header p {
    color: #666;
    margin: 10px 0 0 0;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-back {
    background: #6c757d;
    color: white;
}

.btn-back:hover {
    background: #545b62;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-info {
    background: #17a2b8;
    color: white;
    margin-right: 5px;
}

.btn-info:hover {
    background: #138496;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
    margin-right: 5px;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.8em;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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
    font-size: 1.1em;
}

.stat-note {
    color: #999;
    font-size: 0.8em;
    margin-top: 5px;
}

.users-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.users-section h2 {
    color: #333;
    margin-bottom: 30px;
    border-bottom: 3px solid #4CAF50;
    padding-bottom: 10px;
}

.users-table-container {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9em;
}

.users-table th,
.users-table td {
    padding: 12px 8px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.users-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px;
}

.gender-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 500;
}

.gender-boy {
    background: #e3f2fd;
    color: #1976d2;
}

.gender-girl {
    background: #fce4ec;
    color: #c2185b;
}

.progress-cell,
.badge-cell,
.score-cell {
    text-align: center;
    font-weight: 600;
    color: #4CAF50;
}

.activity-recent {
    background: #d4edda;
    color: #155724;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8em;
}

.activity-none {
    color: #999;
    font-style: italic;
}

.actions-cell {
    text-align: center;
}

/* 進捗詳細表示 */
.progress-detail-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-top: 30px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #4CAF50;
}

.progress-header h2 {
    color: #333;
    margin: 0;
    font-size: 1.8em;
}

.progress-info {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}

.info-item {
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.9em;
    color: #666;
}

.progress-grid {
    display: grid;
    gap: 30px;
}

.lesson-group {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.lesson-title {
    background: #f8f9fa;
    padding: 15px 20px;
    margin: 0;
    color: #495057;
    font-size: 1.2em;
    border-bottom: 1px solid #e9ecef;
}

.sub-lessons {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.sub-lesson-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
}

.sub-lesson-card.completed {
    border-color: #28a745;
    background: #f8fff9;
}

.sub-lesson-card.not-started {
    border-color: #dee2e6;
    background: #f8f9fa;
}

.sub-lesson-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.sub-lesson-header h4 {
    margin: 0;
    color: #333;
    font-size: 1.1em;
}

.progress-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.progress-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 600;
}

.progress-badge.completed {
    background: #d4edda;
    color: #155724;
}

.progress-badge.not-started {
    background: #f8d7da;
    color: #721c24;
}

.badge-count {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 600;
}

.sub-lesson-details {
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    color: #333;
    font-weight: 600;
}

.step-progress {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.step-indicator {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9em;
}

.step-indicator.completed {
    background: #28a745;
    color: white;
}

.step-indicator.pending {
    background: #dee2e6;
    color: #6c757d;
}

.no-progress {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px;
}

.game-scores-section {
    margin-bottom: 30px;
}

.game-scores-section h3 {
    color: #333;
    margin-bottom: 20px;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
}

.game-scores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.game-score-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
}

.game-score-card h4 {
    color: #333;
    margin: 0 0 15px 0;
    font-size: 1.1em;
    text-align: center;
}

.score-details {
    display: grid;
    gap: 8px;
}

.score-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.score-label {
    color: #666;
    font-size: 0.9em;
}

.score-value {
    color: #333;
    font-weight: 600;
}

.name-link {
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
}

.name-link:hover {
    color: #45a049;
    text-decoration: underline;
}

/* 管理者とユーザーセクションの区別 */
.admin-section-table {
    margin-bottom: 40px;
    border-left: 5px solid #ff6b6b;
}

.admin-section-table h2 {
    color: #ff6b6b;
    border-bottom: 3px solid #ff6b6b;
}

/* 管理者アコーディオンスタイル */
.admin-section-accordion {
    margin-bottom: 40px;
    border-left: 5px solid #ff6b6b;
    border-radius: 15px;
    overflow: hidden;
}

.accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
    padding: 20px 30px;
    background: #fff5f5;
}

.accordion-header:hover {
    background: #ffe6e6;
}

.accordion-header h2 {
    color: #ff6b6b;
    margin: 0;
    border-bottom: none;
}

.accordion-icon {
    font-size: 18px;
    color: #ff6b6b;
    transition: transform 0.3s ease;
}

.accordion-icon.rotated {
    transform: rotate(180deg);
}

.accordion-content {
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: white;
}

.accordion-content .users-table-container {
    padding: 0 30px 30px 30px;
}

.user-section-table {
    border-left: 5px solid #4CAF50;
}

.user-section-table h2 {
    color: #4CAF50;
    border-bottom: 3px solid #4CAF50;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .users-table {
        font-size: 0.8em;
    }
    
    .users-table th,
    .users-table td {
        padding: 8px 4px;
    }
    
    .progress-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .progress-info {
        justify-content: center;
    }
    
    .sub-lessons {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleAdminAccordion() {
    const accordion = document.getElementById('adminAccordion');
    const icon = document.querySelector('.accordion-icon');
    
    if (accordion.style.display === 'none' || accordion.style.display === '') {
        accordion.style.display = 'block';
        icon.classList.add('rotated');
        icon.textContent = '▲';
    } else {
        accordion.style.display = 'none';
        icon.classList.remove('rotated');
        icon.textContent = '▼';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>