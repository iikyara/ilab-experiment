<?php
/*
 * スケッチ実験時に視点情報を記録するためのスクリプト
 * ここへはスケッチ実験のページから視点情報が100件ずつ送られてくるので，被験者ごとに分けてファイルへ保存
 * 作成したファイルはherokuサーバだと30分で消えるので（スリープで消えるので）注意が必要
 */
/*
$stdout= fopen( 'php://stdout', 'w' );

fwrite( $stdout, var_export($_POST , true) );

*/

//データを準備
$isLast = $_POST['last'];	//データが最後かどうか（実験終了時に送られてくる）
$data = json_encode($_POST['data']);
//$data = ltrim($data, '{');
//$data = rtrim($data, '}');
$data = substr($data, 1, strlen($data) - 2);

//tmpフォルダに一時的に保存
$fn = $_POST['id'].'.data';
$filename = '/tmp/'.$fn;
$stdout= fopen( 'php://stdout', 'w' );
fwrite( $stdout, $filename );
if(is_writable($filename))
{
  if (!$handle = fopen($filename, 'a')) {
       echo "Cannot open file ($filename)";echo '<br>';
  }
  else {
    if($islast)
    {
      fwrite($handle, $data.'}');
    }
    else {
      fwrite($handle, $data.',');
    }
    fclose($handle);
  }
}
else
{
  if (!$handle = fopen($filename, 'w')) {
       echo "Cannot open file ($filename)";echo '<br>';
  }
  else {
    if($islast)
    {
      fwrite('{'.$handle, $data.'}');
    }
    else {
      fwrite('{'.$handle, $data.',');
    }
    fclose($handle);
  }
}

//echo file_get_contents($filename);

//成功を返す．
$return = [
  'success' => true
];

echo json_encode($return);

?>
