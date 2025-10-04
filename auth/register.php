<?php
// auth/register.php

// セッション開始（最初に）
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログイン済みの場合はリダイレクト
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// 必要なファイルを読み込み
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = '新規登録 - nihongonote';

$error = '';
$success = '';
$form_data = [
    'parent_name' => '',
    'child_name' => '',
    'child_nickname' => '',
    'child_gender' => '',
    'child_birthdate' => '',
    'child_country' => '',
    'child_grade' => '',
    'school_name' => '',
    'family_members' => [],
    'family_size' => '',
    'email' => '',
    'native_language' => 'en'
];

// 登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // フォームデータ取得
    $form_data['parent_name'] = trim($_POST['parent_name']);
    $form_data['child_name'] = trim($_POST['child_name']);
    $form_data['child_nickname'] = trim($_POST['child_nickname']);
    $form_data['child_gender'] = $_POST['child_gender'];
    $form_data['child_birthdate'] = $_POST['child_birthdate'];
    $form_data['child_country'] = trim($_POST['child_country']);
    $form_data['child_grade'] = $_POST['child_grade'];
    $form_data['school_name'] = trim($_POST['school_name']);
    $form_data['family_members'] = isset($_POST['family_members']) ? $_POST['family_members'] : [];
    $form_data['family_size'] = $_POST['family_size'];
    $form_data['email'] = trim($_POST['email']);
    $form_data['native_language'] = $_POST['native_language'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // バリデーション
    if (empty($form_data['parent_name'])) {
        $error = 'error_parent_name_required';
    } elseif (empty($form_data['child_name'])) {
        $error = 'error_child_name_required';
    } elseif (empty($form_data['child_gender'])) {
        $error = 'error_child_gender_required';
    } elseif (empty($form_data['email'])) {
        $error = 'error_email_required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'error_email_invalid';
    } elseif (empty($password)) {
        $error = 'error_password_required';
    } elseif (strlen($password) < 6) {
        $error = 'error_password_min_length';
    } elseif ($password !== $password_confirm) {
        $error = 'error_password_mismatch';
    } else {
        // ユーザー登録（基本情報のみ）
        $result = registerUser(
            $form_data['parent_name'],
            $form_data['child_name'], 
            $form_data['child_gender'],
            $form_data['email'],
            $password,
            $form_data['native_language'],
            $form_data['child_nickname'],
            $form_data['child_birthdate'],
            $form_data['child_country'],
            $form_data['child_grade'],
            $form_data['school_name'],
            $form_data['family_members'],
            $form_data['family_size']
        );
        
        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            setFlashMessage('success', 'アカウント作成が完了しました。');
            header('Location: ../index.php');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// ヘッダー読み込み（処理後）
require_once '../includes/header.php';
?>

<style>
:root {
    /* デフォルト青・緑系 */
    --primary-color: hsl(200, 50%, 65%);
    --primary-light: hsl(200, 40%, 80%);
    --background: hsl(200, 30%, 94%);
    --card-background: hsl(200, 25%, 97%);
}


/* 言語別フォント設定 */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    background: var(--background);
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('../assets/images/bg_top.png') no-repeat top center;
    background-size: 100% auto;
    z-index: -1;
    pointer-events: none;
}

/* 英語 */
.lang-en *,
.language-tab[data-lang="en"],
.language-tab[data-lang="en"].active {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
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

.close-button {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
    z-index: 10;
}

.close-button:hover {
    background: #f0f0f0;
    color: #333;
}

.back-to-login {
    text-align: center;
    margin-top: 20px;
}

.back-button {
    background: #666;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.back-button:hover {
    background: #555;
}
</style>

<div class="auth-container" style="position: relative;">
    
    <button type="button" class="close-button" onclick="goBackToLogin()">×</button>
    <h1 class="auth-title" data-translate="page_title">新規登録</h1>
    
    <?php if ($error): ?>
        <div class="error-message" id="errorMessage" data-error-key="<?= h($error) ?>"><?= h($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?= h($success) ?></div>
    <?php endif; ?>
    
    <form method="POST" id="registrationForm">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <!-- 母語選択（最初に表示） -->
        <div class="form-group" id="languageSelector">
            <label class="form-label" data-translate="native_language_label">母語を選択してください *</label>
            <select name="native_language" class="form-select" id="nativeLanguageSelect" required onchange="translateForm()">
                <option value="">選択してください</option>
                <option value="ja" <?= $form_data['native_language'] === 'ja' ? 'selected' : '' ?>>日本語</option>
                <option value="en" <?= $form_data['native_language'] === 'en' ? 'selected' : '' ?>>English</option>
                <option value="zh" <?= $form_data['native_language'] === 'zh' ? 'selected' : '' ?>>中文</option>
                <option value="tl" <?= $form_data['native_language'] === 'tl' ? 'selected' : '' ?>>Tagalog</option>
            </select>
        </div>

        <!-- 残りのフォームフィールド（翻訳対象） -->
        <div id="mainForm" style="<?= empty($form_data['native_language']) ? 'display: none;' : '' ?>">
            <div class="form-group">
                <label class="form-label" data-translate="parent_name_label">保護者のお名前 *</label>
                <input type="text" name="parent_name" class="form-input" value="<?= h($form_data['parent_name']) ?>" required>
            </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="child_name_label">お子様のお名前 *</label>
                <input type="text" name="child_name" class="form-input" value="<?= h($form_data['child_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" data-translate="child_nickname_label">お子様のニックネーム</label>
                <input type="text" name="child_nickname" class="form-input" value="<?= h($form_data['child_nickname']) ?>" data-translate-placeholder="child_nickname_placeholder">
            </div>
            
            <div class="form-group">
                <label class="form-label" data-translate="child_gender_label">お子様の性別 *</label>
                <select name="child_gender" class="form-select" required>
                    <option value="" data-translate="select_option">選択してください</option>
                    <option value="boy" <?= $form_data['child_gender'] === 'boy' ? 'selected' : '' ?> data-translate="boy_option">男の子</option>
                    <option value="girl" <?= $form_data['child_gender'] === 'girl' ? 'selected' : '' ?> data-translate="girl_option">女の子</option>
                </select>
            </div>
        
        <div class="form-group">
            <div class="form-group">
                <label class="form-label" data-translate="child_birthdate_label">お子様の生年月日 *</label>
                <input type="date" name="child_birthdate" class="form-input" value="<?= h($form_data['child_birthdate']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" data-translate="child_country_label">お子様の出身国 *</label>
                <select name="child_country" class="form-select" required>
                    <option value="" data-translate="select_option">選択してください</option>
                    <!-- 英語圏 -->
                    <option value="United States" <?= $form_data['child_country'] === 'United States' ? 'selected' : '' ?> data-translate="country_usa">アメリカ</option>
                    <option value="United Kingdom" <?= $form_data['child_country'] === 'United Kingdom' ? 'selected' : '' ?> data-translate="country_uk">イギリス</option>
                    <option value="Canada" <?= $form_data['child_country'] === 'Canada' ? 'selected' : '' ?> data-translate="country_canada">カナダ</option>
                    <option value="Australia" <?= $form_data['child_country'] === 'Australia' ? 'selected' : '' ?> data-translate="country_australia">オーストラリア</option>
                    <option value="New Zealand" <?= $form_data['child_country'] === 'New Zealand' ? 'selected' : '' ?> data-translate="country_nz">ニュージーランド</option>
                    <option value="Ireland" <?= $form_data['child_country'] === 'Ireland' ? 'selected' : '' ?> data-translate="country_ireland">アイルランド</option>
                    <!-- 中国語圏 -->
                    <option value="China" <?= $form_data['child_country'] === 'China' ? 'selected' : '' ?> data-translate="country_china">中国</option>
                    <option value="Taiwan" <?= $form_data['child_country'] === 'Taiwan' ? 'selected' : '' ?> data-translate="country_taiwan">台湾</option>
                    <option value="Hong Kong" <?= $form_data['child_country'] === 'Hong Kong' ? 'selected' : '' ?> data-translate="country_hongkong">香港</option>
                    <option value="Singapore" <?= $form_data['child_country'] === 'Singapore' ? 'selected' : '' ?> data-translate="country_singapore">シンガポール</option>
                    <!-- 韓国語圏 -->
                    <option value="South Korea" <?= $form_data['child_country'] === 'South Korea' ? 'selected' : '' ?> data-translate="country_korea">韓国</option>
                    <!-- ベトナム語圏 -->
                    <option value="Vietnam" <?= $form_data['child_country'] === 'Vietnam' ? 'selected' : '' ?> data-translate="country_vietnam">ベトナム</option>
                    <!-- フィリピノ語圏 -->
                    <option value="Philippines" <?= $form_data['child_country'] === 'Philippines' ? 'selected' : '' ?> data-translate="country_philippines">フィリピン</option>
                    <!-- ネパール語圏 -->
                    <option value="Nepal" <?= $form_data['child_country'] === 'Nepal' ? 'selected' : '' ?> data-translate="country_nepal">ネパール</option>
                    <!-- ポルトガル語圏 -->
                    <option value="Brazil" <?= $form_data['child_country'] === 'Brazil' ? 'selected' : '' ?> data-translate="country_brazil">ブラジル</option>
                    <option value="Portugal" <?= $form_data['child_country'] === 'Portugal' ? 'selected' : '' ?> data-translate="country_portugal">ポルトガル</option>
                    <!-- その他主要国 -->
                    <option value="India" <?= $form_data['child_country'] === 'India' ? 'selected' : '' ?> data-translate="country_india">インド</option>
                    <option value="Bangladesh" <?= $form_data['child_country'] === 'Bangladesh' ? 'selected' : '' ?> data-translate="country_bangladesh">バングラデシュ</option>
                    <option value="Pakistan" <?= $form_data['child_country'] === 'Pakistan' ? 'selected' : '' ?> data-translate="country_pakistan">パキスタン</option>
                    <option value="Sri Lanka" <?= $form_data['child_country'] === 'Sri Lanka' ? 'selected' : '' ?> data-translate="country_srilanka">スリランカ</option>
                    <option value="Myanmar" <?= $form_data['child_country'] === 'Myanmar' ? 'selected' : '' ?> data-translate="country_myanmar">ミャンマー</option>
                    <option value="Thailand" <?= $form_data['child_country'] === 'Thailand' ? 'selected' : '' ?> data-translate="country_thailand">タイ</option>
                    <option value="Indonesia" <?= $form_data['child_country'] === 'Indonesia' ? 'selected' : '' ?> data-translate="country_indonesia">インドネシア</option>
                    <option value="Malaysia" <?= $form_data['child_country'] === 'Malaysia' ? 'selected' : '' ?> data-translate="country_malaysia">マレーシア</option>
                    <option value="Cambodia" <?= $form_data['child_country'] === 'Cambodia' ? 'selected' : '' ?> data-translate="country_cambodia">カンボジア</option>
                    <option value="Laos" <?= $form_data['child_country'] === 'Laos' ? 'selected' : '' ?> data-translate="country_laos">ラオス</option>
                    <option value="Peru" <?= $form_data['child_country'] === 'Peru' ? 'selected' : '' ?> data-translate="country_peru">ペルー</option>
                    <option value="Mexico" <?= $form_data['child_country'] === 'Mexico' ? 'selected' : '' ?> data-translate="country_mexico">メキシコ</option>
                    <option value="Colombia" <?= $form_data['child_country'] === 'Colombia' ? 'selected' : '' ?> data-translate="country_colombia">コロンビア</option>
                    <option value="Other" <?= $form_data['child_country'] === 'Other' ? 'selected' : '' ?> data-translate="country_other">その他</option>
                </select>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="child_grade_label">学年 *</label>
                <select name="child_grade" class="form-select" required>
                    <option value="" data-translate="select_option">選択してください</option>
                    <option value="1" <?= $form_data['child_grade'] === '1' ? 'selected' : '' ?> data-translate="grade_1">小学1年生</option>
                    <option value="2" <?= $form_data['child_grade'] === '2' ? 'selected' : '' ?> data-translate="grade_2">小学2年生</option>
                    <option value="3" <?= $form_data['child_grade'] === '3' ? 'selected' : '' ?> data-translate="grade_3">小学3年生</option>
                    <option value="4" <?= $form_data['child_grade'] === '4' ? 'selected' : '' ?> data-translate="grade_4">小学4年生</option>
                    <option value="5" <?= $form_data['child_grade'] === '5' ? 'selected' : '' ?> data-translate="grade_5">小学5年生</option>
                    <option value="6" <?= $form_data['child_grade'] === '6' ? 'selected' : '' ?> data-translate="grade_6">小学6年生</option>
                    <option value="7" <?= $form_data['child_grade'] === '7' ? 'selected' : '' ?> data-translate="grade_7">中学1年生</option>
                    <option value="8" <?= $form_data['child_grade'] === '8' ? 'selected' : '' ?> data-translate="grade_8">中学2年生</option>
                    <option value="9" <?= $form_data['child_grade'] === '9' ? 'selected' : '' ?> data-translate="grade_9">中学3年生</option>
                </select>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="school_name_label">学校名</label>
                <input type="text" name="school_name" class="form-input" value="<?= h($form_data['school_name']) ?>" data-translate-placeholder="school_name_placeholder">
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="family_members_label">家族構成 *</label>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px;">
                <?php 
                $family_options = [
                    'father' => '父',
                    'mother' => '母', 
                    'older_brother' => '兄',
                    'older_sister' => '姉',
                    'younger_brother' => '弟',
                    'younger_sister' => '妹',
                    'grandfather' => '祖父',
                    'grandmother' => '祖母'
                ];
                foreach ($family_options as $value => $label): 
                    $checked = in_array($value, $form_data['family_members']) ? 'checked' : '';
                ?>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="family_members[]" value="<?= $value ?>" <?= $checked ?>>
                    <span data-translate="family_<?= $value ?>"><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="family_size_label">何人家族ですか？ *</label>
                <select name="family_size" class="form-select" required>
                    <option value="" data-translate="select_option">選択してください</option>
                    <option value="2" <?= $form_data['family_size'] === '2' ? 'selected' : '' ?> data-translate="family_size_2">2人</option>
                    <option value="3" <?= $form_data['family_size'] === '3' ? 'selected' : '' ?> data-translate="family_size_3">3人</option>
                    <option value="4" <?= $form_data['family_size'] === '4' ? 'selected' : '' ?> data-translate="family_size_4">4人</option>
                    <option value="5" <?= $form_data['family_size'] === '5' ? 'selected' : '' ?> data-translate="family_size_5">5人</option>
                    <option value="6" <?= $form_data['family_size'] === '6' ? 'selected' : '' ?> data-translate="family_size_6">6人</option>
                    <option value="7" <?= $form_data['family_size'] === '7' ? 'selected' : '' ?> data-translate="family_size_7">7人</option>
                    <option value="8" <?= $form_data['family_size'] === '8' ? 'selected' : '' ?> data-translate="family_size_8">8人以上</option>
                </select>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="email_label">メールアドレス *</label>
            <input type="email" name="email" class="form-input" value="<?= h($form_data['email']) ?>" required>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="password_label">パスワード *</label>
            <input type="password" name="password" class="form-input" minlength="6" required>
            <small data-translate="password_hint" style="color: #666; font-size: 0.8em;">6文字以上で入力してください</small>
        </div>
        
            <div class="form-group">
                <label class="form-label" data-translate="password_confirm_label">パスワード確認 *</label>
            <input type="password" name="password_confirm" class="form-input" minlength="6" required>
        </div>
        
            <button type="submit" class="btn-primary" data-translate="create_account_button">アカウント作成</button>
        </div>
    </form>
    
    <div class="back-to-login">
        <button type="button" class="back-button" onclick="goBackToLogin()" data-translate="back_to_login">ログイン画面に戻る</button>
    </div>
    
    <div style="margin-top: 15px; text-align: center;">
        <p data-translate="login_link_text" style="font-size: 0.9em; color: #666;">
            既にアカウントをお持ちの場合は
            <button type="button" onclick="goBackToLogin()" style="color: #4CAF50; text-decoration: none; background: none; border: none; cursor: pointer;" data-translate="login_link">こちらからログイン</button>
        </p>
    </div>
</div>

<script>
// 翻訳辞書（日本語がデフォルト）
const translations = {
    ja: {
        native_language_label: '母語を選択してください *',
        parent_name_label: '保護者の方のお名前 *',
        child_name_label: 'お子様のお名前 *',
        child_nickname_label: 'お子様のニックネーム',
        child_nickname_placeholder: '普段どのようにお呼びしますか？（任意）',
        child_gender_label: 'お子様の性別 *',
        child_birthdate_label: 'お子様の生年月日 *',
        child_country_label: 'お子様の出身国 *',
        child_grade_label: '学年 *',
        school_name_label: '学校名',
        school_name_placeholder: '学校名を入力してください（任意）',
        family_members_label: '家族構成 *',
        family_size_label: '何人家族ですか？ *',
        email_label: 'メールアドレス *',
        password_label: 'パスワード *',
        password_confirm_label: 'パスワード確認 *',
        password_hint: '6文字以上で入力してください',
        page_title: '新規登録',
        create_account_button: 'アカウント作成',
        back_to_login: 'ログイン画面に戻る',
        login_link_text: '既にアカウントをお持ちの場合は',
        login_link: 'こちらからログイン',
        select_option: '選択してください',
        gender_male: '男の子',
        gender_female: '女の子',
        grade_1: '小学1年生',
        grade_2: '小学2年生',
        grade_3: '小学3年生',
        grade_4: '小学4年生',
        grade_5: '小学5年生',
        grade_6: '小学6年生',
        grade_7: '中学1年生',
        grade_8: '中学2年生',
        grade_9: '中学3年生',
        family_father: '父',
        family_mother: '母',
        family_older_brother: '兄',
        family_older_sister: '姉',
        family_younger_brother: '弟',
        family_younger_sister: '妹',
        family_grandfather: '祖父',
        family_grandmother: '祖母',
        family_size_2: '2人',
        family_size_3: '3人',
        family_size_4: '4人',
        family_size_5: '5人',
        family_size_6: '6人',
        family_size_7: '7人',
        family_size_8: '8人以上',
        country_usa: 'アメリカ',
        country_uk: 'イギリス',
        country_canada: 'カナダ',
        country_australia: 'オーストラリア',
        country_nz: 'ニュージーランド',
        country_ireland: 'アイルランド',
        country_china: '中国',
        country_taiwan: '台湾',
        country_hongkong: '香港',
        country_singapore: 'シンガポール',
        country_korea: '韓国',
        country_vietnam: 'ベトナム',
        country_philippines: 'フィリピン',
        country_nepal: 'ネパール',
        country_brazil: 'ブラジル',
        country_portugal: 'ポルトガル',
        country_india: 'インド',
        country_bangladesh: 'バングラデシュ',
        country_pakistan: 'パキスタン',
        country_srilanka: 'スリランカ',
        country_myanmar: 'ミャンマー',
        country_thailand: 'タイ',
        country_indonesia: 'インドネシア',
        country_malaysia: 'マレーシア',
        country_cambodia: 'カンボジア',
        country_laos: 'ラオス',
        country_peru: 'ペルー',
        country_mexico: 'メキシコ',
        country_colombia: 'コロンビア',
        country_other: 'その他',
        // Error messages
        error_duplicate_email: 'このメールアドレスは既に登録されています',
        error_parent_name_required: '保護者のお名前を入力してください',
        error_child_name_required: 'お子様のお名前を入力してください',
        error_child_gender_required: 'お子様の性別を選択してください',
        error_email_required: 'メールアドレスを入力してください',
        error_email_invalid: '有効なメールアドレスを入力してください',
        error_password_required: 'パスワードを入力してください',
        error_password_min_length: 'パスワードは6文字以上で入力してください',
        error_password_mismatch: 'パスワードが一致しません'
    },
    en: {
        native_language_label: 'Select your native language *',
        parent_name_label: 'Parent/Guardian Name *',
        child_name_label: 'Child\'s Name *',
        child_nickname_label: 'Child\'s Nickname',
        child_nickname_placeholder: 'What you usually call your child (optional)',
        child_gender_label: 'Child\'s Gender *',
        child_birthdate_label: 'Child\'s Date of Birth *',
        child_country_label: 'Child\'s Country of Origin *',
        child_grade_label: 'Grade *',
        school_name_label: 'School Name',
        school_name_placeholder: 'Name of school your child attends (optional)',
        family_members_label: 'Family Members *',
        family_size_label: 'Family Size *',
        email_label: 'Email Address *',
        password_label: 'Password *',
        password_confirm_label: 'Confirm Password *',
        password_hint: '6 characters or more required',
        page_title: 'New Registration',
        create_account_button: 'Create Account',
        back_to_login: 'Back to Login',
        login_link_text: 'Already have an account?',
        login_link: 'Login here',
        select_option: 'Please select',
        gender_male: 'Boy',
        gender_female: 'Girl',
        // Countries
        country_usa: 'United States',
        country_uk: 'United Kingdom',
        country_canada: 'Canada',
        country_australia: 'Australia',
        country_nz: 'New Zealand',
        country_ireland: 'Ireland',
        country_china: 'China',
        country_taiwan: 'Taiwan',
        country_hongkong: 'Hong Kong',
        country_singapore: 'Singapore',
        country_korea: 'South Korea',
        country_vietnam: 'Vietnam',
        country_philippines: 'Philippines',
        country_nepal: 'Nepal',
        country_brazil: 'Brazil',
        country_portugal: 'Portugal',
        country_india: 'India',
        country_bangladesh: 'Bangladesh',
        country_pakistan: 'Pakistan',
        country_srilanka: 'Sri Lanka',
        country_myanmar: 'Myanmar',
        country_thailand: 'Thailand',
        country_indonesia: 'Indonesia',
        country_malaysia: 'Malaysia',
        country_cambodia: 'Cambodia',
        country_laos: 'Laos',
        country_peru: 'Peru',
        country_mexico: 'Mexico',
        country_colombia: 'Colombia',
        country_other: 'Other',
        // Grades
        grade_1: '1st Grade',
        grade_2: '2nd Grade',
        grade_3: '3rd Grade',
        grade_4: '4th Grade',
        grade_5: '5th Grade',
        grade_6: '6th Grade',
        grade_7: '7th Grade (Middle School)',
        grade_8: '8th Grade (Middle School)',
        grade_9: '9th Grade (Middle School)',
        // Family members
        family_father: 'Father',
        family_mother: 'Mother',
        family_older_brother: 'Older Brother',
        family_older_sister: 'Older Sister',
        family_younger_brother: 'Younger Brother',
        family_younger_sister: 'Younger Sister',
        family_grandfather: 'Grandfather',
        family_grandmother: 'Grandmother',
        // Family size
        family_size_2: '2 people',
        family_size_3: '3 people',
        family_size_4: '4 people',
        family_size_5: '5 people',
        family_size_6: '6 people',
        family_size_7: '7 people',
        family_size_8: '8+ people',
        // Error messages
        error_duplicate_email: 'This email address is already registered',
        error_parent_name_required: 'Please enter parent/guardian name',
        error_child_name_required: 'Please enter child\'s name',
        error_child_gender_required: 'Please select child\'s gender',
        error_email_required: 'Please enter email address',
        error_email_invalid: 'Please enter a valid email address',
        error_password_required: 'Please enter password',
        error_password_min_length: '6 characters or more required',
        error_password_mismatch: 'Passwords do not match'
    },
    zh: {
        native_language_label: '请选择您的母语 *',
        parent_name_label: '监护人姓名 *',
        child_name_label: '孩子姓名 *',
        child_nickname_label: '孩子昵称',
        child_nickname_placeholder: '平时怎么称呼孩子（可选）',
        child_gender_label: '孩子性别 *',
        child_birthdate_label: '孩子出生日期 *',
        child_country_label: '孩子出生国家 *',
        child_grade_label: '年级 *',
        school_name_label: '学校名称',
        school_name_placeholder: '孩子就读的学校名称（可选）',
        family_members_label: '家庭成员 *',
        family_size_label: '家庭人数 *',
        email_label: '电子邮箱 *',
        password_label: '密码 *',
        password_confirm_label: '确认密码 *',
        password_hint: '请输入6个或更多字符',
        page_title: '新用户注册',
        create_account_button: '创建账户',
        back_to_login: '返回登录',
        login_link_text: '已有账户？',
        login_link: '点击登录',
        select_option: '请选择',
        gender_male: '男孩',
        gender_female: '女孩',
        // Countries
        country_usa: '美国',
        country_uk: '英国',
        country_canada: '加拿大',
        country_australia: '澳大利亚',
        country_nz: '新西兰',
        country_ireland: '爱尔兰',
        country_china: '中国',
        country_taiwan: '台湾',
        country_hongkong: '香港',
        country_singapore: '新加坡',
        country_korea: '韩国',
        country_vietnam: '越南',
        country_philippines: '菲律宾',
        country_nepal: '尼泊尔',
        country_brazil: '巴西',
        country_portugal: '葡萄牙',
        country_india: '印度',
        country_bangladesh: '孟加拉国',
        country_pakistan: '巴基斯坦',
        country_srilanka: '斯里兰卡',
        country_myanmar: '缅甸',
        country_thailand: '泰国',
        country_indonesia: '印度尼西亚',
        country_malaysia: '马来西亚',
        country_cambodia: '柬埔寨',
        country_laos: '老挝',
        country_peru: '秘鲁',
        country_mexico: '墨西哥',
        country_colombia: '哥伦比亚',
        country_other: '其他',
        // Grades
        grade_1: '小学1年级',
        grade_2: '小学2年级',
        grade_3: '小学3年级',
        grade_4: '小学4年级',
        grade_5: '小学5年级',
        grade_6: '小学6年级',
        grade_7: '初中1年级',
        grade_8: '初中2年级',
        grade_9: '初中3年级',
        // Family members
        family_father: '父亲',
        family_mother: '母亲',
        family_older_brother: '哥哥',
        family_older_sister: '姐姐',
        family_younger_brother: '弟弟',
        family_younger_sister: '妹妹',
        family_grandfather: '爷爷',
        family_grandmother: '奶奶',
        // Family size
        family_size_2: '2人',
        family_size_3: '3人',
        family_size_4: '4人',
        family_size_5: '5人',
        family_size_6: '6人',
        family_size_7: '7人',
        family_size_8: '8人以上',
        // Error messages
        error_duplicate_email: '此邮箱地址已被注册',
        error_parent_name_required: '请输入监护人姓名',
        error_child_name_required: '请输入孩子姓名',
        error_child_gender_required: '请选择孩子性别',
        error_email_required: '请输入邮箱地址',
        error_email_invalid: '请输入有效的邮箱地址',
        error_password_required: '请输入密码',
        error_password_min_length: '请输入6个或更多字符',
        error_password_mismatch: '密码不匹配'
    },
    tl: {
        native_language_label: 'Piliin ang inyong katutubong wika *',
        parent_name_label: 'Pangalan ng Magulang/Guardian *',
        child_name_label: 'Pangalan ng Anak *',
        child_nickname_label: 'Palayaw ng Anak',
        child_nickname_placeholder: 'Ano ang tawag ninyo sa inyong anak? (opsyonal)',
        child_gender_label: 'Kasarian ng Anak *',
        child_birthdate_label: 'Petsa ng Kapanganakan ng Anak *',
        child_country_label: 'Bansa ng Pinagmulan ng Anak *',
        child_grade_label: 'Baitang *',
        school_name_label: 'Pangalan ng Paaralan',
        school_name_placeholder: 'Pangalan ng paaralan na pinapasukan (opsyonal)',
        family_members_label: 'Mga Miyembro ng Pamilya *',
        family_size_label: 'Bilang ng mga Tao sa Pamilya *',
        email_label: 'Email Address *',
        password_label: 'Password *',
        password_confirm_label: 'Kumpirmahin ang Password *',
        password_hint: 'Maglagay ng 6 o higit pang character',
        page_title: 'Pagpaparehistro',
        create_account_button: 'Lumikha ng Account',
        back_to_login: 'Bumalik sa Login',
        login_link_text: 'May account na?',
        login_link: 'Mag-login dito',
        select_option: 'Pakipili',
        boy_option: 'Lalaki',
        girl_option: 'Babae',
        // Countries
        country_usa: 'Estados Unidos',
        country_uk: 'United Kingdom',
        country_canada: 'Canada',
        country_australia: 'Australia',
        country_nz: 'New Zealand',
        country_ireland: 'Ireland',
        country_china: 'Tsina',
        country_taiwan: 'Taiwan',
        country_hongkong: 'Hong Kong',
        country_singapore: 'Singapore',
        country_korea: 'South Korea',
        country_vietnam: 'Vietnam',
        country_philippines: 'Pilipinas',
        country_nepal: 'Nepal',
        country_brazil: 'Brazil',
        country_portugal: 'Portugal',
        country_india: 'India',
        country_bangladesh: 'Bangladesh',
        country_pakistan: 'Pakistan',
        country_srilanka: 'Sri Lanka',
        country_myanmar: 'Myanmar',
        country_thailand: 'Thailand',
        country_indonesia: 'Indonesia',
        country_malaysia: 'Malaysia',
        country_cambodia: 'Cambodia',
        country_laos: 'Laos',
        country_peru: 'Peru',
        country_mexico: 'Mexico',
        country_colombia: 'Colombia',
        country_other: 'Iba pa',
        // Grades
        grade_1: 'Unang Baitang',
        grade_2: 'Ikalawang Baitang',
        grade_3: 'Ikatlong Baitang',
        grade_4: 'Ikaapat na Baitang',
        grade_5: 'Ikalimang Baitang',
        grade_6: 'Ikaanim na Baitang',
        grade_7: 'Ikapitong Baitang',
        grade_8: 'Ikawalong Baitang',
        grade_9: 'Ikasiyam na Baitang',
        // Family members
        family_father: 'Tatay',
        family_mother: 'Nanay',
        family_older_brother: 'Kuya',
        family_older_sister: 'Ate',
        family_younger_brother: 'Nakababatang Kapatid na Lalaki',
        family_younger_sister: 'Nakababatang Kapatid na Babae',
        family_grandfather: 'Lolo',
        family_grandmother: 'Lola',
        // Family size
        family_size_2: '2 tao',
        family_size_3: '3 tao',
        family_size_4: '4 na tao',
        family_size_5: '5 tao',
        family_size_6: '6 na tao',
        family_size_7: '7 tao',
        family_size_8: '8 o higit pang tao',
        // Error messages
        error_duplicate_email: 'Ang email address na ito ay naka-rehistro na',
        error_parent_name_required: 'Pakisulat ang pangalan ng magulang/guardian',
        error_child_name_required: 'Pakisulat ang pangalan ng anak',
        error_child_gender_required: 'Pakipili ang kasarian ng anak',
        error_email_required: 'Pakisulat ang email address',
        error_email_invalid: 'Pakisulat ang tamang email address',
        error_password_required: 'Pakisulat ang password',
        error_password_min_length: 'Maglagay ng 6 o higit pang character',
        error_password_mismatch: 'Hindi tumugma ang mga password'
    }
    /*,
    ko: {
        native_language_label: '모국어를 선택해주세요 *',
        parent_name_label: '보호자 성함 *',
        child_name_label: '자녀 이름 *',
        child_nickname_label: '자녀 애칭',
        child_nickname_placeholder: '평소 부르는 이름 (선택사항)',
        child_gender_label: '자녀 성별 *',
        child_birthdate_label: '자녀 생년월일 *',
        child_country_label: '자녀 출신국가 *',
        child_grade_label: '학년 *',
        school_name_label: '학교명',
        school_name_placeholder: '다니고 있는 학교명 (선택사항)',
        family_members_label: '가족구성 *',
        family_size_label: '가족 인원수 *',
        email_label: '이메일 주소 *',
        password_label: '비밀번호 *',
        password_confirm_label: '비밀번호 확인 *',
        password_hint: '6자 이상 입력해주세요',
        page_title: '신규 등록',
        create_account_button: '계정 생성',
        login_link_text: '이미 계정이 있으신가요?',
        login_link: '로그인하기',
        select_option: '선택해주세요',
        gender_male: '남아',
        gender_female: '여아',
        // Countries  
        country_usa: '미국',
        country_uk: '영국',
        country_canada: '캐나다',
        country_australia: '호주',
        country_nz: '뉴질랜드',
        country_ireland: '아일랜드',
        country_china: '중국',
        country_taiwan: '대만',
        country_hongkong: '홍콩',
        country_singapore: '싱가포르',
        country_korea: '한국',
        country_vietnam: '베트남',
        country_philippines: '필리핀',
        country_nepal: '네팔',
        country_brazil: '브라질',
        country_portugal: '포르투갈',
        country_india: '인도',
        country_bangladesh: '방글라데시',
        country_pakistan: '파키스탄',
        country_srilanka: '스리랑카',
        country_myanmar: '미얀마',
        country_thailand: '태국',
        country_indonesia: '인도네시아',
        country_malaysia: '말레이시아',
        country_cambodia: '캄보디아',
        country_laos: '라오스',
        country_peru: '페루',
        country_mexico: '멕시코',
        country_colombia: '콜롬비아',
        country_other: '기타',
        // Grades
        grade_1: '초등학교 1학년',
        grade_2: '초등학교 2학년',
        grade_3: '초등학교 3학년',
        grade_4: '초등학교 4학년',
        grade_5: '초등학교 5학년',
        grade_6: '초등학교 6학년',
        grade_7: '중학교 1학년',
        grade_8: '중학교 2학년',
        grade_9: '중학교 3학년',
        // Family members
        family_father: '아버지',
        family_mother: '어머니',
        family_older_brother: '형/오빠',
        family_older_sister: '누나/언니',
        family_younger_brother: '남동생',
        family_younger_sister: '여동생',
        family_grandfather: '할아버지',
        family_grandmother: '할머니',
        // Family size
        family_size_2: '2명',
        family_size_3: '3명',
        family_size_4: '4명',
        family_size_5: '5명',
        family_size_6: '6명',
        family_size_7: '7명',
        family_size_8: '8명 이상',
        // Error messages
        error_duplicate_email: '이 이메일 주소는 이미 등록되어 있습니다',
        error_parent_name_required: '보호자 성함을 입력해주세요',
        error_child_name_required: '자녀 이름을 입력해주세요',
        error_child_gender_required: '자녀 성별을 선택해주세요',
        error_email_required: '이메일 주소를 입력해주세요',
        error_email_invalid: '유효한 이메일 주소를 입력해주세요',
        error_password_required: '비밀번호를 입력해주세요',
        error_password_min_length: '6자 이상 입력해주세요',
        error_password_mismatch: '비밀번호가 일치하지 않습니다'
    },
    vi: {
        native_language_label: 'Chọn ngôn ngữ mẹ đẻ của bạn *',
        parent_name_label: 'Tên phụ huynh/người giám hộ *',
        child_name_label: 'Tên con *',
        child_nickname_label: 'Biệt danh của con',
        child_nickname_placeholder: 'Bạn thường gọi con như thế nào? (không bắt buộc)',
        child_gender_label: 'Giới tính của con *',
        child_birthdate_label: 'Ngày sinh của con *',
        child_country_label: 'Quốc gia xuất thân của con *',
        child_grade_label: 'Lớp *',
        school_name_label: 'Tên trường',
        school_name_placeholder: 'Tên trường con đang học (tùy chọn)',
        family_members_label: 'Thành viên gia đình *',
        family_size_label: 'Số người trong gia đình *',
        email_label: 'Địa chỉ email *',
        password_label: 'Mật khẩu *',
        password_confirm_label: 'Xác nhận mật khẩu *',
        password_hint: 'Vui lòng nhập 6 ký tự trở lên',
        page_title: 'Đăng ký mới',
        create_account_button: 'Tạo tài khoản',
        login_link_text: 'Đã có tài khoản?',
        login_link: 'Đăng nhập tại đây',
        select_option: 'Vui lòng chọn',
        gender_male: 'Bé trai',
        gender_female: 'Bé gái',
        country_usa: 'Hoa Kỳ', country_china: 'Trung Quốc', country_korea: 'Hàn Quốc', country_vietnam: 'Việt Nam', country_thailand: 'Thái Lan', country_philippines: 'Philippines', country_brazil: 'Brazil', country_peru: 'Peru', country_other: 'Khác',
        grade_1: 'Lớp 1', grade_2: 'Lớp 2', grade_3: 'Lớp 3', grade_4: 'Lớp 4', grade_5: 'Lớp 5', grade_6: 'Lớp 6', grade_7: 'Lớp 7', grade_8: 'Lớp 8', grade_9: 'Lớp 9',
        family_father: 'Bố', family_mother: 'Mẹ', family_older_brother: 'Anh trai', family_older_sister: 'Chị gái', family_younger_brother: 'Em trai', family_younger_sister: 'Em gái', family_grandfather: 'Ông', family_grandmother: 'Bà',
        family_size_2: '2 người', family_size_3: '3 người', family_size_4: '4 người', family_size_5: '5 người', family_size_6: '6 người', family_size_7: '7 người', family_size_8: '8+ người',
        // Error messages
        error_duplicate_email: 'Địa chỉ email này đã được đăng ký',
        error_parent_name_required: 'Vui lòng nhập tên phụ huynh/người giám hộ',
        error_child_name_required: 'Vui lòng nhập tên con',
        error_child_gender_required: 'Vui lòng chọn giới tính của con',
        error_email_required: 'Vui lòng nhập địa chỉ email',
        error_email_invalid: 'Vui lòng nhập địa chỉ email hợp lệ',
        error_password_required: 'Vui lòng nhập mật khẩu',
        error_password_min_length: 'Vui lòng nhập 6 ký tự trở lên',
        error_password_mismatch: 'Mật khẩu không khớp'
        },
    tl: {
        native_language_label: 'Piliin ang inyong katutubong wika *',
        parent_name_label: 'Pangalan ng Magulang/Guardian *',
        child_name_label: 'Pangalan ng Bata *',
        child_nickname_label: 'Palayaw ng Bata',
        child_nickname_placeholder: 'Ano ang tawag ninyo sa inyong anak? (opsyonal)',
        child_gender_label: 'Kasarian ng Bata *',
        child_birthdate_label: 'Petsa ng Kapanganakan *',
        child_country_label: 'Bansa ng Pinagmulan ng Bata *',
        child_grade_label: 'Grado *',
        school_name_label: 'Pangalan ng Paaralan',
        school_name_placeholder: 'Pangalan ng paaralan na pinapasukan (opsyonal)',
        family_members_label: 'Mga Miyembro ng Pamilya *',
        family_size_label: 'Laki ng Pamilya *',
        email_label: 'Email Address *',
        password_label: 'Password *',
        password_confirm_label: 'Kumpirmahin ang Password *',
        password_hint: 'Maglagay ng 6 o higit pang character',
        page_title: 'Magpatala',
        create_account_button: 'Lumikha ng Account',
        login_link_text: 'May account na?',
        login_link: 'Mag-login dito',
        select_option: 'Pakipili',
        gender_male: 'Lalaki',
        gender_female: 'Babae',
        country_usa: 'Estados Unidos', country_uk: 'United Kingdom', country_canada: 'Canada', country_australia: 'Australia', country_nz: 'New Zealand', country_ireland: 'Ireland', country_china: 'Tsina', country_taiwan: 'Taiwan', country_hongkong: 'Hong Kong', country_singapore: 'Singapore', country_korea: 'South Korea', country_vietnam: 'Vietnam', country_philippines: 'Pilipinas', country_nepal: 'Nepal', country_brazil: 'Brazil', country_portugal: 'Portugal', country_india: 'India', country_bangladesh: 'Bangladesh', country_pakistan: 'Pakistan', country_srilanka: 'Sri Lanka', country_myanmar: 'Myanmar', country_thailand: 'Thailand', country_indonesia: 'Indonesia', country_malaysia: 'Malaysia', country_cambodia: 'Cambodia', country_laos: 'Laos', country_peru: 'Peru', country_mexico: 'Mexico', country_colombia: 'Colombia', country_other: 'Iba pa',
        grade_1: 'Ika-1 Baitang', grade_2: 'Ika-2 Baitang', grade_3: 'Ika-3 Baitang', grade_4: 'Ika-4 Baitang', grade_5: 'Ika-5 Baitang', grade_6: 'Ika-6 Baitang', grade_7: 'Ika-7 Baitang', grade_8: 'Ika-8 Baitang', grade_9: 'Ika-9 Baitang',
        family_father: 'Tatay', family_mother: 'Nanay', family_older_brother: 'Kuya', family_older_sister: 'Ate', family_younger_brother: 'Bunso (Lalaki)', family_younger_sister: 'Bunso (Babae)', family_grandfather: 'Lolo', family_grandmother: 'Lola',
        family_size_2: '2 tao', family_size_3: '3 tao', family_size_4: '4 na tao', family_size_5: '5 tao', family_size_6: '6 na tao', family_size_7: '7 tao', family_size_8: '8+ tao',
        // Error messages
        error_duplicate_email: 'Ang email address na ito ay naka-rehistro na',
        error_parent_name_required: 'Pakisulatang ang pangalan ng magulang/guardian',
        error_child_name_required: 'Pakisulat ang pangalan ng bata',
        error_child_gender_required: 'Pakipili ang kasarian ng bata',
        error_email_required: 'Pakisulat ang email address',
        error_email_invalid: 'Pakisulat ang tamang email address',
        error_password_required: 'Pakisulat ang password',
        error_password_min_length: 'Maglagay ng 6 o higit pang character',
        error_password_mismatch: 'Hindi tumugma ang mga password'
    },
    ne: {
        native_language_label: 'तपाईंको मातृभाषा छनोट गर्नुहोस् *',
        parent_name_label: 'अभिभावकको नाम *',
        child_name_label: 'बच्चाको नाम *',
        child_nickname_label: 'बच्चाको उपनाम',
        child_nickname_placeholder: 'तपाईं आफ्नो बच्चालाई के भन्नुहुन्छ? (वैकल्पिक)',
        child_gender_label: 'बच्चाको लिंग *',
        child_birthdate_label: 'बच्चाको जन्म मिति *',
        child_country_label: 'बच्चाको जन्म देश *',
        child_grade_label: 'कक्षा *',
        school_name_label: 'विद्यालयको नाम',
        school_name_placeholder: 'बच्चाले पढ्ने विद्यालयको नाम (वैकल्पिक)',
        family_members_label: 'पारिवारिक सदस्यहरू *',
        family_size_label: 'परिवारको आकार *',
        email_label: 'इमेल ठेगाना *',
        password_label: 'पासवर्ड *',
        password_confirm_label: 'पासवर्ड पुष्टि गर्नुहोस् *',
        password_hint: '६ वा सोभन्दा बढी अक्षर राख्नुहोस्',
        page_title: 'नयाँ दर्ता',
        create_account_button: 'खाता बनाउनुहोस्',
        login_link_text: 'पहिले नै खाता छ?',
        login_link: 'यहाँ लगिन गर्नुहोस्',
        select_option: 'कृपया छनोट गर्नुहोस्',
        gender_male: 'केटा',
        gender_female: 'केटी',
        country_usa: 'संयुक्त राज्य अमेरिका', country_uk: 'युनाइटेड किंगडम', country_canada: 'क्यानाडा', country_australia: 'अष्ट्रेलिया', country_nz: 'न्यूजीलान्ड', country_ireland: 'आयरल्यान्ड', country_china: 'चीन', country_taiwan: 'ताइवान', country_hongkong: 'हङकङ', country_singapore: 'सिंगापुर', country_korea: 'दक्षिण कोरिया', country_vietnam: 'भियतनाम', country_philippines: 'फिलिपिन्स', country_nepal: 'नेपाल', country_brazil: 'ब्राजिल', country_portugal: 'पोर्तुगल', country_india: 'भारत', country_bangladesh: 'बंगलादेश', country_pakistan: 'पाकिस्तान', country_srilanka: 'श्रीलंका', country_myanmar: 'म्यानमार', country_thailand: 'थाइल्यान्ड', country_indonesia: 'इन्डोनेसिया', country_malaysia: 'मलेसिया', country_cambodia: 'कम्बोडिया', country_laos: 'लाओस', country_peru: 'पेरु', country_mexico: 'मेक्सिको', country_colombia: 'कोलम्बिया', country_other: 'अन्य',
        grade_1: 'कक्षा १', grade_2: 'कक्षा २', grade_3: 'कक्षा ३', grade_4: 'कक्षा ४', grade_5: 'कक्षा ५', grade_6: 'कक्षा ६', grade_7: 'कक्षा ७', grade_8: 'कक्षा ८', grade_9: 'कक्षा ९',
        family_father: 'बुवा', family_mother: 'आमा', family_older_brother: 'दाजु', family_older_sister: 'दिदी', family_younger_brother: 'भाइ', family_younger_sister: 'बहिनी', family_grandfather: 'हजुरबुवा', family_grandmother: 'हजुरआमा',
        family_size_2: '२ जना', family_size_3: '३ जना', family_size_4: '४ जना', family_size_5: '५ जना', family_size_6: '६ जना', family_size_7: '७ जना', family_size_8: '८+ जना',
        // Error messages
        error_duplicate_email: 'यो इमेल ठेगाना पहिले नै दर्ता भइसकेको छ',
        error_parent_name_required: 'कृपया अभिभावकको नाम लेख्नुहोस्',
        error_child_name_required: 'कृपया बच्चाको नाम लेख्नुहोस्',
        error_child_gender_required: 'कृपया बच्चाको लिंग छनोट गर्नुहोस्',
        error_email_required: 'कृपया इमेल ठेगाना लेख्नुहोस्',
        error_email_invalid: 'कृपया मान्य इमेल ठेगाना लेख्नुहोस्',
        error_password_required: 'कृपया पासवर्ड लेख्नुहोस्',
        error_password_min_length: 'कृपया ६ वा सोभन्दा बढी अक्षर राख्नुहोस्',
        error_password_mismatch: 'पासवर्डहरू मेल खाँदैनन्'
    },
    pt: {
        native_language_label: 'Selecione sua língua nativa *',
        parent_name_label: 'Nome dos Pais/Responsável *',
        child_name_label: 'Nome da Criança *',
        child_nickname_label: 'Apelido da Criança',
        child_nickname_placeholder: 'Como você chama seu filho? (opcional)',
        child_gender_label: 'Gênero da Criança *',
        child_birthdate_label: 'Data de Nascimento da Criança *',
        child_country_label: 'País de Origem da Criança *',
        child_grade_label: 'Série/Ano *',
        school_name_label: 'Nome da Escola',
        school_name_placeholder: 'Nome da escola que a criança frequenta (opcional)',
        family_members_label: 'Membros da Família *',
        family_size_label: 'Tamanho da Família *',
        email_label: 'Endereço de Email *',
        password_label: 'Senha *',
        password_confirm_label: 'Confirmar Senha *',
        password_hint: 'Digite 6 ou mais caracteres',
        page_title: 'Novo Registro',
        create_account_button: 'Criar Conta',
        login_link_text: 'Já tem uma conta?',
        login_link: 'Faça login aqui',
        select_option: 'Por favor selecione',
        gender_male: 'Menino',
        gender_female: 'Menina',
        country_usa: 'Estados Unidos', country_uk: 'Reino Unido', country_canada: 'Canadá', country_australia: 'Austrália', country_nz: 'Nova Zelândia', country_ireland: 'Irlanda', country_china: 'China', country_taiwan: 'Taiwan', country_hongkong: 'Hong Kong', country_singapore: 'Singapura', country_korea: 'Coreia do Sul', country_vietnam: 'Vietnã', country_philippines: 'Filipinas', country_nepal: 'Nepal', country_brazil: 'Brasil', country_portugal: 'Portugal', country_india: 'Índia', country_bangladesh: 'Bangladesh', country_pakistan: 'Paquistão', country_srilanka: 'Sri Lanka', country_myanmar: 'Mianmar', country_thailand: 'Tailândia', country_indonesia: 'Indonésia', country_malaysia: 'Malásia', country_cambodia: 'Camboja', country_laos: 'Laos', country_peru: 'Peru', country_mexico: 'México', country_colombia: 'Colômbia', country_other: 'Outro',
        grade_1: '1º Ano', grade_2: '2º Ano', grade_3: '3º Ano', grade_4: '4º Ano', grade_5: '5º Ano', grade_6: '6º Ano', grade_7: '7º Ano', grade_8: '8º Ano', grade_9: '9º Ano',
        family_father: 'Pai', family_mother: 'Mãe', family_older_brother: 'Irmão mais velho', family_older_sister: 'Irmã mais velha', family_younger_brother: 'Irmão mais novo', family_younger_sister: 'Irmã mais nova', family_grandfather: 'Avô', family_grandmother: 'Avó',
        family_size_2: '2 pessoas', family_size_3: '3 pessoas', family_size_4: '4 pessoas', family_size_5: '5 pessoas', family_size_6: '6 pessoas', family_size_7: '7 pessoas', family_size_8: '8+ pessoas',
        // Error messages
        error_duplicate_email: 'Este endereço de email já está registrado',
        error_parent_name_required: 'Por favor, insira o nome dos pais/responsável',
        error_child_name_required: 'Por favor, insira o nome da criança',
        error_child_gender_required: 'Por favor, selecione o gênero da criança',
        error_email_required: 'Por favor, insira o endereço de email',
        error_email_invalid: 'Por favor, insira um endereço de email válido',
        error_password_required: 'Por favor, insira a senha',
        error_password_min_length: 'Digite 6 ou mais caracteres',
        error_password_mismatch: 'As senhas não coincidem'
    }
    */
};

// ログイン画面に戻る（母語選択の言語情報を保持）
function goBackToLogin() {
    const currentLang = document.getElementById('nativeLanguageSelect').value || 'ja';
    window.location.href = `login.php?lang=${currentLang}`;
}

function translateForm() {
    const selectedLang = document.getElementById('nativeLanguageSelect').value;
    const mainForm = document.getElementById('mainForm');
    const authContainer = document.querySelector('.auth-container');
    
    // フォームを表示
    if (selectedLang) {
        mainForm.style.display = 'block';
        
        // 言語に応じたフォントクラスを適用
        authContainer.className = 'auth-container lang-' + selectedLang;
        
        // 翻訳実行
        if (translations[selectedLang]) {
            const trans = translations[selectedLang];
            
            // data-translate属性を持つ要素を翻訳
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (trans[key]) {
                    element.textContent = trans[key];
                }
            });
            
            // プレースホルダーも翻訳
            document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
                const key = element.getAttribute('data-translate-placeholder');
                if (trans[key]) {
                    element.placeholder = trans[key];
                }
            });
            
            // エラーメッセージも翻訳
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage && errorMessage.dataset.errorKey) {
                const errorKey = errorMessage.dataset.errorKey;
                if (trans[errorKey]) {
                    errorMessage.textContent = trans[errorKey];
                }
            }
        }
    } else {
        mainForm.style.display = 'none';
        authContainer.className = 'auth-container lang-ja';
    }
}

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', function() {
    // URLパラメータから言語を取得
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang') || 'ja';
    
    // URLパラメータに言語がある場合は母語選択を自動設定
    if (lang && translations[lang]) {
        document.getElementById('nativeLanguageSelect').value = lang;
        translateForm();
    }
    
    const selectedLang = document.getElementById('nativeLanguageSelect').value;
    if (selectedLang) {
        translateForm();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>