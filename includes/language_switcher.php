<?php
// includes/language_switcher.php - 言語切り替えボタンコンポーネント
require_once __DIR__ . '/language.php';

// 現在の言語を取得
$current_language = $lang->getLanguage();
$available_languages = $lang->getAvailableLanguages();

// タガログ語フォント用CSS
$tagalog_font_css = "
.lang-tl * {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}
";
?>

<style>
.language-switcher {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    gap: 8px;
    background: rgba(255, 255, 255, 0.95);
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
}

.language-btn {
    background: transparent;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 6px 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.3s ease;
    color: #666;
    min-width: 50px;
}

.language-btn:hover {
    border-color: #4CAF50;
    color: #4CAF50;
    transform: translateY(-1px);
}

.language-btn.active {
    background: #4CAF50;
    border-color: #4CAF50;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.language-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

<?= $tagalog_font_css ?>
</style>

<div class="language-switcher" id="languageSwitcher">
    <?php foreach ($available_languages as $code => $name): ?>
        <button 
            class="language-btn <?= $current_language === $code ? 'active' : '' ?>" 
            onclick="switchLanguage('<?= $code ?>')"
            data-lang="<?= $code ?>"
        >
            <?= $name ?>
        </button>
    <?php endforeach; ?>
</div>

<script>
async function switchLanguage(newLanguage) {
    // ボタンの状態を更新
    document.querySelectorAll('.language-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.lang === newLanguage) {
            btn.classList.add('active');
        }
    });
    
    try {
        // APIで言語設定を保存
        const response = await fetch('../api/set_language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ language: newLanguage })
        });
        
        if (response.ok) {
            // ページをリロードして新しい言語を適用
            window.location.reload();
        } else {
            console.error('Failed to set language');
        }
    } catch (error) {
        console.error('Error setting language:', error);
    }
}

// ページにタガログ語用のクラスを適用
document.addEventListener('DOMContentLoaded', function() {
    const currentLang = document.querySelector('.language-btn.active')?.dataset.lang;
    if (currentLang === 'tl') {
        document.body.classList.add('lang-tl');
    }
});
</script>