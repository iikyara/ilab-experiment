<?php
/*
 * �X�P�b�`�����̎��_�����_�E�����[�h����y�[�W
 */
if(!isset($_GET['id']))
{
  header('./');
  exit();
}
$filename = '/tmp/'.$_GET['id'].'.data';
echo file_get_contents($filename);
?>
