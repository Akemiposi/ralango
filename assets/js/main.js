// assets/js/main.js
// メインJavaScript - 共通機能

// アプリケーション名前空間
window.NihongonoteApp = window.NihongonoteApp || {};

// 共通ユーティリティ
NihongonoteApp.utils = {
    // HTMLエスケープ
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // クラスの切り替え
    toggleClass(element, className) {
        if (element.classList.contains(className)) {
            element.classList.remove(className);
        } else {
            element.classList.add(className);
        }
    },

    // 要素の表示/非表示
    show(element) {
        element.classList.remove('hidden');
        element.style.display = '';
    },

    hide(element) {
        element.classList.add('hidden');
    },

    // アニメーション付きスクロール
    scrollToElement(element, offset = 0) {
        const elementPosition = element.offsetTop - offset;
        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });
    },

    // ローディング状態の制御
    setLoading(button, isLoading) {
        if (isLoading) {
            button.classList.add('btn-loading');
            button.disabled = true;
        } else {
            button.classList.remove('btn-loading');
            button.disabled = false;
        }
    },

    // 通知メッセージの表示
    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // スタイル設定
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '500',
            zIndex: '10000',
            transform: 'translateX(300px)',
            transition: 'transform 0.3s ease',
            maxWidth: '300px',
            wordWrap: 'break-word'
        });

        // タイプ別の背景色
        const colors = {
            success: '#4CAF50',
            error: '#f44336',
            warning: '#ff9800',
            info: '#2196F3'
        };
        notification.style.background = colors[type] || colors.info;

        document.body.appendChild(notification);

        // アニメーションで表示
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // 自動で非表示
        setTimeout(() => {
            notification.style.transform = 'translateX(300px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
    }
};

// API通信用ヘルパー
NihongonoteApp.api = {
    // 基本的なPOSTリクエスト
    async post(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // 進捗保存
    async saveProgress(lessonId, step) {
        return await this.post('api/save_progress.php', {
            lesson_id: lessonId,
            step: step
        });
    },

    // バッジ情報取得
    async getBadges(userId) {
        try {
            const response = await fetch(`api/get_badges.php?user_id=${userId}`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching badges:', error);
            return { badges: [] };
        }
    }
};

// イベント管理
NihongonoteApp.events = {
    listeners: {},

    // イベントリスナー登録
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    },

    // イベント発火
    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    },

    // イベントリスナー削除
    off(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        }
    }
};

// ローカルストレージ管理
NihongonoteApp.storage = {
    // データ保存
    set(key, value) {
        try {
            localStorage.setItem(`nihongonote_${key}`, JSON.stringify(value));
        } catch (error) {
            console.error('Storage set error:', error);
        }
    },

    // データ取得
    get(key) {
        try {
            const item = localStorage.getItem(`nihongonote_${key}`);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Storage get error:', error);
            return null;
        }
    },

    // データ削除
    remove(key) {
        try {
            localStorage.removeItem(`nihongonote_${key}`);
        } catch (error) {
            console.error('Storage remove error:', error);
        }
    }
};

// フォーム管理
NihongonoteApp.forms = {
    // フォームバリデーション
    validate(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        const errors = [];

        inputs.forEach(input => {
            const value = input.value.trim();
            const fieldName = input.name || input.id;

            // 必須チェック
            if (!value) {
                isValid = false;
                errors.push(`${fieldName}は必須です`);
                input.classList.add('error');
            } else {
                input.classList.remove('error');
                input.classList.add('success');
            }

            // メール形式チェック
            if (input.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errors.push('有効なメールアドレスを入力してください');
                    input.classList.add('error');
                }
            }

            // パスワード長チェック
            if (input.type === 'password' && value && value.length < 6) {
                isValid = false;
                errors.push('パスワードは6文字以上で入力してください');
                input.classList.add('error');
            }
        });

        return { isValid, errors };
    },

    // パスワード強度チェック
    checkPasswordStrength(password) {
        const checks = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        const score = Object.values(checks).filter(Boolean).length;
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }
};

// 初期化処理
NihongonoteApp.init = {
    // DOM読み込み完了後の初期化
    domReady() {
        // モバイルメニュー初期化
        this.initMobileMenu();
        
        // フォーム初期化
        this.initForms();
        
        // 通知システム初期化
        this.initNotifications();

        // パフォーマンス監視
        this.initPerformanceMonitoring();

        console.log('NihongonoteApp initialized successfully');
    },

    // モバイルメニュー初期化
    initMobileMenu() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileNav = document.querySelector('.mobile-nav');

        if (mobileMenuBtn && mobileNav) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenuBtn.classList.toggle('active');
                mobileNav.classList.toggle('active');
            });

            // メニュー外クリックで閉じる
            document.addEventListener('click', (e) => {
                if (!mobileMenuBtn.contains(e.target) && !mobileNav.contains(e.target)) {
                    mobileMenuBtn.classList.remove('active');
                    mobileNav.classList.remove('active');
                }
            });
        }
    },

    // フォーム初期化
    initForms() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const validation = NihongonoteApp.forms.validate(form);
                
                if (!validation.isValid) {
                    e.preventDefault();
                    NihongonoteApp.utils.showNotification(
                        validation.errors[0], 
                        'error'
                    );
                }
            });
        });

        // パスワード強度チェック
        const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
        passwordInputs.forEach(input => {
            const strengthIndicator = input.parentNode.querySelector('.password-strength');
            
            if (strengthIndicator) {
                input.addEventListener('input', () => {
                    const strength = NihongonoteApp.forms.checkPasswordStrength(input.value);
                    const fill = strengthIndicator.querySelector('.password-strength-fill');
                    
                    strengthIndicator.className = `password-strength strength-${strength}`;
                });
            }
        });
    },

    // 通知システム初期化
    initNotifications() {
        // サーバーサイドのフラッシュメッセージを表示
        const flashMessages = document.querySelectorAll('.flash-message[data-message]');
        flashMessages.forEach(msg => {
            const type = msg.dataset.type || 'info';
            const message = msg.dataset.message;
            NihongonoteApp.utils.showNotification(message, type);
            msg.remove();
        });
    },

    // パフォーマンス監視初期化
    initPerformanceMonitoring() {
        // ページロード時間の記録
        if (window.performance && window.performance.timing) {
            const loadTime = window.performance.timing.loadEventEnd - 
                           window.performance.timing.navigationStart;
            
            if (loadTime > 3000) { // 3秒以上の場合警告
                console.warn(`Page load time: ${loadTime}ms - Consider optimizing`);
            }
        }

        // メモリ使用量の監視（開発用）
        if (window.performance && window.performance.memory) {
            setInterval(() => {
                const memory = window.performance.memory;
                if (memory.usedJSHeapSize > 50 * 1024 * 1024) { // 50MB以上
                    console.warn('High memory usage detected:', memory);
                }
            }, 30000); // 30秒ごと
        }
    }
};

// DOMContentLoaded イベントで初期化
document.addEventListener('DOMContentLoaded', () => {
    NihongonoteApp.init.domReady();
});

// エラーハンドリング
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
    // 本番環境では適切なエラー報告サービスに送信
});

// 未処理のPromise拒否をキャッチ
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    e.preventDefault(); // デフォルトのエラー表示を防ぐ
});