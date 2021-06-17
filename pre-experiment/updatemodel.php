<?php
/*
 * 被験者情報管理ページで編集した結果をデータベースへ反映
 * その後，被験者情報編集完了を表示
 */
//POSTメソッドでない場合，登録ページへ飛ばす．
if($_SERVER['REQUEST_METHOD'] != 'POST'){
  if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
  } else {
    $uri = 'http://';
  }
  $uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/');
  exit;
}

//ポスト情報の格納
//print_r($_POST); echo '<br>';
$updates = [];
foreach($_POST as $key => $post){
  $data = explode(':', $key);
  if(count($data)!=2){
    continue;
  }
  $id = $data[0];
  $head = $data[1];
  $updates[$id][$head] = $post;
}

//print_r($updates); echo '<br>';

try{
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);

  $msg = '';
  $ctl_name = '';
  foreach($updates as $key => $value)
  {
    if($value['isDelete']=='delete')
    {
      $ctl_name = '削除';
      //echo 'delete：ID['.$key.']<br>';
      $stmt = $pdo->prepare(
        "delete from userinfo where id = :id;"
      );
      $stmt->bindValue('id', $key);
    }
    else
    {
      $ctl_name = '更新';
      //echo 'update：ID['.$key.']<br>';
      $stmt = $pdo->prepare(
        "update userinfo set modelname = :m_name where id = :id;"
      );
      $stmt->bindValue('id', $key);
      $stmt->bindValue('m_name', $value['modelname']);
    }
    $stmt->execute();
    //echo 'rowCount:'.$stmt->rowCount().'<br>';
    if($stmt->rowCount() == 0) {
      $msg .= '失敗：ID['.$key.']('.$ctl_name.')<br>';
    } else {
      $msg .= '成功：ID['.$key.']('.$ctl_name.')<br>';
    }
  }

  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
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
  <title>被験者情報管理ページ | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>登録情報編集ページ</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="regist_area">
        <div class="line"></div>
        <div class="info">
          以下の様に完了しました．<br>
          <?= $msg ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
