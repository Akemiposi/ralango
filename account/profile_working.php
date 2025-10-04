<?php
// account/profile.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['user'];

// 性別に応じた色設定
$gender = $user['child_gender'] ?? 'boy';
$color_scheme = $gender === 'girl' ? 'pink' : 'blue';

$page_title = 'アカウント情報 - nihongonote';

// 国名の母国語対応
$country_names = [
    'ja' => [
        'japan' => '日本',
        'usa' => 'アメリカ',
        'china' => '中国',
        'south_korea' => '韓国',
        'vietnam' => 'ベトナム',
        'philippines' => 'フィリピン',
        'nepal' => 'ネパール',
        'brazil' => 'ブラジル',
        'thailand' => 'タイ',
        'indonesia' => 'インドネシア'
    ],
    'en' => [
        'japan' => 'Japan',
        'usa' => 'United States',
        'china' => 'China',
        'south_korea' => 'South Korea',
        'vietnam' => 'Vietnam',
        'philippines' => 'Philippines',
        'nepal' => 'Nepal',
        'brazil' => 'Brazil',
        'thailand' => 'Thailand',
        'indonesia' => 'Indonesia'
    ],
    'zh' => [
        'japan' => '日本',
        'usa' => '美国',
        'china' => '中国',
        'south_korea' => '韩国',
        'vietnam' => '越南',
        'philippines' => '菲律宾',
        'nepal' => '尼泊尔',
        'brazil' => '巴西',
        'thailand' => '泰国',
        'indonesia' => '印度尼西亚'
    ]
];

function getCountryName($country_code, $language, $country_names) {
    return $country_names[$language][$country_code] ?? $country_code;
}

require_once '../includes/header.php';
?>

<style>
/* 言語別フォント設定 */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
}

/* 英語 */
.lang-en * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* 中国語 */
.lang-zh * {
    font-family: -apple-system, BlinkMacSystemFont, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif !important;
}

/* 韓国語 */
.lang-ko * {
    font-family: -apple-system, BlinkMacSystemFont, 'Apple SD Gothic Neo', 'Malgun Gothic', '맑은 고딕', dotum, '돋움', Arial, sans-serif !important;
}

/* ベトナム語 */
.lang-vi * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* フィリピン語 */
.lang-tl * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* ネパール語 */
.lang-ne * {
    font-family: -apple-system, BlinkMacSystemFont, 'Noto Sans Devanagari', 'Mangal', 'Kokila', 'Devanagari MT', serif !important;
}

/* ポルトガル語 */
.lang-pt * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* 日本語（デフォルト） */
.lang-ja * {
    font-family: -apple-system, BlinkMacSystemFont, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif !important;
}

body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--background);
}

.body-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
    padding: 20px 0;
}

.account-container {
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    padding: 20px;
    position: relative;
    background: transparent;
}

.account-header {
    text-align: center;
    padding: 20px;
    margin-bottom: 30px;
    color: var(--primary-dark);
}

.account-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.account-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.full-width-section {
    grid-column: 1 / -1;
}

.section-title {
    color: #333;
    margin-bottom: 20px;
    font-size: 20px;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    padding: 15px;
    background: #f8f9ff;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.info-label {
    font-weight: bold;
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
    font-size: 16px;
}

.btn-group {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-align: center;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
}

.btn-secondary {
    background: #f8f9ff;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary:hover {
    background: #667eea;
    color: white;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    margin: 0 auto 20px;
    color: white;
}

