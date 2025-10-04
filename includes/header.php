<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// パスを動的に解決
$config_path = dirname(__DIR__) . '/config/database.php';
$functions_path = dirname(__DIR__) . '/includes/functions.php';

if (file_exists($config_path)) {
    require_once $config_path;
} else {
    require_once 'config/database.php';
}

if (file_exists($functions_path)) {
    require_once $functions_path;
} else {
    require_once 'includes/functions.php';
}

$user = $_SESSION['user'] ?? null;
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// 言語設定（URLパラメータを優先、その次にセッション言語、その次にユーザーの母語、デフォルトは日本語）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? ($user ? $user['native_language'] : 'ja') ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'tl'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// ナビゲーションの翻訳
$nav_translations = [];
if ($current_language === 'en') {
    $nav_translations = [
        'menu' => 'Menu',
        'lesson' => 'Practice',
        'stamps' => 'Stamps',
        'my_steps' => 'My Steps',
        'return_to_menu' => 'Back to Menu',
        'logout' => 'Logout',
        'name_label' => 'name',
        'lang_label' => 'lang'
    ];
} elseif ($current_language === 'tl') {
    $nav_translations = [
        'menu' => 'Menu',
        'lesson' => 'Aralin',
        'stamps' => 'Mga Selyo',
        'my_steps' => 'Mga Hakbang Ko',
        'return_to_menu' => 'Bumalik sa Menu',
        'logout' => 'Mag-logout',
        'name_label' => 'pangalan',
        'lang_label' => 'wika'
    ];
} elseif ($current_language === 'zh') {
    $nav_translations = [
        'menu' => '菜单',
        'lesson' => '课程',
        'stamps' => '印章',
        'my_steps' => '我的步伐',
        'return_to_menu' => '返回菜单',
        'logout' => '退出登录',
        'name_label' => '姓名',
        'lang_label' => '语言'
    ];
} elseif ($current_language === 'ko') {
    $nav_translations = [
        'menu' => '메뉴',
        'lesson' => '수업',
        'stamps' => '도장',
        'my_steps' => '나의 발걸음',
        'return_to_menu' => '메뉴로 돌아가기',
        'logout' => '로그아웃',
        'name_label' => '이름',
        'lang_label' => '언어'
    ];
} elseif ($current_language === 'vi') {
    $nav_translations = [
        'menu' => 'Menu',
        'lesson' => 'Bài học',
        'stamps' => 'Huy hiệu',
        'my_steps' => 'Bước đi của tôi',
        'return_to_menu' => 'Quay về Menu',
        'logout' => 'Đăng xuất',
        'name_label' => 'tên',
        'lang_label' => 'ngôn ngữ'
    ];
} elseif ($current_language === 'ne') {
    $nav_translations = [
        'menu' => 'मेनु',
        'lesson' => 'पाठ',
        'stamps' => 'मुद्रिका',
        'my_steps' => 'मेरा पाइला',
        'return_to_menu' => 'मेनुमा फर्किनुहोस्',
        'logout' => 'लगआउट',
        'name_label' => 'नाम',
        'lang_label' => 'भाषा'
    ];
} elseif ($current_language === 'pt') {
    $nav_translations = [
        'menu' => 'Menu',
        'lesson' => 'Lição',
        'stamps' => 'Medalhas',
        'my_steps' => 'Meus Passos',
        'return_to_menu' => 'Voltar ao Menu',
        'logout' => 'Sair',
        'name_label' => 'nome',
        'lang_label' => 'idioma'
    ];
} else {
    $nav_translations = [
        'menu' => 'メニュー',
        'lesson' => 'れんしゅう',
        'stamps' => 'できたマーク',
        'my_steps' => 'のびのびメーター',
        'return_to_menu' => 'メニューに戻る',
        'logout' => 'やめる',
        'name_label' => 'なまえ',
        'lang_label' => 'ことば'
    ];
}

