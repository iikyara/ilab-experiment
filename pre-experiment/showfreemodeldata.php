<?php
/*
 * ������Ƃ悭�킩��Ȃ��y�[�W
 */
if(!isset($_GET['data']))
{
  header('./');
  exit();
}
$filename = '/tmp/'.$_GET['data'];
echo file_get_contents($filename);
?>
