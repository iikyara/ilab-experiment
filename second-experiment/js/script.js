/*
 * sketch.phpとfreemodelsketch.phpで使用するスクリプト
 * 主にタイマーのボタンに関するリスナーの設定
 */

//htmlのidからデータを取得
//取得したデータを変数に代入
var timer = document.getElementById('timer');
var start = document.getElementById('start_button');
var stop = document.getElementById('stop_button');
var finish = document.getElementById('finish_button');
//クリック時の時間を保持するための変数定義
var startTime;
//経過時刻を更新するための変数。 初めはだから0で初期化
var elapsedTime = 0;
//タイマーを止めるにはclearTimeoutを使う必要があり、そのためにはclearTimeoutの引数に渡すためのタイマーのidが必要
var timerId;
//タイマーをストップ -> 再開させたら0になってしまうのを避けるための変数。
var timeToadd = 0;

start.disabled = true;
stop.disabled = true;
finish.disabled = true;

//ミリ秒の表示ではなく、分とか秒に直すための関数, 他のところからも呼び出すので別関数として作る
//計算方法として135200ミリ秒経過したとしてそれを分とか秒に直すと -> 02:15:200
function updateTimetText(){
  var h = Math.floor(elapsedTime / 3600000);
  //m(分) = 135200 / 60000ミリ秒で割った数の商　-> 2分
  var m = Math.floor(elapsedTime / 60000) - h * 60;
  //s(秒) = 135200 % 60000ミリ秒で / 1000 (ミリ秒なので1000で割ってやる) -> 15秒
  var s = Math.floor(elapsedTime % 60000 / 1000);
  //ms(ミリ秒) = 135200ミリ秒を % 1000ミリ秒で割った数の余り
  var ms = elapsedTime % 1000;

  //HTML 上で表示の際の桁数を固定する　例）3 => 03　、 12 -> 012
  //javascriptでは文字列数列を連結すると文字列になる
  //文字列の末尾2桁を表示したいのでsliceで負の値(-2)引数で渡してやる。
  m = ('0' + m).slice(-2);
  s = ('0' + s).slice(-2);
  //HTMLのid　timer部分に表示させる　
  timer.textContent = '経過時間：' + h + '時間' + m + '分' + s + '秒';
}


//再帰的に使える用の関数
function countUp(){
  //timerId変数はsetTimeoutの返り値になるので代入する
  timerId = setTimeout(function(){
    //経過時刻は現在時刻をミリ秒で示すDate.now()からstartを押した時の時刻(startTime)を引く
    elapsedTime = Date.now() - startTime + timeToadd;
    updateTimetText();
    //countUp関数自身を呼ぶことで10ミリ秒毎に以下の計算を始める
    countUp();
    //1秒以下の時間を表示するために10ミリ秒後に始めるよう宣言
  },10);
}

//startボタンにクリック時のイベントを追加(タイマースタートイベント)
start.addEventListener('click',function(){
  //在時刻を示すDate.nowを代入
  startTime = Date.now();
  //再帰的に使えるように関数を作る
  countUp();

  start.disabled = true;
  stop.disabled = false;
  finish.disabled = false;
});

//stopボタンにクリック時のイベントを追加(タイマーストップイベント)
stop.addEventListener('click',function(){
  //タイマーを止めるにはclearTimeoutを使う必要があり、そのためにはclearTimeoutの引数に渡すためのタイマーのidが必要
  clearTimeout(timerId);
  //タイマーに表示される時間elapsedTimeが現在時刻かたスタートボタンを押した時刻を引いたものなので、
  //タイマーを再開させたら0になってしまう。elapsedTime = Date.now - startTime
  //それを回避するためには過去のスタート時間からストップ時間までの経過時間を足してあげなければならない。elapsedTime = Date.now - startTime + timeToadd (timeToadd = ストップを押した時刻(Date.now)から直近のスタート時刻(startTime)を引く)
  timeToadd += Date.now() - startTime;

  start.disabled = false;
  stop.disabled = true;
  finish.disabled = false;
});

// マウスクリックのリスナーを設定
var click_num = document.getElementById('click_num');
var click_time = document.getElementById('click_time');
var click_num_cnt = 0;
var click_startTime;
var click_timerId;
var click_elapsedTime = 0;
var click_timeToadd = 0;
var isClicked = false;
addEventListener('mousedown', function (e){
  if(!isClicked)
  {
    click_num_cnt += 1;
    click_num.textContent = "クリック回数：" + click_num_cnt + "回";
    click_startTime = Date.now();
    click_countUp();
    isClicked = true;
  }
});

addEventListener('mouseup', function (e){
  if (isClicked) {
    clearTimeout(click_timerId);
    click_timeToadd += Date.now() - click_startTime;
    isClicked = false;
  }
});

function click_countUp(){
  //timerId変数はsetTimeoutの返り値になるので代入する
  click_timerId = setTimeout(function(){
    //経過時刻は現在時刻をミリ秒で示すDate.now()からstartを押した時の時刻(startTime)を引く
    click_elapsedTime = Date.now() - click_startTime + click_timeToadd;
    var h = Math.floor(click_elapsedTime / 3600000);
    //m(分) = 135200 / 60000ミリ秒で割った数の商　-> 2分
    var m = Math.floor(click_elapsedTime / 60000) - h * 60;
    //s(秒) = 135200 % 60000ミリ秒で / 1000 (ミリ秒なので1000で割ってやる) -> 15秒
    var s = Math.floor(click_elapsedTime % 60000 / 1000);
    //ms(ミリ秒) = 135200ミリ秒を % 1000ミリ秒で割った数の余り
    var ms = click_elapsedTime % 1000;

    //HTML 上で表示の際の桁数を固定する　例）3 => 03　、 12 -> 012
    //javascriptでは文字列数列を連結すると文字列になる
    //文字列の末尾2桁を表示したいのでsliceで負の値(-2)引数で渡してやる。
    m = ('0' + m).slice(-2);
    s = ('0' + s).slice(-2);
    ms = ('00' + ms).slice(-3);
    //HTMLのid　timer部分に表示させる　
    document.getElementById('click_time').textContent = 'クリック時間：' + m +'分' + s + '秒' + ms;
    //countUp関数自身を呼ぶことで10ミリ秒毎に以下の計算を始める
    click_countUp();
    //1秒以下の時間を表示するために10ミリ秒後に始めるよう宣言
  },10);
}
