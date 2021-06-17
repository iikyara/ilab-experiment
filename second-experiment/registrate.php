<?php
require './lib/utils.php';
/*
 * 被験者情報をデータベースへ登録するスクリプト
 * 登録完了（または失敗）したらそれを表示する
 */
//POSTメソッドでない場合，登録ページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  goPage('./');
}

//ポスト情報の格納
$username = $_POST['username'];
$password = $_POST['password'];

try{
  //データベース接続
  $pdo = connectMyDB();

  //同じユーザー名が存在するかチェック
  $stmt = $pdo->prepare('select * from otheruser where username = :username;');
  $stmt->bindValue('username', $username);
  $stmt->execute();

  //同じユーザー名が存在した場合，登録できない．
  if($stmt->fetch(PDO::FETCH_ASSOC))
  {
    //ログインページへ移動
    goPage('./registration.php?errno=1');
  }

  //ユーザー情報を登録
  $stmt = $pdo->prepare(
    "insert into otheruser (username, password, registerdate)"
   ."values(:username, :password, :regdate);"
  );
  $stmt->bindValue('username', $username);
  $stmt->bindValue('password', $password);
  $stmt->bindValue('regdate', date("Y-m-d H:i:s+09"));

  $stmt->execute();

} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//セッションIDを更新
session_regenerate_id();
//トークンを生成
updateToken();
//セッション情報をDBに登録
setSessionID($username);
//ユーザーページへ移動
goPage('./userpage.php');

?>
