<?php
// account/edit.php - プロフィール編集ページ
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['user'];

// 更新されたユーザー情報を取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$current_user = $stmt->fetch();

if ($current_user) {
    $user = $current_user;
    $_SESSION['user'] = $user;
}

$page_title = 'プロフィール編集 - nihongonote';

// エラーとサクセスメッセージ
$error = '';
$success = '';

if (isset($_SESSION['flash']['error'])) {
    $error = $_SESSION['flash']['error'];
    unset($_SESSION['flash']['error']);
}

if (isset($_SESSION['flash']['success'])) {
    $success = $_SESSION['flash']['success'];
    unset($_SESSION['flash']['success']);
}

// フォームデータの準備
$form_data = [
    'parent_name' => $user['parent_name'],
    'child_name' => $user['child_name'],
    'child_nickname' => $user['child_nickname'],
    'child_gender' => $user['child_gender'],
    'child_birthdate' => $user['child_birthdate'],
    'child_country' => $user['child_country'],
    'child_grade' => $user['child_grade'],
    'school_name' => $user['school_name'],
    'family_size' => $user['family_size'],
    'family_members' => is_string($user['family_members']) ? json_decode($user['family_members'], true) : $user['family_members'],
    'other_family_member' => $user['other_family_member'] ?? '',
    'native_language' => $user['native_language'],
];

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // バリデーション
        $parent_name = trim($_POST['parent_name'] ?? '');
        $child_name = trim($_POST['child_name'] ?? '');
        $child_nickname = trim($_POST['child_nickname'] ?? '');
        $child_gender = $_POST['child_gender'] ?? '';
        $child_birthdate = $_POST['child_birthdate'] ?? '';
        $child_country = $_POST['child_country'] ?? '';
        $child_grade = (int)($_POST['child_grade'] ?? 0);
        $school_name = trim($_POST['school_name'] ?? '');
        $family_size = (int)($_POST['family_size'] ?? 0);
        $family_members = $_POST['family_members'] ?? [];
        $other_family_member = trim($_POST['other_family_member_text'] ?? '');
        $native_language = $_POST['native_language'] ?? 'ja';

        if (empty($parent_name) || empty($child_name) || empty($child_gender)) {
            throw new Exception('必須フィールドを入力してください。');
        }

        // データベース更新
        $stmt = $pdo->prepare("
            UPDATE users SET 
                parent_name = ?, child_name = ?, child_nickname = ?, 
                child_gender = ?, child_birthdate = ?, child_country = ?,
                child_grade = ?, school_name = ?, family_size = ?, 
                family_members = ?, other_family_member = ?, native_language = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $parent_name, $child_name, $child_nickname,
            $child_gender, $child_birthdate, $child_country,
            $child_grade, $school_name, $family_size,
            json_encode($family_members), $other_family_member, $native_language,
            $user['id']
        ]);

        // セッション更新
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $_SESSION['user'] = $stmt->fetch();
        
        // 言語設定をセッションに反映
        $_SESSION['dashboard_language'] = $native_language;

        $_SESSION['flash']['success'] = 'プロフィールを更新しました。';
        
        // 言語変更があった場合は、URLパラメータで言語を指定してリダイレクト
        if ($user['native_language'] !== $native_language) {
            header('Location: profile.php?lang=' . $native_language);
        } else {
            header('Location: profile.php');
        }
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$additional_css = '<style>
.edit-container {
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    padding: 20px;
    position: relative;
    background: transparent;
}

.edit-header {
    text-align: center;
    padding: 20px;
    margin-bottom: 30px;
    color: var(--primary-dark);
}

.edit-form {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.form-section {
    background: var(--card-background);
    padding: 25px;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
}

.full-width-section {
    grid-column: 1 / -1;
}

.section-title {
    color: var(--primary-dark);
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: bold;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: bold;
    color: #555;
    font-size: 14px;
    margin-bottom: 5px;
}

.form-input, .form-select {
    width: 100%;
    padding: 12px;
    border: 2px solid var(--primary-light);
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 8px;
    margin-top: 10px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-item input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.other-input {
    margin-left: 8px;
    width: 100px;
    padding: 4px 8px;
    border: 1px solid var(--primary-light);
    border-radius: 4px;
    font-size: 14px;
}

.btn-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid var(--primary-light);
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    margin: 0 auto 20px;
    color: white;
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #c33;
    margin-bottom: 20px;
}

.success-message {
    background: #efe;
    color: #383;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #383;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .full-width-section {
        grid-column: 1;
    }
    
    .edit-container {
        max-width: 100%;
        padding: 15px;
    }
    
    .checkbox-group {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        gap: 15px;
    }
}
</style>';

