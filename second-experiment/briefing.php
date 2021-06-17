<?php
/*
 * アンケートを実施するページ
 * 元の3Dモデルと作成された3Dモデル，スケッチが並んでいる
 * アンケートはすべての3Dモデルに対して答えるまで繰り返し行われる（再帰的にこのページへアクセス）
 */
require './lib/utils.php';

//トークンをチェック
/*
if(!checkToken())
{
  goPage('./');
}
*/

//アンケート番号を用いてDBから，対応モデルの情報を取ってくる．
$sketchid = '5I05';
$modelname = '1';

//各種変数にファイル名の情報を格納
$model1 = 'original.obj';
$model2 = 'production'.$modelname.'.obj';
$image = '../pre-experiment/questionModel/'.$sketchid.'/sketch'.$modelname.'.png';
$mtl = 'mat.mtl';

//アンケートの設定
$q1 = ['', '', '', ''];
$q2 = ['', '', '', ''];
$q3 = '';

//トークンを更新
//updateToken();

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
  <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
  <link rel="stylesheet" href="./css/base.css">
  <link rel="stylesheet" href="./css/briefing.css">
  <title>説明 | 市川研究室 被験者実験2</title>
</head>
<body>
  <div class="container1">
    <div id="area1-1" class="title">
      <h1>操作説明</h1>
    </div>
    <div id="area1-2">
      <div class="line"></div>
      <div class="info">
        実験にご協力いただきありがとうございます．このページは以下のような構成となっております．<br>
        全て読んでからアンケートにご協力ください．
        <ol class="tableOfContents">
          <li>実験の説明</li>
          <li>アンケートページの見方</li>
          <li>アンケート一覧ページの使い方</li>
          <li>その他注意点（必ずお読みください）</li>
        </ol>
      </div>
      <div class="subtitle">
        １．実験の説明
      </div>
      <div class="info">
        この実験を何のためにやっているのか，今回の実験では何をするのかを説明します．
      </div>
      <div class="container3 info">
        <div class="area3-1" style="min-width: 500px; max-width: 500px;">
          <img src="./img/briefing_explain1.png" alt="この実験の説明１" style="border: 2px black solid; width: 500px;"><br>
        </div>
        <div class="area3-2">
          この実験の最終目標は，手書きスケッチを3Dモデルに自動変換することです．<br>
          図のようにスケッチを描くと，3Dモデルが出力されるというものを目指しています．<r>
        </div>
      </div>
      <div class="info">
        そこで今回は以下のような実験をします．
      </div>
      <div class="container3 info">
        <div class="area3-1" style="min-width: 500px; max-width: 500px;">
          <img src="./img/briefing_explain2.png" alt="この実験の説明２" style="border: 2px black solid; width: 500px;"><br>
        </div>
        <div class="area3-2">
          前回の実験では，「元の3Dモデル」を見て被験者がスケッチをしたのちに，その「スケッチ」を見ながら別の被験者が「3Dモデル」を作成しました．<br>
          前回もアンケートは実施したのですが，スケッチをした被験者のみで実施したため，データが不足しております．<br>
          そこで今回，貴方にご協力いただきたい所存でございます．<br>
          アンケートの内容は，
          <ol class="tableOfContents">
            <li>作成した3Dモデルは元の3Dモデルが再現されているか？</li>
            <li>作成した3Dモデルはスケッチが再現されているか？</li>
            <li>作成した3Dモデルについて「もっとこうしてほしい」「ここは良かった」などの点を記述してください．</li>
          </ol>
          というようになっております．
        </div>
      </div>
      <div class="info">
        少し長めのアンケートとなっておりますがご協力よろしくお願いします．
      </div>
      <div class="subtitle">
        ２．アンケートページの見方
      </div>
      <div class="info">
        アンケートページの見方の説明をします．
      </div>
      <div class="container3 info">
        <div class="area3-1">
          <img src="./img/briefing_question1.png" alt="アンケート画面の説明１"><br>
        </div>
        <div class="area3-2">
          <br>
          アンケート画面は図のような構成となっております．<br>
          左から，基の3Dモデル，スケッチを基に作成された3Dモデル，基の3Dモデルのスケッチです．<br>
          下半分はアンケートの回答場所となっております．<br>
          貴方が感じたことを正直にアンケートにご回答ください．
        </div>
      </div>
      <div class="container3 info">
        <div class="area3-1">
          <img src="./img/briefing_question2.png" alt="アンケート画面の説明２"><br>
        </div>
        <div class="area3-2">
          アンケート内容は文章だけだとわかりにくいので，図にしました．<br>
          この図を覚えておけば，アンケート時に困ることはないと思います．
        </div>
      </div>
      <div class="container3 info">
        <div class="area3-1">
          <img src="./img/briefing_question3.png" alt="アンケート画面の説明３"><br>
        </div>
        <div class="area3-2">
          アンケート画面の操作方法です．<br>
          3Dモデルはマウスで動かすことができます．<br>
          次の操作ができます．<br>
          <ol class="tableOfContents">
            <li>カメラ回転　　：マウスの左ドラッグ</li>
            <li>カメラ移動　　：マウスの右ドラッグ</li>
            <li>拡大縮小　　　：マウスホイール</li>
            <li>カメラリセット：スペースキー</li>
            <li>画面拡大　　　：調整中です．現在使用できません．</li>
          </ol>
          3Dモデルの操作は画面にカーソルを乗せることでできます．<br><br>

          アンケートの回答が終了したら，画面下の送信ボタンを押してください．<br>
          また，アンケートの回答状況は右上のボタンを押すことでいつでも確認できます．<br>
          この際，現在入力しているアンケートは削除されてしまうので，送信ボタンを押してから右上のボタンを押して下さい．
        </div>
      </div>
      <div class="subtitle">
        ３．アンケート一覧ページの使い方
      </div>
      <div class="container3 info">
        <div class="area3-1">
          <img src="./img/briefing_questionlist.png" alt="アンケート一覧の説明"><br>
        </div>
        <div class="area3-2">
          アンケートの回答状況を一覧で見ることができるページです．<br>
          アンケートの回答をすべて終えると，上の「アンケートを終了する」ボタンを押すことができます．<br>
          アンケートを途中からやり直したい場合は，そのアンケート番号の行の最右列の「ここからアンケートを再開する」というリンクをクリックしてください．<br>
        </div>
      </div>
      <div class="subtitle">
        ４．その他注意点
      </div>
      <div class="info">
        <ul style="list-style-type: square; margin-left: 2em;">
          <li>アンケートは全部で36個です．</li>
          <li>途中でやめても回答状況は記録されているので，ログインし直せば再開できます．</li>
          <li>
            従量課金制の通信でアンケートをする場合はご注意ください．<br>
            3Dモデルをダウンロードするので，全部で約130MB程の通信が発生します．
          </li>
        </ul>
      </div>
      <a href="./question.php?q=0" class="lastbutton">アンケートを始める</a>
    </div>
  </div>
</body>
</html>
