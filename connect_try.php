<?php
// DB接続情報
$dbname = 'mysql:host=localhost;dbname=staff;charset=utf8';
$id = 'root';
$pw = '';

try {
    // 接続オプションを設定
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    
          // エラー時に例外を投げる（デバッグしやすくなる）
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        // 結果を連想配列で受け取る
        PDO::ATTR_EMULATE_PREPARES => false,             
        // SQLインジェクション対策を強化
    ];

    $pdo = new PDO($dbname, $id, $pw, $options);
    
} catch (PDOException $e) {
    // エラーメッセージの表示（開発環境用）
    exit('データベース接続失敗：' . $e->getMessage());
}

//  PHPのみのファイルなので、//phpのみなので閉じの？とカッコは書かない
//（意図しない空白出力を防ぐため）