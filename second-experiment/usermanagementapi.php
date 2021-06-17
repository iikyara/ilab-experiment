<?php
require "./lib/utils.php";

//DBに接続
$pdo = connectMyDB();

$return = [
  'result' => null,
  'message' => '',
  'success' => false
];

//トークンをチェック
if(!checkToken() || !checkAuthor($pdo))
{
  echo json_encode($return);
  exit();
}

//opで指定された操作を実行
if(isset($_POST['op']))
{
  $operation = $_POST['op'];
  if($operation == 'load')
  {
    $return['result'] = loadAllUserInfo($pdo);
    $return['success'] = true;
  }
  else if($operation == 'initPass')
  {
    $return['result'] = initPass($pdo);
    $return['success'] = true;
  }
  else if($operation == 'deleteUser')
  {
    $return['result'] = deleteUser($pdo);
    $return['result']['users'] = loadAllUserInfo($pdo);
    $return['success'] = true;
  }
}

if(isset($return['result']['error']))
{
  $return['message'] = $return['return']['error'];
  $return['success'] = false;
}

echo json_encode($return);

//トークンを更新
updateToken();

$pdo = null;

//全ユーザーの情報を読み込むAPI
function loadAllUserInfo($pdo){
  $users = [];
  $template = [
    'userid' => 'id',
    'username' => 'name',
    'registerdate' => 'r-date',
    'questiondate' => 'u-date',
    'isquestion' => 'question'
  ];

  //テストユーザー
  /*
  $result = [
    [
      'userid' => 5,
      'username' => 'takumaaa',
      'registerdate' => date("Y-m-d H:i:s+09"),
      'questiondate' => date("Y-m-d H:i:s+09"),
      'isquestion' => true
    ],
    [
      'userid' => 17,
      'username' => 'sammansamosumosu',
      'registerdate' => date("Y-m-d H:i:s+09"),
      'questiondate' => date("Y-m-d H:i:s+09"),
      'isquestion' => false
    ],
    [
      'userid' => 1554,
      'username' => 'marattakotta',
      'registerdate' => date("Y-m-d H:i:s+09"),
      'questiondate' => date("Y-m-d H:i:s+09"),
      'isquestion' => false
    ]
  ];
  */

  //DBから読み込み
  try{
    //クエリの作成
    $stmt = $pdo->prepare('select * from otheruser;');
    $stmt->execute();
    //ユーザー情報の取得
    if($result = $stmt->fetchAll(PDO::FETCH_ASSOC))
    {
      for($i = 0; $i < count($result); $i++)
      {
        $users[$i] = [];
        foreach($template as $key => $value)
        {
          $users[$i][$value] = $result[$i][$key];
        }
      }
    }
    else {
      $users = [];
    }
  } catch(PDOException $e) {
    return ['error' => $e->getMessage()];
  }

  return $users;
}

//指定されたユーザーIDのパスワードを初期化するAPI
function initPass($pdo)
{
  $return = [];
  if(isset($_POST['id']))
  {
    $id = $_POST['id'];
    //DBから読み込み
    $password = generateRandomString(8);
    try{
      //クエリの作成
      $stmt = $pdo->prepare('update otheruser set password=:pw where userid=:id;');
      $stmt->bindValue('pw', hash("sha256", $password));
      $stmt->bindValue('id', $id);
      $stmt->execute();
      //失敗した場合，
      if($stmt->rowCount() == 0)
      {
        $return['success'] = false;
        $return['error'] = strval($id).' : The userid is not found.';
      }
      //成功した場合
      else
      {
        $return['success'] = true;
        $return['password'] = $password;
      }
    } catch(PDOException $e) {
      return ['error' => $e->getMessage()];
    }
  }
  else {
    $return['success'] = false;
    $return['error'] = 'Id is not found.';
  }
  return $return;
}

//指定されたユーザー情報を削除するAPI
function deleteUser($pdo)
{
  if(isset($_POST['id']))
  {
    $id = $_POST['id'];
    try{
      //クエリの作成
      $stmt = $pdo->prepare('delete from otheruser where userid=:id;');
      $stmt->bindValue('pw', hash("sha256", $password));
      $stmt->bindValue('id', $id);
      $stmt->execute();
      //失敗した場合，
      if($stmt->rowCount() == 0)
      {
        $return['success'] = false;
        $return['error'] = strval($id).' : The userid is not found.';
      }
      //成功した場合
      else
      {
        $return['success'] = true;
        $return['deleteID'] = $id;
      }
    } catch(PDOException $e) {
      return ['error' => $e->getMessage()];
    }
  }
  else {
    $return['success'] = false;
    $return['error'] = 'Id is not found.';
  }
  return $return;
}
?>
