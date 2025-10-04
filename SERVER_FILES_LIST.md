# 🚀 サーバーアップロード用ファイル一覧

## 📋 必須ファイル（public_html/に配置）

### 🏠 ルートファイル
```
index.php
README.md
.htaccess
```

### 📁 ディレクトリ構造

#### 🔐 about_school/
```
about_school/index.php
```

#### 👤 account/
```
account/edit.php
account/profile.php
account/profile_working.php
```

#### 🛠️ admin/
```
admin/badge_generator.php
admin/index.php
admin/lessons.php
admin/translations.php
admin/users.php
admin/videos.php
```

#### 🔌 api/
```
api/README.md
api/earn_badge.php
api/get_badges.php
api/save_progress.php
api/set_language.php
api/translate.php
api/v1/.htaccess
api/v1/index.php
api/v1/endpoints/lessons.php
api/v1/endpoints/progress.php
```

#### 🎨 assets/
```
assets/css/auth.css
assets/css/base.css
assets/css/header.css
assets/css/lesson.css
assets/css/style.css

assets/js/app.js
assets/js/lesson.js
assets/js/main.js

assets/images/bg_bottom.png
assets/images/bg_top.png
assets/images/baroon_left.png
assets/images/baroon_right.png
assets/images/ralango_logo_en.png
assets/images/ralango_logo_jp.png
assets/images/ralango_logo_zh.png

assets/images/badge/badge1.png
assets/images/badge/badge2.png
assets/images/badge/badge3.png
assets/images/badge/BL1_1.png
assets/images/badge/BL1_2.png
assets/images/badge/BL1_3.png

assets/images/badges/badge_L1_1.png
assets/images/badges/badge_L1_2.png
assets/images/badges/badge_L1_3.png

assets/images/badge/generated/ (全60個のバッジ画像)
assets/images/icons/ (全22個のアイコン)

assets/videos/Lesson1_1A.mp4
assets/videos/Lesson1_1B.mp4
assets/videos/Lesson1_2A.mp4
assets/videos/Lesson1_2B.mp4
assets/videos/Lesson1_3A.mp4
assets/videos/Lesson1_3B.mp4
```

#### 🔑 auth/
```
auth/login.php
auth/logout.php
auth/register.php
auth/reset_password.php
```

#### ⚙️ config/
```
config/api_config.php
config/database.php
config/session_config.php
```

#### 🎮 games/
```
games/index.php

games/janken/index.php
games/janken/index.html
games/janken/janken_rich.html
games/janken/css/index.css
games/janken/css/sample.css
games/janken/js/jquery-2.1.3.min.js
games/janken/img_janken/ (全15個の画像)

games/kanacard/index.php
games/kanacard/index.html
games/kanacard/css/reset.css
games/kanacard/css/style.css
games/kanacard/js/index.js
games/kanacard/js/jquery-2.1.3.min.js
games/kanacard/img/img_e/ (全40個の画像)
games/kanacard/img/img_kana/ (全9個の画像)
```

#### 📚 includes/
```
includes/footer.php
includes/functions.php
includes/GeminiTranslator.php
includes/GoogleTTS.php
includes/header.php
includes/language.php
includes/language_switcher.php
includes/logo.php
includes/translation.php
```

#### 📖 lessons/
```
lessons/badge_tree.php
lessons/curriculum.php
lessons/lesson.php
```

#### 📊 progress/
```
progress/admin_progress.php
progress/user_list.php
progress/user_progress.php
```

## 🔒 別途手動作成が必要

### .env (ルートディレクトリ)
```env
# データベース設定
DB_HOST=localhost
DB_NAME=glassposi_nihongonote
DB_USER=glassposi_akemi
DB_PASSWORD=あなたのDBパスワード

# Google APIs
GOOGLE_TTS_API_KEY=AIzaSyCQu3osPBqwPwYMCoVH7iL56RO-Xq5Ko-0
GEMINI_API_KEY=AIzaSyCQu3osPBqwPwYMCoVH7iL56RO-Xq5Ko-0

# アプリケーション設定
APP_ENV=production
APP_DEBUG=false
```

## 📤 アップロード除外ファイル
- `.env` → 手動作成
- `.gitignore` → 不要
- `sitemap.md` → 不要
- `.git/` → 不要
- `.claude/` → 不要

## 📊 ファイル数
- **総ファイル数**: 約200個
- **PHPファイル**: 35個
- **画像ファイル**: 130個
- **その他**: 35個