<?php
/*
 * 被験者が選択した3Dモデルをダウンロードするページ
 * 被験者IDをGETに乗せてアクセスするとzip圧縮された3Dモデルファイルがダウンロードできる
 */
if(!isset($_GET['id']))
{
  header('./');
  exit();
}
$compressDir = '/tmp/freemodel/'.$_GET['id'];
$filename = 'modeldata'.$_GET['id'];
$zipFileSavePath = '/tmp/';

// コマンド
// cd：ディレクトリの移動
// zip:zipファイルの作成
$command = "cd ".$compressDir.";"."zip -r ".$zipFileSavePath.$fileName.".zip .";

// Linuxコマンドの実行
    exec($command);

// 圧縮したファイルをダウンロードさせる。
    header('Pragma: public');
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$fileName.".zip");
    readfile($zipFileSavePath.$fileName.".zip");

//    消す
    unlink($zipFileSavePath.$fileName.".zip");
?>
