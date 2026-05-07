<?php
session_start();
require_once('connect_try.php');

$message = "";

// --- 更新処理 (POSTされた時) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $staff_c = $_POST['staff_c'];

    if (preg_match("/^[0-9]{6}$/", $staff_c)) {
        try {
            // new_staffテーブルに社員番号を書き込む（またはmanage_nを作成する）
            // ここではnew_staffテーブルに staff_c カラムがある前提、
            // もしくは manage_n テーブルに新規追加する処理を書きます。
            $sql = "UPDATE `new_staff` SET `staff_c` = :staff_c WHERE `id` = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':staff_c' => $staff_c, ':id' => $id]);
            $message = "社員番号 {$staff_c} を登録しました。";
        } catch (PDOException $e) {
            $message = "エラー: " . $e->getMessage();
        }
    } else {
        $message = "社員番号は数字6桁で入力してください。";
    }
}

// --- 一覧表示用のデータ取得 ---
// 社員番号(staff_c)が未設定の人を取得
$sql = "SELECT id, name, birth FROM `new_staff` WHERE `staff_c` IS NULL OR `staff_c` = ''";
$stmt = $pdo->query($sql);
$unassigned_staff = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="m_touroku.css">
    <title>社員番号割り当て</title>
</head>
<body>
    <div class="main-container">
        <h2>社員番号 未登録者リスト</h2>
        <?php if ($message): ?>
            <p style="color: blue;"><?php echo htmlspecialchars($message, ENT_QUOTES); ?></p>
        <?php endif; ?>

        <table class="force-center">
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>生年月日</th>
                    <th>社員番号入力</th>
                    <th>操作</th>
                </tr>
            </thead>    
            <?php foreach ($unassigned_staff as $staff): ?>
            <tr>
                <form action="" method="post">
                    <td><?php echo htmlspecialchars($staff['name'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($staff['birth'], ENT_QUOTES); ?></td>
                    <td>
                        <input type="text" name="staff_c" placeholder="6桁の数字"原本>
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                    </td>
                    <td>
                        <input type="submit" name="update" value="番号を確定">
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <?php if (empty($unassigned_staff)): ?>
            <p>未登録者はいません。</p>
        <?php endif; ?>

        <p><a href="index.html">戻る</a></p>
    </div>
</body>
</html>