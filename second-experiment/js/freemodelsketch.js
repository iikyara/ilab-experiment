/*
 * freemodelsketch.phpで使用するスクリプトファイル
 * 主にsketch.jsと一緒（three.jsの扱いが難しかったため，このようにしてファイルを分けた．改善の余地あり）
 */

/*
 * 描画に必要な変数の定義
 */
var mesh, camera, scene, renderer, mouse2D, controls,
radius = 150,
theta = 45,
phi = 0,
width = document.getElementById('area2-1').clientWidth,
height = document.getElementById('area2-1').clientHeight,
lastAnimTime = window.performance.now(),
RENDER_INTERVAL = 30,
TICK_INTERVAL = 500;

/*
 * 参照すべき3Dモデルの置き場所を格納する変数
 */
var dataPath = 'freemodel/' + document.getElementById('id').value + '/';
var assetsPath = './showfreemodeldata.php?data=' + encodeURIComponent(dataPath);
var modelName = assetsPath + encodeURIComponent(document.getElementById('mainModelName').value);
var objName = encodeURIComponent(document.getElementById('mainModelName').value);
var mtlName = encodeURIComponent(document.getElementById('mtlFileName').value);
var modelFormat = document.getElementById('fileFormat').value;

/*
 * 視点情報の記録をサーバに送信するときに，一度に送信する件数
 */
var MAX_RECORD = 100;

/*
 * デバッグ情報を表示するかを切り替える
 * 被験者実験の時は表示したままにしてしまった（笑）
 */
var isDebug = false;
if(isDebug) {
  document.getElementById('forDebug').style.display = 'block';
} else {
  document.getElementById('forDebug').style.display = 'none';
}

/*
 * 3Dモデルの描画
 */
init();
// start();
render();

/*
 * 描画の準備とモデルの読み込み
 */
function init() {
  // renderer ------------------------------
  renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setClearColor( 0x222222 ); // 背景色
  renderer.setSize( width, height );
  //document.body.appendChild( renderer.domElement );
  document.getElementById('model-viewer').appendChild( renderer.domElement );
  // scene ------------------------------
  scene = new THREE.Scene();

  // camera ------------------------------
  var perscamera = new THREE.PerspectiveCamera( 45, width / height, 1, 10000 ); // fov(視野角),aspect,near,far
  var orthocamera = new THREE.OrthographicCamera( width / -2, width / 2, height / 2, height / -2, 1, 10000 );
  camera = perscamera;
  camera.position.set(1000, 1000, 1000);
  camera.up.set(0, 1, 0);
  camera.lookAt({ x:0, y:0, z:0 });

  // add light ３点光にしてあげる
  var light = new THREE.DirectionalLight( 0xffffff );
  light.position.set( 1, 1, 1 );
  scene.add( light );

  var light = new THREE.DirectionalLight( 0xffffff );
  light.position.set( -1, -1, -1 );
  scene.add( light );

  var light = new THREE.AmbientLight( 0x444444 );
  scene.add( light );

  //モデルのロード（複数の形式に対応）
  var modelFormat_lower = modelFormat.toLowerCase();
  if(modelFormat_lower == 'obj')
  {
    console.log(modelFormat + ': objでロードします');
    loadOBJ();
  }
  else if(modelFormat_lower == '3ds')
  {
    console.log(modelFormat + ': 3dsでロードします');
    loadTDS();
  }
  else if(modelFormat_lower == 'fbx')
  {
    console.log(modelFormat + ': fbxでロードします');
    loadFBX();
  }
  else if(modelFormat_lower == 'dae')
  {
    console.log(modelFormat + ': colladaでロードします');
    loadCOLLADA();
  }

  // controls ------------------------------
  controls = new THREE.OrbitControls(camera);

  // axis ------------------------------
  var axis = new THREE.AxisHelper(1000);
  axis.position.set(0,0,0);
  scene.add(axis);
}

/*
 * 描画をthree.jsに依頼
 */
function render(){
  requestAnimationFrame( render );
  renderer.render( scene, camera );
}

