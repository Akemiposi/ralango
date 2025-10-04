// nihongonote app.js
document.addEventListener('DOMContentLoaded', function() {
    // ページ読み込み時の初期化処理
    console.log('Nihongonote app loaded');
});

// 共通のユーティリティ関数
function fadeIn(element, duration = 300) {
    element.style.opacity = 0;
    element.style.display = 'block';
    
    const start = performance.now();
    
    function fade(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        
        element.style.opacity = progress;
        
        if (progress < 1) {
            requestAnimationFrame(fade);
        }
    }
    
    requestAnimationFrame(fade);
}

// エラーハンドリング
window.addEventListener('error', function(event) {
    console.error('JavaScript Error:', event.error);
});