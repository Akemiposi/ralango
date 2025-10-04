# RALA N GO! - 日本語学習アプリ

多言語対応の日本語学習アプリケーションです。子供向けに設計され、インタラクティブなレッスンとゲームを提供します。

## 特徴

- 🌏 多言語対応（日本語、英語、中国語、タガログ語など）
- 📚 段階的なレッスンシステム
- 🎮 学習ゲーム（じゃんけん、かなカード）
- 🏆 バッジシステムによる進捗管理
- 📊 学習進捗の可視化
- 🔊 音声読み上げ機能

## セットアップ

### 必要な環境

- PHP 7.4以上
- MySQL 5.7以上
- Webサーバー（Apache/Nginx）

### インストール手順

1. **リポジトリのクローン**
```bash
git clone [repository-url]
cd ralango
```

2. **データベースの設定**
```bash
# MySQLでデータベースを作成
mysql -u root -p
CREATE DATABASE nihongonote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **環境変数の設定**
プロジェクトルートに `.env` ファイルを作成：
```env
# データベース設定
DB_HOST=localhost
DB_NAME=nihongonote
DB_USER=your_username
DB_PASSWORD=your_password

# Xserver環境用（本番環境）
XSERVER_DB_HOST=your_server.xserver.jp
XSERVER_DB_NAME=your_account_nihongonote
XSERVER_DB_USER=your_account
XSERVER_DB_PASSWORD=your_password

# API設定
GOOGLE_TTS_API_KEY=your_google_tts_api_key
GEMINI_API_KEY=your_gemini_api_key
```

4. **ディレクトリ権限の設定**
```bash
chmod 755 assets/audio/cache/
chmod 755 assets/images/badge/generated/
chmod 755 assets/videos/
```

5. **動画ファイルの配置**
動画ファイル（*.mp4）は大容量のため、GitHubリポジトリには含まれていません。
以下のファイルを `assets/videos/` ディレクトリに配置してください：
- Lesson1_1A.mp4
- Lesson1_1B.mp4
- Lesson1_2A.mp4
- Lesson1_2B.mp4
- Lesson1_3A.mp4
- Lesson1_3B.mp4

### データベーススキーマ

初回セットアップ時は、以下のSQLファイルを実行してください：
- `config/create_translation_cache_table.sql`
- `config/update_users_table.sql`
- `config/add_user_language.sql`

## 使用技術

- **バックエンド**: PHP, MySQL
- **フロントエンド**: HTML5, CSS3, JavaScript
- **API**: Google Text-to-Speech, Google Gemini
- **認証**: セッションベース認証

## ディレクトリ構造

```
ralango/
├── assets/           # 静的ファイル
│   ├── css/         # スタイルシート
│   ├── js/          # JavaScript
│   ├── images/      # 画像ファイル
│   └── audio/       # 音声ファイル
├── auth/            # 認証関連
├── config/          # 設定ファイル
├── includes/        # 共通ファイル
├── lessons/         # レッスン機能
├── progress/        # 進捗管理
├── games/           # ゲーム機能
└── admin/           # 管理者機能
```

## セキュリティ

- 認証情報は環境変数で管理
- CSRFトークンによる攻撃防止
- SQLインジェクション対策（PDO使用）
- XSS対策（エスケープ処理）

## ライセンス

このプロジェクトは教育目的で作成されています。

## 貢献

バグ報告や機能リクエストは、GitHubのIssueでお知らせください。

## 注意事項

- APIキーやデータベース認証情報を直接コードにコミットしないでください
- 本番環境では必ずHTTPS接続を使用してください
- 定期的にバックアップを取得してください