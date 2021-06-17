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

header('Content-Type: text/html; charset=SJIS');

//DBから結果を読み込む
try{
  //クエリの作成
  $stmt = $pdo->prepare('select * from otheranswer;');
  $stmt->execute();
  //ユーザー情報の取得
  while($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    echo mb_convert_encoding($result['userid'].','.$result['questionid'].','.$result['q1'].','.$result['q2'].','.preg_replace('/\n|\r|\r\n/', '', $result['q3'] ).','.$result['update']."\n", 'sjis', 'utf-8');
  }
} catch(PDOException $e) {
  return ['error' => $e->getMessage()];
}

//トークンを更新
updateToken();

//DBとの接続を切る
$pdo = null;
?>
