<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// 管理者権限チェック
requireAdmin();

// バッジ生成関数（位置調整可能版）
function generateBadgeAdmin($base_image_path, $lesson_number, $step_number, $output_path, $x_offset = 35, $y_offset = 30, $font_size = 25) {
    if (!file_exists($base_image_path)) {
        return ['success' => false, 'error' => "ベース画像が見つかりません: $base_image_path"];
    }
    
    $image = imagecreatefrompng($base_image_path);
    if (!$image) {
        return ['success' => false, 'error' => "画像の読み込みに失敗: $base_image_path"];
    }
    
    // 透明度を保持する設定
    imagealphablending($image, true);
    imagesavealpha($image, true);
    
    // 画像サイズを取得
    $width = imagesx($image);
    $height = imagesy($image);
    
    // テキストの色を設定
    $text_color = imagecolorallocate($image, 255, 255, 255);
    
    // レッスン番号テキスト
    $lesson_text = "{$lesson_number}";
    
    // フォントファイルのパス
    $font_path = '/System/Library/Fonts/Supplemental/Arial Rounded Bold.ttf';
    if (!file_exists($font_path)) {
        $font_path = 'C:/Windows/Fonts/ARLRDBD.TTF';
        if (!file_exists($font_path)) {
            $font_path = null;
        }
    }
    
    if ($font_path && file_exists($font_path)) {
        // TrueTypeフォントを使用
        $text_box = imagettfbbox($font_size, 0, $font_path, $lesson_text);
        $text_width = $text_box[4] - $text_box[0];
        
        // テキスト位置を計算
        $x = intval(($width - $text_width) / 2) + $x_offset;
        $y = intval($height - 15) - $y_offset;
        
        // テキストを描画
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $lesson_text);
    } else {
        // フォールバック：内蔵フォント
        $font_size_builtin = 5;
        $text_width = strlen($lesson_text) * imagefontwidth($font_size_builtin);
        
        $x = intval(($width - $text_width) / 2) + $x_offset;
        $y = intval($height - imagefontheight($font_size_builtin) - 10) - $y_offset;
        
        imagestring($image, $font_size_builtin, $x, $y, $lesson_text, $text_color);
    }
    
    // 出力ディレクトリの権限確認
    $output_dir = dirname($output_path);
    if (!is_writable($output_dir)) {
        chmod($output_dir, 0777);
        if (!is_writable($output_dir)) {
            imagedestroy($image);
            return ['success' => false, 'error' => "出力ディレクトリに書き込み権限がありません: $output_dir"];
        }
    }
    
    // 画像を保存
    imagesavealpha($image, true);
    $result = imagepng($image, $output_path);
    
    imagedestroy($image);
    
    return ['success' => $result, 'path' => $output_path];
}

// アップロード処理
$upload_message = '';
if (isset($_POST['upload_badges'])) {
    $upload_dir = '../assets/images/badge/';
    $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
    
    for ($i = 1; $i <= 3; $i++) {
        $file_key = "badge{$i}";
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file_info = $_FILES[$file_key];
            
            // ファイルタイプチェック
            if (in_array($file_info['type'], $allowed_types)) {
                $target_path = $upload_dir . "badge{$i}.png";
                
                // PNG形式に変換して保存
                $temp_image = null;
                switch ($file_info['type']) {
                    case 'image/png':
                        $temp_image = imagecreatefrompng($file_info['tmp_name']);
                        break;
                    case 'image/jpeg':
                        $temp_image = imagecreatefromjpeg($file_info['tmp_name']);
                        break;
                    case 'image/gif':
                        $temp_image = imagecreatefromgif($file_info['tmp_name']);
                        break;
                }
                
                if ($temp_image) {
                    // ディレクトリの権限確認
                    if (!is_writable($upload_dir)) {
                        chmod($upload_dir, 0777);
                    }
                    
                    // 透明度を保持してPNGで保存
                    imagealphablending($temp_image, false);
                    imagesavealpha($temp_image, true);
                    
                    if (imagepng($temp_image, $target_path)) {
                        $upload_message .= "バッジ{$i}がアップロードされました。<br>";
                    } else {
                        $upload_message .= "バッジ{$i}の保存に失敗しました。権限: " . (is_writable($upload_dir) ? '有効' : '無効') . " パス: $target_path<br>";
                    }
                    imagedestroy($temp_image);
                } else {
                    $upload_message .= "バッジ{$i}: 画像の読み込みに失敗しました。<br>";
                }
            } else {
                $upload_message .= "バッジ{$i}: 対応していないファイル形式です。<br>";
            }
        }
    }
}

