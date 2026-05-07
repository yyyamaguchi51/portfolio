<?php
// 1. DB接続
require_once('connect_try.php');

// 2. SQL実行（テーブル結合）
// new_staffとmanage_nを名前で結合
$sql = "SELECT * FROM `new_staff` INNER JOIN `manage_n` ON `new_staff`.`name` = `manage_n`.`name`";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// 3. CSVヘッダーの準備
$csvstr = "名前,生年月日,住所１,住所２,扶養氏名,扶養生年月日,続柄,入社日,保険\r\n";

// 4. データのループ処理
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    // 続柄の変換ロジック
    $relation_text = "";
    if ($result['relation'] === '0') {
        $relation_text = "配偶者";
    } else if ($result['relation'] === '1') {
        $relation_text = "子"; 
    } else if ($result['relation'] === '2') {
        $relation_text = "親";
    } else {
        $relation_text = "その他";
    }

    // CSV一行分のデータを作成
    $csvstr .= $result['name'] . ",";
    $csvstr .= $result['birth'] . ",";
    $csvstr .= $result['addr1'] . ",";
    $csvstr .= $result['addr2'] . ",";
    $csvstr .= $result['f_name'] . ",";
    $csvstr .= $result['f_birth'] . ",";
    $csvstr .= $relation_text . ",";
    $csvstr .= $result['start_d'] . ",";
    $csvstr .= $result['health_c'] . "\r\n"; // 行末は改行のみ
}

// 5. CSV出力設定
$fileName = "staff_joined_data.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=' . $fileName);

// Excel用にShift-JISに変換して出力
echo mb_convert_encoding($csvstr, "SJIS-win", "UTF-8");

$stmt = null;
$pdo = null;
exit; // 余計な出力を防ぐため終了