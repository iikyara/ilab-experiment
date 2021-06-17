<?php
/*
 * 被験者情報を編集する管理者ページ
 * [できること]
 * ・被験者ごとの3Dモデルを割り当てる
 * ・被験者実験結果をダウンロード
 * ・被験者が選んだ3Dモデルをダウンロード
 */
//糞みたいな認証
if($_GET['who'] != 'takuma'){
  if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
  } else {
    $uri = 'http://';
  }
  $uri .= $_SERVER['HTTP_HOST'];
  header('Location: '.$uri.'/');
  exit;
}

//ポスト情報の格納
$users = [];

try{
  //データベース接続
  $url = parse_url(getenv('DATABASE_URL'));
  $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
  $pdo = new PDO($dsn, $url['user'], $url['pass']);

  //クエリの作成
  $stmt = $pdo->prepare('select * from userinfo;');
  $stmt->execute();
  //登録されていない場合
  while($result = $stmt->fetch(PDO::FETCH_ASSOC))
  {
    print_r($result);echo '<br />';
    $users[] = [
      'id' => $result['id'],
      'firstname' => $result['firstname'],
      'lastname' => $result['lastname'],
      'email' => $result['email'],
      'modelname' => $result['modelname'],
      'startexperiment' => $result['startexperiment'],
      'updatedate' => $result['updatedate'],
      'registerdate' => $result['registerdate']
    ];
  }

  //DBとの接続を切る
  $pdo = null;
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//モデルの読み込み
$m_names = glob('./3dmodel/*.obj');
for ($i = 0; $i < count($m_names); $i++) {
  $m_names[$i] = str_replace('.obj', '', $m_names[$i]);
  $m_names[$i] = str_replace('./3dmodel/', '', $m_names[$i]);
}
$m_names = array_merge([''], $m_names, ['select']);
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
  <link rel="stylesheet" href="./css/setmodel.css">
  <title>被験者情報管理ページ | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>登録情報編集ページ</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="edit_area">
        <div class="line"></div>
        <form name="edit_form" class="edit_form info" action="updatemodel.php" method="post" onsubmit="return check()">
          <div id="users">
            <table>
              <thead>
                <tr>
                  <th class="sort" data-sort="id">ID</th>
                  <th class="sort" data-sort="name">名前</th>
                  <th class="sort" data-sort="email">メールアドレス</th>
                  <th>担当モデル名</th>
                  <th class="sort" data-sort="e-date">実験日時</th>
                  <th class="sort" data-sort="u-date">更新日時</th>
                  <th class="sort" data-sort="r-date">登録日時</th>
                  <th>実験データ</th>
                  <th>モデルデータ</th>
                  <th>削除</th>
                </tr>
              </thead>
              <tbody class="list">
                <?php
                foreach ($users as $user) {
                  echo '<tr>';
                  echo '<td class="id">'.$user['id'].'</td>';
                  echo '<td class="name">'.$user['lastname'].' '.$user['firstname'].'</td>';
                  echo '<td class="email">'.$user['email'].'</td>';
                  //モデル選択リストを作成
                  echo '<td><select name="'.$user['id'].':modelname">';
                  foreach ($m_names as &$m_name) {
                    $isSelected = '';
                    if($m_name == $user['modelname'])
                    {
                      $isSelected = ' selected';
                    }
                    echo '<option value="'.$m_name.'"'.$isSelected.'>'.$m_name.'</option>';
                  }
                  echo '</select></td>';
                  echo '<td class="e-date">'.$user['startexperiment'].'</td>';
                  echo '<td class="u-date">'.$user['updatedate'].'</td>';
                  echo '<td class="r-date">'.$user['registerdate'].'</td>';
                  if(file_exists('/tmp/'.$user['id'].'.data')) //.$user['id'].'.data'
                  {
                    echo '<td><a href="./showedata.php?id='.$user['id'].'">ファイル</a></td>';
                  }
                  else
                  {
                    echo '<td>なし</td>';
                  }
                  if(file_exists('/tmp/freemodel/'.$user['id'])) //.$user['id'].'.data'
                  {
                    echo '<td><a href="./downloadmodeldata.php?id='.$user['id'].'">ファイル</a></td>';
                  }
                  else
                  {
                    echo '<td>なし</td>';
                  }
                  echo '<td><input type="checkbox" name="'.$user['id'].':isDelete" value="delete"></td>';
                  echo '</tr>';
                }
                ?>
              </tbody>
            </table><br>
          </div>
          <input type="submit" name="submit_button" value="編集完了">
        </form>
        <?php
        /*
        echo 'file1<br>';
        $filename = '/app/tmp/5I18.data';
        echo file_get_contents($filename);
        echo '<br>';
        echo 'file2<br>';
        $filename = '/tmp/5I18.data';
        echo file_get_contents($filename);
        echo '<br>';
        */
        ?>
      </div>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
  <style>
    .sort.desc:after {
      content:"▼";
    }
    .sort.asc:after {
      content:"▲";
    }
  </style>

  <script type="text/javascript">
    var options = {
      valueNames: [ 'id', 'name', 'email', 'e-date', 'u-date', 'r-date' ]
    };
    var userList = new List('users', options);
    userList.sort( 'id', {order : 'asc' });

    function check()
    {
      var form = document.forms.edit_form;
      var message = "以下の被験者を削除します．\n";
      var isMsg = false;

      for(var i in form.elements)
      {
        if(!form.elements[i].name)
        {
          continue;
        }
        var name = form.elements[i].name.toString();
        var split = name.split(':');
        if(split.length != 2)
        {
          continue;
        }
        var id = split[0];
        var head = split[1];
        if(head == 'isDelete')
        {
          if(form.elements[i].checked)
          {
            message += "ID : " + id + "\n";
            isMsg = true;
          }
        }
      }
      if (isMsg) {
        if(!window.confirm(message))
        {
          return false;
        }
      }
    }
  </script>
</body>
</html>
