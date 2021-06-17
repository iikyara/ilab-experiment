<?php
  //ログアウト処理
  session_start();
  unset($_SESSION['token']);
  setcookie("token", '', time() - 1);
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
  <title>ログアウト | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>ログアウトしました</h1>
      <div class="home"><a href="./">HOME</a></div>
    </div>
    <div id="area1-2" class="container2">
      <div class="intro_area">
        <div class="line"></div>
        <div class="info">
          ありがとうございました．<br>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
