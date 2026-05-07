<?php
session_start();

// 1. ログインチェック
if (!isset($_SESSION['staff_c'])) {
    header("Location: login.php");
    exit;
}

// 2. 共通設定・DB接続
header('X-FRAME-OPTIONS:SAMEORIGIN');
require_once('connect_try.php'); // $pdoは定義済みと想定

// トークン生成（初回のみ）
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// 3. ダウンロード処理（POSTされた時だけ実行）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRFトークンチェック
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        exit('不正なリクエストです');
    }

    try {
        // SQL実行
        $sql = "SELECT * FROM `manage_n` ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // CSV見出し（Excelで見られるよう、最後にShift-JISへ変換する前提で作成）
        $csvstr = "名前,入社日,保険\r\n";

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $csvstr .= $result['name'] . ",";
            $csvstr .= $result['start_d'] . ",";
            $csvstr .= $result['health_c'] . "\r\n"; // 最後の列はカンマ不要で改行
        }

        // ダウンロード設定
        $fileName = "manage_n_" . date('Ymd') . ".csv"; // ファイル名に日付を入れると便利です

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $fileName);
        
        // Excel用（Shift-JIS）に変換して出力
        echo mb_convert_encoding($csvstr, "SJIS-win", "UTF-8");
        
        exit; // ダウンロード処理が終わったらスクリプトを終了させる

    } catch (PDOException $e) {
        $error_msg = "データベースエラー: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="m_touroku.css"> <title>CSVダウンロード</title>
</head>
<body>
    <div class="main-container">
        <h2>データダウンロード</h2>
        <p>健康保険整理番号を含む社員リストを<br>CSV形式でダウンロードします。</p>

        <?php if (isset($error_msg)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form action="d_health.php" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="btn-container">
                <input type="submit" class="btn" value="ダウンロード開始">
            </div>
        </form>

        <p style="margin-top: 20px;"><a href="index.html">メニューに戻る</a></p>
    </div>
</body>
</html>