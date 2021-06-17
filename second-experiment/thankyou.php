<?php
/*
 * スケッチ実験ありがとうございましたのページ
 */
require './lib/utils.php';
//トークンをチェック
if(!checkToken())
{
  goPage("./userpage.php");
}

//DBに接続
$pdo = connectMyDB();

//ユーザー情報をDBからとってくる
try{
  //ユーザーIDを取得
  $id = getUserIDFromSession($pdo);

  //アンケート完了時刻を記録
  $stmt = $pdo->prepare('update otheruser set questiondate=:questiondate, isquestion=:isquestion where userid=:id;');
  $stmt->bindValue('id', $id);
  $stmt->bindValue('questiondate', date("Y-m-d H:i:s+09"));
  $stmt->bindValue('isquestion', "True");
  $stmt->execute();
  if($stmt->rowCount() == 0)
  {
    $msg = 'エラーが発生しました．<br>実験監督者に連絡してください．';
    printError($msg);
    exit;
  }
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//tokenを更新
updateToken();

//DBから切断
$pdo = null;
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
  <title>ありがとうございました！ | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>ご協力ありがとうございました！</h1>
      <div class="home">
        <a href="./userpage.php">ユーザーページへ戻る</a>
      </div>
    </div>
    <div id="area1-2" class="container2">
      <div class="intro_area">
        <div class="line"></div>
        <div class="info">
          ご協力ありがとうございました！！<br><br>
          アンケートを修正したい場合は以下のURLにアクセスしてください．<br>
          <a href="./questionlist.php">アンケート回答一覧</a>
        </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
