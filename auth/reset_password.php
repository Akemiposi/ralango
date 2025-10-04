<?php
// auth/reset_password.php
$page_title = 'パスワード再設定 - nihongonote';
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
</style>

<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../includes/header.php';

$error = '';
$success = '';
$email = '';

// パスワードリセット処理（簡易版）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'メールアドレスを入力してください';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '有効なメールアドレスを入力してください';
    } else {
        // 実際のアプリケーションではここでメール送信処理を行う
        // 今回は簡易版のため、成功メッセージのみ表示
        $success = 'パスワードリセット用のメールを送信しました。メールをご確認ください。';
        $email = '';
        
        // TODO: 実際のメール送信機能を実装する場合
        // 1. 一意のリセットトークンを生成
        // 2. データベースにトークンと有効期限を保存
        // 3. リセット用URLを含むメールを送信
    }
}
?>

<div class="auth-container lang-ja" id="authContainer">
    <!-- 言語選択 -->
    <div class="form-group" style="margin-bottom: 30px;">
        <label class="form-label" data-translate="language_label">言語 / Language *</label>
        <select class="form-select" id="languageSelect" onchange="translateResetForm()">
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

    <h1 class="auth-title" data-translate="reset_title">パスワード再設定</h1>
    
    <?php if ($error): ?>
        <div class="error-message"><?= h($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?= h($success) ?></div>
    <?php else: ?>
        <p style="text-align: center; margin-bottom: 20px; color: #666;" data-translate="reset_instructions">
            登録時のメールアドレスを入力してください。<br>
            パスワードリセット用のメールをお送りします。
        </p>
        
        <form method="POST">
            <input type="hidden" name="action" value="reset">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-group">
                <label class="form-label" data-translate="email_label">メールアドレス</label>
                <input type="email" name="email" class="form-input" value="<?= h($email) ?>" required>
            </div>
            
            <button type="submit" class="btn-primary" data-translate="reset_button">リセット用メールを送信</button>
        </form>
    <?php endif; ?>
    
    <div style="margin-top: 20px; text-align: center;">
        <p>
            <a href="login.php" id="loginLink" style="color: #4CAF50; text-decoration: none;" data-translate="back_to_login">ログインページに戻る</a>
        </p>
    </div>
</div>

<style>
.auth-container p {
    line-height: 1.6;
}
</style>

<script>
// パスワード再設定ページ翻訳辞書
const resetTranslations = {
    ja: {
        language_label: '言語 / Language *',
        reset_title: 'パスワード再設定',
        reset_instructions: '登録時のメールアドレスを入力してください。<br>パスワードリセット用のメールをお送りします。',
        email_label: 'メールアドレス',
        reset_button: 'リセット用メールを送信',
        back_to_login: 'ログインページに戻る'
    },
    en: {
        language_label: 'Language / 言語 *',
        reset_title: 'Password Reset',
        reset_instructions: 'Please enter your registered email address.<br>We will send you a password reset email.',
        email_label: 'Email',
        reset_button: 'Send Reset Email',
        back_to_login: 'Back to Login'
    },
    zh: {
        language_label: '语言 / Language *',
        reset_title: '重置密码',
        reset_instructions: '请输入您注册时的邮箱地址。<br>我们将发送密码重置邮件给您。',
        email_label: '邮箱',
        reset_button: '发送重置邮件',
        back_to_login: '返回登录页面'
    },
    ko: {
        language_label: '언어 / Language *',
        reset_title: '비밀번호 재설정',
        reset_instructions: '등록하신 이메일 주소를 입력해주세요.<br>비밀번호 재설정 이메일을 보내드리겠습니다.',
        email_label: '이메일',
        reset_button: '재설정 이메일 발송',
        back_to_login: '로그인 페이지로 돌아가기'
    },
    vi: {
        language_label: 'Ngôn ngữ / Language *',
        reset_title: 'Đặt lại mật khẩu',
        reset_instructions: 'Vui lòng nhập địa chỉ email đã đăng ký.<br>Chúng tôi sẽ gửi email đặt lại mật khẩu cho bạn.',
        email_label: 'Email',
        reset_button: 'Gửi email đặt lại',
        back_to_login: 'Quay lại trang đăng nhập'
    },
    tl: {
        language_label: 'Wika / Language *',
        reset_title: 'I-reset ang Password',
        reset_instructions: 'Pakiulit ang inyong nirehistrong email address.<br>Magpapadala kami ng password reset email sa inyo.',
        email_label: 'Email',
        reset_button: 'Magpadala ng Reset Email',
        back_to_login: 'Bumalik sa Login Page'
    },
    ne: {
        language_label: 'भाषा / Language *',
        reset_title: 'पासवर्ड रिसेट',
        reset_instructions: 'कृपया आफ्नो दर्ता गरिएको इमेल ठेगाना प्रविष्ट गर्नुहोस्।<br>हामी तपाईंलाई पासवर्ड रिसेट इमेल पठाउनेछौं।',
        email_label: 'इमेल',
        reset_button: 'रिसेट इमेल पठाउनुहोस्',
        back_to_login: 'लगिन पृष्ठमा फर्कनुहोस्'
    },
    pt: {
        language_label: 'Idioma / Language *',
        reset_title: 'Redefinir Senha',
        reset_instructions: 'Por favor, digite seu endereço de email registrado.<br>Enviaremos um email de redefinição de senha.',
        email_label: 'Email',
        reset_button: 'Enviar Email de Redefinição',
        back_to_login: 'Voltar ao Login'
    }
};

function translateResetForm() {
    const selectedLang = document.getElementById('languageSelect').value;
    const authContainer = document.getElementById('authContainer');
    const loginLink = document.getElementById('loginLink');
    
    // フォントクラスを適用
    authContainer.className = 'auth-container lang-' + selectedLang;
    
    // URLに言語パラメータを追加
    const loginUrl = new URL(loginLink.href, window.location.origin);
    loginUrl.searchParams.set('lang', selectedLang);
    loginLink.href = loginUrl.toString();
    
    // 翻訳実行
    if (resetTranslations[selectedLang]) {
        const trans = resetTranslations[selectedLang];
        
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (trans[key]) {
                if (key === 'reset_instructions') {
                    element.innerHTML = trans[key]; // HTMLを許可（<br>タグのため）
                } else {
                    element.textContent = trans[key];
                }
            }
        });
    }
}

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', function() {
    // URLパラメータから言語を取得
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('lang');
    
    if (lang && resetTranslations[lang]) {
        document.getElementById('languageSelect').value = lang;
        translateResetForm();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>