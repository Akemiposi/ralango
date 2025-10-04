<?php
// account/profile.php - プロフィールページ
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

// エラーと成功メッセージの初期化
$error = '';
$success = '';

// 性別に応じた色設定
$gender = $user['child_gender'] ?? 'boy';
$color_scheme = $gender === 'girl' ? 'pink' : 'blue';

$page_title = 'アカウント情報 - nihongonote';

// 国名の母国語対応
$country_names = [
    'ja' => [
        'japan' => '日本',
        'usa' => 'アメリカ',
        'china' => '中国',
        'south_korea' => '韓国',
        'vietnam' => 'ベトナム',
        'philippines' => 'フィリピン',
        'nepal' => 'ネパール',
        'brazil' => 'ブラジル',
        'thailand' => 'タイ',
        'indonesia' => 'インドネシア'
    ],
    'en' => [
        'japan' => 'Japan',
        'usa' => 'United States',
        'china' => 'China',
        'south_korea' => 'South Korea',
        'vietnam' => 'Vietnam',
        'philippines' => 'Philippines',
        'nepal' => 'Nepal',
        'brazil' => 'Brazil',
        'thailand' => 'Thailand',
        'indonesia' => 'Indonesia'
    ],
    'zh' => [
        'japan' => '日本',
        'usa' => '美国',
        'china' => '中国',
        'south_korea' => '韩国',
        'vietnam' => '越南',
        'philippines' => '菲律宾',
        'nepal' => '尼泊尔',
        'brazil' => '巴西',
        'thailand' => '泰国',
        'indonesia' => '印度尼西亚'
    ]
];

function getCountryName($country_code, $language, $country_names) {
    return $country_names[$language][$country_code] ?? $country_code;
}

// 更新されたユーザー情報を取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$current_user = $stmt->fetch();

if ($current_user) {
    $user = $current_user;
    $_SESSION['user'] = $user; // セッションも更新
}

// 家族構成を配列として処理
$family_members = [];
if (!empty($user['family_members'])) {
    if (is_string($user['family_members'])) {
        $family_members = json_decode($user['family_members'], true) ?: [];
    } else {
        $family_members = $user['family_members'];
    }
}

// 家族構成の翻訳マップ
$family_labels = [
    'father' => '父',
    'mother' => '母',
    'older_brother' => '兄',
    'older_sister' => '姉',
    'younger_brother' => '弟',
    'younger_sister' => '妹',
    'grandfather' => '祖父',
    'grandmother' => '祖母'
];

// 追加CSS設定
$additional_css = '<style>
/* 背景画像設定 */
body {
    background-image: url("../assets/images/bg_top.png"), url("../assets/images/bg_bottom.png");
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
}

.account-container {
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    padding: 20px;
    position: relative;
    background: transparent;
}

.account-header {
    text-align: center;
    padding: 20px;
    margin-bottom: 30px;
    color: var(--primary-dark);
}

.account-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.account-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.full-width-section {
    grid-column: 1 / -1;
}

