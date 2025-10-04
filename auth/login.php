<?php
// auth/login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログイン済みの場合はリダイレクト
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// 共通ファイルを読み込み
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/language.php';
require_once '../includes/GeminiTranslator.php';

// 翻訳機能を初期化
$translator = new GeminiTranslator();

// 翻訳テキストを定義
$translations = [
    'ja' => [
        'hajimete' => 'はじめて',
        'login' => 'ログイン',
        'email_label' => 'メールアドレス',
        'password_label' => 'パスワード',
        'back' => '戻る'
    ],
    'en' => [
        'hajimete' => 'Get Started',
        'login' => 'Login',
        'email_label' => 'Email Address',
        'password_label' => 'Password',
        'back' => 'Back'
    ],
    'zh' => [
        'hajimete' => '开始',
        'login' => '登录',
        'email_label' => '电子邮件地址',
        'password_label' => '密码',
        'back' => '返回'
    ]
];

$page_title = 'ログイン - nihongonote';

$error = '';

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'メールアドレスとパスワードを入力してください';
    } else {
        $user = authenticateUser($email, $password);
        
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'ログインに失敗しました。メールアドレスまたはパスワードが正しくありません。';
        }
    }
}
?>

<style>
:root {
    --primary-color: hsl(200, 50%, 65%);
    --primary-light: hsl(200, 40%, 80%);
    --background: hsl(200, 30%, 94%);
    --card-background: hsl(200, 25%, 97%);
}

.language-tabs {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 1000;
}

.language-tab {
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    color: #666;
}

.language-tab:hover {
    background: rgba(255, 255, 255, 1);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.language-tab.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', 'SimHei', system-ui, sans-serif;
    background: var(--background);
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
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

/* 英語フォント統一 */
.language-tab[data-lang="en"],
.language-tab[data-lang="en"].active,
body[data-current-lang="en"] .main-button,
body[data-current-lang="en"] .form-label,
body[data-current-lang="en"] .submit-button,
body[data-current-lang="en"] .back-button {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif !important;
}

/* 中国語フォント統一 */
.language-tab[data-lang="zh"],
.language-tab[data-lang="zh"].active,
body[data-current-lang="zh"] .main-button,
body[data-current-lang="zh"] .form-label,
body[data-current-lang="zh"] .submit-button,
body[data-current-lang="zh"] .back-button {
    font-family: 'PingFang SC', 'Microsoft YaHei', 'SimHei', sans-serif !important;
}

.login-container {
    text-align: center;
    max-width: 800px;
    width: 90%;
    margin: 0 auto;
}

.logo-container {
    margin-bottom: 50px;
}

.logo-container img {
    max-width: 1936px;
    height: auto;
    width: 95%;
}

.button-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
    margin-bottom: 30px;
    justify-content: center;
    position: relative;
    top: -6%;
}

.main-button {
    padding: 24px 48px;
    font-size: 1.8em;
    font-weight: bold;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    max-width: 240px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn-hajimete {
    background: linear-gradient(45deg, #FF6B6B, #FF8E53);
    color: white;
}

.btn-hajimete:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

.btn-login {
    background: linear-gradient(45deg, #4ECDC4, #44A08D);
    color: white;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(78, 205, 196, 0.4);
}

.login-form {
    display: none;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    margin-top: 20px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.submit-button {
    width: 100%;
    background: linear-gradient(45deg, #4ECDC4, #44A08D);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 1.1em;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-button:hover {
    background: linear-gradient(45deg, #44A08D, #3d8b7a);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(78, 205, 196, 0.4);
}

.back-button {
    background: #666;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}

.back-button:hover {
    background: #555;
}



.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid #c62828;
}

@media (max-width: 768px) {
    .logo-container img {
        max-width: 1520px;
    }
    
    .button-container {
        flex-direction: column;
    }
    
    .main-button {
        padding: 22px 36px;
        font-size: 1.56em;
        max-width: none;
    }
    
    .login-form {
        padding: 20px;
    }
}
</style>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="login-container">
    <!-- 言語切り替えタブ -->
    <div class="language-tabs">
        <div class="language-tab active" data-lang="ja" onclick="switchLanguage('ja')">日本語</div>
        <div class="language-tab" data-lang="en" onclick="switchLanguage('en')">English</div>
        <div class="language-tab" data-lang="zh" onclick="switchLanguage('zh')">中文</div>
        <div class="language-tab" data-lang="tl" onclick="switchLanguage('tl')">Tagalog</div>
    </div>
    
    <!-- ロゴ -->
    <div class="logo-container">
        <img id="logoImage" src="../assets/images/ralango_logo_jp.png" alt="nihongonote">
    </div>
    
    <!-- メインボタン -->
    <div class="button-container" id="mainButtons">
        <button class="main-button btn-hajimete" onclick="goToRegister()" id="hajimeteBtn"><?= $translations['ja']['hajimete'] ?></button>
        <button class="main-button btn-login" onclick="showLoginForm()" id="loginBtn"><?= $translations['ja']['login'] ?></button>
    </div>
    
    <!-- ログインフォーム -->
    <div class="login-form" id="loginForm">
        <?php if ($error): ?>
            <div class="error-message"><?= h($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label class="form-label" id="emailLabel"><?= $translations['ja']['email_label'] ?></label>
                <input type="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" id="passwordLabel"><?= $translations['ja']['password_label'] ?></label>
                <input type="password" name="password" class="form-input" required>
            </div>
            
            <button type="submit" class="submit-button" id="submitBtn"><?= $translations['ja']['login'] ?></button>
            <button type="button" class="back-button" onclick="hideLoginForm()" id="backBtn"><?= $translations['ja']['back'] ?></button>
        </form>
    </div>
</div>

<script>
function showLoginForm() {
    document.getElementById('mainButtons').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
}

function hideLoginForm() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('mainButtons').style.display = 'block';
}

function goToRegister() {
    // 現在選択されている言語をURLパラメータとして渡す
    const currentLang = document.querySelector('.language-tab.active')?.getAttribute('data-lang') || 'ja';
    window.location.href = `register.php?lang=${currentLang}`;
}

// 翻訳テキスト
const translations = <?= json_encode($translations) ?>;

function switchLanguage(lang) {
    // アクティブなタブを更新
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
    
    // body要素に現在の言語を設定（CSS用）
    document.body.setAttribute('data-current-lang', lang);
    
    // ロゴを切り替え
    const logoImage = document.getElementById('logoImage');
    switch(lang) {
        case 'ja':
            logoImage.src = '../assets/images/ralango_logo_jp.png';
            break;
        case 'en':
            logoImage.src = '../assets/images/ralango_logo_en.png';
            break;
        case 'zh':
            logoImage.src = '../assets/images/ralango_logo_zh.png';
            break;
    }
    
    // ボタンとラベルのテキストを更新
    if (translations[lang]) {
        document.getElementById('hajimeteBtn').textContent = translations[lang].hajimete;
        document.getElementById('loginBtn').textContent = translations[lang].login;
        document.getElementById('emailLabel').textContent = translations[lang].email_label;
        document.getElementById('passwordLabel').textContent = translations[lang].password_label;
        document.getElementById('submitBtn').textContent = translations[lang].login;
        document.getElementById('backBtn').textContent = translations[lang].back;
    }
}

// エラーがある場合は自動でログインフォームを表示
<?php if ($error): ?>
showLoginForm();
<?php endif; ?>
</script>

</body>
</html>