<?php
// admin/videos.php - 動画管理
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

$page_title = '動画管理 - nihongonote';
$message = '';

// 動画アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    try {
        $uploadDir = '../uploads/videos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $_FILES['video_file']['name'];
        $fileTmpName = $_FILES['video_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['mp4', 'webm', 'ogg', 'mov'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('対応していない動画形式です。MP4, WebM, OGG, MOVファイルを選択してください。');
        }

        // ファイルサイズチェック (100MB制限)
        if ($_FILES['video_file']['size'] > 100 * 1024 * 1024) {
            throw new Exception('ファイルサイズが大きすぎます。100MB以下のファイルを選択してください。');
        }

        $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('動画ファイルのアップロードに失敗しました。');
        }

        // データベースに動画情報を保存
        $stmt = $pdo->prepare("
            UPDATE sub_lessons 
            SET video_filename = ?, video_url = ?
            WHERE lesson_id = ? AND sub_number = ?
        ");
        $stmt->execute([
            $newFileName,
            'uploads/videos/' . $newFileName,
            $_POST['lesson_id'],
            $_POST['sub_number']
        ]);

        // アップロード履歴に記録
        $stmt = $pdo->prepare("
            INSERT INTO upload_history (admin_user_id, file_type, original_filename, file_path, upload_status, records_processed)
            VALUES (?, 'videos', ?, ?, 'success', 1)
        ");
        $stmt->execute([$_SESSION['user']['id'], $fileName, $uploadPath]);

        $message = "動画が正常にアップロードされました。";

    } catch (Exception $e) {
        $message = 'エラー: ' . $e->getMessage();
    }
}

