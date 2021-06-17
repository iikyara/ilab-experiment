<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}
	$uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/pre-experiment/home.html');
	exit;
?>
ページの読み込みに失敗しました．
