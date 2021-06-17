<?php
require "./lib/utils.php";

//POSTメソッドでない場合，ホームページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  goPage('./');
}

//POSTデータの取得
$username = $_POST["username"];
$password = $_POST["password"];

//セッション情報（チャレンジ値）を取得
session_start();
$challenge = $_SESSION["challenge"];

//DBからユーザー名で検索．
$db_password = '';
try{
  //データベース接続
  $pdo = connectMyDB();

  //ユーザー名をキーとして選択
  $stmt = $pdo->prepare('select * from otheruser where username=:username;');
  $stmt->bindValue('username', $username);
  $stmt->execute();
  //ユーザー名が見つかった場合
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $db_password = $result['password'];
  }
  //ユーザー名が見つからなかった場合
  else
  {
    //ユーザー名が存在しなかったら$db_password=hash("sha256", "none")とする．
    //計算することによって，ユーザー名の有無によるレスポンス速度を同じにする．
    $db_password = hash("sha256", "none");
  }

  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//チャレンジ値を含めたパスワードを計算
$ch_password = hash("sha256", $db_password.$challenge);

//パスワードの一致確認
if($password == $ch_password)
{
  //セッションIDを更新
  session_regenerate_id();
  updateToken();
  setSessionID($username);
  goPage('./userpage.php');
}
else {
  goPage('./login.php?errno=1');
}

?>
