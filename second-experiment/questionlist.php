<?php
require './lib/utils.php';

//トークンをチェック
if(!checkToken())
{
  goPage('./');
}

//DBに接続
$pdo = connectMyDB();

//回答状況を取得
$questions = [];
$isComplete = True;
try{
  //ユーザーIDを取得
  $id = getUserIDFromSession($pdo);

  //既に回答済みか調べる．
  $stmt = $pdo->prepare('select * from otheranswer where userid=:userid order by questionid;');
  $stmt->bindValue('userid', $id);
  $stmt->execute();

  //回答済みの場合，前回の回答を表示
  $result = $stmt->fetchAll();
  $j = 0;
  for($i = 0; $i < 36; $i+=1)
  {
    if(isset($result[$j]) && $result[$j]['questionid'] == $i)
    {
      $questions[] = [
        'id' => $i + 1,
        'isAnswered' => True,
        'q1' => $result[$j]['q1'],
        'q2' => $result[$j]['q2'],
        'q3' => $result[$j]['q3']
      ];
      $j += 1;
    }
    else
    {
      $questions[] = [
        'id' => $i + 1,
        'isAnswered' => False,
        'q1' => '',
        'q2' => '',
        'q3' => ''
      ];
      $isComplete = False;
    }
  }
} catch(PDOException $e) {
  print('Error:'.$e->getMessage());
  die();
}

//トークンを更新
updateToken();

//DBから切断
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
  <link rel="stylesheet" href="./css/questionlist.css">
  <title>回答状況 | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>アンケートの回答状況</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="edit_area">
        <div class="line"></div>
        <input type="button" name="submitbutton" value="アンケートを終了する">
        <div class="question_table">
          <table>
            <tr>
              <th>番号</th>
              <th>回答状況</th>
              <th>質問１</th>
              <th>質問２</th>
              <th>質問３</th>
              <th></th>
            </tr>
            <?php
            foreach ($questions as $question) {
              echo '<tr>';
              //id
              echo '<td>'.$question['id'].'</td>';
              //isAnswered
              if($question['isAnswered'])
              {
                echo '<td><font color="#3BAF75">回答済み</font></td>';
              }
              else
              {
                echo '<td><font color="#FF0000">未回答</font></td>';
              }
              //q1
              echo '<td>'.$question['q1'].'</td>';
              //q2
              echo '<td>'.$question['q2'].'</td>';
              //q3
              echo '<td>'.$question['q3'].'</td>';
              //アンケート再開用URL
              echo '<td><a href="./question.php?q='.strval($question['id']-1).'">ここからアンケートを再開する</a></td>';

              echo '</tr>';
            }
            ?>
          </table><br>
        </div>
        <input type="hidden" name="isComplete" value="<?= strval($isComplete) ?>">
      </div>
    </div>
  </div>
  <script src="./js/questionlist.js"></script>
</body>
</html>
