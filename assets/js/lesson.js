// assets/js/lesson.js
// レッスン画面専用JavaScript

class LessonApp {
    constructor() {
        this.currentTab = 'miru';
        this.currentLesson = 1;
        this.user = null;
        this.audioContext = null;
        this.isPlaying = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadUserData();
        this.initializeTabs();
        this.setupAudio();
        this.loadProgress();
    }
    
    // イベントバインド
    bindEvents() {
        // タブクリック
        document.querySelectorAll('.tab').forEach((tab, index) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const tabNames = ['miru', 'yatte', 'dekita'];
                this.showTab(tabNames[index]);
            });
        });
        
        // 動画再生ボタン
        document.querySelectorAll('.play-button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.playVideo();
            });
        });
        
        // TTS音声再生ボタン
        document.querySelectorAll('.tts-button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const text = button.getAttribute('data-text') || 'せんせい、おはようございます。';
                this.playTTS(text);
            });
        });
        
        // 次へボタン
        document.querySelectorAll('.next-button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const action = button.getAttribute('data-action');
                
                if (action === 'next-tab') {
                    this.nextTab();
                } else if (action === 'show-badge') {
                    this.showBadgeModal();
                } else {
                    // デフォルトは次のタブへ
                    this.nextTab();
                }
            });
        });
        
        // バッジモーダル関連
        const badgeModal = document.getElementById('badgeModal');
        if (badgeModal) {
            // モーダル外をクリックで閉じる
            badgeModal.addEventListener('click', (e) => {
                if (e.target === badgeModal) {
                    this.closeBadgeModal();
                }
            });
            
            // 閉じるボタン
            const closeBtn = badgeModal.querySelector('.close-modal');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.closeBadgeModal();
                });
            }
        }
        
        // ESCキーでモーダルを閉じる
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeBadgeModal();
            }
        });
        
        // キーボードナビゲーション
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            switch(e.key) {
                case '1':
                    this.showTab('miru');
                    break;
                case '2':
                    this.showTab('yatte');
                    break;
                case '3':
                    this.showTab('dekita');
                    break;
                case ' ': // スペースキーで動画/音声再生
                    e.preventDefault();
                    if (this.currentTab === 'miru') {
                        this.playVideo();
                    } else if (this.currentTab === 'yatte') {
                        this.playTTS('せんせい、おはようございます。');
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.nextTab();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    this.prevTab();
                    break;
            }
        });
        
        // ページ離脱前に進捗を保存
        window.addEventListener('beforeunload', () => {
            this.saveProgress();
        });
    }
    
    // タブ初期化
    initializeTabs() {
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.lesson-content');
        
        if (tabs.length > 0) {
            tabs[0].classList.add('active');
            if (contents.length > 0) {
                contents[0].classList.remove('hidden');
            }
        }
        
        // タブインジケーター更新
        this.updateTabIndicator();
    }
    
    // タブ表示
    showTab(tabName) {
        // 現在のタブから離脱時の処理
        this.onTabLeave(this.currentTab);
        
        // タブの状態更新
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        document.querySelectorAll('.lesson-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // 新しいタブをアクティブに
        const contentMap = {
            'miru': 'miruContent',
            'yatte': 'yatteContent', 
            'dekita': 'dekitaContent'
        };
        
        const tabIndex = Object.keys(contentMap).indexOf(tabName);
        const activeTab = document.querySelectorAll('.tab')[tabIndex];
        const targetContent = document.getElementById(contentMap[tabName]);
        
        if (activeTab) {
            activeTab.classList.add('active');
        }
        
        if (targetContent) {
            targetContent.classList.remove('hidden');
            // コンテンツ表示アニメーション
            targetContent.style.opacity = '0';
            targetContent.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                targetContent.style.transition = 'all 0.5s ease';
                targetContent.style.opacity = '1';
                targetContent.style.transform = 'translateY(0)';
            }, 50);
        }
        
        this.currentTab = tabName;
        this.updateTabIndicator();
        this.onTabEnter(tabName);
        
        // 進捗を記録
        this.recordStep(tabName);
    }
    
    // タブインジケーター更新
    updateTabIndicator() {
        const tabContainer = document.querySelector('.lesson-tabs');
        if (tabContainer) {
            const tabIndex = ['miru', 'yatte', 'dekita'].indexOf(this.currentTab);
            tabContainer.setAttribute('data-active', tabIndex + 1);
        }
    }
    
    // 次のタブへ移動
    nextTab() {
        const tabOrder = ['miru', 'yatte', 'dekita'];
        const currentIndex = tabOrder.indexOf(this.currentTab);
        
        if (currentIndex < tabOrder.length - 1) {
            this.showTab(tabOrder[currentIndex + 1]);
        } else {
            // 最後のタブの場合はバッジモーダルを表示
            this.completeLesson();
        }
    }
    
    // 前のタブへ移動
    prevTab() {
        const tabOrder = ['miru', 'yatte', 'dekita'];
        const currentIndex = tabOrder.indexOf(this.currentTab);
        
        if (currentIndex > 0) {
            this.showTab(tabOrder[currentIndex - 1]);
        }
    }
    
    // タブ入場時の処理
    onTabEnter(tabName) {
        switch(tabName) {
            case 'miru':
                this.initVideoTab();
                break;
            case 'yatte':
                this.initPracticeTab();
                break;
            case 'dekita':
                this.initCompletionTab();
                break;
        }
    }
    
    // タブ離脱時の処理
    onTabLeave(tabName) {
        // 音声を停止
        if (this.isPlaying) {
            speechSynthesis.cancel();
            this.isPlaying = false;
        }
    }
    
    // ビデオタブ初期化
    initVideoTab() {
        console.log('ビデオタブが表示されました');
        // ビデオタブ特有の処理
    }
    
    // 練習タブ初期化
    initPracticeTab() {
        console.log('練習タブが表示されました');
        // 練習タブ特有の処理
        this.setupPronunciationGuide();
    }
    
    // 完了タブ初期化
    initCompletionTab() {
        console.log('完了タブが表示されました');
        // 完了時のアニメーション
        this.animateCompletion();
    }
    
    // 動画再生
    playVideo() {
        console.log('動画を再生します');
        
        const playButtons = document.querySelectorAll('.play-button');
        playButtons.forEach(button => {
            // クリックアニメーション
            button.style.transform = 'scale(0.9)';
            button.style.transition = 'transform 0.1s ease';
            
            setTimeout(() => {
                button.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    button.style.transform = 'scale(1)';
                }, 200);
            }, 100);
        });
        
        // 実際のビデオ再生処理をここに追加
        // 例：HTML5 Video API、YouTube API等
    }
    
    // TTS音声再生
    playTTS(text = 'せんせい、おはようございます。') {
        console.log('TTS再生:', text);
        
        // 既に再生中の場合は停止
        if (this.isPlaying) {
            speechSynthesis.cancel();
            this.isPlaying = false;
            return;
        }
        
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'ja-JP';
            utterance.rate = 0.8;
            utterance.pitch = 1.1;
            utterance.volume = 0.9;
            
            // イベントリスナー
            utterance.onstart = () => {
                this.isPlaying = true;
                this.updateTTSButtons(true);
            };
            
            utterance.onend = () => {
                this.isPlaying = false;
                this.updateTTSButtons(false);
            };
            
            utterance.onerror = () => {
                this.isPlaying = false;
                this.updateTTSButtons(false);
                console.error('音声再生エラー');
            };
            
            speechSynthesis.speak(utterance);
        } else {
            alert('お使いのブラウザは音声機能に対応していません。');
        }
    }
    
    // TTSボタンの状態更新
    updateTTSButtons(isPlaying) {
        document.querySelectorAll('.tts-button').forEach(button => {
            if (isPlaying) {
                button.textContent = '🔇 停止';
                button.classList.add('playing');
            } else {
                button.textContent = '🔊 音声再生';
                button.classList.remove('playing');
            }
        });
    }
    
    // バッジモーダル表示
    showBadgeModal() {
        const modal = document.getElementById('badgeModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('active');
            
            // バッジアニメーション
            const badgeIcon = modal.querySelector('.badge-icon');
            if (badgeIcon) {
                badgeIcon.style.animation = 'sparkle 2s ease-in-out';
            }
            
            // 効果音（オプション）
            this.playSuccessSound();
        }
    }
    
    // バッジモーダル閉じる
    closeBadgeModal() {
        const modal = document.getElementById('badgeModal');
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('active');
                modal.style.animation = '';
                
                // 次のレッスンへ移動またはカリキュラムに戻る
                this.onLessonComplete();
            }, 300);
        }
    }
    
    // レッスン完了処理
    completeLesson() {
        // バッジ獲得処理
        this.earnBadge();
        
        // バッジモーダル表示
        setTimeout(() => {
            this.showBadgeModal();
        }, 500);
    }
    
    // バッジ獲得
    earnBadge() {
        if (this.user) {
            // サーバーにバッジ獲得を送信
            fetch('../api/earn_badge.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.user.id,
                    lesson_id: this.currentLesson,
                    badge_type: 'completion'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('バッジ獲得結果:', data);
            })
            .catch(error => {
                console.error('バッジ獲得エラー:', error);
            });
        }
    }
    
    // ステップ記録
    recordStep(step) {
        if (this.user) {
            fetch('../api/save_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.user.id,
                    lesson_id: this.currentLesson,
                    step: step
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('進捗保存結果:', data);
            })
            .catch(error => {
                console.error('進捗保存エラー:', error);
            });
        }
    }
    
    // 進捗保存
    saveProgress() {
        if (this.user && this.currentTab) {
            this.recordStep(this.currentTab);
        }
    }
    
    // 進捗読み込み
    loadProgress() {
        const lessonId = new URLSearchParams(window.location.search).get('id');
        if (lessonId) {
            this.currentLesson = parseInt(lessonId);
        }
        
        // サーバーから進捗を取得して適切なタブを表示
        // 実装は省略
    }
    
    // 音声設定
    setupAudio() {
        // Web Audio API の初期化など
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('Web Audio API not supported');
        }
    }
    
    // 成功音再生
    playSuccessSound() {
        if (this.audioContext) {
            // 簡単な成功音を生成
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, this.audioContext.currentTime + 0.1);
            oscillator.frequency.setValueAtTime(1200, this.audioContext.currentTime + 0.2);
            
            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        }
    }
    
    // 発音ガイド設定
    setupPronunciationGuide() {
        const pronunciationSpans = document.querySelectorAll('.pronunciation-guide span');
        pronunciationSpans.forEach(span => {
            span.addEventListener('click', () => {
                const text = span.textContent;
                this.playTTS(text);
                
                // クリックエフェクト
                span.style.transform = 'scale(1.2)';
                span.style.background = '#4CAF50';
                span.style.color = 'white';
                
                setTimeout(() => {
                    span.style.transform = '';
                    span.style.background = '';
                    span.style.color = '';
                }, 200);
            });
        });
    }
    
    // 完了アニメーション
    animateCompletion() {
        const badgeIcon = document.querySelector('#dekitaContent .badge-icon');
        if (badgeIcon) {
            badgeIcon.style.animation = 'bounce 1s ease-in-out';
        }
    }
    
    // レッスン完了後の処理
    onLessonComplete() {
        // 次のレッスンが利用可能かチェック
        const nextLessonId = this.currentLesson + 1;
        const maxLessons = 20;
        
        if (nextLessonId <= maxLessons) {
            // 次のレッスンへ移動するか確認
            if (confirm(`Lesson ${nextLessonId} に進みますか？`)) {
                window.location.href = `lesson.php?id=${nextLessonId}`;
            } else {
                window.location.href = 'curriculum.php';
            }
        } else {
            // 全レッスン完了
            alert('おめでとうございます！全てのレッスンを完了しました！');
            window.location.href = 'curriculum.php';
        }
    }
    
    // ユーザーデータ読み込み
    loadUserData() {
        const userElement = document.getElementById('userData');
        if (userElement && userElement.textContent) {
            try {
                this.user = JSON.parse(userElement.textContent);
                console.log('ユーザーデータ読み込み完了:', this.user);
            } catch (e) {
                console.error('ユーザーデータの読み込みに失敗:', e);
            }
        }
    }
}

// DOM読み込み完了時に初期化
document.addEventListener('DOMContentLoaded', () => {
    window.lessonApp = new LessonApp();
});

// グローバル関数（後方互換性のため）
function showTab(tabName) {
    if (window.lessonApp) {
        window.lessonApp.showTab(tabName);
    }
}

function playVideo() {
    if (window.lessonApp) {
        window.lessonApp.playVideo();
    }
}

function playTTS(text) {
    if (window.lessonApp) {
        window.lessonApp.playTTS(text);
    }
}

function showBadgeModal() {
    if (window.lessonApp) {
        window.lessonApp.showBadgeModal();
    }
}

function closeBadgeModal() {
    if (window.lessonApp) {
        window.lessonApp.closeBadgeModal();
    }
}

console.log('lesson.js loaded successfully');