// CSSパスを正しく生成
function getCSSPath() {
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($current_dir === '/') {
        return '/assets/css/style.css';
    }
    
    // auth/ や lessons/ などのサブディレクトリにいる場合
    if (strpos($current_dir, '/auth') !== false || 
        strpos($current_dir, '/lessons') !== false || 
        strpos($current_dir, '/progress') !== false ||
        strpos($current_dir, '/games') !== false ||
        strpos($current_dir, '/account') !== false ||
        strpos($current_dir, '/about_school') !== false ||
        strpos($current_dir, '/admin') !== false) {
        return '../assets/css/style.css';
    }
    
    return 'assets/css/style.css';
}

// JSパスを正しく生成
function getJSPath() {
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($current_dir === '/') {
        return '/assets/js/app.js';
    }
    
    if (strpos($current_dir, '/auth') !== false || 
        strpos($current_dir, '/lessons') !== false || 
        strpos($current_dir, '/progress') !== false ||
        strpos($current_dir, '/games') !== false ||
        strpos($current_dir, '/account') !== false ||
        strpos($current_dir, '/about_school') !== false ||
        strpos($current_dir, '/admin') !== false) {
        return '../assets/js/app.js';
    }
    
    return 'assets/js/app.js';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'nihongonote' ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= getBasePath('assets/images/favicon.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= getBasePath('assets/images/favicon.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= getBasePath('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= getCSSPath() ?>">
    <?php if (isset($additional_css)): ?>
        <?= $additional_css ?>
    <?php endif; ?>
    
    <?php if ($user): ?>
    <style>
    :root {
        --gender-factor: <?= $user['child_gender'] == 'boy' ? '1' : '0' ?>;
        /* 男の子: 青・緑系 (160-220度), 女の子: 赤・ピンク・オレンジ系 (320-40度) */
        --base-hue: calc(var(--gender-factor) * 200 + (1 - var(--gender-factor)) * 350);
        --accent-hue: calc(var(--gender-factor) * 180 + (1 - var(--gender-factor)) * 15);
        --secondary-hue: calc(var(--gender-factor) * 160 + (1 - var(--gender-factor)) * 330);
        
        --primary-color: hsl(var(--base-hue), 55%, 65%);
        --primary-light: hsl(var(--base-hue), 45%, 80%);
        --primary-dark: hsl(var(--base-hue), 65%, 50%);
        --accent-color: hsl(var(--accent-hue), 60%, 60%);
        --secondary-color: hsl(var(--secondary-hue), 50%, 70%);
        --background: hsl(var(--base-hue), 35%, 94%);
        --card-background: hsl(var(--base-hue), 30%, 97%);
    }
    </style>
    <?php endif; ?>
</head>
<body>


<?php if ($user && $current_page !== 'login' && $current_page !== 'register'): ?>
<!-- 上部背景画像は CSS で表示されるため、この img 要素は削除 -->

<!-- 言語切り替えタブ -->
<div class="language-tabs-global">
    <div class="language-tab" data-lang="ja" onclick="switchLanguage('ja')">日本語</div>
    <div class="language-tab" data-lang="en" onclick="switchLanguage('en')">English</div>
    <div class="language-tab" data-lang="zh" onclick="switchLanguage('zh')">中文</div>
    <div class="language-tab" data-lang="tl" onclick="switchLanguage('tl')">Tagalog</div>
</div>

<!-- 中央ロゴ -->
<div class="top-logo-center" id="topLogo">
    <?php
    // 現在の言語を取得
    $current_lang = $_SESSION['language'] ?? 'ja';
    $logo_files = [
        'en' => 'ralango_logo_en.png',
        'zh' => 'ralango_logo_zh.png', 
        'ja' => 'ralango_logo_jp.png'
    ];
    $logo_file = $logo_files[$current_lang] ?? $logo_files['ja'];
    ?>
    <img src="<?= getBasePath('assets/images/' . $logo_file) ?>" alt="nihongonote" class="top-logo-image" id="topLogoImg">
</div>

<!-- 右上の子供の情報 -->
<div class="child-info-top">
    <div class="user-details-top">
        <div class="user-info-row">
            <span class="user-name"><?= h($nav_translations['name_label']) ?>: <?= h($user['child_name']) ?></span>
            <span class="user-lang"><?= h($nav_translations['lang_label']) ?>: <?= h($user['native_language'] ?? 'ja') ?></span>
        </div>
        <?php 
        $current_dir = dirname($_SERVER['SCRIPT_NAME']);
        $is_in_subdirectory = (strpos($current_dir, '/auth') !== false || 
                              strpos($current_dir, '/lessons') !== false || 
                              strpos($current_dir, '/progress') !== false ||
                              strpos($current_dir, '/games') !== false ||
                              strpos($current_dir, '/account') !== false ||
                              strpos($current_dir, '/about_school') !== false ||
                              strpos($current_dir, '/admin') !== false);
        
        if ($current_page !== 'index' || $is_in_subdirectory): ?>
        <div class="button-row">
            <a href="<?= getBasePath('index.php') ?>" class="menu-return-btn-small"><?= h($nav_translations['return_to_menu']) ?></a>
            <a href="<?= getBasePath('auth/logout.php') ?>" class="logout-button"><?= h($nav_translations['logout']) ?></a>
        </div>
        <?php else: ?>
        <div class="logout-row">
            <a href="<?= getBasePath('auth/logout.php') ?>" class="logout-button"><?= h($nav_translations['logout']) ?></a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function handleHeaderScroll() {
    const logo = document.getElementById('topLogo');
    const logoImg = document.getElementById('topLogoImg');
    
    if (window.scrollY > 100) {
        logo.classList.add('scrolled');
        logoImg.classList.add('scrolled');
    } else {
        logo.classList.remove('scrolled');
        logoImg.classList.remove('scrolled');
    }
}

window.headerScrollHandler = handleHeaderScroll;
window.addEventListener('scroll', window.headerScrollHandler);

function switchLanguage(lang) {
    // アクティブなタブを更新
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
    
    // body要素に現在の言語を設定
    document.body.setAttribute('data-current-lang', lang);
    
    // ロゴを言語に応じて切り替え
    const logoImg = document.getElementById('topLogoImg');
    if (logoImg) {
        let logoSrc = '';
        switch(lang) {
            case 'en':
                logoSrc = getBasePath('assets/images/ralango_logo_en.png');
                break;
            case 'zh':
                logoSrc = getBasePath('assets/images/ralango_logo_zh.png');
                break;
            case 'tl':
                logoSrc = getBasePath('assets/images/ralango_logo_en.png'); // タガログ語は英語ロゴを使用
                break;
            default:
                logoSrc = getBasePath('assets/images/ralango_logo_jp.png');
                break;
        }
        logoImg.src = logoSrc;
    }
    
    // URLパラメータを更新してページをリロード
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('lang', lang);
    window.location.search = urlParams.toString();
}

// getBasePath関数をJavaScriptで定義
function getBasePath(path) {
    const currentPath = window.location.pathname;
    if (currentPath.includes('/auth/') || currentPath.includes('/lessons/') || 
        currentPath.includes('/progress/') || currentPath.includes('/games/') || 
        currentPath.includes('/account/') || currentPath.includes('/about_school/') || 
        currentPath.includes('/admin/')) {
        return '../' + path;
    }
    return path;
}

// ページロード時に現在の言語を設定
document.addEventListener('DOMContentLoaded', function() {
    const currentLang = '<?= $current_language ?>';
    const activeTab = document.querySelector(`[data-lang="${currentLang}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
    document.body.setAttribute('data-current-lang', currentLang);
    
    // ページロード時にロゴを現在の言語に設定
    const logoImg = document.getElementById('topLogoImg');
    if (logoImg) {
        let logoSrc = '';
        switch(currentLang) {
            case 'en':
                logoSrc = getBasePath('assets/images/ralango_logo_en.png');
                break;
            case 'zh':
                logoSrc = getBasePath('assets/images/ralango_logo_zh.png');
                break;
            case 'tl':
                logoSrc = getBasePath('assets/images/ralango_logo_en.png'); // タガログ語は英語ロゴを使用
                break;
            default:
                logoSrc = getBasePath('assets/images/ralango_logo_jp.png');
                break;
        }
        logoImg.src = logoSrc;
    }
});
</script>

<div class="container">