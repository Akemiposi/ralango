<?php
// admin/lessons.php - レッスン管理
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインチェック
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'レッスン管理 - nihongonote';
$message = '';

// レッスン追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_lesson') {
            $stmt = $pdo->prepare("
                INSERT INTO lessons_master (lesson_number, title_ja, title_en, title_zh, description_ja, description_en, description_zh)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['lesson_number'],
                $_POST['title_ja'],
                $_POST['title_en'],
                $_POST['title_zh'],
                $_POST['description_ja'],
                $_POST['description_en'],
                $_POST['description_zh']
            ]);
            $lesson_id = $pdo->lastInsertId();

            // サブレッスンを自動作成（1-2-3構成）
            for ($i = 1; $i <= 3; $i++) {
                $stmt = $pdo->prepare("
                    INSERT INTO sub_lessons (lesson_id, sub_number, title_ja, title_en, title_zh)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $lesson_id,
                    $i,
                    $_POST["sub_title_ja_{$i}"] ?? "サブレッスン{$i}",
                    $_POST["sub_title_en_{$i}"] ?? "Sub Lesson {$i}",
                    $_POST["sub_title_zh_{$i}"] ?? "子课程{$i}"
                ]);
            }

            $message = 'レッスンが正常に作成されました。';

        } elseif ($_POST['action'] === 'delete_lesson') {
            $stmt = $pdo->prepare("UPDATE lessons_master SET is_active = 0 WHERE id = ?");
            $stmt->execute([$_POST['lesson_id']]);
            $message = 'レッスンが削除されました。';
        }
    } catch (Exception $e) {
        $message = 'エラー: ' . $e->getMessage();
    }
}

// レッスン一覧を取得
$lessons = $pdo->query("
    SELECT lm.*, 
           COUNT(sl.id) as sub_lesson_count,
           COUNT(CASE WHEN sl.video_filename IS NOT NULL THEN 1 END) as video_count
    FROM lessons_master lm
    LEFT JOIN sub_lessons sl ON lm.id = sl.lesson_id AND sl.is_active = 1
    WHERE lm.is_active = 1
    GROUP BY lm.id
    ORDER BY lm.lesson_number
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>レッスン管理</h1>
        <p>レッスンとサブレッスンの作成・編集・管理</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'エラー') === 0 ? 'error' : 'success' ?>">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <!-- 新規レッスン作成フォーム -->
    <div class="create-lesson-section">
        <h2>新規レッスン作成</h2>
        <form method="POST" class="lesson-form">
            <input type="hidden" name="action" value="add_lesson">
            
            <div class="form-row">
                <div class="form-group">
                    <label>レッスン番号</label>
                    <input type="number" name="lesson_number" required>
                </div>
            </div>

            <div class="form-section">
                <h3>レッスンタイトル</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>日本語</label>
                        <input type="text" name="title_ja" required>
                    </div>
                    <div class="form-group">
                        <label>English</label>
                        <input type="text" name="title_en">
                    </div>
                    <div class="form-group">
                        <label>中文</label>
                        <input type="text" name="title_zh">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>レッスン説明</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>日本語</label>
                        <textarea name="description_ja" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>English</label>
                        <textarea name="description_en" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>中文</label>
                        <textarea name="description_zh" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>サブレッスンタイトル（1-2-3構成）</h3>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="sub-lesson-group">
                        <h4>サブレッスン <?= $i ?></h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>日本語</label>
                                <input type="text" name="sub_title_ja_<?= $i ?>">
                            </div>
                            <div class="form-group">
                                <label>English</label>
                                <input type="text" name="sub_title_en_<?= $i ?>">
                            </div>
                            <div class="form-group">
                                <label>中文</label>
                                <input type="text" name="sub_title_zh_<?= $i ?>">
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <button type="submit" class="create-btn">レッスンを作成</button>
        </form>
    </div>

    <!-- 既存レッスン一覧 -->
    <div class="lessons-list">
        <h2>既存レッスン一覧</h2>
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-card">
                    <div class="lesson-header">
                        <div class="lesson-number">L<?= $lesson['lesson_number'] ?></div>
                        <div class="lesson-actions">
                            <button class="edit-btn" onclick="editLesson(<?= $lesson['id'] ?>)">編集</button>
                            <button class="delete-btn" onclick="deleteLesson(<?= $lesson['id'] ?>)">削除</button>
                        </div>
                    </div>
                    <div class="lesson-content">
                        <h3><?= h($lesson['title_ja']) ?></h3>
                        <p class="lesson-description"><?= h($lesson['description_ja']) ?></p>
                        <div class="lesson-stats">
                            <span class="stat">📝 サブレッスン: <?= $lesson['sub_lesson_count'] ?></span>
                            <span class="stat">🎬 動画: <?= $lesson['video_count'] ?></span>
                        </div>
                    </div>
                    <div class="lesson-languages">
                        <div class="lang-item">
                            <strong>EN:</strong> <?= h($lesson['title_en']) ?>
                        </div>
                        <div class="lang-item">
                            <strong>ZH:</strong> <?= h($lesson['title_zh']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 500;
}

.message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.create-lesson-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.lesson-form {
    max-width: 800px;
}

.form-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.form-section h3 {
    color: #333;
    margin-bottom: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #555;
}

.form-group input,
.form-group textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.sub-lesson-group {
    margin-bottom: 20px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.sub-lesson-group h4 {
    color: #666;
    margin-bottom: 10px;
}

.create-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
}

.lessons-list {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.lesson-card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    background: #fafafa;
}

.lesson-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.lesson-number {
    background: #007bff;
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: bold;
}

.lesson-actions {
    display: flex;
    gap: 10px;
}

.edit-btn,
.delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.edit-btn {
    background: #ffc107;
    color: #212529;
}

.delete-btn {
    background: #dc3545;
    color: white;
}

.lesson-content h3 {
    color: #333;
    margin-bottom: 10px;
}

.lesson-description {
    color: #666;
    margin-bottom: 15px;
    font-size: 14px;
}

.lesson-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.stat {
    font-size: 12px;
    color: #888;
}

.lesson-languages {
    border-top: 1px solid #e0e0e0;
    padding-top: 15px;
}

.lang-item {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .lessons-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function editLesson(id) {
    // 編集機能の実装
    window.location.href = `lesson_edit.php?id=${id}`;
}

function deleteLesson(id) {
    if (confirm('このレッスンを削除しますか？サブレッスンと動画も削除されます。')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_lesson">
            <input type="hidden" name="lesson_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>