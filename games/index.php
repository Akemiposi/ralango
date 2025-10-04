<?php
// games/index.php - ゲーム一覧ページ
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインチェック
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

// 必要な関数を読み込み
require_once '../includes/functions.php';

$page_title = 'ゲームで遊ぶ - nihongonote';
require_once '../includes/header.php';

require_once '../includes/translation.php';

$user = $_SESSION['user'];

// 言語設定（URLパラメータを優先、その次にセッション言語、その次にユーザーの母語、デフォルトは日本語）
$current_language = $_GET['lang'] ?? $_SESSION['dashboard_language'] ?? ($user ? $user['native_language'] : 'ja') ?? 'ja';

// サポートされている言語かチェック
$supported_languages = ['ja', 'en', 'zh', 'ko', 'vi', 'tl', 'ne', 'pt'];
if (!in_array($current_language, $supported_languages)) {
    $current_language = 'ja';
}

// 翻訳するテキスト群
$texts_to_translate = [
    'games_title' => 'ゲームであそぼう！',
    'games_description' => 'ゲームをしながらにほんごをおぼえよう！',
    'janken_title' => 'じゃんけんゲーム',
    'janken_description' => 'じゃんけんしようよ',
    'kanacard_title' => 'かなカードゲーム',
    'kanacard_description' => 'ひらがなであそぼう',
    'play_button' => 'あそぶ',
    'score_label' => 'てんすう：',
    'score_unit' => 'てん'
];

// 翻訳実行（日本語以外の場合のみ）
$translations = [];
if ($current_language !== 'ja') {
    if ($current_language === 'en') {
        $translations = [
            'games_title' => 'Let\'s Play Games!',
            'games_description' => 'Learn Japanese through fun games',
            'janken_title' => 'Rock Paper Scissors',
            'janken_description' => 'Play rock paper scissors with the computer!',
            'kanacard_title' => 'Kana Card Game',
            'kanacard_description' => 'Card game to learn Hiragana',
            'play_button' => 'Play',
            'score_label' => 'Score:',
            'score_unit' => 'pts'
        ];
    } elseif ($current_language === 'tl') {
        $translations = [
            'games_title' => 'Maglaro Tayo!',
            'games_description' => 'Matuto ng Japanese sa pamamagitan ng mga laro',
            'janken_title' => 'Jack en Poy',
            'janken_description' => 'Maglaro ng jack en poy sa computer!',
            'kanacard_title' => 'Kana Card Game',
            'kanacard_description' => 'Laro ng baraha para matuto ng Hiragana',
            'play_button' => 'Maglaro',
            'score_label' => 'Puntos:',
            'score_unit' => 'pts'
        ];
    } elseif ($current_language === 'zh') {
        $translations = [
            'games_title' => '用游戏学习吧！',
            'games_description' => '边玩游戏边学习日文吧',
            'janken_title' => '剪刀石头布',
            'janken_description' => '来玩猜拳吧',
            'kanacard_title' => '假名卡片',
            'kanacard_description' => '用平假名玩耍吧',
            'play_button' => '开始游戏',
            'score_label' => '分数：',
            'score_unit' => '分'
        ];
    } elseif ($current_language === 'ko') {
        $translations = [
            'games_title' => '게임하자!',
            'games_description' => '재미있는 게임으로 일본어 배우기',
            'janken_title' => '가위바위보',
            'janken_description' => '컴퓨터와 가위바위보 해보세요!',
            'kanacard_title' => '가나 카드 게임',
            'kanacard_description' => '히라가나를 배우는 카드 게임',
            'play_button' => '게임하기',
            'score_label' => '점수:',
            'score_unit' => '점'
        ];
    } elseif ($current_language === 'vi') {
        $translations = [
            'games_title' => 'Cùng chơi game nào!',
            'games_description' => 'Học tiếng Nhật qua các trò chơi thú vị',
            'janken_title' => 'Kéo búa bao',
            'janken_description' => 'Chơi kéo búa bao với máy tính!',
            'kanacard_title' => 'Game thẻ Kana',
            'kanacard_description' => 'Game thẻ bài để học Hiragana',
            'play_button' => 'Chơi',
            'score_label' => 'Điểm:',
            'score_unit' => 'điểm'
        ];
    } elseif ($current_language === 'ne') {
        $translations = [
            'games_title' => 'खेल खेलौं!',
            'games_description' => 'रमाइलो खेलहरू मार्फत जापानी सिक्नुहोस्',
            'janken_title' => 'ढुङ्गा कागज कैंची',
            'janken_description' => 'कम्प्युटरसँग ढुङ्गा कागज कैंची खेल्नुहोस्!',
            'kanacard_title' => 'काना कार्ड खेल',
            'kanacard_description' => 'हिरागाना सिक्ने कार्ड खेल',
            'play_button' => 'खेल्नुहोस्',
            'score_label' => 'अंक:',
            'score_unit' => 'अंक'
        ];
    } elseif ($current_language === 'pt') {
        $translations = [
            'games_title' => 'Vamos jogar!',
            'games_description' => 'Aprenda japonês através de jogos divertidos',
            'janken_title' => 'Pedra Papel Tesoura',
            'janken_description' => 'Jogue pedra papel tesoura com o computador!',
            'kanacard_title' => 'Jogo de Cartas Kana',
            'kanacard_description' => 'Jogo de cartas para aprender Hiragana',
            'play_button' => 'Jogar',
            'score_label' => 'Pontuação:',
            'score_unit' => 'pts'
        ];
    } else {
        $translations = translateMultipleTexts($texts_to_translate, $current_language, 'ja');
    }
} else {
    $translations = $texts_to_translate;
}
?>