.section-title {
    color: #333;
    margin-bottom: 20px;
    font-size: 20px;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-item {
    padding: 15px;
    background: var(--card-background);
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.info-label {
    font-weight: bold;
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
    font-size: 16px;
}

.family-members {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.family-member {
    background: var(--primary-light);
    color: var(--primary-dark);
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 14px;
}

.btn-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    display: inline-block;
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

@media (max-width: 768px) {
    
    .account-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .full-width-section {
        grid-column: 1;
    }
    
    .account-container {
        max-width: 100%;
        padding: 15px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 10px;
    }
}
</style>';

require_once '../includes/header.php';
?>

<div class="account-container lang-ja" id="accountContainer">

    <!-- ヘッダー -->
    <div class="account-header">
        <div class="avatar">
            <?= h(getAvatarInitials($user['parent_name'])) ?>
        </div>
        <h1 data-translate="welcome_message"><?= h($user['child_name']) ?>ちゃんのアカウント</h1>
        <p data-translate="account_subtitle">アカウント情報の確認と編集ができます</p>
    </div>

    <?php if ($error): ?>
        <div class="error-message" style="margin-bottom: 20px;"><?= h($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message" style="margin-bottom: 20px;"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- 2カラムレイアウト -->
    <div class="account-content">
        <!-- 左カラム：基本情報 -->
        <div class="account-section">
        <h2 class="section-title" data-translate="basic_info_title">基本情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="parent_name_label">保護者の方のお名前</div>
                <div class="info-value"><?= h($user['parent_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_name_label">お子様のお名前</div>
                <div class="info-value"><?= h($user['child_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_nickname_label">お子様のニックネーム</div>
                <div class="info-value" id="childNickname"><?= h($user['child_nickname'] ?: '未設定') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_gender_label">お子様の性別</div>
                <div class="info-value" id="childGender"><?= $user['child_gender'] === 'boy' ? '男の子' : '女の子' ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_birthdate_label">お子様の生年月日</div>
                <div class="info-value" id="childBirthdate"><?= h($user['child_birthdate'] ?: '未設定') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="child_country_label">お子様の出身国</div>
                <div class="info-value" id="childCountry">
                    <?php 
                    if ($user['child_country']) {
                        $native_lang = $user['native_language'] ?? 'ja';
                        echo h(getCountryName($user['child_country'], $native_lang, $country_names));
                    } else {
                        echo '未設定';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

        <!-- 右カラム：学校情報 -->
        <div class="account-section">
        <h2 class="section-title" data-translate="school_info_title">学校情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="child_grade_label">学年</div>
                <div class="info-value" id="childGrade">
                    <?php if ($user['child_grade']): ?>
                        <?= $user['child_grade'] <= 6 ? '小学' . $user['child_grade'] . '年生' : '中学' . ($user['child_grade'] - 6) . '年生' ?>
                    <?php else: ?>
                        未設定
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="school_name_label">学校名</div>
                <div class="info-value" id="schoolName"><?= h($user['school_name'] ?: '未設定') ?></div>
            </div>
        </div>
    </div>

        <!-- 家族情報セクション -->
        <div class="account-section">
        <h2 class="section-title" data-translate="family_info_title">家族情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="family_members_label">家族構成</div>
                <div class="info-value" id="familyMembers">
                    <?php if (!empty($family_members) || !empty($user['other_family_member'])): ?>
                        <div class="family-members">
                            <?php if (!empty($family_members)): ?>
                                <?php foreach ($family_members as $member): ?>
                                    <span class="family-member"><?= h($family_labels[$member] ?? $member) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($user['other_family_member'])): ?>
                                <span class="family-member"><?= h($user['other_family_member']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        未設定
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="family_size_label">家族の人数</div>
                <div class="info-value" id="familySize"><?= h($user['family_size'] ? $user['family_size'] . '人' : '未設定') ?></div>
            </div>
        </div>
    </div>

        <!-- アカウント情報セクション -->
        <div class="account-section">
        <h2 class="section-title" data-translate="account_info_title">アカウント情報</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label" data-translate="email_label">メールアドレス</div>
                <div class="info-value"><?= h($user['email']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="native_language_label">母語</div>
                <div class="info-value"><?= getLanguageName($user['native_language']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label" data-translate="registration_date_label">登録日</div>
                <div class="info-value" id="registrationDate"><?= date('Y年m月d日', strtotime($user['created_at'])) ?></div>
            </div>
        </div>
    </div>
    </div>

    <!-- アクションボタン - 全幅 -->
    <div class="account-section full-width-section">
        <div class="btn-group">
            <a href="edit.php" class="btn btn-primary" data-translate="edit_profile_button">プロフィールを編集</a>
            <a href="../lessons/curriculum.php" class="btn btn-secondary" data-translate="back_to_lessons_button">レッスンに戻る</a>
            <a href="../auth/logout.php" class="btn btn-secondary" onclick="return confirm('ログアウトしますか？')" data-translate="logout_button">ログアウト</a>
        </div>
    </div>
</div>

<script>
// ユーザーの子供の名前をJavaScriptで使用
const user_child_name = '<?= addslashes($user['child_name']) ?>';

// アカウントページ翻訳辞書
const accountTranslations = {
    ja: {
        language_label: '言語 / Language *',
        welcome_message: user_child_name + 'ちゃんのアカウント',
        account_subtitle: 'アカウント情報の確認と編集ができます',
        basic_info_title: '基本情報',
        parent_name_label: '保護者の方のお名前',
        child_name_label: 'お子様のお名前',
        child_nickname_label: 'お子様のニックネーム',
        child_gender_label: 'お子様の性別',
        child_birthdate_label: 'お子様の生年月日',
        child_country_label: 'お子様の出身国',
        school_info_title: '学校情報',
        child_grade_label: '学年',
        school_name_label: '学校名',
        family_info_title: '家族情報',
        family_members_label: '家族構成',
        family_size_label: '家族の人数',
        account_info_title: 'アカウント情報',
        email_label: 'メールアドレス',
        native_language_label: '母語',
        registration_date_label: '登録日',
        edit_profile_button: 'プロフィールを編集',
        back_to_lessons_button: 'レッスンに戻る',
        logout_button: 'ログアウト',
        not_set: '未設定',
        boy: '男の子',
        girl: '女の子',
        elementary: '小学',
        junior_high: '中学',
        grade_suffix: '年生',
        people_count: '人'
    },
    en: {
        language_label: 'Language / 言語 *',
        welcome_message: user_child_name + '\'s Account',
        account_subtitle: 'View and edit your account information',
        basic_info_title: 'Basic Information',
        parent_name_label: 'Parent/Guardian Name',
        child_name_label: 'Child\'s Name',
        child_nickname_label: 'Child\'s Nickname',
        child_gender_label: 'Child\'s Gender',
        child_birthdate_label: 'Child\'s Date of Birth',
        child_country_label: 'Child\'s Country of Origin',
        school_info_title: 'School Information',
        child_grade_label: 'Grade',
        school_name_label: 'School Name',
        family_info_title: 'Family Information',
        family_members_label: 'Family Members',
        family_size_label: 'Family Size',
        account_info_title: 'Account Information',
        email_label: 'Email Address',
        native_language_label: 'Native Language',
        registration_date_label: 'Registration Date',
        edit_profile_button: 'Edit Profile',
        back_to_lessons_button: 'Back to Lessons',
        logout_button: 'Logout',
        not_set: 'Not Set',
        boy: 'Boy',
        girl: 'Girl',
        elementary: 'Elementary ',
        junior_high: 'Junior High ',
        grade_suffix: '',
        people_count: ' people'
    },
    zh: {
        language_label: '语言 / Language *',
        welcome_message: user_child_name + '的账户',
        account_subtitle: '查看和编辑您的账户信息',
        basic_info_title: '基本信息',
        parent_name_label: '监护人姓名',
        child_name_label: '孩子姓名',
        child_nickname_label: '孩子昵称',
        child_gender_label: '孩子性别',
        child_birthdate_label: '孩子生日',
        child_country_label: '孩子出生国',
        school_info_title: '学校信息',
        child_grade_label: '年级',
        school_name_label: '学校名称',
        family_info_title: '家庭信息',
        family_members_label: '家庭成员',
        family_size_label: '家庭人数',
        account_info_title: '账户信息',
        email_label: '邮箱地址',
        native_language_label: '母语',
        registration_date_label: '注册日期',
        edit_profile_button: '编辑资料',
        back_to_lessons_button: '返回课程',
        logout_button: '退出登录',
        not_set: '未设定',
        boy: '男孩',
        girl: '女孩',
        elementary: '小学',
        junior_high: '中学',
        grade_suffix: '年级',
        people_count: '人'
    },
    ko: {
        language_label: '언어 / Language *',
        welcome_message: user_child_name + '의 계정',
        account_subtitle: '계정 정보를 확인하고 편집할 수 있습니다',
        basic_info_title: '기본 정보',
        parent_name_label: '보호자 성함',
        child_name_label: '자녀 이름',
        child_nickname_label: '자녀 별명',
        child_gender_label: '자녀 성별',
        child_birthdate_label: '자녀 생년월일',
        child_country_label: '자녀 출신국',
        school_info_title: '학교 정보',
        child_grade_label: '학년',
        school_name_label: '학교명',
        family_info_title: '가족 정보',
        family_members_label: '가족 구성',
        family_size_label: '가족 수',
        account_info_title: '계정 정보',
        email_label: '이메일 주소',
        native_language_label: '모국어',
        registration_date_label: '등록일',
        edit_profile_button: '프로필 편집',
        back_to_lessons_button: '수업으로 돌아가기',
        logout_button: '로그아웃',
        not_set: '미설정',
        boy: '남자',
        girl: '여자',
        elementary: '초등 ',
        junior_high: '중등 ',
        grade_suffix: '학년',
        people_count: '명'
    },
    vi: {
        language_label: 'Ngôn ngữ / Language *',
        welcome_message: 'Tài khoản của ' + user_child_name,
        account_subtitle: 'Xem và chỉnh sửa thông tin tài khoản của bạn',
        basic_info_title: 'Thông tin cơ bản',
        parent_name_label: 'Tên phụ huynh/người giám hộ',
        child_name_label: 'Tên con',
        child_nickname_label: 'Biệt danh của con',
        child_gender_label: 'Giới tính của con',
        child_birthdate_label: 'Ngày sinh của con',
        child_country_label: 'Quốc gia xuất thân',
        school_info_title: 'Thông tin trường học',
        child_grade_label: 'Lớp',
        school_name_label: 'Tên trường',
        family_info_title: 'Thông tin gia đình',
        family_members_label: 'Thành viên gia đình',
        family_size_label: 'Quy mô gia đình',
        account_info_title: 'Thông tin tài khoản',
        email_label: 'Địa chỉ email',
        native_language_label: 'Ngôn ngữ mẹ đẻ',
        registration_date_label: 'Ngày đăng ký',
        edit_profile_button: 'Chỉnh sửa hồ sơ',
        back_to_lessons_button: 'Quay lại bài học',
        logout_button: 'Đăng xuất',
        not_set: 'Chưa thiết lập',
        boy: 'Nam',
        girl: 'Nữ',
        elementary: 'Tiểu học lớp ',
        junior_high: 'Trung học lớp ',
        grade_suffix: '',
        people_count: ' người'
    },
    tl: {
        language_label: 'Wika / Language *',
        welcome_message: 'Account ni ' + user_child_name,
        account_subtitle: 'Tingnan at i-edit ang inyong account information',
        basic_info_title: 'Basic Information',
        parent_name_label: 'Pangalan ng Magulang/Guardian',
        child_name_label: 'Pangalan ng Bata',
        child_nickname_label: 'Palayaw ng Bata',
        child_gender_label: 'Kasarian ng Bata',
        child_birthdate_label: 'Kapanganakan ng Bata',
        child_country_label: 'Bansang Pinagmulan',
        school_info_title: 'School Information',
        child_grade_label: 'Grade',
        school_name_label: 'Pangalan ng Paaralan',
        family_info_title: 'Family Information',
        family_members_label: 'Mga Miyembro ng Pamilya',
        family_size_label: 'Laki ng Pamilya',
        account_info_title: 'Account Information',
        email_label: 'Email Address',
        native_language_label: 'Katutubong Wika',
        registration_date_label: 'Petsa ng Pagkakaregister',
        edit_profile_button: 'I-edit ang Profile',
        back_to_lessons_button: 'Bumalik sa mga Aralin',
        logout_button: 'Logout',
        not_set: 'Hindi nakatakda',
        boy: 'Lalaki',
        girl: 'Babae',
        elementary: 'Elementary Grade ',
        junior_high: 'High School Grade ',
        grade_suffix: '',
        people_count: ' tao'
    },
    ne: {
        language_label: 'भाषा / Language *',
        welcome_message: user_child_name + 'को खाता',
        account_subtitle: 'तपाईंको खाता जानकारी हेर्नुहोस् र सम्पादन गर्नुहोस्',
        basic_info_title: 'आधारभूत जानकारी',
        parent_name_label: 'अभिभावकको नाम',
        child_name_label: 'बच्चाको नाम',
        child_nickname_label: 'बच्चाको उपनाम',
        child_gender_label: 'बच्चाको लिङ्ग',
        child_birthdate_label: 'बच्चाको जन्म मिति',
        child_country_label: 'उत्पत्ति देश',
        school_info_title: 'विद्यालय जानकारी',
        child_grade_label: 'कक्षा',
        school_name_label: 'विद्यालयको नाम',
        family_info_title: 'पारिवारिक जानकारी',
        family_members_label: 'परिवारका सदस्यहरू',
        family_size_label: 'परिवारको आकार',
        account_info_title: 'खाता जानकारी',
        email_label: 'इमेल ठेगाना',
        native_language_label: 'मातृभाषा',
        registration_date_label: 'दर्ता मिति',
        edit_profile_button: 'प्रोफाइल सम्पादन गर्नुहोस्',
        back_to_lessons_button: 'पाठहरूमा फर्कनुहोस्',
        logout_button: 'लग आउट',
        not_set: 'सेट गरिएको छैन',
        boy: 'केटा',
        girl: 'केटी',
        elementary: 'प्राथमिक कक्षा ',
        junior_high: 'माध्यमिक कक्षा ',
        grade_suffix: '',
        people_count: ' जना'
    },
    pt: {
        language_label: 'Idioma / Language *',
        welcome_message: 'Conta de ' + user_child_name,
        account_subtitle: 'Visualize e edite as informações da sua conta',
        basic_info_title: 'Informações Básicas',
        parent_name_label: 'Nome dos Pais/Responsável',
        child_name_label: 'Nome da Criança',
        child_nickname_label: 'Apelido da Criança',
        child_gender_label: 'Sexo da Criança',
        child_birthdate_label: 'Data de Nascimento',
        child_country_label: 'País de Origem',
        school_info_title: 'Informações da Escola',
        child_grade_label: 'Série',
        school_name_label: 'Nome da Escola',
        family_info_title: 'Informações da Família',
        family_members_label: 'Membros da Família',
        family_size_label: 'Tamanho da Família',
        account_info_title: 'Informações da Conta',
        email_label: 'Endereço de Email',
        native_language_label: 'Idioma Nativo',
        registration_date_label: 'Data de Registro',
        edit_profile_button: 'Editar Perfil',
        back_to_lessons_button: 'Voltar às Lições',
        logout_button: 'Sair',
        not_set: 'Não definido',
        boy: 'Menino',
        girl: 'Menina',
        elementary: 'Ensino Fundamental ',
        junior_high: 'Ensino Médio ',
        grade_suffix: '° ano',
        people_count: ' pessoas'
    }
};

function translateAccountPage(lang = null) {
    // 言語が指定されていない場合はユーザーの母語を使用
    const selectedLang = lang || '<?= $user['native_language'] ?? 'ja' ?>';
    const accountContainer = document.getElementById('accountContainer');
    
    // フォントクラスを適用
    accountContainer.className = 'account-container lang-' + selectedLang;
    
    // 翻訳実行
    if (accountTranslations[selectedLang]) {
        const trans = accountTranslations[selectedLang];
        
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (trans[key]) {
                // welcomeメッセージの場合は、子供の名前を含める
                if (key === 'welcome_message') {
                    element.textContent = trans[key];
                } else {
                    element.textContent = trans[key];
                }
                console.log('Translated:', key, '->', trans[key]);
            } else {
                console.warn('Translation key not found:', key);
            }
        });
        
        // 動的な値も翻訳
        translateDynamicValues(selectedLang, trans);
        
        // 登録日の日付フォーマットを言語に応じて変更
        const registrationDate = document.getElementById('registrationDate');
        if (registrationDate) {
            const dateStr = '<?= $user['created_at'] ?>';
            const date = new Date(dateStr);
            
            switch(selectedLang) {
                case 'en':
                    registrationDate.textContent = date.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    });
                    break;
                case 'zh':
                    registrationDate.textContent = date.getFullYear() + '年' + 
                        (date.getMonth() + 1) + '月' + date.getDate() + '日';
                    break;
                case 'ko':
                    registrationDate.textContent = date.getFullYear() + '년 ' + 
                        (date.getMonth() + 1) + '월 ' + date.getDate() + '일';
                    break;
                case 'vi':
                    registrationDate.textContent = date.getDate() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getFullYear();
                    break;
                case 'tl':
                    registrationDate.textContent = date.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    });
                    break;
                case 'ne':
                    registrationDate.textContent = date.getFullYear() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getDate();
                    break;
                case 'pt':
                    registrationDate.textContent = date.getDate() + '/' + 
                        (date.getMonth() + 1) + '/' + date.getFullYear();
                    break;
                case 'ja':
                default:
                    registrationDate.textContent = date.getFullYear() + '年' + 
                        (date.getMonth() + 1) + '月' + date.getDate() + '日';
                    break;
            }
        }
    }
}

function translateDynamicValues(lang, trans) {
    // PHPから値を取得
    const userData = {
        nickname: '<?= addslashes($user['child_nickname'] ?? '') ?>',
        gender: '<?= $user['child_gender'] ?>',
        birthdate: '<?= addslashes($user['child_birthdate'] ?? '') ?>',
        country: '<?= addslashes($user['child_country'] ?? '') ?>',
        grade: <?= intval($user['child_grade'] ?? 0) ?>,
        schoolName: '<?= addslashes($user['school_name'] ?? '') ?>',
        familySize: <?= intval($user['family_size'] ?? 0) ?>
    };
    
    // ニックネームの翻訳
    const nicknameElement = document.getElementById('childNickname');
    if (nicknameElement) {
        nicknameElement.textContent = userData.nickname || trans.not_set;
    }
    
    // 性別の翻訳
    const genderElement = document.getElementById('childGender');
    if (genderElement) {
        genderElement.textContent = userData.gender === 'boy' ? trans.boy : trans.girl;
    }
    
    // 生年月日の翻訳
    const birthdateElement = document.getElementById('childBirthdate');
    if (birthdateElement) {
        birthdateElement.textContent = userData.birthdate || trans.not_set;
    }
    
    // 出身国の翻訳（既存の値を保持）
    const countryElement = document.getElementById('childCountry');
    if (countryElement && countryElement.textContent.trim() === '未設定') {
        countryElement.textContent = trans.not_set;
    }
    
    // 学年の翻訳
    const gradeElement = document.getElementById('childGrade');
    if (gradeElement && userData.grade > 0) {
        if (userData.grade <= 6) {
            // 小学生
            if (lang === 'en') {
                gradeElement.textContent = `${trans.elementary}${userData.grade}${trans.grade_suffix}`;
            } else if (lang === 'zh') {
                gradeElement.textContent = `${trans.elementary}${userData.grade}${trans.grade_suffix}`;
            } else {
                gradeElement.textContent = `${trans.elementary}${userData.grade}${trans.grade_suffix}`;
            }
        } else {
            // 中学生
            const juniorGrade = userData.grade - 6;
            if (lang === 'en') {
                gradeElement.textContent = `${trans.junior_high}${juniorGrade}${trans.grade_suffix}`;
            } else if (lang === 'zh') {
                gradeElement.textContent = `${trans.junior_high}${juniorGrade}${trans.grade_suffix}`;
            } else {
                gradeElement.textContent = `${trans.junior_high}${juniorGrade}${trans.grade_suffix}`;
            }
        }
    } else if (gradeElement) {
        gradeElement.textContent = trans.not_set;
    }
    
    // 学校名の翻訳
    const schoolNameElement = document.getElementById('schoolName');
    if (schoolNameElement) {
        schoolNameElement.textContent = userData.schoolName || trans.not_set;
    }
    
    // 家族構成の翻訳（「未設定」のみ）
    const familyMembersElement = document.getElementById('familyMembers');
    if (familyMembersElement && familyMembersElement.textContent.trim() === '未設定') {
        familyMembersElement.textContent = trans.not_set;
    }
    
    // 家族人数の翻訳
    const familySizeElement = document.getElementById('familySize');
    if (familySizeElement && userData.familySize > 0) {
        if (lang === 'en') {
            familySizeElement.textContent = `${userData.familySize}${trans.people_count}`;
        } else if (lang === 'zh') {
            familySizeElement.textContent = `${userData.familySize}${trans.people_count}`;
        } else {
            familySizeElement.textContent = `${userData.familySize}${trans.people_count}`;
        }
    } else if (familySizeElement) {
        familySizeElement.textContent = trans.not_set;
    }
}

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', function() {
    // ユーザーの登録言語を使用（URLパラメータがあればそれを優先）
    const urlParams = new URLSearchParams(window.location.search);
    const urlLang = urlParams.get('lang');
    const userNativeLang = '<?= $user['native_language'] ?? 'ja' ?>';
    const lang = urlLang || userNativeLang;
    
    console.log('User native language:', userNativeLang);
    console.log('Selected language:', lang);
    console.log('Available translations:', Object.keys(accountTranslations));
    
    if (accountTranslations[lang]) {
        translateAccountPage(lang);
    } else {
        console.warn('Translation not found for language:', lang);
        // フォールバックとして日本語を使用
        translateAccountPage('ja');
    }
    
    // header.phpのswitchLanguage関数をオーバーライド
    window.originalSwitchLanguage = window.switchLanguage;
    window.switchLanguage = function(lang) {
        // プロフィールページでは動的に言語を切り替え
        if (accountTranslations[lang]) {
            translateAccountPage(lang);
        }
        
        // アクティブなタブを更新
        document.querySelectorAll('.language-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-lang="${lang}"]`).classList.add('active');
        
        // body要素に現在の言語を設定
        document.body.setAttribute('data-current-lang', lang);
        
        // ロゴを言語に応じて切り替え
        const logoImg = document.getElementById('topLogoImg');
        if (logoImg) {
            let logoSrc = '';
            switch(lang) {
                case 'en':
                    logoSrc = '../assets/images/ralango_logo_en.png';
                    break;
                case 'zh':
                    logoSrc = '../assets/images/ralango_logo_zh.png';
                    break;
                default:
                    logoSrc = '../assets/images/ralango_logo_jp.png';
                    break;
            }
            logoImg.src = logoSrc;
        }
        
        // セッションに言語設定を保存（リロードなし）
        fetch('../api/set_language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                language: lang
            })
        });
    };
});
</script>

<?php require_once '../includes/footer.php'; ?>