.family-members {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.family-member {
    background: #e3f2fd;
    color: #1976d2;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
}

/* 右上の言語選択 */
.language-selector-top {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
}

.language-selector-top .form-group {
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 2px solid var(--primary-light);
}

.language-selector-top .form-label {
    color: var(--primary-dark);
    font-weight: 600;
    margin: 0;
    font-size: 0.9em;
    white-space: nowrap;
}

.language-selector-top .form-select {
    padding: 6px 10px;
    border: 1px solid var(--primary-light);
    border-radius: 6px;
    background: white;
    color: var(--primary-dark);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9em;
    min-width: 100px;
}

.language-selector-top .form-select:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* 男の子用ブルーテーマ */
.blue-theme {
    --primary-color: #4a90e2;
    --primary-dark: #357abd;
    --primary-light: #e3f2fd;
}

.blue-theme .account-section {
    background: linear-gradient(135deg, #f3f8ff, #e8f2ff);
    border: 2px solid #e3f2fd;
}

.blue-theme .section-title {
    color: #357abd;
    border-bottom: 2px solid #4a90e2;
}

.blue-theme .info-item {
    background: #e3f2fd;
    border-left: 4px solid #4a90e2;
}

.blue-theme .info-label {
    color: #357abd;
}

.blue-theme .info-value {
    color: #2c5aa0;
}

/* 女の子用ピンクテーマ */
.pink-theme {
    --primary-color: #e91e63;
    --primary-dark: #c2185b;
    --primary-light: #fce4ec;
}

.pink-theme .account-section {
    background: linear-gradient(135deg, #fff0f5, #ffe4ec);
    border: 2px solid #fce4ec;
}

.pink-theme .section-title {
    color: #c2185b;
    border-bottom: 2px solid #e91e63;
}

.pink-theme .info-item {
    background: #fce4ec;
    border-left: 4px solid #e91e63;
}

.pink-theme .info-label {
    color: #c2185b;
}

.pink-theme .info-value {
    color: #ad1457;
}

/* フッターのスタイル */
.main-footer {
    background: #333;
    color: white;
    text-align: center;
    padding: 15px 0;
    margin-top: auto;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

@media (max-width: 768px) {
    .language-selector-top {
        position: relative;
        top: auto;
        right: auto;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }
    
    .account-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .full-width-section {
        grid-column: 1;
    }
    
    .body-wrapper {
        padding: 10px 0;
    }
    
    .account-container {
        max-width: 100%;
        padding: 15px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<?php
// ログインチェック
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$user = $_SESSION['user'];
$error = '';
$success = '';

// フラッシュメッセージの取得
if (isset($_SESSION['flash']['error'])) {
    $error = $_SESSION['flash']['error'];
    unset($_SESSION['flash']['error']);
}

if (isset($_SESSION['flash']['success'])) {
    $success = $_SESSION['flash']['success'];
    unset($_SESSION['flash']['success']);
}

// 更新されたユーザー情報を取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$current_user = $stmt->fetch();

if ($current_user) {
    $user = $current_user;
    $_SESSION['user'] = $user; // セッションも更新
}

// 家族構成を配列として処理
$family_members = [];
if (!empty($user['family_members'])) {
    if (is_string($user['family_members'])) {
        $family_members = json_decode($user['family_members'], true) ?: [];
    } else {
        $family_members = $user['family_members'];
    }
}

// 家族構成の翻訳マップ
$family_labels = [
    'father' => '父',
    'mother' => '母',
    'older_brother' => '兄',
    'older_sister' => '姉',
    'younger_brother' => '弟',
    'younger_sister' => '妹',
    'grandfather' => '祖父',
    'grandmother' => '祖母'
];
?>

<style>
:root {
    /* デフォルト青・緑系 */
    --primary-color: hsl(200, 50%, 65%);
    --primary-light: hsl(200, 40%, 80%);
    --background: hsl(200, 30%, 94%);
    --card-background: hsl(200, 25%, 97%);
    --primary-dark: #4a5568;
}

/* 言語別フォント設定 */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
}

/* 英語 */
.lang-en * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* 中国語 */
.lang-zh * {
    font-family: -apple-system, BlinkMacSystemFont, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif !important;
}

/* 韓国語 */
.lang-ko * {
    font-family: -apple-system, BlinkMacSystemFont, 'Apple SD Gothic Neo', 'Malgun Gothic', '맑은 고딕', dotum, '돋움', Arial, sans-serif !important;
}

/* ベトナム語 */
.lang-vi * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* フィリピン語 */
.lang-tl * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* ネパール語 */
.lang-ne * {
    font-family: -apple-system, BlinkMacSystemFont, 'Noto Sans Devanagari', 'Mangal', 'Kokila', 'Devanagari MT', serif !important;
}

/* ポルトガル語 */
.lang-pt * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

/* 日本語（デフォルト） */
.lang-ja * {
    font-family: -apple-system, BlinkMacSystemFont, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif !important;
}

.account-container {
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    padding: 20px;
    position: relative;
    background: transparent;
    min-height: calc(100vh - 200px);
}

.account-header {
    text-align: center;
    padding: 20px;
    margin-bottom: 30px;
    color: var(--primary-dark);
}

.account-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.account-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.full-width-section {
    grid-column: 1 / -1;
}

.section-title {
    color: #333;
    margin-bottom: 20px;
    font-size: 20px;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-item {
    padding: 10px 0;
}

.info-label {
    font-weight: bold;
    color: #555;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
    font-size: 16px;
}

.family-members {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.family-member {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 14px;
}

.btn-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* 右上の言語選択 */
.language-selector-top {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
}

.language-selector-top .form-group {
    margin: 0;
}

.language-selector-top .form-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.language-selector-top .form-select {
    padding: 8px 12px;
    border: 2px solid #667eea;
    border-radius: 6px;
    background: white;
    font-size: 14px;
    color: #333;
    min-width: 150px;
}

/* 性別テーマ */
.blue-theme .section-title {
    border-bottom-color: #667eea;
}

.blue-theme .family-member {
    background: #e3f2fd;
    color: #1976d2;
}

.blue-theme .info-value {
    color: #1565c0;
}

.pink-theme .section-title {
    border-bottom-color: #e91e63;
}

.pink-theme .family-member {
    background: #fce4ec;
    color: #c2185b;
}

.pink-theme .info-value {
    color: #ad1457;
}

@media (max-width: 768px) {
    .language-selector-top {
        position: relative;
        top: auto;
        right: auto;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }
    
    .account-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .full-width-section {
        grid-column: 1;
    }
    
    .account-container {
        max-width: 100%;
        padding: 15px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<!-- 性別対応カラーパレット -->
<?= getGenderColorCSS($user) ?>

<div class="account-container <?= $color_scheme ?>-theme lang-ja" id="accountContainer">
    <!-- 言語選択 - 右上に移動 -->
    <div class="language-selector-top">
        <div class="form-group">
            <label class="form-label" data-translate="language_label">言語 / Language</label>
            <select class="form-select" id="languageSelect" onchange="translateAccountPage()">
            <option value="ja">日本語</option>
            <option value="en">English</option>
            <option value="zh">中文</option>
            <!-- <option value="ko">한국어</option>
            <option value="vi">Tiếng Việt</option>
            <option value="tl">Filipino</option>
            <option value="ne">नेपाली</option>
            <option value="pt">Português</option> -->
        </select>
    </div>

    <!-- ヘッダー -->
    <div class="account-header">
        <div class="avatar">
            <?= h(getAvatarInitials($user['parent_name'])) ?>
        </div>
        <h1 data-translate="welcome_message">
            <?= h($user['child_name']) ?>ちゃんのアカウント
        </h1>
        <p data-translate="account_subtitle">アカウント情報の確認と編集ができます</p>
    </div>

    <?php if ($error): ?>
        <div class="error-message" style="margin-bottom: 20px;"><?= h($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message" style="margin-bottom: 20px;"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- 2カラムレイアウト -->
    <div class="account-content">
        <!-- 左カラム：基本情報 -->
        <div class="account-section">
        <h2 class="section-title" data-translate="basic_info_title">基本情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="parent_name_label">保護者の方のお名前</div>
                <div class="info-value"><?= h($user['parent_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_name_label">お子様のお名前</div>
                <div class="info-value"><?= h($user['child_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_nickname_label">お子様のニックネーム</div>
                <div class="info-value"><?= h($user['child_nickname'] ?: '未設定') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_gender_label">お子様の性別</div>
                <div class="info-value"><?= $user['child_gender'] === 'boy' ? '男の子' : '女の子' ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_birthdate_label">お子様の生年月日</div>
                <div class="info-value"><?= h($user['child_birthdate'] ?: '未設定') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_country_label">お子様の出身国</div>
                <div class="info-value">
                    <?php 
                    if ($user['child_country']) {
                        $native_lang = $user['native_language'] ?? 'ja';
                        echo h(getCountryName($user['child_country'], $native_lang, $country_names));
                    } else {
                        echo '未設定';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

        <!-- 右カラム：学校情報 -->
        <div class="account-section">
        <h2 class="section-title" data-translate="school_info_title">学校情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="child_grade_label">学年</div>
                <div class="info-value">
                    <?php if ($user['child_grade']): ?>
                        <?= $user['child_grade'] <= 6 ? '小学' . $user['child_grade'] . '年生' : '中学' . ($user['child_grade'] - 6) . '年生' ?>
                    <?php else: ?>
                        未設定
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="school_name_label">学校名</div>
                <div class="info-value"><?= h($user['school_name'] ?: '未設定') ?></div>
            </div>
        </div>
    </div>

        <!-- 家族情報セクション -->
        <div class="account-section">
        <h2 class="section-title" data-translate="family_info_title">家族情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="family_members_label">家族構成</div>
                <div class="info-value">
                    <?php if (!empty($family_members)): ?>
                        <div class="family-members">
                            <?php foreach ($family_members as $member): ?>
                                <span class="family-member"><?= h($family_labels[$member] ?? $member) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        未設定
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="family_size_label">家族の人数</div>
                <div class="info-value"><?= h($user['family_size'] ? $user['family_size'] . '人' : '未設定') ?></div>
            </div>
        </div>
    </div>

        <!-- アカウント情報セクション -->
        <div class="account-section">
        <h2 class="section-title" data-translate="account_info_title">アカウント情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="email_label">メールアドレス</div>
                <div class="info-value"><?= h($user['email']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="native_language_label">母語</div>
                <div class="info-value"><?= getLanguageName($user['native_language']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="registration_date_label">登録日</div>
                <div class="info-value" id="registrationDate"><?= date('Y年m月d日', strtotime($user['created_at'])) ?></div>
            </div>
        </div>
    </div>
    </div>

    <!-- アクションボタン - 全幅 -->
    <div class="account-section full-width-section">
        <div class="btn-group">
            <a href="edit.php" class="btn btn-primary" data-translate="edit_profile_button">プロフィールを編集</a>
            <a href="../lessons/curriculum.php" class="btn btn-secondary" data-translate="back_to_lessons_button">レッスンに戻る</a>
            <a href="../auth/login.php" class="btn btn-secondary" onclick="return confirm('ログアウトしますか？')" data-translate="logout_button">ログアウト</a>
        </div>
    </div>
</div>
</div>

<script>
// アカウントページ翻訳辞書
const accountTranslations = {
    ja: {
        language_label: '言語 / Language *',
        welcome_message: user_child_name + 'ちゃんのアカウント',
        account_subtitle: 'アカウント情報の確認と編集ができます',
        basic_info_title: '基本情報',
        parent_name_label: '保護者の方のお名前',
        child_name_label: 'お子様のお名前',
        child_nickname_label: 'お子様のニックネーム',
        child_gender_label: 'お子様の性別',
        child_birthdate_label: 'お子様の生年月日',
        child_country_label: 'お子様の出身国',
        school_info_title: '学校情報',
        child_grade_label: '学年',
        school_name_label: '学校名',
        family_info_title: '家族情報',
        family_members_label: '家族構成',
        family_size_label: '家族の人数',
        account_info_title: 'アカウント情報',
        email_label: 'メールアドレス',
        native_language_label: '母語',
        registration_date_label: '登録日',
        edit_profile_button: 'プロフィールを編集',
        back_to_lessons_button: 'レッスンに戻る',
        logout_button: 'ログアウト'
    },
    en: {
        language_label: 'Language / 言語 *',
        welcome_message: user_child_name + '\'s Account',
        account_subtitle: 'View and edit your account information',
        basic_info_title: 'Basic Information',
        parent_name_label: 'Parent/Guardian Name',
        child_name_label: 'Child\'s Name',
        child_nickname_label: 'Child\'s Nickname',
        child_gender_label: 'Child\'s Gender',
        child_birthdate_label: 'Child\'s Date of Birth',
        child_country_label: 'Child\'s Country of Origin',
        school_info_title: 'School Information',
        child_grade_label: 'Grade',
        school_name_label: 'School Name',
        family_info_title: 'Family Information',
        family_members_label: 'Family Members',
        family_size_label: 'Family Size',
        account_info_title: 'Account Information',
        email_label: 'Email Address',
        native_language_label: 'Native Language',
        registration_date_label: 'Registration Date',
        edit_profile_button: 'Edit Profile',
        back_to_lessons_button: 'Back to Lessons',
        logout_button: 'Logout'
    }
    // 他の言語の翻訳も同様に追加...
};

function translateAccountPage() {
    const selectedLang = document.getElementById('languageSelect').value;
    const accountContainer = document.getElementById('accountContainer');
    
    // フォントクラスを適用
    accountContainer.className = 'account-container lang-' + selectedLang;
    
    // 翻訳実行
    if (accountTranslations[selectedLang]) {
        const trans = accountTranslations[selectedLang];
        
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (trans[key]) {
                element.textContent = trans[key];
            }
        });
        
        // 登録日の日付フォーマットを言語に応じて変更
        const registrationDate = document.getElementById('registrationDate');
        if (registrationDate) {
            const dateStr = '<?= $user['created_at'] ?>';
            const date = new Date(dateStr);
            
            switch(selectedLang) {
                case 'en':
                    registrationDate.textContent = date.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    });
                    break;
                case 'zh':
                    registrationDate.textContent = date.getFullYear() + '年' + 
                        (date.getMonth() + 1) + '月' + date.getDate() + '日';
                    break;
                case 'ko':
                    registrationDate.textContent = date.getFullYear() + '년 ' + 
                        (date.getMonth() + 1) + '월 ' + date.getDate() + '일';
                    break;
                case 'vi':
                    registrationDate.textContent = date.getDate() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getFullYear();
                    break;
                case 'tl':
                    registrationDate.textContent = date.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    });
                    break;
                case 'ne':
                    registrationDate.textContent = date.getFullYear() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getDate();
                    break;
                case 'pt':
                    registrationDate.textContent = date.getDate() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getFullYear();
                    break;
                case 'ja':
                default:
                    registrationDate.textContent = date.getFullYear() + '年' + 
                        (date.getMonth() + 1) + '月' + date.getDate() + '日';
                    break;
            }
        }
    }
}

// ログアウト処理
document.querySelector('[data-translate="logout_button"]').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm(accountTranslations[document.getElementById('languageSelect').value]?.logout_confirm || 'ログアウトしますか？')) {
        // セッションを破棄してログイン画面にリダイレクト
        fetch('../auth/logout.php', {method: 'POST'})
            .then(() => {
                window.location.href = '../auth/login.php';
            });
    }
});

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', function() {
    // URLパラメータから言語を取得
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang');
    
    if (lang && accountTranslations[lang]) {
        document.getElementById('languageSelect').value = lang;
        translateAccountPage();
    }
});

// ユーザーの子供の名前をJavaScriptで使用
const user_child_name = '<?= addslashes($user['child_name']) ?>';
</script>

<?php require_once '../includes/footer.php'; ?>