// obj mtl を読み込んでいる時の処理
function onProgress( xhr ) {
  console.log('読み込み中…');
  if ( xhr.lengthComputable ) {
    var percentComplete = xhr.loaded / xhr.total * 100;
    var text = Math.round(percentComplete, 2) + '% downloaded';
    if(Math.round(percentComplete, 2)==100) {
      document.getElementById('viewer-message3').textContent = '';
    } else {
      document.getElementById('viewer-message3').textContent = text;
    }
  }
}

// obj mtl が読み込めなかったときのエラー処理
function onError( xhr ) {
  document.getElementById('viewer-message3').textContent = '読み込みエラーが発生しました．<br>';
}

/*
 * 3Dモデルの読み込み
 */
//OBJLoader
function loadOBJ() {
  // obj mtlの読み込み
  var mtlLoader = new THREE.MTLLoader();
  mtlLoader.setPath( assetsPath  );              // this/is/obj/path/
  mtlLoader.load( mtlName, function( materials ) {
    materials.preload();
    var objLoader = new THREE.OBJLoader();
    objLoader.setMaterials( materials );
    objLoader.setPath( assetsPath );            // this/is/obj/path/
    objLoader.load( objName, function ( object ) {

      //大きさを調節
      normalizeObjModel(object, 800, [0, 0, 0]);
      object.rotation.set(0, 0, 0);         // 角度の初期化

      mesh = object;
      setMaterial();
      scene.add(object);                         // sceneに追加
    }, onProgress, onError );
  });
}

//3DSMAXLoader
function loadTDS() {
  var tdsLoader = new THREE.TDSLoader();
  tdsLoader.setResourcePath(assetsPath);
  tdsLoader.load(modelName, function(object){
    //大きさを調節
    normalizeObjModel(object, 800, [0, 0, 0]);
    object.rotation.set(-90 / 180 * Math.PI, 0, 0);         // 角度の初期化
    mesh = object;
    setMaterial();
    scene.add(object);
  }, onProgress, onError );
}

//FBXLoader
function loadFBX() {
  var fbxLoader = new THREE.FBXLoader();
  fbxLoader.setResourcePath(assetsPath);
  fbxLoader.setPath(assetsPath);
  fbxLoader.load(modelName, function(object){
    //大きさを調節
    normalizeObjModel(object, 800, [0, 0, 0]);
    mesh = object;
    setMaterial();
    scene.add(object);
  }, onProgress, onError );
}

//COLLADALoader
function loadCOLLADA() {
  var loadingManager = new THREE.LoadingManager(function(){
    scene.add(mesh);
  });
  var colladaLoader = new THREE.ColladaLoader(loadingManager);
  colladaLoader.load(modelName, function(object){
    mesh = object.scene;
    //大きさを調節
    normalizeObjModel(mesh, 800, [0, 0, 0]);
    setMaterial();
  }, onProgress, onError );
}

//メッシュにマテリアルを設定する
function setMaterial()
{
  console.log(mesh);
  var material = new THREE.MeshPhongMaterial( {
    color: 0xa3a3a3,
    specular: 0x808080,
    emissive: 0x000000,
    shininess: 5
  } );
  for(var i = 0; i < mesh.children.length; i++)
  {
    mesh.children[i].material = material;
  }
  document.getElementById('viewer-message3').textContent = '';
}

