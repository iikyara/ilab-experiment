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
 * ページ遷移メソッド
 */
function goPage($url)
{
  header('Location: '.$url);
  exit;
}

/*
 * ランダムなトークンを生成する．
 */
function generateToken()
{
  $random = generateRandomString(10);
  return hash("sha256", $random);
}

/*
 * 指定された文字数分ランダムな文字列を返す
 */
function generateRandomString($num)
{
  $str_set = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  return substr(str_shuffle($str_set), 0, $num);
}

/*
 * トークンを更新する
 */
function updateToken()
{
  session_start();
  $_SESSION["token"] = generateToken(); //トークンを生成
  setcookie("token", $_SESSION["token"], time() + 60*60*24); //有効期限は一日
}

/*
 * ユーザーDBにセッションIDを登録
 */
function setSessionID($username)
{
  session_start();
  //DBにセッションIDを登録
  try{
    //データベース接続
    $pdo = connectMyDB();

    //ユーザー名をキーとして選択
    $stmt = $pdo->prepare('update otheruser set session=:sessionid where username=:username;');
    $stmt->bindValue('sessionid', session_id());
    $stmt->bindValue('username', $username);
    $stmt->execute();

    $pdo = null;
  } catch(PDOException $e) {
    print('Error:'.$e->getMessage());
    die();
  }
}

/*
 * ログインしているかと，
 * トークンが有効であるかをチェック
 * （有効期限が切れてないか，間違ったトークンでないか）
 */
function checkToken()
{
  //tokenが生成されていなかったらページを移動
  session_start();
  return isset($_SESSION["token"]) & $_SESSION["token"] == $_COOKIE["token"];
}

/*
 * セッションIDからユーザーIDを取得
 */
function getUserIDFromSession($pdo)
{
  //セッションを再開
  session_start();

  //ユーザーIDをセッションIDを用いて取得
  $stmt = $pdo->prepare('select userid from otheruser where session = :sessionid;');
  $stmt->bindValue('sessionid', session_id());
  $stmt->execute();

  //ユーザー情報が見つかった場合
  $id = -1;
  if($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    $id = $result['userid'];
  }
  //ユーザー情報が見つからなかった場合
  else {
    $msg = 'セッションがタイムアウトしました．<br>もう一度ログインしなおしてください．';
    printError($msg);
    exit;
  }
  return $id;
}

/*
 * 管理者認証
 */
function checkAuthor($pdo)
{
  $authorname = "takuma";

  //トークンチェックの他に，管理者認証も必要
  try{
    //ユーザーIDを取得
    $id = getUserIDFromSession($pdo);

    //クエリの作成
    $stmt = $pdo->prepare('select username from otheruser where userid=:id;');
    $stmt->bindValue("id", $id);
    $stmt->execute();
    //ユーザー情報の取得
    if($result = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      if($result['username'] == $authorname)
      {
        return true;
      }
      else
      {
        print('You are not author.');
        die();
      }
    }
    else
    {
      print('User information is not found.');
      die();
    }
  } catch(PDOException $e) {
    print('Error:'.$e->getMessage());
    die();
  }

  return false;
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
