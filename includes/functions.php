<?php
// includes/functions.php
// 共通関数 - 更新版

// HTMLエスケープ
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// ベースパス取得
function getBasePath($path = '') {
    // 環境判定：XAMPP環境かXserver環境かを判定
    $is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
                 file_exists('/Applications/XAMPP')) || 
                (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
    
    if ($is_xampp) {
        // XAMPP環境では /nihongonote/ を含める
        $base = '/nihongonote';
    } else {
        // Xserver環境では空文字（ルートパス）
        $base = '';
    }
    
    if (empty($path)) {
        return $base;
    }
    
    return $base . '/' . ltrim($path, '/');
}

// アセットパス取得
function getAssetPath($path) {
    return getBasePath('assets/' . $path);
}

// 翻訳関数（簡易版）
function translate($text, $target_lang = 'en') {
    $translations = [
        'みる' => ['en' => 'Watch', 'zh' => '观看', 'ko' => '보기'],
        'やってみる' => ['en' => "Let's try it!", 'zh' => '试试看！', 'ko' => '해보자!'],
        'できた' => ['en' => 'You did it!', 'zh' => '做到了！', 'ko' => '해냈다!'],
        'せんせい、おはようございます。' => ['en' => 'Good Morning, Teacher!', 'zh' => '老师，早上好！', 'ko' => '선생님, 안녕하세요!'],
        'おはよう。' => ['en' => 'Good Morning!', 'zh' => '早上好！', 'ko' => '안녕하세요!'],
        'You got a new badge!' => ['en' => 'You got a new badge!', 'zh' => '你获得了新徽章！', 'ko' => '새 배지를 얻었습니다!'],
        'You learned it!' => ['en' => 'You learned it!', 'zh' => '你学会了！', 'ko' => '배웠어요!'],
        'Student' => ['en' => 'Student', 'zh' => '学生', 'ko' => '학생'],
        'Teacher Ms.Sato' => ['en' => 'Teacher Ms.Sato', 'zh' => '佐藤老师', 'ko' => '사토 선생님'],
        "Let's say it!" => ['en' => "Let's say it!", 'zh' => '一起说吧！', 'ko' => '말해보자!']
    ];
    
    return $translations[$text][$target_lang] ?? $text;
}

// 性別対応のCSSカスタムプロパティを生成
function getGenderColorCSS($user) {
    $gender_factor = ($user['child_gender'] ?? 'boy') == 'boy' ? '1' : '0';
    
    return "
    <style>
        :root {
            --gender-factor: {$gender_factor};
            
            --base-hue: calc(var(--gender-factor) * 200 + (1 - var(--gender-factor)) * 350);
            --accent-hue: calc(var(--gender-factor) * 180 + (1 - var(--gender-factor)) * 15);
            --secondary-hue: calc(var(--gender-factor) * 160 + (1 - var(--gender-factor)) * 330);
            
            /* カラーパレット */
            --primary-color: hsl(var(--base-hue), 50%, 65%);
            --primary-light: hsl(var(--base-hue), 40%, 80%);
            --primary-dark: hsl(var(--base-hue), 60%, 50%);
            --accent-color: hsl(var(--accent-hue), 55%, 60%);
            --secondary-color: hsl(var(--secondary-hue), 45%, 70%);
            --background: hsl(var(--base-hue), 30%, 94%);
            --card-background: hsl(var(--base-hue), 25%, 97%);
        }
    </style>";
}

