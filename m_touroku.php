<?php
session_start();

// 1. ログインチェック
if (!isset($_SESSION['staff_c'])) {
    header("Location: login.php");
    exit;
}

// 2. 共通設定・DB接続
header('X-FRAME-OPTIONS:SAMEORIGIN');
require_once('connect_try.php'); // connect_try.php内に$pdo定義済みと想定

// トークン生成
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$errors = array();
$complete_flag = false;

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
    
    $allowed_parts = ['0', '1', '2', '3', '4'];
    if (!in_array($_POST['part_of'] ?? '', $allowed_parts, true)) {
        $errors[] = "所属を選択してください";
    }

    if (!preg_match("/^[ぁ-んァ-ヶーー-龠々　a-zA-Z-0-9 ]+$/u", $_POST['job_type'] ?? '')) {
        $errors[] = '業務の種類を正しく入力してください';
    }
    if (empty($_POST['start_d'])) {
        $errors[] = '入社日を選択してください';
    }

    $allowed_status = ['0', '1', '2', '3'];
    if (!in_array($_POST['status'] ?? '', $allowed_status, true)) {
        $errors[] = "雇用形態を選択してください";
    }
    if (!preg_match("/^[0-9]{6}$/", $_POST['health_c'] ?? '')) {
        $errors[] = '被保険者整理番号を正しく入力してください（数字6桁）';
    }

    // エラーがなければDB登録
    if (count($errors) === 0) {
        try {
            $sql = 'INSERT INTO `manage_n` 
                    (`staff_c`, `name`, `part_of`, `job_type`, `start_d`, `status`, `health_c`) 
                    VALUES 
                    (:staff_c, :name, :part_of, :job_type, :start_d, :status, :health_c)';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':staff_c'  => $_POST['staff_c'],
                ':name'     => $_POST['name'],
                ':part_of'  => $_POST['part_of'],
                ':job_type' => $_POST['job_type'],
                ':start_d'  => $_POST['start_d'],
                ':status'   => $_POST['status'],
                ':health_c' => $_POST['health_c'],
            ]);
            $complete_flag = true;
            // 登録成功時にトークンを更新
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="m_touroku.css">
    <title>社員登録</title>
</head>
<body>
    <div class="main-container">

        <?php if ($complete_flag): ?>
            <h2>登録完了しました</h2>  
            <p><a href="index.html">メニュー画面に戻る</a></p>
            <p><a href="m_touroku.php">続けて登録する</a></p>

        <?php else: ?>
            <h2>入社者登録</h2>  

            <?php if (count($errors) > 0): ?>
                <ul class="error_list" style="color: red; text-align: left; display: inline-block;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?> 

            <form action="m_touroku.php" method="post">
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
                        <th>所属</th>
                        <td>
                            <select class="option" name="part_of">
                                <option value="">選択してください</option>
                                <?php
                                $parts = ["営業部", "人事部", "総務部", "経理部", "情報システム部"];
                                foreach ($parts as $key => $val) {
                                    $selected = (($_POST['part_of'] ?? '') === (string)$key) ? 'selected' : '';
                                    echo "<option value=\"{$key}\" {$selected}>{$val}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>       
                        <th>業務の種類</th>
                        <td><input type="text" name="job_type" value="<?php echo htmlspecialchars($_POST['job_type'] ?? '', ENT_QUOTES); ?>"></td>
                    </tr>
                    <tr>
                        <th>入社日</th>
                        <td><input type="date" name="start_d" value="<?php echo htmlspecialchars($_POST['start_d'] ?? '2026-04-01', ENT_QUOTES); ?>"></td>
                    </tr>
                    <tr>
                        <th>雇用形態</th>
                        <td>
                            <select class="option" name="status">
                                <option value="">選択してください</option>
                                <?php
                                $statuses = ["正社員", "契約社員", "パート", "アルバイト"];
                                foreach ($statuses as $key => $val) {
                                    $selected = (($_POST['status'] ?? '') === (string)$key) ? 'selected' : '';
                                    echo "<option value=\"{$key}\" {$selected}>{$val}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>被保険者整理番号</th>
                        <td><input type="text" name="health_c" value="<?php echo htmlspecialchars($_POST['health_c'] ?? '', ENT_QUOTES); ?>" placeholder="123456"></td>     
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