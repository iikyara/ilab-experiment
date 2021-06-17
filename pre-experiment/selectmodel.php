<?php
/*
 * 3Dモデルを選択するページ
 * １．3Dモデルを探してきてファイルを選択
 * ２．saveselectmodel.phpに3Dモデルを送信
 * ３．テストページを別ウィンドウで表示
 * ４．テスト成功なら，freemodelsketch.phpに移動してスケッチ実験開始
 */
//POSTメソッドでない場合，ホームへ飛ばす．
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
$id = $_POST['subject_number'];
$f_name;
$l_name;
$m_name;
$success = true;
$title = '';
$msg = '';

try{
  //データベース接続
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);

  //クエリの作成
  $stmt = $pdo->prepare('select * from userinfo where id = :id;');
  $stmt->bindValue('id', $id);
  $stmt->execute();

  if($result = $stmt->fetch(PDO::FETCH_ASSOC))  //登録されている場合
  {
    $f_name =  $result['firstname'];
    $l_name = $result['lastname'];
    $m_name = $result['modelname'];
    $title = 'ログイン成功';
  }
  else                                          //登録されていない場合
  {
    $title = 'ログイン失敗';
    $success = false;
  }

  //選択ではない場合
  if(preg_match('/^select/', $m_name, $matches) !== 1)
  {
    $url = 'sketch.php';
    $data = [
      'subject_number' => $id,
      'select' => 'false'
    ];
    $content = http_build_query($data, '', '&');

    $header = [
      'Content-Type: application/x-www-form-urlencoded',
      'Content-Length: '.strlen($content)
    ];
    $context = [
      'http' => [
        'ignore_errors' => true,
        'method' => 'POST',
        'header' => implode("\r\n", $header),
        'content' => $content
      ]
    ];
    $url = 'http://ilab-experiment.herokuapp.com/pre-experiment/sketch.php';
    $res = file_get_contents($url, false, stream_context_create($context));
    echo $res;
    exit;
  }

  //DBとの接続を切る
  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//モデルの読み込み
$m_names = glob('./3dmodel/*.obj');
for ($i = 0; $i < count($m_names); $i++) {
  $m_names[$i] = str_replace('.obj', '', $m_names[$i]);
  $m_names[$i] = str_replace('./3dmodel/', '', $m_names[$i]);
}
$m_names = array_merge([''], $m_names, ['select']);
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
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
  <link rel="stylesheet" href="./css/base.css">
  <link rel="stylesheet" href="./css/setmodel.css">
  <title>初めに | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>
        <?php if($success): ?>
          <!-- ログイン成功 -->
          モデルを選んできてください．
        <? else: ?>
        <!-- ログイン失敗 -->
        ログイン失敗
      <?php endif; ?>
    </h1>
  </div>
  <div id="area1-2" class="container2">
    <div class="edit_area">
      <div class="line"></div>
      <div class="info">
        <?php if($success): ?>
          <!-- ログイン成功 -->
          あなたには自由に選んできたモデルをスケッチしてもらいます．<br>
          以下のWebサイトからモデルを選んできてください．<br>
          <ul>
            <li>
              <a href="https://www.3dcadbrowser.com/" target="_blank">3D CAD BROWSER</a><br>
              乗り物系の3Dモデルを見ることができる．<br>
              一部有料（無料検索機能あり）<br>
            </li>
            <li>
              <a href="https://3dwarehouse.sketchup.com/" target="_blank">3D Warehouse</a><br>
              家具系の3Dモデルを見ることができる．<br>
              全て無料である．<br>
            </li>
            <li>
              <a href="https://free3d.com/" target="_blank">free3d</a><br>
              様々な3Dモデルを見ることができる．<br>
              無料と有料が混ざっている．（無料のみで検索する場合，一覧でしか表示できない．）<br>
            </li>
            <li>
              <a href="http://artist-3d.com/" target="_blank">Artist 3D Model</a><br>
              ほぼすべてのジャンルの3Dモデルがそろっている．<br>
              検索しにくい・・・<br>
            </li>
            <li>
              <a href="https://archive3d.net/" target="_blank">archive3d</a><br>
              インテリア系のモデルがそろっている<br>
              3dx形式でダウンロード可能<br>
            </li>
          </ul>
          ダウンロードしたファイルは下にアップロードしてください．<br>
          必要なファイルを入れたら，チェックボタンを押して使用可能なものかチェックをしてください．<br>
          <form id="selectModelForm" name="selectModelForm" action="freemodelsketch.php" method="post" enctype="multipart/form-data">
            <div id="modelfiles">
              モデル本体のファイル：
              <input type="file" name="modelFile1"><br>
              その他のファイルを添付する<button type="button" id="addFileButton"name="addFileButton">＋</button>
              <button type="button" id="subFileButton"name="subFileButton">ー</button><br>
            </div><br>
            <button type="button" id="checkButton" name="checkButton">チェック</button><br>
            <input type="submit" id="submitButton" name="submitButton" value="実験を開始する"><br>
            <input type="hidden" name="modelFileNum" value="1">
            <input type="hidden" name="id" value="<?= $id ?>">
          </form>
        <?php else: ?>
          <!-- ログイン失敗 -->
          ID:<?= $id ?>は登録されていません．<br>
          もう一度確認の上，実験を開始してください．<br>
          <a href="/">戻る</a><br>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript" src="./js/selectmodel.js"></script>
</body>
</html>