// プレビュー生成処理
$preview_result = null;
$debug_info = [];
if (isset($_POST['generate_preview'])) {
    $x_offset = intval($_POST['x_offset'] ?? 35);
    $y_offset = intval($_POST['y_offset'] ?? 30);
    $font_size = intval($_POST['font_size'] ?? 25);
    $test_lesson = intval($_POST['test_lesson'] ?? 1);
    $test_step = intval($_POST['test_step'] ?? 1);
    
    $base_file = "../assets/images/badge/badge{$test_step}.png";
    $preview_file = "../assets/images/badge/generated/preview_L{$test_lesson}_{$test_step}.png";
    
    $debug_info[] = "ベースファイル: $base_file";
    $debug_info[] = "プレビューファイル: $preview_file";
    $debug_info[] = "ベースファイル存在: " . (file_exists($base_file) ? 'YES' : 'NO');
    
    if (!file_exists('../assets/images/badge/generated/')) {
        mkdir('../assets/images/badge/generated/', 0777, true);
        $debug_info[] = "生成ディレクトリを作成しました";
    }
    
    $debug_info[] = "生成ディレクトリ書き込み可能: " . (is_writable('../assets/images/badge/generated/') ? 'YES' : 'NO');
    
    $preview_result = generateBadgeAdmin($base_file, $test_lesson, $test_step, $preview_file, $x_offset, $y_offset, $font_size);
    
    if ($preview_result) {
        $debug_info[] = "生成結果: " . ($preview_result['success'] ? '成功' : '失敗');
        if ($preview_result['success']) {
            $debug_info[] = "生成ファイル存在: " . (file_exists($preview_file) ? 'YES' : 'NO');
            // 環境判定
            $is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
                         file_exists('/Applications/XAMPP')) || 
                        (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
            
            if ($is_xampp) {
                $image_path = str_replace('../', '/nihongonote/', $preview_result['path']);
            } else {
                $image_path = str_replace('../', '/', $preview_result['path']);
            }
            $debug_info[] = "画像URL: " . $image_path;
        } else {
            $debug_info[] = "エラー: " . $preview_result['error'];
        }
    }
}

