<?php
/*
 * 被験者情報をデータベースへ登録するスクリプト
 * 登録完了（または失敗）したらそれを表示する
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
  //var_dump($pdo->getAttribute(PDO::ATTR_SERVER_VERSION));

  //$query = 'insert into userinfo(id, firstname, lastname, email) values()';
  /*
  $sql = "insert into userinfo (id, firstname, lastname, email, registerdate, updatedate) values("
    ."'abcd',"
    ."'".$_POST['first_name']."',"
    ."'".$_POST['last_name']."',"
    ."'".$_POST['mail_address']."',"
    ."'".date("Y-m-d H:i:s+09")."',"
    ."'".date("Y-m-d H:i:s+09")."'"
    .");";
  */
  $stmt = $pdo->prepare('select * from userinfo where id = :id;');
  $stmt->bindValue('id', $id);
  $stmt->execute();
  if($stmt->fetch(PDO::FETCH_ASSOC))
  {
    $msg = $_POST['grade'].'年'.$_POST['class'].'科'.$_POST['class_number'].'番は既に登録済みです．<br>';
    $msg .= '思い当たる節が無い場合は，実験監督者に連絡してください．<br>';
    $msg .= '';
    printMessage($msg);
    $pdo = null;
    exit;
  }
  $stmt = $pdo->prepare(
    "insert into userinfo (id, firstname, lastname, email, registerdate, updatedate)"
   ."values(:id, :firstname, :lastname, :email, :regdate, :update);"
  );
  $stmt->bindValue('id', $id);
  $stmt->bindValue('firstname', $f_name);
  $stmt->bindValue('lastname', $l_name);
  $stmt->bindValue('email', $email);
  $stmt->bindValue('regdate', date("Y-m-d H:i:s+09"));
  $stmt->bindValue('update', date("Y-m-d H:i:s+09"));
  //print('exec sql : '.$sql);print('<br>');
  //print('id:'.$id.'<br>');
  //print(date("Y-m-d H:i:s+09"));print('<br>');
  //$stmt = $pdo->query($sql);
  $stmt->execute();
  //var_dump($stmt);print('<br>');
  /*
  $sql = 'select firstname, lastname, email from userinfo;';
  $stmt = $pdo->query($sql);
  while($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    print('username : ');
    print($result['firstname'].' ');
    print($result['lastname'].' || ');
    print('メールアドレス : ');
    print($result['email'].'<br>');
  }*/
  $pdo = null;

  $msg = '以下の内容で登録を完了しました．<br>';
  $msg .= 'ID：'.$id.'<br>';
  $msg .= '名前：'.$l_name.' '.$f_name.'<br>';
  $msg .= 'メールアドレス：'.$email.'<br>';
  $msg .= 'IDは実験当日に思い出せるようにしておいてください．';

  printMessage($msg);
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//登録完了を表示するメソッド
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
