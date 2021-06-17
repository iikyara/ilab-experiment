<?php
/*
 * アンケート回答をデータベースへ保存するスクリプト
 * 完了したら再度アンケートページを表示させる
 */
require './lib/utils.php';

//POSTメソッドでない場合，introページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
  } else {
    $uri = 'http://';
  }
  $uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/');
  exit;
}

$snum = $_POST['subject_number'];
$acsCnt = $_POST['access_count'];
$a1 = $_POST['q1'];
$a2 = $_POST['q2'];
$a3 = $_POST['q3'];
$id = $snum;

//IDから対応モデルを読み込む
try{
  //データベース接続
  $pdo = connectMyDB();

  $stmt = $pdo->prepare('select * from answer where id=:id and number=:acsCnt;');
  $stmt->bindValue('id', $id);
  $stmt->bindValue('acsCnt', $acsCnt);
  $stmt->execute();
  //登録済みの場合
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $stmt = $pdo->prepare('update answer set q1=:a1, q2=:a2, q3=:a3 where id=:id and number=:acsCnt');
  }
  //登録されていない場合
  else
  {
    $stmt = $pdo->prepare('insert into answer(id, q1, q2, q3, number) values(:id, :a1, :a2, :a3, :acsCnt);');
  }

  $stmt->bindValue('id', $id);
  $stmt->bindValue('a1', $a1);
  $stmt->bindValue('a2', $a2);
  $stmt->bindValue('a3', $a3);
  $stmt->bindValue('acsCnt', $acsCnt);
  $stmt->execute();
  if($stmt->rowCount() == 0)
  {
    $msg = 'アンケートデータの登録に失敗しました<br />実験監督者にお知らせください．';
    printError($msg);
    $pdo = null;
    exit;
  }
  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//登録に成功したらquestion.phpに戻す
$acsCnt = intval($acsCnt) + 1;
$url = 'question.php';
$data = [
  'subject_number' => $id,
  'access_count' => $acsCnt
];
$content = http_build_query($data, '', '&');

$header = [
  'Content-Type: application/x-www-form-urlencoded',
  'Content-Length: '.strlen($content)
];
$context = [
  'http' => [
    'ignore_errors' => true,
    'method' => 'POST',
    'header' => implode("\r\n", $header),
    'content' => $content
  ]
];
$url = 'http://ilab-experiment.herokuapp.com/pre-experiment/question.php';
$res = file_get_contents($url, false, stream_context_create($context));
echo $res;
exit;

?>