// 全バッジ生成処理
$generation_result = null;
if (isset($_POST['generate_all'])) {
    $x_offset = intval($_POST['x_offset'] ?? 35);
    $y_offset = intval($_POST['y_offset'] ?? 30);
    $font_size = intval($_POST['font_size'] ?? 25);
    
    // 範囲指定の処理
    $lesson_range = $_POST['lesson_range'] ?? '';
    $start_lesson = 1;
    $end_lesson = 20;
    
    if (!empty($lesson_range)) {
        if (strpos($lesson_range, '-') !== false) {
            // 範囲指定（例：20-25）
            $range_parts = explode('-', $lesson_range);
            $start_lesson = intval(trim($range_parts[0]));
            $end_lesson = intval(trim($range_parts[1]));
        } else {
            // 単一レッスン（例：20）
            $start_lesson = $end_lesson = intval($lesson_range);
        }
    } else {
        // 従来の最大レッスン数指定
        $max_lessons = intval($_POST['max_lessons'] ?? 20);
        $end_lesson = $max_lessons;
    }
    
    // 範囲の妥当性チェック
    if ($start_lesson > $end_lesson) {
        $temp = $start_lesson;
        $start_lesson = $end_lesson;
        $end_lesson = $temp;
    }
    
    if (!file_exists('../assets/images/badge/generated/')) {
        mkdir('../assets/images/badge/generated/', 0777, true);
    }
    
    $generated_count = 0;
    $errors = [];
    $total_count = ($end_lesson - $start_lesson + 1) * 3;
    
    for ($lesson = $start_lesson; $lesson <= $end_lesson; $lesson++) {
        for ($step = 1; $step <= 3; $step++) {
            $base_file = "../assets/images/badge/badge{$step}.png";
            $output_file = "../assets/images/badge/generated/badge_L{$lesson}_{$step}.png";
            
            $result = generateBadgeAdmin($base_file, $lesson, $step, $output_file, $x_offset, $y_offset, $font_size);
            
            if ($result['success']) {
                $generated_count++;
            } else {
                $errors[] = "L{$lesson}-{$step}: " . $result['error'];
            }
        }
    }
    
    $generation_result = [
        'generated_count' => $generated_count,
        'total_count' => $total_count,
        'errors' => $errors,
        'range' => "L{$start_lesson}〜L{$end_lesson}"
    ];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バッジ生成システム - 管理者画面</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            width: 100%;
        }
        
        .section-title {
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
            font-size: 1.8em;
        }
        
        /* アップロードセクション */
        .upload-container {
            width: 100%;
        }
        
        .upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .upload-box {
            border: 3px dashed #ddd;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
            height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .upload-box:hover {
            border-color: #4CAF50;
            background: #f0f8f0;
        }
        
        .upload-box h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .upload-box p {
            margin-bottom: 20px;
            color: #666;
        }
        
        .file-input {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        
        .upload-button-container {
            text-align: center;
            margin-top: 30px;
        }
        
        /* プレビューセクション */
        .preview-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }
        
        .controls-panel {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group small {
            color: #666;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        
        .preview-panel {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .preview-image {
            max-width: 300px;
            height: auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        /* 生成セクション */
        .generation-container {
            width: 100%;
        }
        
        .instruction-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .instruction-box ol {
            margin: 15px 0 15px 20px;
        }
        
        .settings-display {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #4CAF50;
            margin-bottom: 30px;
        }
        
        /* ボタン */
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .btn-large {
            padding: 20px 40px;
            font-size: 18px;
        }
        
        .btn-preview {
            background: #2196F3;
        }
        
        .btn-preview:hover {
            background: #1976D2;
        }
        
        .btn-generate {
            background: #FF5722;
        }
        
        .btn-generate:hover {
            background: #E64A19;
        }
        
        .btn-back {
            background: #6c757d;
        }
        
        .btn-back:hover {
            background: #545b62;
        }
        
        /* メッセージ */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
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
        
        /* レスポンシブ */
        @media (max-width: 1024px) {
            .upload-grid {
                grid-template-columns: 1fr;
            }
            
            .preview-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* ユーティリティ */
        .text-center {
            text-align: center;
        }
        
        .mt-30 {
            margin-top: 30px;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ページヘッダー -->
        <div class="page-header">
            <h1 class="page-title">バッジ生成システム</h1>
            <p class="page-subtitle">管理者専用 - バッジ画像の管理と生成</p>
        </div>

        <!-- 1. バッジ画像アップロード -->
        <div class="section">
            <h2 class="section-title">1. バッジ画像アップロード</h2>
            
            <?php if ($upload_message): ?>
                <div class="message <?= strpos($upload_message, '失敗') !== false ? 'error' : 'success' ?>">
                    <?= $upload_message ?>
                </div>
            <?php endif; ?>
            
            <div class="upload-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="upload-grid">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="upload-box">
                                <h3>バッジ<?= $i ?></h3>
                                <p><?= ['', 'みる', 'やってみる', 'できた'][$i] ?></p>
                                <input type="file" name="badge<?= $i ?>" accept="image/*" class="file-input">
                                <small>PNG, JPEG, GIF対応</small>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="upload-button-container">
                        <button type="submit" name="upload_badges" class="btn btn-large">バッジ画像をアップロード</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. プレビュー＆位置調整 -->
        <div class="section">
            <h2 class="section-title">2. プレビュー＆位置調整</h2>
            
            <div class="preview-container">
                <div class="controls-panel">
                    <form method="POST">
                        <div class="form-group">
                            <label>テストレッスン番号:</label>
                            <input type="number" name="test_lesson" value="<?= $_POST['test_lesson'] ?? 1 ?>" min="1" max="99">
                        </div>
                        
                        <div class="form-group">
                            <label>テストステップ:</label>
                            <select name="test_step">
                                <option value="1" <?= ($_POST['test_step'] ?? 1) == 1 ? 'selected' : '' ?>>1 (みる)</option>
                                <option value="2" <?= ($_POST['test_step'] ?? 1) == 2 ? 'selected' : '' ?>>2 (やってみる)</option>
                                <option value="3" <?= ($_POST['test_step'] ?? 1) == 3 ? 'selected' : '' ?>>3 (できた)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>横位置調整 (px):</label>
                            <input type="number" name="x_offset" value="<?= $_POST['x_offset'] ?? 35 ?>" min="-100" max="100">
                            <small>正の値で右へ、負の値で左へ</small>
                        </div>
                        
                        <div class="form-group">
                            <label>縦位置調整 (px):</label>
                            <input type="number" name="y_offset" value="<?= $_POST['y_offset'] ?? 30 ?>" min="-100" max="100">
                            <small>正の値で上へ、負の値で下へ</small>
                        </div>
                        
                        <div class="form-group">
                            <label>フォントサイズ (px):</label>
                            <input type="number" name="font_size" value="<?= $_POST['font_size'] ?? 25 ?>" min="10" max="50">
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="generate_preview" class="btn btn-preview btn-large">プレビュー生成</button>
                        </div>
                    </form>
                </div>
                
                <div class="preview-panel">
                    <?php if ($preview_result): ?>
                        <?php if ($preview_result['success']): ?>
                            <h3>プレビュー結果</h3>
                            <?php 
                            // 環境判定
            $is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
                         file_exists('/Applications/XAMPP')) || 
                        (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
            
            if ($is_xampp) {
                $image_path = str_replace('../', '/nihongonote/', $preview_result['path']);
            } else {
                $image_path = str_replace('../', '/', $preview_result['path']);
            }
                            ?>
                            <img src="<?= $image_path ?>?<?= time() ?>" class="preview-image" alt="プレビュー">
                            <p style="font-size: 14px; color: #666;">
                                位置: (<?= $_POST['x_offset'] ?? 35 ?>, <?= $_POST['y_offset'] ?? 30 ?>), 
                                サイズ: <?= $_POST['font_size'] ?? 25 ?>px
                            </p>
                        <?php else: ?>
                            <div class="message error">
                                プレビュー生成エラー: <?= htmlspecialchars($preview_result['error']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- デバッグ情報 -->
                        <?php if (!empty($debug_info)): ?>
                            <details style="margin-top: 20px; text-align: left;">
                                <summary style="cursor: pointer; color: #666;">デバッグ情報</summary>
                                <div style="background: #f5f5f5; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 12px;">
                                    <?php foreach ($debug_info as $info): ?>
                                        <div><?= htmlspecialchars($info) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <h3>プレビューを生成してください</h3>
                        <p>左側の設定を調整して「プレビュー生成」ボタンを押すと、ここにバッジのプレビューが表示されます。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 3. 全バッジ生成 -->
        <div class="section">
            <h2 class="section-title">3. 全バッジ生成（最終ステップ）</h2>
            
            <div class="generation-container">
                <div class="instruction-box">
                    <strong>手順:</strong>
                    <ol>
                        <li>上記で画像をアップロード</li>
                        <li>プレビューで位置を調整</li>
                        <li>下記のボタンで全バッジを生成</li>
                    </ol>
                </div>
                
                <?php if ($generation_result): ?>
                    <div class="message <?= empty($generation_result['errors']) ? 'success' : 'error' ?>">
                        <strong>生成完了:</strong> <?= $generation_result['generated_count'] ?> / <?= $generation_result['total_count'] ?> 個のバッジが生成されました。
                        <br><strong>範囲:</strong> <?= $generation_result['range'] ?? '' ?>
                        <?php if (!empty($generation_result['errors'])): ?>
                            <details style="margin-top: 10px;">
                                <summary>エラー詳細 (<?= count($generation_result['errors']) ?>件)</summary>
                                <ul style="margin-top: 10px;">
                                    <?php foreach ($generation_result['errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="x_offset" value="<?= $_POST['x_offset'] ?? 35 ?>">
                    <input type="hidden" name="y_offset" value="<?= $_POST['y_offset'] ?? 30 ?>">
                    <input type="hidden" name="font_size" value="<?= $_POST['font_size'] ?? 25 ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 600px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label>レッスン範囲指定:</label>
                            <input type="text" name="lesson_range" value="<?= $_POST['lesson_range'] ?? '' ?>" placeholder="例: 20-25 または 20">
                            <small>範囲: 20-25、単一: 20</small>
                        </div>
                        
                        <div class="form-group">
                            <label>または従来の方式:</label>
                            <input type="number" name="max_lessons" value="<?= $_POST['max_lessons'] ?? 20 ?>" min="1" max="100" placeholder="1から指定数まで">
                            <small>1〜指定数まで生成</small>
                        </div>
                    </div>
                    
                    <div class="settings-display">
                        <strong>現在の設定:</strong><br>
                        横位置: <?= $_POST['x_offset'] ?? 35 ?>px | 
                        縦位置: <?= $_POST['y_offset'] ?? 30 ?>px | 
                        フォントサイズ: <?= $_POST['font_size'] ?? 25 ?>px<br>
                        <small>※ 位置を変更したい場合は、上記のプレビューで調整してからこのボタンを押してください。</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="generate_all" class="btn btn-generate btn-large" onclick="return confirm('全バッジを生成しますか？既存のバッジは上書きされます。');">
                            全バッジ生成実行
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 戻るリンク -->
        <div class="text-center mt-30">
            <a href="../admin/" class="btn btn-back">管理者画面に戻る</a>
        </div>
    </div>
</body>
</html>