//objmodelの大きさ，位置を指定されたものに正規化する．
function normalizeObjModel(objmodel, size, center)
{
  var positions = objmodel.children[0].geometry.attributes.position;
  var max_x = positions.array[0];
  var max_y = positions.array[1];
  var max_z = positions.array[2];
  var min_x = positions.array[0];
  var min_y = positions.array[1];
  var min_z = positions.array[2];
  var volume = 0;      //体積
  var cog = [0, 0, 0]; //重心
  for(var c = 0; c < objmodel.children.length; c++)
  {
    var positions = objmodel.children[c].geometry.attributes.position;
    var _volume = 0;      //体積
    var _cog = [0, 0, 0]; //重心
    var length = positions.length;  //頂点数*3(3次元だから)
    var poly_num = 3;               //三角面
    var vertex_num = length / 3;    //頂点数
    var surface_num = vertex_num / poly_num;  //面数（ポリゴン数）

    for(var i = 0; i < surface_num; i+=1)
    {
      var surface = []; //面（3頂点）
      for(var j = 0; j < poly_num; j+=1)
      {
        //各座標を取得
        var x = positions.array[i * poly_num * 3 + j * 3 + 0];
        var y = positions.array[i * poly_num * 3 + j * 3 + 1];
        var z = positions.array[i * poly_num * 3 + j * 3 + 2];
        //それぞれの軸での最大，最小値を見つける
        if(x > max_x){max_x = x;}
        if(x < min_x){min_x = x;}
        if(y > max_y){max_y = y;}
        if(y < min_y){min_y = y;}
        if(z > max_z){max_z = z;}
        if(z < min_z){min_z = z;}
        //面情報を記録
        surface.push([x, y, z]);
      }
      //3頂点と原点との3角錐の体積を求める．
      var volumei = dot3(surface[2], cross3(surface[0], surface[1])) / 6;     //三角錐の体積
      var cogi = mul3(add3(surface[0], add3(surface[1], surface[2])), 1 / 4); //三角錐の重心
      _volume += volumei;
      _cog = add3(_cog, mul3(cogi, volumei));
    }
    volume += _volume;
    cog = add3(cog, _cog);
  }

  //一番幅の大きい軸についてsizeを合わせる
  var x_width = max_x - min_x;
  var y_width = max_y - min_y;
  var z_width = max_z - min_z;
  var max_width = Math.max(x_width, y_width, z_width);
  var scale =  size / max_width;
  objmodel.scale.set(scale, scale, scale);

  //位置を調整する
  cog = mul3(cog, 1 / volume * scale);  //体積で割って，更に大きさ調整後の重心を求める
  objmodel.position.set(
    center[0] - cog[0],
    center[1] - cog[1],
    center[2] - cog[2]
  );
  start.disabled = false;
}

//3次元ベクトルの外積を計算
function cross3(v1, v2)
{
  return [
    v1[1] * v2[2] - v1[2] * v2[1],
    v1[2] * v2[0] - v1[0] * v2[2],
    v1[0] * v2[1] - v1[1] * v2[0]
  ];
}

//3次元ベクトルの内積を計算
function dot3(v1, v2)
{
  return v1[0] * v2[0] + v1[1] * v2[1] + v1[2] * v2[2];
}

//3次元ベクトルの和を計算
function add3(v1, v2)
{
  return [
    v1[0] + v2[0],
    v1[1] + v2[1],
    v1[2] + v2[2]
  ]
}

//3次元ベクトルとスカラーの積を計算
function mul3(v, a)
{
  return[
    v[0] * a,
    v[1] * a,
    v[2] * a
  ]
}

//ウィンドウのサイズ変更に対応する
var area2_2_width = 250;
onResize();
window.addEventListener('resize', onResize);
function onResize(){
  //サイズを取得
  var width = window.innerWidth - area2_2_width;
  var height = document.getElementById('area2-1').clientHeight;

  //レンダラーのサイズを調整
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(width, height);

  //カメラのアスペクト比を正す
  camera.aspect = width / height;
  camera.updateProjectionMatrix();

  record();
}

//キーイベントの設定
var isFullScreen = false;
var space_cnt = 0;
var timeout= 1000;// ms

// タイマー、入力キーのキャッシュ
var timeoutId= null;
var previousKey= null;
addEventListener('keydown',function(event){
  //ファンクションキーを無効化
  if(event.keyCode == 116)
  {
    event.keyCode = null;
    event.returnValue = false;
    return false;
  }

  // 入力キーを記憶する
  if(previousKey!==event.keyCode){
    previousKey = event.keyCode;

    keyevent(event.keyCode);

    // 時間超過で入力キーをクリア
    clearTimeout(timeoutId);
    timeoutId= setTimeout(function(){
      previousKey= null;
    },timeout)

    return;
  }
})

