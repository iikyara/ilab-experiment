<?php
/*
 * データベースに接続するメソッド
 */
function connectMyDB()
{
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);
  return $pdo;
}

/*
 * エラーページを表示するメソッド
 */
function printError($errorMsg)
{
  printInfo('エラーページ', 'エラー', $errorMsg);
}

/*
 * 何らかのメッセージを表示するページを作成するメソッド
 */
function printInfo($title, $head, $msg)
{
  $text = <<<EOT
  <!DOCTYPE html>
  <html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <!-- キャッシュ対策用 -->
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <!-- キャッシュ対策用ここまで -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/registration.css">
    <title>
EOT;
  $text .= $title;
  $text .= <<<EOT
    </title>
  </head>
  <body>
    <div class="container1">
      <div id="area1-1" class="title">
        <h1>
EOT;
  $text .= $head;
  $text .= <<<EOT
        </h1>
      </div>
      <div id="area1-2" class="container2">
        <div class="regist_area">
          <div class="line"></div>
          <div class="info">
EOT;
  $text .= $msg;
  $text .= <<<EOT
          </div>
        </div>
      </div>
    </div>
  </body>
  </html>
EOT;
  echo $text;
}
?>
