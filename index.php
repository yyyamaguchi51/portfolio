<?php
session_start();

//ログインしていない人をログイン画面に（セキュリティ）
if(!isset($_SESSION['staff_c'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="index.css">
    <title>メニュー画面</title>
</head>
  <body>
    <h1>メニュー画面</h1>
        

    <p><a href="m_touroku.php">入社者登録（担当者用）</a></p>
    <p><a href="add_code.php">社員番号追加</a></p>
    <p><a href="d_touroku.php">本人登録確認用（CSV）</a></p>
    <p><a href="join_h.php">健康保険加入証明書用(csv)</a></p>    
    <p><a href="retire.php">退職者登録</a></p>
    <p><a href="logout.php">ログアウト</a></p>
    

  </body>
</html>