//キーイベントが発生した時の処理
function keyevent(keycode){
  var code = keycode;
  if(code == 32) //スペースキーが押されたとき
  {
    controls.reset(); //視点のリセット
    space_cnt += 1;   //スペースキークリック回数のカウント（デバッグ用）
    document.getElementById('click_space').textContent = "Spaceクリック回数：" + space_cnt +"回";
    record();
  }
  if(code == 70)  //Fが押されたとき
  {
    var area1_1 = document.getElementById('area1-1');
    var area2_2 = document.getElementById('area2-2');
    if(isFullScreen)  //3Dモデル表示部分を元に戻す
    {
      area1_1.style.display="block";
      area2_2.style.display="inline-block";
      area2_2_width = 250;
      isFullScreen = false;
    }
    else              //3Dモデル表示部分を大きくする
    {
      area1_1.style.display="none";
      area2_2.style.display="none";
      area2_2_width = 0;
      isFullScreen = true;
    }
    onResize();
  }
}

//カメラの動きを記録するためのマウスイベントリスナー
var isMouseDown = false;
var isDuringExperiment = false;
var startExperimentTime;
var startExperimentDate;

//タイマースタート
document.getElementById('start_button').addEventListener('click', function (){
  isDuringExperiment = true;
  if(startExperimentTime == null)
  {
    startExperimentTime = Date.now();
    startExperimentDate = new Date();
    printLog("Start!!\ntime:" + startExperimentTime);
    printLog(new Date);
  }
});

//タイマーストップ
document.getElementById('stop_button').addEventListener('click', function (){
  isDuringExperiment = false;
});

//視点変更時の記録を取る
//Firefox
if(window.addEventListener){
  window.addEventListener('DOMMouseScroll', function(){
    record();
  }, false);
}
//IE
if(document.attachEvent){
  document.attachEvent('onmousewheel', function(){
    record();
  });
}
//Chrome
window.onmousewheel = function(){
  record();
}
//マウスが動いた時の記録
addEventListener('mousedown', function (e){
  isMouseDown = true;
});
addEventListener('mouseup', function (e){
  isMouseDown = false;
});
addEventListener('mousemove', function (e){
  //マウスのボタンが押されてるときのみ
  if(isMouseDown)
  {
    record();
  }
});

//記録するための構造体
function Recorder(c_x, c_y, c_z, l_x, l_y, l_z, w_w, w_h)
{
  this.date = new Date;         //アクションが発生した時間
  this.camera_position_x = c_x; //カメラの位置（X座標）
  this.camera_position_y = c_y; //カメラの位置（Y座標）
  this.camera_position_z = c_z; //カメラの位置（Z座標）
  this.lookat_vector_x = l_x;   //カメラの向いている方向（X座標）
  this.lookat_vector_y = l_y;   //カメラの向いている方向（Y座標）
  this.lookat_vector_z = l_z;   //カメラの向いている方向（Z座標）
  this.window_size_w = w_w;     //ウィンドウの大きさ（横幅）
  this.window_size_h = w_h;     //ウィンドウの大きさ（縦幅）

  //データ送信時にjson化するためのメソッド
  this.toJSON = function () {
    var time = this.date.getTime() - startExperimentTime;
    //var h = Math.floor(time % (6000 * 6000 * 24) / (6000 * 6000));
    //var h = this.date.getHours();
    //m(分) = 135200 / 60000ミリ秒で割った数の商　-> 2分
    //var m = Math.floor(time % (6000 * 6000) / 60000);
    //var m = this.date.getMinutes();
    //s(秒) = 135200 % 60000ミリ秒で / 1000 (ミリ秒なので1000で割ってやる) -> 15秒
    //var s = Math.floor(time % 60000 / 1000);
    //var s = this.date.getSoconds();
    //ms(ミリ秒) = 135200ミリ秒を % 1000ミリ秒で割った数の余り
    //var ms = this.date.getTime() % 1000;

    var h = Math.floor(time / 3600000);
    //m(分) = 135200 / 60000ミリ秒で割った数の商　-> 2分
    var m = Math.floor(time / 60000) - h * 60;
    //s(秒) = 135200 % 60000ミリ秒で / 1000 (ミリ秒なので1000で割ってやる) -> 15秒
    var s = Math.floor(time % 60000 / 1000);
    //ms(ミリ秒) = 135200ミリ秒を % 1000ミリ秒で割った数の余り
    var ms = time % 1000;

    //HTML 上で表示の際の桁数を固定する　例）3 => 03　、 12 -> 012
    //javascriptでは文字列数列を連結すると文字列になる
    //文字列の末尾2桁を表示したいのでsliceで負の値(-2)引数で渡してやる。
    /*
    m = ('0' + m).slice(-2);
    s = ('0' + s).slice(-2);
    ms = ('00' + ms).slice(-3);
    */

    /*
    var date = this.date.getFullYear() + '/'
             + (this.date.getMonth() + 1)  + '/'
             + this.date.getDate();
    */
    //var time = h + ':' + m + ':' + s + ':' + ms;

    var data = {
      //'date' : date,
      'time' : {
        'hours' : h,
        'minutes' : m,
        'seconds' : s,
        'milliseconds' : ms
      },
      'camera' : {
        'x' : this.camera_position_x,
        'y' : this.camera_position_y,
        'z' : this.camera_position_z
      },
      'lookat' : {
        'x' : this.lookat_vector_x,
        'y' : this.lookat_vector_y,
        'z' : this.lookat_vector_z
      },
      'window' : {
        'width' : this.window_size_w,
        'height' : this.window_size_h
      }
    };

    return data;
  }
}

