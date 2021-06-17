<?php
$errmsg = "";
if(isset($_GET["errno"]))
{
  if($_GET["errno"] == 1)
  $errmsg = "既に使用されているユーザー名です．<br>";
}
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
  <link rel="stylesheet" href="./css/registration.css">
  <title>登録ページ | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>登録ページ</h1>
      <div class="home">
        <a href="./">HOME</a>
      </div>
    </div>
    <div id="area1-2" class="container2">
      <div class="regist_area">
        <div class="line"></div>
        <div class="info">
          被験者情報を登録してください．<br>
          ユーザ名，パスワードはどちらも5文字以上にしてください．<br>
          ユーザ名は他人と重複するものは使用できませんのでご了承ください．<br>
        </div>
        <div class="info info_error">
          <?=$errmsg?>
        </div>
        <form name="regist_form" class="regist_form info" action="registrate.php" method="post" onsubmit="return check()">
          <ul>
            <li>
              ユーザ名　　　　　：　<input type="text" name="username" required><br>
            </li>
            <li>
              パスワード　　　　：　<input type="password" name="password" required><br>
            </li>
            <li>
              パスワード（再度）：　<input type="password" name="password2" required><br>
            </li>
          </ul>
          <input type="submit" name="submit_button" value="登録">
        </form>
      </div>
    </div>
  </div>
  <script src="./js/function.js"></script>
  <script src="./js/registration.js"></script>
</body>
</html>
