<?php
/*
 * スケッチ実験の視点情報をダウンロードするページ
 */
if(!isset($_GET['id']))
{
  header('./');
  exit();
}
$filename = '/tmp/'.$_GET['id'].'.data';
echo file_get_contents($filename);
?>
