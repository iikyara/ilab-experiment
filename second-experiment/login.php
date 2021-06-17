<?php
require "./lib/utils.php";
//ユーザー名とパスワードが一致しなかった時のエラー表示
$errmsg = "";
if(isset($_GET["errno"]))
{
  if($_GET["errno"] == 1){
    $errmsg = "ユーザー名またはパスワードが間違っています．<br>";
  }
}

//チャレンジ値を生成する．
$challenge = generateToken();

//セッションにチャレンジ値を保存
session_start();
$_SESSION["challenge"] = $challenge;
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
  <link rel="stylesheet" href="./css/login.css">
  <title>初めに | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>ログイン</h1>
      <div class="home">
        <a href="./">HOME</a>
      </div>
    </div>
    <div id="area1-2" class="container2">
      <div class="intro_area">
        <div class="line"></div>
        <div class="info">
          登録したユーザー名とパスワードを入力してください．
        </div>
        <div class="info info_error">
          <?=$errmsg?>
        </div>
        <form name="intro_form" class="intro_form" action="logincheck.php" method="post" onsubmit="return check()">
          <ul>
            <li>
              ユーザー名：<input type="text" name="username" value=""><br>
            </li>
            <li>
              パスワード：<input type="password" name="password" value=""><br>
            </li>
          </ul>
          <input type="submit" name="submit_button" value="ログイン">
        </form>
        <div class="info">
          ユーザー登録がまだの人はこちらから→
          <a href="./registration.php">ユーザー登録</a>
        </div>
        <input type="hidden" name="challenge" value="<?=$challenge?>">
      </div>
    </div>
  </div>
  <script src="./js/function.js"></script>
  <script src="./js/intro.js"></script>
</body>
</html>
