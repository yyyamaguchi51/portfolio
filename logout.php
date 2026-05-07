<?php
    session_start();

    // セッション変数をすべて解除
    $_SESSION = array();

    // セッションクッキーを削除（ブラウザ側の鍵を無効化）
    if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000,'/');
    }
    // セッションを破棄
    session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト</title>
    <style>
        body{
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: sans-serif;
        }
        .logout-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            width: 300px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
    <p>ログアウトしました</p>
    <a href="index.html" class="btn">メニュー画面へ</a>
    </div>
</html>