<div class="games-container">

    <div class="games-header">
        <h1 class="games-title"><?= h($translations['games_title']) ?></h1>
        <p class="games-subtitle">
            <?= h($translations['games_description']) ?>
        </p>
    </div>

    <div class="games-grid">
        <!-- じゃんけんゲーム -->
        <div class="game-card" onclick="window.location.href='janken/index.php'">
            <h3 class="game-title"><?= h($translations['janken_title']) ?></h3>
            <div class="game-image">
                <img src="../assets/images/icons/janken.png" alt="<?= h($translations['janken_title']) ?>" class="game-img">
            </div>
            <p class="game-description"><?= h($translations['janken_description']) ?></p>
            <div class="game-score">
                <span class="score-label"><?= h($translations['score_label']) ?></span>
                <span id="janken_score" class="score-value">0</span>
                <span class="score-unit"><?= h($translations['score_unit']) ?></span>
            </div>
            <div class="game-play-btn"><?= h($translations['play_button']) ?></div>
        </div>

        <!-- かなカードゲーム -->
        <div class="game-card" onclick="window.location.href='kanacard/index.php'">
            <h3 class="game-title"><?= h($translations['kanacard_title']) ?></h3>
            <div class="game-image">
                <img src="../assets/images/icons/kanacard.png" alt="<?= h($translations['kanacard_title']) ?>" class="game-img">
            </div>
            <p class="game-description"><?= h($translations['kanacard_description']) ?></p>
            <div class="game-score">
                <span class="score-label"><?= h($translations['score_label']) ?></span>
                <span id="kanacard_score" class="score-value">0</span>
                <span class="score-unit"><?= h($translations['score_unit']) ?></span>
            </div>
            <div class="game-play-btn"><?= h($translations['play_button']) ?></div>
        </div>
    </div>

</div>

<style>
/* 背景画像設定 */
body {
    background-image: url('../assets/images/bg_top.png'), url('../assets/images/bg_bottom.png');
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
}

/* ゲームページスタイル - 他ページと統一 */

/* コンテナ */
.games-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}


/* ヘッダーセクション - 縮小 */
.games-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 20px 20px;
    color: var(--primary-dark);
}

.games-title {
    font-size: 2.5em;
    margin-bottom: 15px;
    font-weight: 700;
}

.games-subtitle {
    font-size: 1.2em;
    margin-bottom: 10px;
    color: #666;
}


/* ゲームグリッド */
.games-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 40px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

/* ゲームカード */
.game-card {
    background: var(--card-background);
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    padding: 40px 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border-top: 5px solid var(--accent-color);
    position: relative;
    overflow: hidden;
}

