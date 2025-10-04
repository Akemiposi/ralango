<?php
// admin/translations.php - 翻訳データ管理
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

$page_title = '翻訳管理 - nihongonote';
$message = '';

// ファイルアップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['translation_file'])) {
    try {
        $uploadDir = '../uploads/translations/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $_FILES['translation_file']['name'];
        $fileTmpName = $_FILES['translation_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, ['xlsx', 'csv'])) {
            throw new Exception('対応していないファイル形式です。Excel(.xlsx)またはCSV(.csv)ファイルを選択してください。');
        }

        $uploadPath = $uploadDir . time() . '_' . $fileName;
        
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('ファイルのアップロードに失敗しました。');
        }

        // アップロード履歴に記録
        $stmt = $pdo->prepare("INSERT INTO upload_history (admin_user_id, file_type, original_filename, file_path, upload_status) VALUES (?, 'translations', ?, ?, 'processing')");
        $stmt->execute([$_SESSION['user']['id'], $fileName, $uploadPath]);
        $uploadHistoryId = $pdo->lastInsertId();

        // ファイルを処理
        $processedCount = processTranslationFile($uploadPath, $fileExtension, $pdo);

        // 処理完了をアップデート
        $stmt = $pdo->prepare("UPDATE upload_history SET upload_status = 'success', records_processed = ? WHERE id = ?");
        $stmt->execute([$processedCount, $uploadHistoryId]);

        $message = "翻訳データのアップロードが完了しました。{$processedCount}件のレコードが処理されました。";

    } catch (Exception $e) {
        if (isset($uploadHistoryId)) {
            $stmt = $pdo->prepare("UPDATE upload_history SET upload_status = 'error', error_message = ? WHERE id = ?");
            $stmt->execute([$e->getMessage(), $uploadHistoryId]);
        }
        $message = 'エラー: ' . $e->getMessage();
    }
}

// 現在の翻訳データを取得
$translations = $pdo->query("
    SELECT t.*, lm.title_ja as lesson_title, sl.title_ja as sub_lesson_title 
    FROM translations t
    LEFT JOIN lessons_master lm ON t.lesson_id = lm.id
    LEFT JOIN sub_lessons sl ON t.sub_lesson_id = sl.id
    ORDER BY t.lesson_id, t.sub_lesson_id, t.text_key
")->fetchAll();

require_once '../includes/header.php';

function processTranslationFile($filePath, $extension, $pdo) {
    if ($extension === 'csv') {
        return processCsvFile($filePath, $pdo);
    } else {
        return processExcelFile($filePath, $pdo);
    }
}

function processCsvFile($filePath, $pdo) {
    $count = 0;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 5) {
                $stmt = $pdo->prepare("
                    INSERT INTO translations (lesson_id, sub_lesson_id, text_key, japanese, english, chinese)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    japanese = VALUES(japanese),
                    english = VALUES(english),
                    chinese = VALUES(chinese)
                ");
                $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5] ?? '']);
                $count++;
            }
        }
        fclose($handle);
    }
    return $count;
}

function processExcelFile($filePath, $pdo) {
    // PHPSpreadsheetが必要（Composerでインストール）
    // この実装は簡略版です
    return processCsvFile($filePath, $pdo);
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>翻訳管理</h1>
        <p>Excelファイルから翻訳データをアップロード・管理</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'エラー') === 0 ? 'error' : 'success' ?>">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <!-- アップロードフォーム -->
    <div class="upload-section">
        <h2>翻訳ファイルのアップロード</h2>
        <div class="upload-instructions">
            <h3>ファイル形式</h3>
            <p>CSVまたはExcelファイル(.xlsx)で以下の列構成にしてください：</p>
            <ul>
                <li><strong>lesson_id</strong>: レッスンID (例: 1, 2, 3)</li>
                <li><strong>sub_lesson_id</strong>: サブレッスンID (例: 1, 2, 3)</li>
                <li><strong>text_key</strong>: テキストキー (例: title, content)</li>
                <li><strong>japanese</strong>: 日本語テキスト</li>
                <li><strong>english</strong>: 英語テキスト</li>
                <li><strong>chinese</strong>: 中国語テキスト</li>
            </ul>
            <a href="sample_translations.csv" class="download-sample">サンプルファイルをダウンロード</a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="file-input-wrapper">
                <input type="file" name="translation_file" accept=".xlsx,.csv" required>
                <label>ファイルを選択</label>
            </div>
            <button type="submit" class="upload-btn">アップロード</button>
        </form>
    </div>

    <!-- 現在の翻訳データ -->
    <div class="translations-list">
        <h2>現在の翻訳データ</h2>
        <div class="translations-table-wrapper">
            <table class="translations-table">
                <thead>
                    <tr>
                        <th>レッスン</th>
                        <th>サブレッスン</th>
                        <th>キー</th>
                        <th>日本語</th>
                        <th>English</th>
                        <th>中文</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($translations as $translation): ?>
                        <tr>
                            <td><?= h($translation['lesson_title']) ?></td>
                            <td><?= h($translation['sub_lesson_title']) ?></td>
                            <td><?= h($translation['text_key']) ?></td>
                            <td><?= h($translation['japanese']) ?></td>
                            <td><?= h($translation['english']) ?></td>
                            <td><?= h($translation['chinese']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editTranslation(<?= $translation['id'] ?>)">編集</button>
                                <button class="delete-btn" onclick="deleteTranslation(<?= $translation['id'] ?>)">削除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

.upload-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.upload-instructions {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.upload-instructions ul {
    margin: 15px 0;
    padding-left: 20px;
}

.download-sample {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 16px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9em;
}

.upload-form {
    display: flex;
    gap: 20px;
    align-items: center;
}

.file-input-wrapper {
    position: relative;
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
    min-width: 200px;
}

.upload-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.translations-list {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.translations-table-wrapper {
    overflow-x: auto;
}

.translations-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.translations-table th,
.translations-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.translations-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.edit-btn,
.delete-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8em;
    margin-right: 5px;
}

.edit-btn {
    background: #ffc107;
    color: #212529;
}

.delete-btn {
    background: #dc3545;
    color: white;
}
</style>

<script>
function editTranslation(id) {
    // 編集機能の実装
    alert('編集機能は次のバージョンで実装予定です');
}

function deleteTranslation(id) {
    if (confirm('この翻訳データを削除しますか？')) {
        // 削除機能の実装
        alert('削除機能は次のバージョンで実装予定です');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>