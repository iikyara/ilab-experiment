<?php
/*
 * ‚¿‚å‚Á‚Æ‚æ‚­‚í‚©‚ç‚È‚¢ƒy[ƒW
 */
if(!isset($_GET['data']))
{
  header('./');
  exit();
}
$filename = '/tmp/'.$_GET['data'];
echo file_get_contents($filename);
?>