require_once '../includes/header.php';
?>

<div class="edit-container">
    <div class="edit-header">
        <div class="avatar">
            <?= h(getAvatarInitials($user['parent_name'])) ?>
        </div>
        <h1 data-translate="page_title">プロフィール編集</h1>
        <p data-translate="page_subtitle">アカウント情報を更新できます</p>
    </div>

    <?php if ($error): ?>
        <div class="error-message"><?= h($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="edit-form">
        <div class="form-grid">
            <!-- 基本情報セクション -->
            <div class="form-section">
                <h2 class="section-title" data-translate="basic_info_title">基本情報</h2>
                
                <div class="form-group">
                    <label class="form-label" data-translate="native_language_label">母語</label>
                    <select name="native_language" class="form-select">
                        <option value="ja" <?= $form_data['native_language'] === 'ja' ? 'selected' : '' ?>>日本語</option>
                        <option value="en" <?= $form_data['native_language'] === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="zh" <?= $form_data['native_language'] === 'zh' ? 'selected' : '' ?>>中文</option>
                        <option value="tl" <?= $form_data['native_language'] === 'tl' ? 'selected' : '' ?>>Tagalog</option>
                        <!-- <option value="ko" <?= $form_data['native_language'] === 'ko' ? 'selected' : '' ?>>한국어</option> -->
                        <!-- <option value="vi" <?= $form_data['native_language'] === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option> -->
                        <!-- <option value="th" <?= $form_data['native_language'] === 'th' ? 'selected' : '' ?>>ไทย</option> -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" data-translate="parent_name_label">保護者の方のお名前 *</label>
                    <input type="text" name="parent_name" class="form-input" value="<?= h($form_data['parent_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="child_name_label">お子様のお名前 *</label>
                    <input type="text" name="child_name" class="form-input" value="<?= h($form_data['child_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="child_nickname_label">お子様のニックネーム</label>
                    <input type="text" name="child_nickname" class="form-input" value="<?= h($form_data['child_nickname']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="child_gender_label">お子様の性別 *</label>
                    <select name="child_gender" class="form-select" required>
                        <option value="">選択してください</option>
                        <option value="boy" <?= $form_data['child_gender'] === 'boy' ? 'selected' : '' ?>>男の子</option>
                        <option value="girl" <?= $form_data['child_gender'] === 'girl' ? 'selected' : '' ?>>女の子</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="child_birthdate_label">お子様の生年月日</label>
                    <input type="date" name="child_birthdate" class="form-input" value="<?= h($form_data['child_birthdate']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="child_country_label">お子様の出身国</label>
                    <select name="child_country" class="form-select">
                        <option value="">選択してください</option>
                        <option value="japan" <?= $form_data['child_country'] === 'japan' ? 'selected' : '' ?>>日本</option>
                        <option value="usa" <?= $form_data['child_country'] === 'usa' ? 'selected' : '' ?>>アメリカ</option>
                        <option value="china" <?= $form_data['child_country'] === 'china' ? 'selected' : '' ?>>中国</option>
                        <option value="south_korea" <?= $form_data['child_country'] === 'south_korea' ? 'selected' : '' ?>>韓国</option>
                        <option value="vietnam" <?= $form_data['child_country'] === 'vietnam' ? 'selected' : '' ?>>ベトナム</option>
                        <option value="philippines" <?= $form_data['child_country'] === 'philippines' ? 'selected' : '' ?>>フィリピン</option>
                        <option value="nepal" <?= $form_data['child_country'] === 'nepal' ? 'selected' : '' ?>>ネパール</option>
                        <option value="brazil" <?= $form_data['child_country'] === 'brazil' ? 'selected' : '' ?>>ブラジル</option>
                        <option value="thailand" <?= $form_data['child_country'] === 'thailand' ? 'selected' : '' ?>>タイ</option>
                        <option value="indonesia" <?= $form_data['child_country'] === 'indonesia' ? 'selected' : '' ?>>インドネシア</option>
                    </select>
                </div>
            </div>

            <!-- 学校・家族情報セクション -->
            <div class="form-section">
                <h2 class="section-title" data-translate="school_family_info_title">学校・家族情報</h2>
                
                <div class="form-group">
                    <label class="form-label" data-translate="child_grade_label">学年</label>
                    <select name="child_grade" class="form-select">
                        <option value="">選択してください</option>
                        <option value="1" <?= $form_data['child_grade'] == 1 ? 'selected' : '' ?>>小学1年生</option>
                        <option value="2" <?= $form_data['child_grade'] == 2 ? 'selected' : '' ?>>小学2年生</option>
                        <option value="3" <?= $form_data['child_grade'] == 3 ? 'selected' : '' ?>>小学3年生</option>
                        <option value="4" <?= $form_data['child_grade'] == 4 ? 'selected' : '' ?>>小学4年生</option>
                        <option value="5" <?= $form_data['child_grade'] == 5 ? 'selected' : '' ?>>小学5年生</option>
                        <option value="6" <?= $form_data['child_grade'] == 6 ? 'selected' : '' ?>>小学6年生</option>
                        <option value="7" <?= $form_data['child_grade'] == 7 ? 'selected' : '' ?>>中学1年生</option>
                        <option value="8" <?= $form_data['child_grade'] == 8 ? 'selected' : '' ?>>中学2年生</option>
                        <option value="9" <?= $form_data['child_grade'] == 9 ? 'selected' : '' ?>>中学3年生</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="school_name_label">学校名</label>
                    <input type="text" name="school_name" class="form-input" value="<?= h($form_data['school_name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" data-translate="family_size_label">家族の人数</label>
                    <select name="family_size" class="form-select">
                        <option value="">選択してください</option>
                        <?php for ($i = 2; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= $form_data['family_size'] == $i ? 'selected' : '' ?>><?= $i ?>人</option>
                        <?php endfor; ?>
                    </select>
                </div>

            </div>

            <!-- 家族構成セクション -->
            <div class="form-section full-width-section">
                <h2 class="section-title" data-translate="family_composition_title">家族構成</h2>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="father" id="father" 
                               <?= in_array('father', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="father" data-translate="father_label">父</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="mother" id="mother"
                               <?= in_array('mother', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="mother" data-translate="mother_label">母</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="older_brother" id="older_brother"
                               <?= in_array('older_brother', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="older_brother" data-translate="older_brother_label">兄</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="older_sister" id="older_sister"
                               <?= in_array('older_sister', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="older_sister" data-translate="older_sister_label">姉</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="younger_brother" id="younger_brother"
                               <?= in_array('younger_brother', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="younger_brother" data-translate="younger_brother_label">弟</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="younger_sister" id="younger_sister"
                               <?= in_array('younger_sister', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="younger_sister" data-translate="younger_sister_label">妹</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="grandfather" id="grandfather"
                               <?= in_array('grandfather', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="grandfather" data-translate="grandfather_label">祖父</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="family_members[]" value="grandmother" id="grandmother"
                               <?= in_array('grandmother', $form_data['family_members'] ?: []) ? 'checked' : '' ?>>
                        <label for="grandmother" data-translate="grandmother_label">祖母</label>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <div class="checkbox-item">
                        <input type="checkbox" name="other_family_member" id="other_family_member"
                               <?= !empty($form_data['other_family_member']) ? 'checked' : '' ?>>
                        <label for="other_family_member" data-translate="other_family_label">その他（<input type="text" name="other_family_member_text" class="other-input" placeholder="続柄" value="<?= h($form_data['other_family_member']) ?>">）</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="btn-group">
            <button type="submit" class="btn btn-primary" data-translate="save_button">変更を保存</button>
            <a href="profile.php" class="btn btn-secondary" data-translate="cancel_button">キャンセル</a>
        </div>
    </form>
</div>

<script>
// プロフィール編集翻訳辞書
const editTranslations = {
    ja: {
        page_title: 'プロフィール編集',
        page_subtitle: 'アカウント情報を編集してください',
        basic_info_title: '基本情報',
        parent_name_label: '保護者の方のお名前',
        child_name_label: 'お子様のお名前',
        child_nickname_label: 'お子様のニックネーム',
        child_gender_label: 'お子様の性別',
        child_birthdate_label: 'お子様の生年月日',
        child_country_label: 'お子様の出身国',
        school_family_info_title: '学校・家族情報',
        child_grade_label: '学年',
        school_name_label: '学校名',
        family_size_label: '家族の人数',
        native_language_label: '母語',
        family_composition_title: '家族構成',
        father_label: '父',
        mother_label: '母',
        older_brother_label: '兄',
        older_sister_label: '姉',
        younger_brother_label: '弟',
        younger_sister_label: '妹',
        grandfather_label: '祖父',
        grandmother_label: '祖母',
        other_family_label: 'その他',
        save_button: '変更を保存',
        cancel_button: 'キャンセル',
        select_please: '選択してください',
        boy: '男の子',
        girl: '女の子',
        elementary_1: '小学1年生',
        elementary_2: '小学2年生',
        elementary_3: '小学3年生',
        elementary_4: '小学4年生',
        elementary_5: '小学5年生',
        elementary_6: '小学6年生',
        junior_high_1: '中学1年生',
        junior_high_2: '中学2年生',
        junior_high_3: '中学3年生',
        people_count: '人'
    },
    en: {
        page_title: 'Edit Profile',
        page_subtitle: 'Edit your account information',
        basic_info_title: 'Basic Information',
        parent_name_label: 'Parent/Guardian Name',
        child_name_label: 'Child\'s Name',
        child_nickname_label: 'Child\'s Nickname',
        child_gender_label: 'Child\'s Gender',
        child_birthdate_label: 'Child\'s Date of Birth',
        child_country_label: 'Child\'s Country of Origin',
        school_family_info_title: 'School & Family Information',
        child_grade_label: 'Grade',
        school_name_label: 'School Name',
        family_size_label: 'Family Size',
        native_language_label: 'Native Language',
        family_composition_title: 'Family Composition',
        father_label: 'Father',
        mother_label: 'Mother',
        older_brother_label: 'Older Brother',
        older_sister_label: 'Older Sister',
        younger_brother_label: 'Younger Brother',
        younger_sister_label: 'Younger Sister',
        grandfather_label: 'Grandfather',
        grandmother_label: 'Grandmother',
        other_family_label: 'Other',
        save_button: 'Save Changes',
        cancel_button: 'Cancel',
        select_please: 'Please select',
        boy: 'Boy',
        girl: 'Girl',
        elementary_1: 'Elementary Grade 1',
        elementary_2: 'Elementary Grade 2',
        elementary_3: 'Elementary Grade 3',
        elementary_4: 'Elementary Grade 4',
        elementary_5: 'Elementary Grade 5',
        elementary_6: 'Elementary Grade 6',
        junior_high_1: 'Junior High Grade 1',
        junior_high_2: 'Junior High Grade 2',
        junior_high_3: 'Junior High Grade 3',
        people_count: ' people'
    },
    zh: {
        page_title: '编辑资料',
        page_subtitle: '编辑您的账户信息',
        basic_info_title: '基本信息',
        parent_name_label: '监护人姓名',
        child_name_label: '孩子姓名',
        child_nickname_label: '孩子昵称',
        child_gender_label: '孩子性别',
        child_birthdate_label: '孩子生日',
        child_country_label: '孩子出生国',
        school_family_info_title: '学校和家庭信息',
        child_grade_label: '年级',
        school_name_label: '学校名称',
        family_size_label: '家庭人数',
        native_language_label: '母语',
        family_composition_title: '家庭成员',
        father_label: '父亲',
        mother_label: '母亲',
        older_brother_label: '哥哥',
        older_sister_label: '姐姐',
        younger_brother_label: '弟弟',
        younger_sister_label: '妹妹',
        grandfather_label: '祖父',
        grandmother_label: '祖母',
        other_family_label: '其他',
        save_button: '保存更改',
        cancel_button: '取消',
        select_please: '请选择',
        boy: '男孩',
        girl: '女孩',
        elementary_1: '小学1年级',
        elementary_2: '小学2年级',
        elementary_3: '小学3年级',
        elementary_4: '小学4年级',
        elementary_5: '小学5年级',
        elementary_6: '小学6年级',
        junior_high_1: '中学1年级',
        junior_high_2: '中学2年级',
        junior_high_3: '中学3年级',
        people_count: '人'
    },
    tl: {
        page_title: 'I-edit ang Profile',
        page_subtitle: 'I-edit ang inyong account information',
        basic_info_title: 'Basic na Impormasyon',
        parent_name_label: 'Pangalan ng Magulang/Guardian',
        child_name_label: 'Pangalan ng Anak',
        child_nickname_label: 'Palayaw ng Anak',
        child_gender_label: 'Kasarian ng Anak',
        child_birthdate_label: 'Kaarawan ng Anak',
        child_country_label: 'Bansang Pinagmulan ng Anak',
        school_family_info_title: 'Impormasyon ng Paaralan at Pamilya',
        child_grade_label: 'Grade',
        school_name_label: 'Pangalan ng Paaralan',
        family_size_label: 'Bilang ng Pamilya',
        native_language_label: 'Katutubong Wika',
        family_composition_title: 'Komposisyon ng Pamilya',
        father_label: 'Ama',
        mother_label: 'Ina',
        older_brother_label: 'Kuya',
        older_sister_label: 'Ate',
        younger_brother_label: 'Bunso (lalaki)',
        younger_sister_label: 'Bunso (babae)',
        grandfather_label: 'Lolo',
        grandmother_label: 'Lola',
        other_family_label: 'Iba pa',
        save_button: 'I-save ang mga Pagbabago',
        cancel_button: 'Cancel',
        select_please: 'Pumili',
        boy: 'Lalaki',
        girl: 'Babae',
        elementary_1: 'Grade 1',
        elementary_2: 'Grade 2',
        elementary_3: 'Grade 3',
        elementary_4: 'Grade 4',
        elementary_5: 'Grade 5',
        elementary_6: 'Grade 6',
        junior_high_1: 'Grade 7',
        junior_high_2: 'Grade 8',
        junior_high_3: 'Grade 9',
        people_count: ' tao'
    }/*,
    ko: {
        page_title: '프로필 편집',
        page_subtitle: '계정 정보를 편집하세요',
        basic_info_title: '기본 정보',
        parent_name_label: '보호자 이름',
        child_name_label: '자녀 이름',
        child_nickname_label: '자녀 별명',
        child_gender_label: '자녀 성별',
        child_birthdate_label: '자녀 생년월일',
        child_country_label: '자녀 출신국',
        school_family_info_title: '학교 및 가족 정보',
        child_grade_label: '학년',
        school_name_label: '학교명',
        family_size_label: '가족 수',
        native_language_label: '모국어',
        family_composition_title: '가족 구성',
        father_label: '아버지',
        mother_label: '어머니',
        older_brother_label: '형',
        older_sister_label: '누나',
        younger_brother_label: '남동생',
        younger_sister_label: '여동생',
        grandfather_label: '할아버지',
        grandmother_label: '할머니',
        other_family_label: '기타',
        save_button: '변경사항 저장',
        cancel_button: '취소',
        select_please: '선택하세요',
        boy: '남자',
        girl: '여자',
        elementary_1: '초등학교 1학년',
        elementary_2: '초등학교 2학년',
        elementary_3: '초등학교 3학년',
        elementary_4: '초등학교 4학년',
        elementary_5: '초등학교 5학년',
        elementary_6: '초등학교 6학년',
        junior_high_1: '중학교 1학년',
        junior_high_2: '중학교 2학년',
        junior_high_3: '중학교 3학년',
        people_count: '명'
    },
    tl: {
        page_title: 'I-edit ang Profile',
        page_subtitle: 'I-edit ang inyong account information',
        basic_info_title: 'Basic na Impormasyon',
        parent_name_label: 'Pangalan ng Magulang/Guardian',
        child_name_label: 'Pangalan ng Anak',
        child_nickname_label: 'Palayaw ng Anak',
        child_gender_label: 'Kasarian ng Anak',
        child_birthdate_label: 'Kaarawan ng Anak',
        child_country_label: 'Bansang Pinagmulan ng Anak',
        school_family_info_title: 'Impormasyon ng Paaralan at Pamilya',
        child_grade_label: 'Grade',
        school_name_label: 'Pangalan ng Paaralan',
        family_size_label: 'Bilang ng Pamilya',
        native_language_label: 'Katutubong Wika',
        family_composition_title: 'Komposisyon ng Pamilya',
        father_label: 'Ama',
        mother_label: 'Ina',
        older_brother_label: 'Kuya',
        older_sister_label: 'Ate',
        younger_brother_label: 'Bunso (lalaki)',
        younger_sister_label: 'Bunso (babae)',
        grandfather_label: 'Lolo',
        grandmother_label: 'Lola',
        other_family_label: 'Iba pa',
        save_button: 'I-save ang mga Pagbabago',
        cancel_button: 'Cancel',
        select_please: 'Pumili',
        boy: 'Lalaki',
        girl: 'Babae',
        elementary_1: 'Grade 1',
        elementary_2: 'Grade 2',
        elementary_3: 'Grade 3',
        elementary_4: 'Grade 4',
        elementary_5: 'Grade 5',
        elementary_6: 'Grade 6',
        junior_high_1: 'Grade 7',
        junior_high_2: 'Grade 8',
        junior_high_3: 'Grade 9',
        people_count: ' tao'
    },
    vi: {
        page_title: 'Chỉnh sửa Hồ sơ',
        page_subtitle: 'Chỉnh sửa thông tin tài khoản của bạn',
        basic_info_title: 'Thông tin Cơ bản',
        parent_name_label: 'Tên Phụ huynh/Người giám hộ',
        child_name_label: 'Tên Con',
        child_nickname_label: 'Biệt danh của Con',
        child_gender_label: 'Giới tính của Con',
        child_birthdate_label: 'Ngày sinh của Con',
        child_country_label: 'Quốc gia gốc của Con',
        school_family_info_title: 'Thông tin Trường học & Gia đình',
        child_grade_label: 'Lớp',
        school_name_label: 'Tên Trường',
        family_size_label: 'Quy mô Gia đình',
        native_language_label: 'Ngôn ngữ Mẹ đẻ',
        family_composition_title: 'Thành phần Gia đình',
        father_label: 'Cha',
        mother_label: 'Mẹ',
        older_brother_label: 'Anh trai',
        older_sister_label: 'Chị gái',
        younger_brother_label: 'Em trai',
        younger_sister_label: 'Em gái',
        grandfather_label: 'Ông',
        grandmother_label: 'Bà',
        other_family_label: 'Khác',
        save_button: 'Lưu Thay đổi',
        cancel_button: 'Hủy',
        select_please: 'Vui lòng chọn',
        boy: 'Con trai',
        girl: 'Con gái',
        elementary_1: 'Lớp 1',
        elementary_2: 'Lớp 2',
        elementary_3: 'Lớp 3',
        elementary_4: 'Lớp 4',
        elementary_5: 'Lớp 5',
        elementary_6: 'Lớp 6',
        junior_high_1: 'Lớp 7',
        junior_high_2: 'Lớp 8',
        junior_high_3: 'Lớp 9',
        people_count: ' người'
    },
    th: {
        page_title: 'แก้ไขโปรไฟล์',
        page_subtitle: 'แก้ไขข้อมูลบัญชีของคุณ',
        basic_info_title: 'ข้อมูลพื้นฐาน',
        parent_name_label: 'ชื่อผู้ปกครอง',
        child_name_label: 'ชื่อลูก',
        child_nickname_label: 'ชื่อเล่นของลูก',
        child_gender_label: 'เพศของลูก',
        child_birthdate_label: 'วันเกิดของลูก',
        child_country_label: 'ประเทศต้นกำเนิดของลูก',
        school_family_info_title: 'ข้อมูลโรงเรียนและครอบครัว',
        child_grade_label: 'ชั้นเรียน',
        school_name_label: 'ชื่อโรงเรียน',
        family_size_label: 'จำนวนคนในครอบครัว',
        native_language_label: 'ภาษาแม่',
        family_composition_title: 'องค์ประกอบครอบครัว',
        father_label: 'พ่อ',
        mother_label: 'แม่',
        older_brother_label: 'พี่ชาย',
        older_sister_label: 'พี่สาว',
        younger_brother_label: 'น้องชาย',
        younger_sister_label: 'น้องสาว',
        grandfather_label: 'ปู่/ตา',
        grandmother_label: 'ย่า/ยาย',
        other_family_label: 'อื่นๆ',
        save_button: 'บันทึกการเปลี่ยนแปลง',
        cancel_button: 'ยกเลิก',
        select_please: 'กรุณาเลือก',
        boy: 'เด็กชาย',
        girl: 'เด็กหญิง',
        elementary_1: 'ประถมศึกษาปีที่ 1',
        elementary_2: 'ประถมศึกษาปีที่ 2',
        elementary_3: 'ประถมศึกษาปีที่ 3',
        elementary_4: 'ประถมศึกษาปีที่ 4',
        elementary_5: 'ประถมศึกษาปีที่ 5',
        elementary_6: 'ประถมศึกษาปีที่ 6',
        junior_high_1: 'มัธยมศึกษาปีที่ 1',
        junior_high_2: 'มัธยมศึกษาปีที่ 2',
        junior_high_3: 'มัธยมศึกษาปีที่ 3',
        people_count: ' คน'
    }*/
};

// 翻訳関数
function translateEditPage(lang) {
    if (editTranslations[lang]) {
        const trans = editTranslations[lang];
        
        // data-translate属性のある要素を翻訳
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (trans[key]) {
                element.textContent = trans[key];
            }
        });
        
        // セレクトボックスのオプションも翻訳
        translateSelectOptions(lang, trans);
    }
}

// セレクトボックスの翻訳
function translateSelectOptions(lang, trans) {
    // 性別のオプション
    const genderSelect = document.querySelector('select[name="child_gender"]');
    if (genderSelect) {
        genderSelect.options[1].textContent = trans.boy;
        genderSelect.options[2].textContent = trans.girl;
    }
    
    // 学年のオプション
    const gradeSelect = document.querySelector('select[name="child_grade"]');
    if (gradeSelect) {
        gradeSelect.options[0].textContent = trans.select_please;
        gradeSelect.options[1].textContent = trans.elementary_1;
        gradeSelect.options[2].textContent = trans.elementary_2;
        gradeSelect.options[3].textContent = trans.elementary_3;
        gradeSelect.options[4].textContent = trans.elementary_4;
        gradeSelect.options[5].textContent = trans.elementary_5;
        gradeSelect.options[6].textContent = trans.elementary_6;
        gradeSelect.options[7].textContent = trans.junior_high_1;
        gradeSelect.options[8].textContent = trans.junior_high_2;
        gradeSelect.options[9].textContent = trans.junior_high_3;
    }
    
    // 家族の人数のオプション
    const familySizeSelect = document.querySelector('select[name="family_size"]');
    if (familySizeSelect) {
        familySizeSelect.options[0].textContent = trans.select_please;
        for (let i = 1; i < familySizeSelect.options.length; i++) {
            const num = i + 1;
            if (lang === 'en') {
                familySizeSelect.options[i].textContent = num + trans.people_count;
            } else {
                familySizeSelect.options[i].textContent = num + trans.people_count;
            }
        }
    }
}

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', function() {
    // ユーザーの登録言語で初期化
    const userNativeLang = '<?= $user['native_language'] ?? 'ja' ?>';
    translateEditPage(userNativeLang);
    
    // 母語選択時に翻訳を切り替え
    const nativeLanguageSelect = document.querySelector('select[name="native_language"]');
    if (nativeLanguageSelect) {
        nativeLanguageSelect.addEventListener('change', function() {
            const selectedLang = this.value;
            translateEditPage(selectedLang);
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>