// レッスン一覧とサブレッスンを取得
$lessons_with_subs = $pdo->query("
    SELECT lm.id as lesson_id, lm.lesson_number, lm.title_ja as lesson_title,
           sl.id as sub_lesson_id, sl.sub_number, sl.title_ja as sub_title,
           sl.video_filename, sl.video_url
    FROM lessons_master lm
    LEFT JOIN sub_lessons sl ON lm.id = sl.lesson_id AND sl.is_active = 1
    WHERE lm.is_active = 1
    ORDER BY lm.lesson_number, sl.sub_number
")->fetchAll();

// レッスンごとにグループ化
$lessons = [];
foreach ($lessons_with_subs as $row) {
    $lesson_id = $row['lesson_id'];
    if (!isset($lessons[$lesson_id])) {
        $lessons[$lesson_id] = [
            'lesson_number' => $row['lesson_number'],
            'lesson_title' => $row['lesson_title'],
            'sub_lessons' => []
        ];
    }
    if ($row['sub_lesson_id']) {
        $lessons[$lesson_id]['sub_lessons'][] = $row;
    }
}

require_once '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>動画管理</h1>
        <p>レッスン動画のアップロード・管理（1-2-3構成）</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'エラー') === 0 ? 'error' : 'success' ?>">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <!-- 動画アップロードフォーム -->
    <div class="upload-section">
        <h2>動画アップロード</h2>
        <form method="POST" enctype="multipart/form-data" class="video-upload-form">
            <div class="form-row">
                <div class="form-group">
                    <label>レッスンを選択</label>
                    <select name="lesson_id" id="lessonSelect" required>
                        <option value="">レッスンを選択してください</option>
                        <?php foreach ($lessons as $lesson_id => $lesson): ?>
                            <option value="<?= $lesson_id ?>">
                                L<?= $lesson['lesson_number'] ?> - <?= h($lesson['lesson_title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>サブレッスン</label>
                    <select name="sub_number" required>
                        <option value="1">1 - みる</option>
                        <option value="2">2 - やってみる</option>
                        <option value="3">3 - できた</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>動画ファイル（MP4, WebM, OGG, MOV - 100MB以下）</label>
                <div class="file-input-wrapper">
                    <input type="file" name="video_file" accept=".mp4,.webm,.ogg,.mov" required>
                    <label>動画ファイルを選択</label>
                </div>
            </div>

            <button type="submit" class="upload-btn">動画をアップロード</button>
        </form>
    </div>

    <!-- 既存動画一覧 -->
    <div class="videos-list">
        <h2>既存動画一覧</h2>
        <?php foreach ($lessons as $lesson_id => $lesson): ?>
            <div class="lesson-videos">
                <h3>L<?= $lesson['lesson_number'] ?> - <?= h($lesson['lesson_title']) ?></h3>
                <div class="sub-lessons-grid">
                    <?php foreach ($lesson['sub_lessons'] as $sub_lesson): ?>
                        <div class="sub-lesson-card">
                            <div class="sub-lesson-header">
                                <div class="sub-number"><?= $sub_lesson['sub_number'] ?></div>
                                <div class="sub-title"><?= h($sub_lesson['sub_title']) ?></div>
                            </div>
                            
                            <div class="video-content">
                                <?php if ($sub_lesson['video_filename']): ?>
                                    <div class="video-preview">
                                        <video controls width="300">
                                            <source src="../<?= h($sub_lesson['video_url']) ?>?v=<?= time() ?>" type="video/mp4">
                                            お使いのブラウザは動画をサポートしていません。
                                        </video>
                                        <div class="video-info">
                                            <p><strong>ファイル:</strong> <?= h($sub_lesson['video_filename']) ?></p>
                                            <div class="video-actions">
                                                <button class="replace-btn" onclick="replaceVideo(<?= $lesson_id ?>, <?= $sub_lesson['sub_number'] ?>)">
                                                    動画を差し替え
                                                </button>
                                                <button class="delete-video-btn" onclick="deleteVideo(<?= $sub_lesson['sub_lesson_id'] ?>)">
                                                    削除
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="no-video">
                                        <div class="no-video-icon">🎬</div>
                                        <p>動画が未アップロード</p>
                                        <button class="upload-video-btn" onclick="uploadVideo(<?= $lesson_id ?>, <?= $sub_lesson['sub_number'] ?>)">
                                            動画をアップロード
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
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

.upload-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.video-upload-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #555;
}

.form-group select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.file-input-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-wrapper label {
    display: block;
    padding: 12px 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s ease;
}

.file-input-wrapper:hover label {
    background: #e9ecef;
    border-color: #adb5bd;
}

.upload-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.videos-list {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.lesson-videos {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.lesson-videos:last-child {
    border-bottom: none;
}

.lesson-videos h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.sub-lessons-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.sub-lesson-card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    background: #fafafa;
}

.sub-lesson-header {
    background: #007bff;
    color: white;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sub-number {
    background: rgba(255,255,255,0.2);
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
}

.video-content {
    padding: 20px;
}

.video-preview video {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 15px;
}

.video-info p {
    font-size: 12px;
    color: #666;
    margin-bottom: 15px;
}

.video-actions {
    display: flex;
    gap: 10px;
}

.replace-btn,
.delete-video-btn,
.upload-video-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.replace-btn {
    background: #ffc107;
    color: #212529;
}

.delete-video-btn {
    background: #dc3545;
    color: white;
}

.upload-video-btn {
    background: #28a745;
    color: white;
}

.no-video {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.no-video-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .sub-lessons-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function uploadVideo(lessonId, subNumber) {
    document.getElementById('lessonSelect').value = lessonId;
    document.querySelector('select[name="sub_number"]').value = subNumber;
    document.querySelector('.upload-section').scrollIntoView({ behavior: 'smooth' });
}

function replaceVideo(lessonId, subNumber) {
    if (confirm('この動画を新しい動画で置き換えますか？')) {
        uploadVideo(lessonId, subNumber);
    }
}

function deleteVideo(subLessonId) {
    if (confirm('この動画を削除しますか？この操作は取り消せません。')) {
        // Ajax処理で動画削除
        fetch('delete_video.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                sub_lesson_id: subLessonId
            })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('削除に失敗しました: ' + data.error);
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>