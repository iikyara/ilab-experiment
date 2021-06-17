<?php
/*
 * アンケート回答をデータベースへ保存するスクリプト
 * 完了したら再度アンケートページを表示させる
 */
require './lib/utils.php';

//DBに接続
$pdo = connectMyDB();

//POSTメソッドでない場合，introページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  goPage('./');
}

//POST情報を取得
$questionid = $_POST['questionid'];
$a1 = $_POST['q1'];
$a2 = $_POST['q2'];
$a3 = $_POST['q3'];

//IDから対応モデルを読み込む
try{
  //ユーザーIDを取得
  $id = getUserIDFromSession($pdo);

  //登録済みかどうかを調べる
  $stmt = $pdo->prepare('select * from otheranswer where userid=:userid and questionid=:questionid;');
  $stmt->bindValue('userid', $id);
  $stmt->bindValue('questionid', $questionid);
  $stmt->execute();
  //登録済みの場合
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $stmt = $pdo->prepare('update otheranswer set q1=:a1, q2=:a2, q3=:a3, update=:update where userid=:userid and questionid=:questionid;');
  }
  //登録されていない場合
  else
  {
    $stmt = $pdo->prepare('insert into otheranswer(userid, questionid, q1, q2, q3, update) values(:userid, :questionid, :a1, :a2, :a3, :update);');
  }

  $stmt->bindValue('userid', $id);
  $stmt->bindValue('questionid', $questionid);
  $stmt->bindValue('a1', $a1);
  $stmt->bindValue('a2', $a2);
  $stmt->bindValue('a3', $a3);
  $stmt->bindValue('update', date("Y-m-d H:i:s+09"));
  $stmt->execute();
  if($stmt->rowCount() == 0)
  {
    $msg = 'アンケートデータの登録に失敗しました<br />実験監督者にお知らせください．';
    printError($msg);
    $pdo = null;
    exit;
  }
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//DBから切断
$pdo = null;

//登録に成功したらquestion.phpに戻す
//次の番号を取得
$nextquestionid = intval($questionid) + 1;

//最後に到達
if($nextquestionid == 36)
{
  goPage('./questionlist.php');
}

goPage('./question.php?q='.$nextquestionid);

?>
