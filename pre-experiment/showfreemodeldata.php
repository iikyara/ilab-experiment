<?php
/*
 * ちょっとよくわからないページ
 */
if(!isset($_GET['data']))
{
  header('./');
  exit();
}
$filename = '/tmp/'.$_GET['data'];
echo file_get_contents($filename);
?>