//記録するための配列
var records = {};
var record_number = 0;  //レコードの数

//特に使っていない関数（何であるんや）
var getDecimalPointLength = function(number) {
    var numbers = String(number).split('.'),
        result  = 0;

    if (numbers[1]) {
        result = numbers[1].length;
    }

    return result;
};

//記録をとるメソッド
function record()
{
  if(!isDuringExperiment) //実験中のみ記録
  {
    return;
  }
  //カメラの向いている方向を計算
  var lookAtVector = new THREE.Vector3(0, 0, -1);
  lookAtVector.applyQuaternion(camera.quaternion);

  //Viewerのサイズを取得
  var width = window.innerWidth - area2_2_width;
  var height = document.getElementById('area2-1').clientHeight;

  //記録を取る
  var recorder = new Recorder(
    camera.position.x,
    camera.position.y,
    camera.position.z,
    lookAtVector.x,
    lookAtVector.y,
    lookAtVector.z,
    width,
    height
  );

  //json化して記録を保存
  records['data_' + record_number] = recorder.toJSON();
  record_number++;

  //100件ごとにデータを送信
  if(record_number % MAX_RECORD == 0)
  {
    pushRecord(records, record_number);
    records = {};
  }

  printLog("record number : " + record_number);
  /*
  printLog(recorder);
  printLog(recorder.toJSON());
  printLog(records);
  */
  printInfo();
}

//デバッグ時，ログを表示させる
function printLog(str)
{
  if(isDebug)
  {
    console.log(str);
  }
}

//現在の情報を表示させる
function printInfo()
{
  var x = camera.position.x;
  var y = camera.position.y;
  var z = camera.position.z;
  var divelem = document.getElementById('camera_position');
  divelem.textContent = 'カメラ座標：\nx=' + x + ','
                                 + '\ny=' + y + ','
                                 + '\nz=' + z;
  //var lookAtVector = new THREE.Vector3(camera.matrix[8], camera.matrix[9], camera.matrix[10]);
  var lookAtVector = new THREE.Vector3(0, 0, -1);
  lookAtVector.applyQuaternion(camera.quaternion);
  x = lookAtVector.x;
  y = lookAtVector.y;
  z = lookAtVector.z;
  divelem = document.getElementById('lookat_position');
  divelem.textContent = '向いてる方向：\nx=' + x + ','
                                  + '\ny=' + y + ','
                                  + '\nz=' + z;
}

