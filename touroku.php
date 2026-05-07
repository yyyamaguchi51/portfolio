<?php
session_start();
header('X-FRAME-OPTIONS:SAMEORIGIN');
require_once('connect_try.php');

$errors = array();
$complete_flag = false;

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        exit('不正なリクエストです。入力画面からやり直してください。');
    }

    // バリデーション
    if (!preg_match("/^[ぁ-んー　 ]+$/u", $_POST['furigana'])) { $errors[] = 'ふりがなは全角ひらがなで入力してください'; }
    if (!preg_match("/^[ぁ-んァ-ヶーー-龠々　a-zA-Z　　]+$/u", $_POST['name'])) { $errors[] = '氏名を正しく入力してください'; }
    if (empty($_POST['birth'])) { $errors[] = '生年月日を選択してください'; }
    if (!isset($_POST['sex'])) { $errors[] = '性別を選択してください'; }
    if (!preg_match("/^[0-9]{7}$/", $_POST['post_c'])) { $errors[] = '郵便番号を正しく入力してください'; }
    if (empty($_POST['addr1'])) { $errors[] = '住所を入力してください'; }
    if (!preg_match("/^0[0-9]{9,10}$/", $_POST['tel'])) { $errors[] = '電話番号を正しく入力してください'; }
    if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'メールアドレスを正しく入力してください'; }
    
    // パスワードチェック（一致確認を削除）
    if (!preg_match("/^[a-zA-Z0-9]{6,60}$/", $_POST['pass'])) { 
        $errors[] = 'パスワードは半角英数字6文字以上で入力してください'; 
    }

    $no_family_flag = isset($_POST['no_family']) ? 1 : 0;
    if ($no_family_flag === 0) {
        if (empty($_POST['f_name'])) { $errors[] = '家族の氏名を入力してください'; }
        if (!isset($_POST['f_sex'])) { $errors[] = '家族の性別を選択してください'; }
    }

    if (count($errors) === 0) {
        $check_sql = "SELECT count(*) FROM `new_staff` WHERE `mail` = :mail";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':mail' => $_POST['mail']]);

        if ($check_stmt->fetchColumn() > 0) {
            $errors[] = 'このメールアドレスは既に登録されています';
        } else {
            $pass_hash = password_hash($_POST['pass'], PASSWORD_DEFAULT);
            $sql = 'INSERT INTO `new_staff` 
                    (`furigana`,`name`,`birth`,`sex`,`post_c`,`addr1`,`addr2`,`tel`,`mail`,`pass`,`no_family`,`f_furigana`,`f_name`,`f_birth`,`f_sex`,`relation`,`income`) 
                    VALUES 
                    (:furigana,:name,:birth,:sex,:post_c,:addr1,:addr2,:tel,:mail,:pass,:no_family,:f_furigana,:f_name,:f_birth,:f_sex,:relation,:income)';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':furigana'   => $_POST['furigana'],
                ':name'       => $_POST['name'],
                ':birth'      => $_POST['birth'],
                ':sex'        => $_POST['sex'],
                ':post_c'     => $_POST['post_c'],
                ':addr1'      => $_POST['addr1'],
                ':addr2'      => $_POST['addr2'],
                ':tel'        => $_POST['tel'],
                ':mail'       => $_POST['mail'],
                ':pass'       => $pass_hash,
                ':no_family'  => $no_family_flag,
                ':f_furigana' => $_POST['f_furigana'] ?? null,
                ':f_name'     => $_POST['f_name'] ?? null,
                ':f_birth'    => $_POST['f_birth'] ?? null,
                ':f_sex'      => $_POST['f_sex'] ?? null,
                ':relation'   => $_POST['relation'] ?? null,
                ':income'     => $_POST['income'] ?: 0
            ]);
            $complete_flag = true;
            unset($_SESSION['token']); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="touroku.css">
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <title>本人情報登録</title>
</head>
<body>
<div class="main-container">
    <?php if ($complete_flag): ?>
        <h2>登録完了しました！</h2>
        <p>データは正常に保存されました。</p>
        
        <!-- <div class="btn-container"><a href="login.php" class="btn">ログイン画面へ</a></div> -->
    <?php else: ?>
        <h2>本人情報登録</h2>
        <?php if (!empty($errors)): ?>
            <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px; background: #fff5f5;">
                <?php foreach ($errors as $e) echo "<p>・".htmlspecialchars($e, ENT_QUOTES, 'UTF-8')."</p>"; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <table class="info">
                <tr><th>ふりがな</th><td><input type="text" name="furigana" value="<?php echo htmlspecialchars($_POST['furigana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="やまだ　たろう" required></td></tr>
                <tr><th>氏名</th><td><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="山田　太郎" required></td></tr>
                <tr><th>生年月日</th><td><input type="date" name="birth" value="<?php echo htmlspecialchars($_POST['birth'] ?? '1986-06-15', ENT_QUOTES, 'UTF-8'); ?>" required></td></tr>
                <tr><th>性別</th><td>
                    <label><input type="radio" name="sex" value="0" <?php if(($_POST['sex'] ?? '') === '0') echo 'checked'; ?> required> 男</label> &emsp; 
                    <label><input type="radio" name="sex" value="1" <?php if(($_POST['sex'] ?? '') === '1') echo 'checked'; ?>> 女</label>
                </td></tr>
                <tr><th>郵便番号</th><td><input type="text" name="post_c" value="<?php echo htmlspecialchars($_POST['post_c'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="060000" onKeyUp="AjaxZip3.zip2addr(this,'','addr1', 'addr1');" required></td></tr>
                <tr><th>住所</th><td><textarea name="addr1" placeholder="住所が自動入力" required><?php echo htmlspecialchars($_POST['addr1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></td></tr>
                <tr><th>住所2</th><td><textarea name="addr2" placeholder="建物名"><?php echo htmlspecialchars($_POST['addr2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></td></tr>
                <tr><th>電話番号</th><td><input type="text" name="tel" value="<?php echo htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="09011112222" required></td></tr>
                <tr><th>メールアドレス</th><td><input type="email" name="mail" value="<?php echo htmlspecialchars($_POST['mail'] ?? '', ENT_QUOTES, 'UTF-8'); ?> "placeholder="半角英数字" required></td></tr>
                <tr>
                    <th>パスワード</th>
                    <td>
                        <input type="password" name="pass" id="password_input" placeholder="8文字以上の半角英数字" required>
                        <div style="margin-top: 5px;">
                            <label><input type="checkbox" id="show_password"> パスワードを表示する</label>
                        </div>
                    </td>
                </tr>
            </table>

            <hr style="margin: 40px 0; border-top: 1px solid #949393;">

            <h3>扶養家族登録</h3>
            <div class="no_family-check">
                <label><input type="checkbox" name="no_family" value="1" <?php if(isset($_POST['no_family'])) echo 'checked'; ?>> <strong>扶養家族はいません(下記入力不要)</strong></label>
            </div>
            
            <table class="family" id="family_table">
                <tr><th>ふりがな</th><td><input type="text" name="f_furigana" value="<?php echo htmlspecialchars($_POST['f_furigana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="やまだ　たろう"></td></tr>
                <tr><th>氏名</th><td><input type="text" name="f_name" value="<?php echo htmlspecialchars($_POST['f_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="山田　太郎"></td></tr>
                <tr><th>生年月日</th><td><input type="date" name="f_birth" value="<?php echo htmlspecialchars($_POST['f_birth'] ?? '1986-06-15', ENT_QUOTES, 'UTF-8'); ?>"></td></tr>
                <tr><th>性別</th><td>
                    <label><input type="radio" name="f_sex" value="0" <?php if(($_POST['f_sex'] ?? '') === '0') echo 'checked'; ?>>男</label> &emsp; 
                    <label><input type="radio" name="f_sex" value="1" <?php if(($_POST['f_sex'] ?? '') === '1') echo 'checked'; ?>>女</label>
                </td></tr>
                <tr><th>続柄</th><td>
                    <label><input type="radio" name="relation" value="0" <?php if(($_POST['relation'] ?? '') === '0') echo 'checked'; ?>>配偶者</label>
                    <label><input type="radio" name="relation" value="1" <?php if(($_POST['relation'] ?? '') === '1') echo 'checked'; ?>>子</label>
                    <label><input type="radio" name="relation" value="3" <?php if(($_POST['relation'] ?? '') === '3') echo 'checked'; ?>>親</label>
                    <label><input type="radio" name="relation" value="4" <?php if(($_POST['relation'] ?? '') === '4') echo 'checked'; ?>>その他</label>
                </td></tr>
                <tr><th>年収</th><td><input type="text" name="income" value="<?php echo htmlspecialchars($_POST['income'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="手取りではなく年収を"></td></tr>
            </table>

            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <div class="btn-container"><input type="submit" class="btn" value="送信"></div>
        </form>
    <?php endif; ?>
</div>

<script>
    const checkbox = document.getElementById('show_password');
    const pass = document.getElementById('password_input');

    checkbox.addEventListener('change', function() {
        pass.type = this.checked ? 'text' : 'password';
    });
</script>
</body>
</html>
<!-- http:localhost/portfolio/touroku.php -->