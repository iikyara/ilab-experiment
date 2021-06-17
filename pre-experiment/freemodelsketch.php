<?php
/*
 * スケッチ実験をするページ
 * このページは被験者が自由に3Dモデルを選択したときに遷移するページ
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

//POST情報を変数へ格納
$id = $_POST['id'];
$modelNum = $_POST['modelFileNum'];

//3Dモデルが格納されているディレクトリが存在するか確認
$directory_path = "/tmp/freemodel";
if(!file_exists($directory_path)){
  mkdir($directory_path);
}
$directory_path = "/tmp/freemodel/".$id;
if(!file_exists($directory_path)){
  mkdir($directory_path);
}

//3Dモデルファイルへのパスを格納
$path = '/tmp/freemodel/'.$id.'/';

//一時ファイルができているか（アップロードされているか）チェック
for($i = 1; $i <= $modelNum; $i++)
{
  $filename = 'modelFile'.strval($i);
  $tmpfilename = $_FILES[$filename]['tmp_name'];
  $targetfilename = $path.$_FILES[$filename]['name'];
  //print($tmpfilename);echo '<br>';
  //print($targetfilename);echo '<br>';
  if(is_uploaded_file($tmpfilename)){
    //一時ファイルを保存ファイルにコピーできたか
    if(move_uploaded_file($tmpfilename, $targetfilename)){
      //正常
      //echo "uploaded";
      //echo file_exists($targetfilename);
    }else{
      //コピーに失敗（だいたい、ディレクトリがないか、パーミッションエラー）
      echo "error while saving.:".$_FILES[$filename]['name']."<br>";
      exit;
    }
  }else{
    //そもそもファイルが来ていない。
    echo "file not uploaded.:".$_FILES[$filename]['name']."<br>";
    exit;
  }
}
//ファイル名やファイルフォーマットを取得
$filename = 'modelFile1';
$targetfilename = $_FILES[$filename]['name'];
$fileFormat = substr($targetfilename, strrpos($targetfilename, '.') + 1);

//OBJファイルの場合，MTLファイルを見つける
$model = $targetfilename;
if($fileFormat == 'obj')
{
  for($i = 2; $i <= $modelNum; $i++)
  {
    $filename = 'modelFile'.strval($i);
    $targetfilename = $_FILES[$filename]['name'];
    $format = substr($targetfilename, strrpos($targetfilename, '.') + 1);
    if($format == 'mtl')
    {
      $mtl = $targetfilename;
    }
  }
}
else
{
  $subfiles = [];
  for($i = 2; $i <= $modelNum; $i++)
  {
    $filename = 'modelFile'.strval($i);
    $targetfilename = $_FILES[$filename]['name'];
    $subfiles[] = $targetfilename;
  }
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
  <link rel="stylesheet" href="./css/sketch.css">
  <title>3Dモデルスケッチ | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>表示されている3Dモデルをスケッチしてください</h1>
    </div>
    <div id="area1-2" class="container2">
      <div id="area2-1" class="left">
        <div id="model-viewer" class="model-viewer"></div>
        <div id="viewer-message1" class="viewer-message1">
          被験者番号：<?php echo $id; ?> <br>
          モデル名：<?php echo $model; ?>
        </div>
        <div id="viewer-message2" class="viewer-message2">
          3Dビューの全画面表示の切り替え：Fキー<br>
          フルスクリーンの切り替え：F11キー
        </div>
        <div id="viewer-message3" class="viewer-message3">読み込み中...この作業は数分かかる可能性があります．</div>
      </div>
      <div id="area2-2" class="right">
        <div id="timer" class="timer">経過時間：0時間00分00秒</div><br>
        <button type="button" id="start_button" class="timer_button">開始</button>
        <button type="button" id="stop_button" class="timer_button">一時停止</button><br><br>
        <div class="description">
          -- 操作方法 --<br><br>
          <ul>
            <li>カメラ回転<ul><li>→左ドラッグ</li></ul></li>
            <li>カメラ中心移動<ul><li>→右ドラッグ</li></ul></li>
            <li>カメラリセット<ul><li>→スペースキーを押す</li></ul></li>
            <li>ズームイン・アウト<ul><li>→マウスホイール回転</li></ul></li>
          </ul>
          <br>
          <ul>
            <li>3Dビューの全画面表示・解除<ul><li>→Fキーを押す</li></ul></li>
          </ul>
        </div>
        <form class="cameraRecordForm" name="cameraRecordForm" action="thankyou.php" method="post" onsubmit="return createRecordData();">
          <input type="submit" name="submit_button" id="finish_button" class="finish_button" value="終了">
        </form>
        <br><br>
        <div id="forDebug" class="forDebug">
          <div id="click_num">
            クリック回数：0回
          </div>
          <div id="click_time">
            クリック時間：00分00秒
          </div>
          <div id="click_space">
            Spaceクリック回数：0回
          </div>
          <div id="camera_position">
            カメラ座標：x=0,y=0,z=0
          </div>
          <div id="lookat_position">
            向いてる方向：x=0,y=0,z=0
          </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="subject_number" value="<?= $id?>">
  <input type="hidden" id="id" value="<?= $id ?>">
  <input type="hidden" id="fileFormat" value="<?= $fileFormat ?>">
  <input type="hidden" id="mainModelName" value="<?= $model ?>">
  <input type="hidden" id="mtlFileName" value="<?= $mtl ?>">
  <script type="application/json" id="subFileName">
    <?php echo json_encode($subfiles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
  </script>

  <!-- Three.js r79 --
  <script src="https://cdn.rawgit.com/mrdoob/three.js/r79/build/three.js"></script>
  <!-- MTLLoader.js --
  <script src="https://cdn.rawgit.com/mrdoob/three.js/r79/examples/js/loaders/MTLLoader.js"></script>
  <!-- OBJLoader.js --
  <script src="https://cdn.rawgit.com/mrdoob/three.js/r79/examples/js/loaders/OBJLoader.js"></script>
  <!-- OrbitControls.js --
  <script src="https://cdn.rawgit.com/mrdoob/three.js/r79/examples/js/controls/OrbitControls.js"></script>
-->
  <script src="./three/three.js"></script>
  <script src="./three/MTLLoader.js"></script>
  <script src="./three/OBJLoader.js"></script>
  <script src="./three/TDSLoader.js"></script>
  <script src="./three/inflate.min.js"></script>
  <script src="./three/FBXLoader.js"></script>
  <script src="./three/ColladaLoader.js"></script>
  <script src="./three/OrbitControls.js"></script>
  <script src="./js/freemodelsketch.js"></script>
  <script src="./js/script.js"></script>
</body>
</html>
