<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}
  header('Location: ./second-experiment/');
	exit;
?>
ページの読み込みに失敗しました．
