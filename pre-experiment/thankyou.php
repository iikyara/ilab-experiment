<?php
/*
 * スケッチ実験ありがとうございましたのページ
 */
require './lib/utils.php';
//POSTメソッドでない場合，ホームページへ飛ばす．
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

//print_r($_POST);
$id = $_POST['id'];
$startexperiment = $_POST['startexperiment'];

$pdo = connectMyDB();

$stmt = $pdo->prepare('update userinfo set startexperiment = :startexperiment where id = :id;');
$stmt->bindValue('id', $id);
$stmt->bindValue('startexperiment', $startexperiment);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <!-- キャッシュ対策用 -->
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="0" />
  <!-- キャッシュ対策用ここまで -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
  <link rel="stylesheet" href="./css/base.css">
  <link rel="stylesheet" href="./css/intro.css">
  <title>ありがとうございました！ | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>ご協力ありがとうございました！</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="intro_area">
        <div class="line"></div>
        <div class="info">
          ご協力ありがとうございました！！
        </div>
        </form>
      </div>
    </div>
  </div>
  <script src="./js/intro.js"></script>
</body>
</html>