.game-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    border-top-color: var(--primary-color);
}

.game-card.back-card {
    border-top-color: hsl(calc(var(--base-hue) - 30), 40%, 60%);
}

.game-card.back-card:hover {
    border-top-color: hsl(calc(var(--base-hue) - 30), 50%, 50%);
}

/* ゲームアイコン */
.game-icon {
    font-size: 4em;
    margin-bottom: 20px;
    display: block;
    filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.1));
}

/* ゲームタイトル */
.game-title {
    color: var(--primary-dark);
    font-size: 1.6em;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

/* ゲーム画像 */
.game-image {
    margin: 15px 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.game-img {
    max-width: 180px;
    max-height: 180px;
    width: auto;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.game-card:hover .game-img {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

/* ゲーム説明 */
.game-description {
    color: #666;
    font-size: 1.1em;
    line-height: 1.6;
    margin-bottom: 25px;
    min-height: 3em;
}

/* ゲーム得点表示 */
.game-score {
    background: rgba(108, 117, 125, 0.1);
    padding: 10px 15px;
    border-radius: 15px;
    margin: 15px 0;
    border: 2px solid #e9ecef;
}

.score-label {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9em;
}

.score-value {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.4em;
    margin: 0 5px;
}

.score-unit {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9em;
}

/* プレイボタン */
.game-play-btn {
    display: inline-block;
    background: linear-gradient(45deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1.1em;
    box-shadow: 0 4px 15px hsla(var(--base-hue), 40%, 70%, 0.3);
    transition: all 0.3s ease;
}

.game-card:hover .game-play-btn {
    transform: scale(1.05);
    box-shadow: 0 6px 20px hsla(var(--base-hue), 40%, 70%, 0.4);
}


/* レスポンシブ */
@media (max-width: 768px) {
    .games-container {
        padding: 15px;
    }
    
    .games-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        max-width: 400px;
    }
    
    .games-title {
        font-size: 2em;
    }
    
    .game-card {
        padding: 30px 20px;
    }
    
    .game-icon {
        font-size: 3em;
    }
    
    .game-img {
        max-width: 140px;
        max-height: 140px;
    }
}

/* 特定言語の微調整 */
.lang-ne *, .lang-ne {
    font-family: -apple-system, BlinkMacSystemFont, "Noto Sans Devanagari", "Mangal", 
                "Segoe UI", Arial, sans-serif !important;
}
</style>

<script>
// header.phpのswitchLanguage関数をオーバーライドして、このページ用の動作を定義
window.switchLanguage = function(lang) {
    // セッションに言語設定を保存
    fetch('../api/set_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            language: lang
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ページをリロードして言語変更を反映
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('lang', lang);
            window.location.search = urlParams.toString();
        } else {
            console.error('Language setting failed:', data.error);
            // エラーが発生してもページをリロード
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('lang', lang);
            window.location.search = urlParams.toString();
        }
    })
    .catch(error => {
        console.error('Error setting language:', error);
        // エラーが発生してもページをリロード
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('lang', lang);
        window.location.search = urlParams.toString();
    });
};

// ゲームの得点をローカルストレージから読み込んで表示
function loadGameScores() {
    const jankenScore = localStorage.getItem('janken_score') || '0';
    const kanacardScore = localStorage.getItem('kanacard_score') || '0';
    
    const jankenElement = document.getElementById('janken_score');
    const kanacardElement = document.getElementById('kanacard_score');
    
    if (jankenElement) {
        jankenElement.textContent = jankenScore;
    }
    
    if (kanacardElement) {
        kanacardElement.textContent = kanacardScore;
    }
}

// ページロード時の初期化
document.addEventListener('DOMContentLoaded', function() {
    // 得点を読み込み
    loadGameScores();
    
    // 定期的に得点を更新（他のタブでゲームをプレイした場合に対応）
    setInterval(loadGameScores, 5000);
    
    // 現在の言語でボディクラスを設定
    const currentLang = '<?= $current_language ?>';
    document.body.className = document.body.className.replace(/\blang-\w+\b/g, '');
    document.body.classList.add('lang-' + currentLang);
});
</script>

<?php require_once '../includes/footer.php'; ?>