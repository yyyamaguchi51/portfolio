<?php
// エラー表示設定（開発時のみ）
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// クリックジャッキング対策
header('X-FRAME-OPTIONS:SAMEORIGIN');

//POSTチェックとCSRFトークン検証
if($_SERVER['REQUEST_METHOD'] !=='POST') {
    exit('直接アクセスは禁止です');
}
if(empty($_POST['token']) || $_POST['token'] !== $_SESSION['token']){
    exit('不正なリクエストです');
}

// DB接続
require_once('connect_try.php');

// 2.データの受け取り
$staff_c=$_POST['staff_c'] ?? '';
$pass = $_POST['pass']?? '';
$errors = '';
 
    //SQL実行
    try{
        // manage_n(m)テーブルとnew_staff(n)を名前で結合
        // パスワードと名前を両方取得
    $sql = "SELECT name, pass FROM new_staff WHERE staff_c = :staff_c";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":staff_c",$staff_c);
    $stmt->execute();
    $result=$stmt->fetch();

    if($result) {
        // ハッシュ化されたパスワードと比較
        // 入力されたパスワードとDBに保存されているパスワードの照合
        if(password_verify($pass, $result["pass"])){

        //ログイン成功時の処理
        // セッションハイジャック対策でIDを更新
        session_regenerate_id(true);

        $_SESSION['staff_c']= $staff_c; 
        $_SESSION['name']= $result['name'];

        // 成功したらメニュー画面（index.php）へ
        header("Location: index.php");
            exit;

        } else {
            $errors='パスワードが違います';
        }
        } else {
            $errors ='社員番号が登録されていないか、間違っています';
        }
    } catch (PDOException $e) {
            //.$e->getMessage()は運用時表示させない
            $errors = 'エラーが発生しました:' .$e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="m_touroku.css">
    <title>ログインエラー</title> 
</head>
<body>
    <div class="main-container">
   <?php if ($errors): ?>
        <div class="error_list" style="color: red; margin-bottom: 20px;">
        <p><?php echo htmlspecialchars($errors, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <p><a href="login.php" class="btn" style="text-decoration: none; display: inline-block; text-align: center;">ログイン画面に戻る</a></p>
    <?php endif; ?>
    </div>
</body>
</html>

