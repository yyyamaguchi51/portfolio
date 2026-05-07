<?php
session_start();

// トークンがなければ生成(CSRF対策)
if(empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// セッションハイジャック対策
  session_regenerate_id(true);

// クリックジャッキング対策
  header('X-FRAME-OPTIONS:SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="m_touroku.css">
    <title>ログイン</title>
    <style>
        .info input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .guide-text {
            text-align: center;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <form action="log.php" method="post">
        <div class="main-container">
            <h2>ログイン</h2>

            <table class="info">
                <tr>
                    <th>社員番号</th>
                    <td><input type="text" name="staff_c" placeholder="000001"></td>
                </tr>               
                <tr>       
                    <th>パスワード</th>
                    <td><input type="password" name="pass" required placeholder="urashima"></td>
                </tr>            
            </table>

            <!-- ログインガイド分を追加 -->
             <p class="guide-text">
                社員番号 000001 パスワード urashima と入力してください
            </p>

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'],ENT_QUOTES,'UTF-8'); ?>">
            
            <div class="btn-container" >
            <input class="btn" type="submit" value="ログイン">
            </div>
        </div>
    </form>
</body>
</html>