// ユーザー認証
function authenticateUser($email, $password) {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// ユーザー登録
function registerUser($parent_name, $child_name, $child_gender, $email, $password, $native_language, $child_nickname = null, $child_birthdate = null, $child_country = null, $child_grade = null, $school_name = null, $family_members = null, $family_size = null) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }
    
    try {
        // 重複チェック
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'error_duplicate_email'];
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // 家族構成をJSONエンコード
        $family_members_json = is_array($family_members) ? json_encode($family_members) : $family_members;
        
        $stmt = $pdo->prepare("
            INSERT INTO users (
                parent_name, 
                child_name, 
                child_nickname, 
                child_gender, 
                child_birthdate, 
                child_country, 
                child_grade, 
                school_name, 
                family_members, 
                family_size, 
                email, 
                password_hash, 
                native_language, 
                role, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', NOW())
        ");
        
        $stmt->execute([
            $parent_name,
            $child_name,
            $child_nickname,
            $child_gender,
            $child_birthdate,
            $child_country,
            $child_grade,
            $school_name,
            $family_members_json,
            $family_size,
            $email,
            $password_hash,
            $native_language
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // 登録されたユーザー情報を取得
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'user' => $user
        ];
    } catch(PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// レッスン取得
function getLesson($lesson_id) {
    global $pdo;
    
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// レッスン一覧取得
function getAllLessons() {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM lessons ORDER BY lesson_number, step_number");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// ユーザーの進捗取得
function getUserProgress($user_id) {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// ユーザーのバッジ取得
function getUserBadges($user_id) {
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        // 重複を除去してユニークなバッジのみを取得
        $stmt = $pdo->prepare("
            SELECT * FROM badges 
            WHERE user_id = ? 
            AND id IN (
                SELECT MIN(id) 
                FROM badges 
                WHERE user_id = ? 
                GROUP BY lesson_id, sub_lesson_id, badge_type
            )
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// バッジ獲得
function earnBadge($user_id, $lesson_id, $badge_type, $sub_lesson_id = null) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }
    
    try {
        // 重複チェック
        $stmt = $pdo->prepare("SELECT id FROM badges WHERE user_id = ? AND lesson_id = ? AND badge_type = ? AND sub_lesson_id = ?");
        $stmt->execute([$user_id, $lesson_id, $badge_type, $sub_lesson_id]);
        
        if ($stmt->fetch()) {
            return ['success' => true, 'message' => 'Already earned'];
        }
        
        // 新規バッジ追加
        $stmt = $pdo->prepare("INSERT INTO badges (user_id, lesson_id, badge_type, sub_lesson_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $lesson_id, $badge_type, $sub_lesson_id]);
        
        return ['success' => true, 'message' => 'Badge earned!'];
    } catch(PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// 進捗記録
function recordProgress($user_id, $lesson_id, $step) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, step, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $lesson_id, $step]);
        
        return ['success' => true];
    } catch(PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// sub_lesson_id対応の進捗記録
function recordProgressWithSubLesson($user_id, $lesson_id, $sub_lesson_id, $step) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, sub_lesson_id, step, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $lesson_id, $sub_lesson_id, $step]);
        
        return ['success' => true];
    } catch(PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// ユーザーのバッジ数取得（重複除去）
function getUserBadgeCount($user_id) {
    global $pdo;
    
    if (!$pdo) return 0;
    
    try {
        // 重複を除去してユニークなバッジのみをカウント
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT CONCAT(lesson_id, '-', COALESCE(sub_lesson_id, ''), '-', badge_type)) as count 
            FROM badges 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// 言語コードから言語名取得
function getLanguageName($code) {
    $languages = [
        'ja' => '日本語',
        'en' => 'English',
        'zh' => '中文',
        'ko' => '한국어',
        'tl' => 'Tagalog',
        'vi' => 'Tiếng Việt',
        'th' => 'ไทย'
    ];
    
    return $languages[$code] ?? 'English';
}

// アバターイニシャル生成
function getAvatarInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    
    return substr($initials, 0, 2);
}

// バッジ画像パス取得
function getBadgeImagePath($lesson_number, $step_number) {
    return getAssetPath("images/badge/BL{$lesson_number}_{$step_number}.png");
}

// バッジ画像の存在確認
function badgeImageExists($lesson_number, $step_number) {
    // 環境判定
    $is_xampp = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false && 
                 file_exists('/Applications/XAMPP')) || 
                (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
    
    if ($is_xampp) {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/nihongonote/assets/images/badge/BL{$lesson_number}_{$step_number}.png";
    } else {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/badge/BL{$lesson_number}_{$step_number}.png";
    }
    
    return file_exists($path);
}

// ログインチェック
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . getBasePath('auth/login.php'));
        exit;
    }
}

// 管理者チェック
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('Location: ' . getBasePath());
        exit;
    }
}

// CSRFトークン生成
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRFトークン検証
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// フラッシュメッセージ設定
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

// フラッシュメッセージ取得
function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

// エラーメッセージ表示
function displayFlashMessages() {
    $messages = '';
    
    if ($error = getFlashMessage('error')) {
        $messages .= '<div class="error-message">' . h($error) . '</div>';
    }
    
    if ($success = getFlashMessage('success')) {
        $messages .= '<div class="success-message">' . h($success) . '</div>';
    }
    
    return $messages;
}
?>
