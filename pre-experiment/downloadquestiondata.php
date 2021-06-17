<?php
/*
 * アンケートデータを表示するページ
 * データベースからアンケートを読みだしてくる
 */
require './lib/utils.php';

//データベース接続
$pdo = connectMyDB();

//クエリの作成
$stmt = $pdo->prepare('select * from answer;');
$stmt->bindValue('id', $id);
$stmt->execute();

$header = 'id, number, q1, q2, q3<br />';
echo $header;

while($result = $stmt->fetch(PDO::FETCH_ASSOC))  //登録されている場合
{
  $data = '';
  $data .= $result['id'].',';
  $data .= $result['number'].',';
  $data .= $result['q1'].',';
  $data .= $result['q2'].',';
  $data .= $result['q3'].'<br />';
  echo $data;
}
?>
