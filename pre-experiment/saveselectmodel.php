<?php
/*
 * 選択した3Dモデルを保存するスクリプト
 * 保存後，テストページを表示させる
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
//file_put_contents('php://stdout', var_dump($_POST));echo '<br>';
//file_put_contents('php://stdout', var_dump($_FILES));echo '<br>';

$id = $_POST['id'];
$modelNum = $_POST['modelFileNum'];

$directory_path = "/tmp/freemodeltmp";
if(!file_exists($directory_path)){
  mkdir($directory_path);
}

$directory_path = "/tmp/freemodeltmp/".$id;
if(!file_exists($directory_path)){
  mkdir($directory_path);
}

$path = '/tmp/freemodeltmp/'.$id.'/';

//一時ファイルができているか（アップロードされているか）チェック
for($i = 1; $i <= $modelNum; $i++)
{
  $filename = 'file'.strval($i);
  $tmpfilename = $_FILES[$filename]['tmp_name'];
  $targetfilename = $path.$_FILES[$filename]['name'];
  //print($tmpfilename);echo '<br>';
  //print($targetfilename);echo '<br>';
  if(is_uploaded_file($tmpfilename)){
    //一字ファイルを保存ファイルにコピーできたか
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
$filename = 'file1';
$targetfilename = $_FILES[$filename]['name'];
$fileFormat = substr($targetfilename, strrpos($targetfilename, '.') + 1);

$model = $targetfilename;
if($fileFormat == 'obj')
{
  for($i = 2; $i <= $modelNum; $i++)
  {
    $filename = 'file'.strval($i);
    $targetfilename = $_FILES[$filename]['name'];
    $format = substr($targetfilename, strrpos($targetfilename, '.') + 1);
    if($format == 'mtl')
    {
      $mtl = $targetfilename;
    }
  }
  //mtlが存在しない場合
  if(!$mtl)
  {
    echo 'mtlファイルがアップロードされていません';
    exit;
  }
}
else
{
  $subfiles = [];
  for($i = 2; $i <= $modelNum; $i++)
  {
    $filename = 'file'.strval($i);
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
  <title>モデルテストビュー</title>
</head>
<body>
  <div id="model-viewer" class="model-viewer"></div>
  <div id="viewer-message3" class="viewer-message3"></div>

  <input type="hidden" id="id" value="<?= $id ?>">
  <input type="hidden" id="fileFormat" value="<?= $fileFormat ?>">
  <input type="hidden" id="mainModelName" value="<?= $model ?>">
  <input type="hidden" id="mtlFileName" value="<?= $mtl ?>">
  <script type="application/json" id="subFileName">
    <?php echo json_encode($subfiles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
  </script>

  <script src="./three/three.js"></script>
  <script src="./three/MTLLoader.js"></script>
  <script src="./three/OBJLoader.js"></script>
  <script src="./three/TDSLoader.js"></script>
  <script src="./three/inflate.min.js"></script>
  <script src="./three/FBXLoader.js"></script>
  <script src="./three/ColladaLoader.js"></script>
  <script src="./three/OrbitControls.js"></script>
  <script src="./js/testviewer.js"></script>
</body>
</html>