// 終了ボタンのリスナーを設定
/*
var finish = document.getElementById('finish_button');
finish.addEventListener('click', function(){

});
*/

//特に使っていない...（多分試行錯誤しているときに出来上がったやつ）
function createRecordData(){
  var form = document.forms.cameraRecordForm;
  pushRecord(records, record_number, true);
  //IDを付加
  ele_id = document.createElement('input');
  ele_id.setAttribute('type', 'hidden');
  ele_id.setAttribute('name', 'id');
  ele_id.setAttribute('value', document.getElementById('subject_number').value);
  form.appendChild(ele_id);
  //実験開始日を付加
  var sdate = startExperimentDate;
  var date = sdate.getFullYear() + '-'
           + (sdate.getMonth() + 1)  + '-'
           + sdate.getDate();
  var h = ('0' + sdate.getHours()).slice(-2);
  var m = ('0' + sdate.getMinutes()).slice(-2);
  var s = ('0' + sdate.getSeconds()).slice(-2);
  ele_estart = document.createElement('input');
  ele_estart.setAttribute('type', 'hidden');
  ele_estart.setAttribute('name', 'startexperiment');
  ele_estart.setAttribute('value', date + ' ' + h + ':' + m  + ':' + s);
  form.appendChild(ele_estart);
  //モデル名を付加
  ele_model = document.createElement('input');
  ele_model.setAttribute('type', 'hidden');
  ele_model.setAttribute('name', 'modelname');
  ele_model.setAttribute('value', document.getElementById('model_name').value);
  form.appendChild(ele_model);
/*
  try{
    for(var i = 0; i < records.length; i++)
    {
      for(var key in records[i])
      {
        var ele = document.createElement('input');
        ele.setAttribute('type', 'hidden');
        ele.setAttribute('name', i + '-' + key);
        ele.setAttribute('value', records[i][key]);
        form.appendChild(ele);
      }
    }
  }
  catch(e)
  {
    console.log(e);
  }*/
}

//記録情報をサーバへ送信するメソッド
function pushRecord(recordData, record_number, last)
{
  if(last === 'undefined')
  {
    last = false;
  }
  var url = '/pre-experiment/recorder.php';

  var postData = {};

  /*
  postData['min'] = record_number - MAX_RECORD;
  postData['max'] = record_number - 1;
  */
  postData['last'] = last;
  postData['id'] = document.getElementById('subject_number').value;
  postData['data'] = recordData;

  $.ajax({
    type : 'POST',
    url : url,
    cache: false,
    data : postData,
    dataType : 'json',
    success : function(data) {
      //success
      printLog('success');
    },
    error : function() {
      // Error
      printLog('error');
    }
  });
/*
  var formdata = {
    'question1' : question1data,
    'question2' : question2data,
    'q1-length' : form.question1.length,
    'q2-length' : form.question2.length,
  };

  //ajaxでページの一部を更新
  $.ajax({
    url: 'answer.php',
    cache: false,
    type: 'POST',
    datatype: 'json',
    data : formdata,
    success: function(data) {
      $('#ajaxload').html(data);
    },
    error: function() {
      alert('送信エラー');
    }
  });*/
}

//サーバは30分で寝てしまうため，実験中はサーバにアクセスしまくって起こし続ける
setInterval(function(){
  var url = '/pre-experiment/session.php';
  $.ajax({
    type : 'POST',
    url : url,
    cache: false,
    data : '{"session" : "continue"}',
    dataType : 'json',
    success : function(data) {
      //success
      printLog('success');
    },
    error : function() {
      // Error
      printLog('error');
    }
  });
}, 10 * 60 * 1000);

//誤ってページを離れそうになった時の注意勧告
var onBeforeunloadHandler = function(e){
  e.returnValue = 'このページから離れないでください．';
}
window.addEventListener('beforeunload', onBeforeunloadHandler, false);

//実験終了時の処理
document.forms.cameraRecordForm.addEventListener('submit', function(e) {
  //注意勧告を発生しないようにする
  window.removeEventListener('beforeunload', onBeforeunloadHandler, false);
}, false);
