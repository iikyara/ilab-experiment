<?php
/*
 * 被験者情報を編集する管理者ページ
 */
require "./lib/utils.php";

//DBに接続
$pdo = connectMyDB();

//トークンをチェック
if(!checkToken() || !checkAuthor($pdo))
{
  goPage("./userpage.php");
}

//トークンを更新
updateToken();

//DBとの接続を切る
$pdo = null;
?>

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
  <link rel="stylesheet" href="./css/usermanager.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <title>管理ページ | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>管理者用ページです．管理者以外の方はご退室願います．（なんで入れたし）</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="edit_area">
        <div class="line"></div>
        <div class="info">
          <a href="./questionresult.php">アンケート結果ダウンロード</a>
        </div>
        <div id="users" class="edit_form">
          <table>
            <thead>
              <tr>
                <th class="sort" data-sort="id">ID</th>
                <th class="sort" data-sort="name">名前</th>
                <th class="sort" data-sort="r-date">登録日時</th>
                <th class="sort" data-sort="u-date">更新日時</th>
                <th class="sort" data-sort="question">Complete</th>
                <th>初期化</th>
                <th>削除</th>
              </tr>
            </thead>
            <tbody id="list" class="list">
            </tbody>
          </table><br>
          <input type="button" id="testButton" value="更新">
        </div>
      </div>
    </div>
  </div>
  <script src="./js/usermanager.js"></script>
</body>
</html>
