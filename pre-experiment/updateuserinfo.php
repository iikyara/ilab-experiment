<?php
/*
 * 被験者がedituserinfo.phpで編集した被験者情報をデータベースへ反映するページ
 * 成功したら完了を表示
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
$f_name = $_POST['first_name'];
$l_name = $_POST['last_name'];
$email = $_POST['mail_address'];

try{
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);

  $stmt = $pdo->prepare(
    "update userinfo set firstname = :fname, lastname = :lname, email = :email where id = :id;"
  );
  $stmt->bindValue('id', $id);
  $stmt->bindValue('fname', $f_name);
  $stmt->bindValue('lname', $l_name);
  $stmt->bindValue('email', $email);
  $stmt->bindValue('update', date("Y-m-d H:i:s+09"));
  $stmt->execute();

  if($stmt->rowCount == 0)
  {
    $msg = '編集に失敗しました．やり直してください．<br>';
    $msg .= 'id:'.$id.'<br>';
    $msg .= 'f_name:'.$f_name.'<br>';
    $msg .= 'l_name:'.$l_name.'<br>';
    $msg .= 'email:'.$email.'<br>';
    printMessage($msg);
    $pdo = null;
    exit;
  }

  $pdo = null;

  $msg = '以下の内容で編集を完了しました．<br>';
  $msg .= 'ID：'.$id.'<br>';
  $msg .= '名前：'.$l_name.' '.$f_name.'<br>';
  $msg .= 'メールアドレス：'.$email.'<br>';
  $msg .= 'IDは実験当日に思い出せるようにしておいてください．';

  printMessage($msg);
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
