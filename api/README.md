# nihongonote API Documentation

## API エンドポイント

### Base URL
```
https://yourdomain.com/api/v1/
```

## エンドポイント一覧

### 1. ヘルスチェック
```
GET /api/v1/health
```

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "status": "ok",
    "version": "1.0.0",
    "server_time": "2024-01-01T12:00:00+00:00"
  },
  "message": "API is running",
  "timestamp": "2024-01-01T12:00:00+00:00"
}
```

### 2. レッスン関連

#### 全レッスン取得
```
GET /api/v1/lessons
```

#### 特定レッスン取得
```
GET /api/v1/lessons/{lesson_id}/{sub_id}
```

#### レッスン完了
```
POST /api/v1/lessons/complete
```

**リクエストボディ:**
```json
{
  "user_id": 1,
  "lesson_id": 1,
  "sub_id": 1
}
```

### 3. 進捗関連

#### 進捗取得
```
GET /api/v1/progress?user_id=1
```

#### 進捗保存
```
POST /api/v1/progress
```

**リクエストボディ:**
```json
{
  "user_id": 1,
  "lesson_id": 1,
  "step": "miru"
}
```

## 認証

現在は認証なしでアクセス可能です。本番環境では API Key や JWT トークンの実装を検討してください。

## エラーレスポンス

```json
{
  "success": false,
  "error": "Error message",
  "timestamp": "2024-01-01T12:00:00+00:00",
  "details": {
    "error_code": 500,
    "file": "index.php",
    "line": 45
  }
}
```

## レスポンス形式

全てのAPIレスポンスは以下の形式に従います：

**成功:**
```json
{
  "success": true,
  "data": {...},
  "message": "Success message",
  "timestamp": "2024-01-01T12:00:00+00:00"
}
```

**エラー:**
```json
{
  "success": false,
  "error": "Error message",
  "timestamp": "2024-01-01T12:00:00+00:00"
}
```

## 使用例

### JavaScript (Fetch API)
```javascript
// レッスンデータ取得
fetch('/api/v1/lessons/1/1')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log(data.data);
    } else {
      console.error(data.error);
    }
  });

// 進捗保存
fetch('/api/v1/progress', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    user_id: 1,
    lesson_id: 1,
    step: 'miru'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### cURL
```bash
# ヘルスチェック
curl -X GET "https://yourdomain.com/api/v1/health"

# レッスンデータ取得
curl -X GET "https://yourdomain.com/api/v1/lessons/1/1"

# 進捗保存
curl -X POST "https://yourdomain.com/api/v1/progress" \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"lesson_id":1,"step":"miru"}'
```