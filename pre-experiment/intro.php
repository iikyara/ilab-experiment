<?php
/*
 * 被験者IDを入力する実験開始前のページ
 * 3Dモデルが割り当てられている被験者はsketch.phpのページへ飛んでスケッチ実験を開始
 * 3Dモデルが割り当てられていない被験者はselectmodel.phpのページへ飛んで3Dモデルを選んでからスケッチ実験を開始
 */
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
  <link rel="stylesheet" href="./css/intro.css">
  <title>初めに | 市川研究室 被験者実験1</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>実験監督者の指示に従って以下の欄を埋めてください．</h1>
    </div>
    <div id="area1-2" class="container2">
      <div class="intro_area">
        <div class="line"></div>
        <form name="intro_form" class="intro_form" action="selectmodel.php" method="post" onsubmit="return check()">
          <ul>
            <li>
              指定された被験者IDを入力してください<br>
              <input type="text" name="subject_number" value="0"><br>
            </li>
            <!--
            <br>
              指定された被験者IDを入力してください<br>
              <input type="text" name="subject_number" min="4" max="4" value="0"><br>
            </li>
            <!--
            <br>
            <li>
              指定された項目にチェックを入れてください．<br>
              <?php
              /*
              $m_names = glob('./3dmodel/*.obj');
              if(empty($m_names))
              {
                echo '実験用3Dモデルが存在しません．実験監督者にお知らせください．';
              }
              else
              {
                foreach ($m_names as &$m_name) {
                  $name = str_replace('.obj', '', $m_name);
                  $name = str_replace('./3dmodel/', '', $name);
                  $str = '<input type="radio" name="model_name" value="'
                         .$name.'" id="'.$name.'">';
                  $str = $str.'<label for="'.$name.'">モデル名「'.$name.'」</label><br>';
                  echo $str;
                }
              }
              */
              ?>
            </li><br>
            -->
          </ul>
          <input type="submit" name="submit_button" value="ログイン">
        </form>
      </div>
    </div>
  </div>
  <script src="./js/intro.js"></script>
</body>
</html>
