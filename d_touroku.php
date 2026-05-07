<?php
//DB接続情報
    require_once('connect_try.php');
    
        $sql = "SELECT * FROM `new_staff` ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        
//CSV文字列生成
//csv形式は「,」で次のセル「\r\n」で改行となる
        $csvstr = "社員番号,ふりがな,名前,生年月日,性別,郵便番号,住所１,住所２,電話番号,メールアドレス,パスワード,扶養家族,扶養家族ふりがな,扶養家族氏名,扶養家族生年月日,扶養家族性別,関係,収入\r\n";
        while ($result = $stmt->fetch
        (PDO::FETCH_ASSOC)) {
            //性別を表示するように変換
            //本人
              $sex_text= ($result['sex'] === '0') ? "男":"女";
              
            //扶養家族
            $no_family_text= ($result['no_family'] == 0) ? "あり": "なし"; //0があり、１がなし
            //扶養家族の性別
              $f_sex_text= ($result['f_sex'] === '0') ?"男" : "女";
             
            //続柄を表示するように変換 
                if($result['relation'] === '0') {
                    $relation_text = "配偶者";
                } else if ($result['relation'] === '1') {
                    $relation_text = "子"; 
                } else if ($result['relation'] === '2') {
                    $relation_text = "親";
                } else {
                    $relation_text = "その他";
                }

            //CSV文字列生成
            $csvstr .= $result['staff_c'] . ",";
            $csvstr .= $result['furigana'] . ",";
            $csvstr .= $result['name'] . ",";
            $csvstr .= $result['birth'] . ",";
            $csvstr .= $sex_text . ",";
            $csvstr .= $result['post_c'] . ",";
            $csvstr .= $result['addr1'] . ",";
            $csvstr .= $result['addr2'] . ",";
            $csvstr .= $result['tel'] . ",";
            $csvstr .= $result['mail'] . ",";
            $csvstr .= $result['pass'] . ",";
            $csvstr .= $no_family_text . ",";
            $csvstr .= $result['f_furigana'] . ",";
            $csvstr .= $result['f_name'] . ",";
            $csvstr .= $result['f_birth'] . ",";
            $csvstr .= $result['f_birth'] . ",";
            $csvstr .= $f_sex_text . ",";                            
            $csvstr .= $relation_text . ",";
            $csvstr .= $result['income'] . ",";
            
            $csvstr .="\r\n";
        }

    // CSV出力
        //ダウンロードファイル名作成
        $fileName = "new_staff.csv";
        //ダウンロードファイル形式指定
        header('Content-Type: text/csv');
        //ダウンロードファイル名指定
        header('Content-Disposition: attachment;filename='.$fileName);
        //ダウンロードデータ出力
    //  echo $csvstr; //UTF8出力の場合こちらを使用
        echo mb_convert_encoding($csvstr, "SJIS","UTF-8");// Shift_JISに変換したい場合のみ(excelはShift-JIS)
        $stmt=null;
        $pdo=null;
?>
        