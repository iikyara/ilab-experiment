<?php
/*
 * アンケートを実施するページ
 * 元の3Dモデルと作成された3Dモデル，スケッチが並んでいる
 * アンケートはすべての3Dモデルに対して答えるまで繰り返し行われる（再帰的にこのページへアクセス）
 */
require './lib/utils.php';

//トークンをチェック
if(!checkToken())
{
  goPage('./');
}

//DBに接続
$pdo = connectMyDB();

//GETでアンケート番号（モデル番号）を取得
if(isset($_GET["q"]))
{
  $questionid = $_GET["q"];
}
else {
  $questionid = 0;
}

//アンケート番号を用いてDBから，対応モデルの情報を取ってくる．
$sketchid = '';
$modelname = '';
try{
  $stmt = $pdo->prepare('select * from modelinfo where modelid=:id ;');
  $stmt->bindValue('id', $questionid);
  $stmt->execute();
  //登録済みの場合
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $sketchid = $result['productorid'];
    $modelname = substr($result['productionid2'], 10);
  }
  else {
    $msg = '3Dモデルの読み込みに失敗しました．<br>実験監督者に連絡してください．';
    printError($msg);
    exit;
  }
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//IDから対応モデルを読み込む
$directory_path = '../pre-experiment/questionModel/'.$sketchid;
if(!file_exists($directory_path)){
  $msg = 'ディレクトリの読み込みに失敗しました．<br>実験監督者に連絡してください．';
  printError($msg);
  exit;
}

//モデルファイルのディレクトリ
$path = $directory_path.'/';

//モデルファイルの存在をチェック
if(!file_exists($path.'production'.$modelname.'.obj')){
  $msg = 'モデルファイルの読み込みに失敗しました．<br>実験監督者に連絡してください．';
  printError($msg);
  exit;
}

//各種変数にファイル名の情報を格納
$model1 = 'original.obj';
$model2 = 'production'.$modelname.'.obj';
$image = '../pre-experiment/questionModel/'.$sketchid.'/sketch'.$modelname.'.png';
$mtl = 'mat.mtl';

//回答済みアンケートの読み込み
$q1 = ['', '', '', ''];
$q2 = ['', '', '', ''];
$q3 = '';

try{
  //ユーザーIDを取得
  $id = getUserIDFromSession($pdo);

  //既に回答済みか調べる．
  $stmt = $pdo->prepare('select * from otheranswer where userid=:userid and questionid=:questionid;');
  $stmt->bindValue('userid', $id);
  $stmt->bindValue('questionid', $questionid);
  $stmt->execute();

  //回答済みの場合，前回の回答を表示
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $q1[intval($result['q1']) - 1] = ' checked';
    $q2[intval($result['q2']) - 1] = ' checked';
    $q3 = $result['q3'];
  }
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//トークンを更新
updateToken();

//DBから切断
$pdo = null;
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
  <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
  <link rel="stylesheet" href="./css/base.css">
  <link rel="stylesheet" href="./css/question.css">
  <title>アンケート | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>アンケートへのご協力をお願いします(アンケート<?= $questionid+1 ?> / 36個)</h1>
      <div class="home">
        <a href="./questionlist.php">アンケート一覧</a>
      </div>
    </div>
    <div id="area1-2" class="container2">
      <div id="area2-1" class="container3">
        <div id="area3-1" class="left viewer-zone">
          <div id="model-viewer1" class="model-viewer"></div>
          <div id="viewer-message1_1" class="viewer-message1">
            基のモデル
          </div>
          <div id="viewer-message1_2" class="viewer-message2">
            Fキー：画面いっぱいにモデルを表示
          </div>
          <div id="viewer-message1_3" class="viewer-message3">
            0%
          </div>
        </div>
        <div id="area3-2" class="right viewer-zone">
          <div id="model-viewer2" class="model-viewer"></div>
          <div id="viewer-message2_1" class="viewer-message1">
            スケッチを基に作られたモデル
          </div>
          <div id="viewer-message2_2" class="viewer-message2">
            Fキー：画面いっぱいにモデルを表示
          </div>
          <div id="viewer-message2_3" class="viewer-message3">
            0%
          </div>
        </div>
        <div id="area3-3" class="right2 viewer-zone sketch-zone">
          <img class="sketch-image" src="<?= $image ?>" alt="Chromeで開いてね">
        </div>
      </div>
      <div id="area2-2">
        <div class="question">
          <form name="questionForm" action="saveanswer.php" method="post">
            <p class="questionLabel">スケッチを基にして作られたモデルは元のモデルが再現されていると思いますか？</p><br>
            <p class="answerLabel">
              <label><input type="radio" name="q1" value="1"<?= $q1[0] ?>>そう思う</label>
              <label><input type="radio" name="q1" value="2"<?= $q1[1] ?>>どちらかといえばそう思う</label>
              <label><input type="radio" name="q1" value="3"<?= $q1[2] ?>>どちらかといえばそう思わない</label>
              <label><input type="radio" name="q1" value="4"<?= $q1[3] ?>>そう思わない</label>
            </p>
            <br>
            <p class="questionLabel">スケッチを基にして作られたモデルはスケッチを再現していると思いますか？</p><br>
            <p class="answerLabel">
              <label><input type="radio" name="q2" value="1"<?= $q2[0] ?>>そう思う</label>
              <label><input type="radio" name="q2" value="2"<?= $q2[1] ?>>どちらかといえばそう思う</label>
              <label><input type="radio" name="q2" value="3"<?= $q2[2] ?>>どちらかといえばそう思わない</label>
              <label><input type="radio" name="q2" value="4"<?= $q2[3] ?>>そう思わない</label>
            </p>
            <br>
            <p class="questionLabel">スケッチを基にして作られたモデルについて，「もっとこうして欲しい」，「ここは良く再現されていた」などの点を教えてください．</p><br>
            <p class="answerLabel">
              <textarea name="q3" rows="8" cols="80"><?= $q3 ?></textarea>
            </p>
            <input type="hidden" name="questionid" value="<?= $questionid ?>">
            <input type="submit" name="submitButton" value="送信">
          </form>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="sketchid" value="<?= $sketchid ?>">
  <input type="hidden" id="model1_name" value="<?= $model1 ?>">
  <input type="hidden" id="model2_name" value="<?= $model2 ?>">
  <input type="hidden" id="sketch" value="<?= $image ?>">
  <input type="hidden" id="mtl_name" value="<?= $mtl ?>">

<script src="./three/three.js"></script>
<script src="./three/MTLLoader.js"></script>
<script src="./three/OBJLoader.js"></script>
<script src="./three/OrbitControls.js"></script>
<script src="./js/question.js"></script>

</body>
</html>
