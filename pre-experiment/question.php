<?php
/*
 * アンケートを実施するページ
 * 元の3Dモデルと作成された3Dモデル，スケッチが並んでいる
 * アンケートはすべての3Dモデルに対して答えるまで繰り返し行われる（再帰的にこのページへアクセス）
 */
require './lib/utils.php';

//POSTメソッドでない場合，introページへ飛ばす．
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

$snum = $_POST['subject_number'];
$acsCnt = $_POST['access_count'];
$id = $snum;

//IDから対応モデルを読み込む
$directory_path = 'questionModel/'.$id;
if(!file_exists($directory_path)){
  $msg = 'ユーザ情報が見つかりませんでした．<br /> 実験監督者に連絡してください';
  printError($msg);
  exit;
}

$path = $directory_path.'/';

if(!file_exists($path.'production'.$acsCnt.'.obj')){
  if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
  } else {
    $uri = 'http://';
  }
  $uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/pre-experiment/thankyouquestion.php');
  exit;
}

$model1 = 'original.obj';
$model2 = 'production'.$acsCnt.'.obj';
$image = 'questionModel/'.$id.'/sketch'.$acsCnt.'.png';
$mtl = 'mat.mtl';

//回答済みアンケートの読み込み
//データベース接続
$pdo = connectMyDB();

//クエリの作成
$stmt = $pdo->prepare('select * from answer where id=:id and number=:acsCnt;');
$stmt->bindValue('id', $id);
$stmt->bindValue('acsCnt', $acsCnt);
$stmt->execute();

$q1 = ['', '', '', ''];
$q2 = ['', '', '', ''];
$q3 = '';

if($result = $stmt->fetch(PDO::FETCH_ASSOC))  //登録されている場合
{
  $q1[intval($result['q1']) - 1] = ' checked';
  $q2[intval($result['q2']) - 1] = ' checked';
  $q3 = $result['q3'];
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
      <h1>アンケートへのご協力をお願いします(アンケート<?= $acsCnt ?>)</h1>
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
            <p class="questionLabel">スケッチを基にして作られたモデルは基のモデルが再現されていると思いますか？</p><br>
            <p class="answerLabel">
              <label><input type="radio" name="q1" value="1"<?= $q1[0] ?>>そう思う</label>
              <label><input type="radio" name="q1" value="2"<?= $q1[1] ?>>どちらかといえばそう思う</label>
              <label><input type="radio" name="q1" value="3"<?= $q1[2] ?>>どちらかといえばそう思わない</label>
              <label><input type="radio" name="q1" value="4"<?= $q1[3] ?>>そう思わない</label>
            </p>
            <br>
            <p class="questionLabel">スケッチを基にして作られたモデルはあなたがスケッチした絵を再現していると思いますか？</p><br>
            <p class="answerLabel">
              <label><input type="radio" name="q2" value="1"<?= $q2[0] ?>>そう思う</label>
              <label><input type="radio" name="q2" value="2"<?= $q2[1] ?>>どちらかといえばそう思う</label>
              <label><input type="radio" name="q2" value="3"<?= $q2[2] ?>>どちらかといえばそう思わない</label>
              <label><input type="radio" name="q2" value="4"<?= $q2[3] ?>>そう思わない</label>
            </p>
            <br>
            <p class="questionLabel">スケッチを基にして作られたモデルについて，「もっとこうして欲しかった」，「ここは良く再現されていた」などの点を教えてください．</p><br>
            <p class="answerLabel">
              <textarea name="q3" rows="8" cols="80"><?= $q3 ?></textarea>
            </p>
            <input type="hidden" name="access_count" value="<?= $acsCnt ?>">
            <input type="hidden" name="subject_number" value="<?= $snum ?>">
            <input type="submit" name="submitButton" value="送信">
          </form>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="subject_number" value="<?= $snum ?>">
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
