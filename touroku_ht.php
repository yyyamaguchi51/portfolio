<?php
session_start();
if(empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="touroku.css">
    <title>本人登録</title>
</head>
<body>
    <form action="touroku.php" method="post">
        <div class="main-container">
    <h2>本人情報登録</h2>
    <p>数字は半角英数字で入力してください</p>
    <table class="info">
        <tr>
            <th>ふりがな</th>
            <td><input type="text" name="furigana" placeholder="やまだ　たろう" required></td>
        </tr>
        <tr>
            <th>氏名</th>
            <td><input type="text" name="name" placeholder="山田　太郎" required></td>
        </tr>
        <tr>
            <th>生年月日</th>
            <td><input type="date" name="birth" value="1986-06-15" required></td>
        </tr>
        <tr>
            <th>性別</th>
            <td><label><input type="radio" name="sex" value="0" required> 男</label> &emsp; <label><input type="radio" name="sex" value="1">女</label></td>
        </tr>
        <tr>
            <th>郵便番号</th>
            <td><input type="text" name="post_c" placeholder="060000" onKeyUp="AjaxZip3.zip2addr(this,'','addr1', 'addr1');" required>
            </td>
        </tr>
        <tr>
            <th>住所</th>
            <td><textarea name="addr1" placeholder ="住所が自動入力" required></textarea></td>
        </tr>
        <tr>
            <th>住所2</th>
            <td><textarea name="addr2" placeholder="建物名"></textarea></td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td><input type="text" name="tel" placeholder="09011112222" required></td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td><input type="email" name="mail" size="60" required></td>
        </tr>
        <tr>
            <th>パスワード</th>
            <td><input type="password" name="pass" id="password_input" placeholder="8文字以上の半角英数字" required>
            <div style="margin-top: 5px;">
                <label><input type="checkbox" id="show_password"> パスワードを表示する</label>
            </div>    
            </td>
        </tr>
    </table>

    <hr style="margin: 40px 0; border-top: 1px solid #949393;">
                
    <h3>扶養家族登録</h3>
        <div class="no_family-check">
        <label>
            <input type="checkbox" name="no_family" id="no_family" value="1">
            <strong>扶養家族はいません(下記入力不要)</strong>
        </label>
    </div>
    <p>扶養家族のいる方は、入社日当日に健康保険の<br>
        加入証明書をお渡しするために必要です。</p>
        <table class="family" id="family_table">
        <tr>
            <th>ふりがな</th>
            <td><input type="text" name="f_furigana" placeholder="やまだ　たろう"></td>
        </tr>
        <tr>
            <th>氏名</th>
            <td><input type="text" name="f_name" placeholder="山田　太郎"></td>
        </tr>
            <tr>
            <th>生年月日</th>
            <td><input type="date" name="f_birth" value="1986-06-15"></td>
        </tr>
        <tr>
            <th>性別</th>
            <td><label><input type="radio" name="f_sex" value="0">男</label> &emsp; 
                <label><input type="radio" name="f_sex" value="1">女</label>
            </td>
        </tr>
        <tr>
            <th>続柄</th>
            <td>
                <label><input type="radio" name="relation" value="0">配偶者</label>
                <label><input type="radio" name="relation" value="1">子</label>
                <label><input type="radio" name="relation" value="3">親</label>
                <label><input type="radio" name="relation" value="4">その他</label>
            </td>
        </tr>
        <tr>
            <th>年収</th>
            <td><input type="text" name="income" placeholder="手取りではなく年収を"></td>
        </tr>
    </table>

    <div class="btn-container">  
    <input class="btn" type="submit" value="送信">
    </div>
    </div>
</form>
<script>
    // パスワード表示切替
        const checkbox = document.getElementById('show_password');
        const passwordInput = document.getElementById('password_input')
        checkbox.addEventListener('change', function() {
            if(this.checked){
                passwordInput.type ='text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>    
</body>
</html>


http://localhost//portfolio/touroku.php