<?php
/*
 * 被験者が被験者情報を変更するページ
 * 編集した情報はupdateuserinfo.phpへ送信
 */
//POSTメソッドでない場合，登録ページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
  } else {
    $uri = 'http://';
  }
  $uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/pre-experiment/registration.html');
  exit;
}

//ポスト情報の格納
$id = $_POST['grade'].$_POST['class'].str_pad($_POST['class_number'], 2, 0, STR_PAD_LEFT);
$grade = $_POST['grade'];
$class = $_POST['class'];
$c_number = $_POST['class_number'];

try{
  //データベース接続
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);

  //クエリの作成
  $stmt = $pdo->prepare('select * from userinfo where id = :id;');
  $stmt->bindValue('id', $id);
  $stmt->execute();
  //登録されていない場合
  if(!($result = $stmt->fetch(PDO::FETCH_ASSOC)))
  {
    $msg = 'まだ登録していません．<br>';
    $msg .= '登録ページはこちら→<a href="./registration.html">登録ページ</a>';
    printMessage($msg);
    exit;
  }

  //DBとの接続を切る
  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

function printMessage($msg)
{
  $text = <<<EOT
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
    <title>登録完了 | 市川研究室 被験者実験1</title>
  </head>
  <body>
    <div class="container1">
      <div id="area1-1" class="title">
        <h1>登録ページ</h1>
      </div>
      <div id="area1-2" class="container2">
        <div class="regist_area">
          <div class="line"></div>
          <div class="info">
EOT;
  $text .= $msg;
  $text .= <<<EOT
          </div>
        </div>
      </div>
    </div>
  </body>
  </html>
EOT;
  echo $text;
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
  <title>登録情報編集ページ | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>登録情報編集ページ</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="regist_area">
        <div class="line"></div>
        <form name="edit_form" class="regist_form info" action="updateuserinfo.php" method="post">
          <ul>
            <li>
              学年：
              <select name="grade" required>
                <?php
                $val = [1, 2, 3, 4, 5, 0];
                foreach($val as $v)
                {
                  $isSelected = '';
                  if(intval($v) == $grade)
                  {
                    $isSelected = ' selected';
                  }
                  $textContent = $v.'年生';
                  if($v == 0)
                  {
                    $textContent = 'その他';
                  }
                  echo '<option value="'.$v.'"'.$isSelected.'>'.$textContent.'</option>';
                }
                ?>
              </select><br>
            </li>
            <li>
              クラス：
              <select name="class" required>
                <?php
                $val = array(
                  'M' => '機械工学科',
                  'E' => '電気工学科',
                  'S' => '電子制御工学科',
                  'I' => '情報工学科',
                  'C' => '物質工学科',
                  'X' => 'その他'
                );
                foreach($val as $key => $v)
                {
                  $isSelected = '';
                  if($key == $class)
                  {
                    $isSelected = ' selected';
                  }
                  $textContent = $v;
                  echo '<option value="'.$key.'"'.$isSelected.'>'.$textContent.'</option>';
                }
                ?>
              </select>
              <br>
            </li>
            <li>
              出席番号：<input type="number" name="class_number" min="1" max="99" value="<?= $c_number ?>" required><br>
            </li>
            <li>
              姓：<input type="text" name="last_name" required><br>
            </li>
            <li>
              名：<input type="text" name="first_name" required><br>
            </li>
            <li>
              メールアドレス：<input type="email" name="mail_address" required><br>
            </li>
          </ul>
          <input type="submit" name="submit_button" value="編集完了">
        </form>
      </div>
    </div>
  </div>
</body>
</html>
