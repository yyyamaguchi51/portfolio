<?php
session_start();

// 1. ログインチェック（必要であれば追加）
// if (!isset($_SESSION['staff_c'])) { header("Location: login.php"); exit; }

// 2. 共通設定
header('X-FRAME-OPTIONS:SAMEORIGIN');
require_once('connect_try.php'); // $pdoは定義済みと想定

$errors = array();
$complete_flag = false;

// トークン生成
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// 3. POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRFトークンチェック
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        exit('不正なリクエストです');
    }

    // バリデーション
    if (!preg_match("/^[0-9]{6}$/", $_POST['staff_c'] ?? '')) {
        $errors[] = '社員番号を正しく入力してください（数字6桁）';
    }
    if (!preg_match("/^[ぁ-んァ-ヶーー-龠々　a-zA-Z ]+$/u", $_POST['name'] ?? '')) {
        $errors[] = '氏名を正しく入力してください';
    }
    if (empty($_POST['retire_d'])) {
        $errors[] = '退職日を選択してください';
    }
    
    $allowed_reasons = ['0', '1', '2', '3'];
    if (!isset($_POST['retire_r']) || !in_array($_POST['retire_r'], $allowed_reasons, true)) {
        $errors[] = "退職理由を選択してください";
    }

    // エラーがなければDB登録
    if (count($errors) === 0) {
        try {
            $sql = 'INSERT INTO `retire` (`staff_c`, `name`, `retire_d`, `retire_r`) 
                    VALUES (:staff_c, :name, :retire_d, :retire_r)';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':staff_c'  => $_POST['staff_c'],
                ':name'     => $_POST['name'],
                ':retire_d' => $_POST['retire_d'],
                ':retire_r' => $_POST['retire_r'],
            ]);
            $complete_flag = true;
            // 成功時にトークンを更新
            $_SESSION['token'] = bin2hex(random_bytes(32));
        } catch (PDOException $e) {
            $errors[] = "DBエラーが発生しました: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="m_touroku.css">
    <title>退職者登録</title>
</head>
<body>
    <div class="main-container">
        <?php if ($complete_flag): ?>
            <h2>登録完了</h2>
            <p>退職者の登録が完了しました。</p>
            <p><a href="index.html">メニュー画面に戻る</a></p>
            <p><a href="retire.php">続けて登録する</a></p>

        <?php else: ?>
            <h2>退職者登録</h2>

            <?php if (count($errors) > 0): ?>
                <ul class="error_list" style="color: red; text-align: left; display: inline-block;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form action="retire.php" method="post">
                <table class="info">
                    <tr> 
                        <th>社員番号</th>
                        <td><input type="text" name="staff_c" value="<?php echo htmlspecialchars($_POST['staff_c'] ?? '', ENT_QUOTES); ?>" placeholder="123456"></td>
                    </tr>
                    <tr>
                        <th>氏名</th>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>" placeholder="山田　太郎"></td>
                    </tr>
                    <tr>
                        <th>退職日</th>
                        <td><input type="date" name="retire_d" value="<?php echo htmlspecialchars($_POST['retire_d'] ?? date('Y-m-d'), ENT_QUOTES); ?>"></td>
                    </tr>
                    <tr>
                        <th>退職理由</th>
                        <td>
                            <select class="option" name="retire_r">
                                <option value="">選択してください</option>
                                <?php
                                $reasons = ["自己都合", "契約期間満了", "解雇", "死亡"];
                                foreach ($reasons as $key => $val) {
                                    $selected = (($_POST['retire_r'] ?? '') === (string)$key) ? 'selected' : '';
                                    echo "<option value=\"{$key}\" {$selected}>{$val}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="btn-container">
                    <input class="btn" type="submit" value="登録">
                </div>
                <p style="margin-top:20px;"><a href="index.html">戻る</a></p>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>