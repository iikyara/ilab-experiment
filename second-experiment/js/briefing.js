/*
 * question.phpで使用するスクリプト
 * 3Dモデルの表示とアンケート送信の処理をしている
 */

/*
 * 描画に必要な変数の定義
 */
var view1, view2,
radius = 150,
theta = 45,
phi = 0,
width = document.getElementById('area2-1').clientWidth / 3,
height = document.getElementById('area2-1').clientHeight,
lastAnimTime = window.performance.now(),
RENDER_INTERVAL = 30,
TICK_INTERVAL = 500;

/*
 * 参照すべき3Dモデルの置き場所を格納する変数
 */
assetsPath = '../pre-experiment/questionModel/' + document.getElementById('sketchid').value + '/';
//var modelname = document.getElementById('model_name').value;
var modelname1 = document.getElementById('model1_name').value;
var modelname2 = document.getElementById('model2_name').value;
var mtlName = document.getElementById('mtl_name').value;

//それぞれのカメラ位置を記録
view1 = {
  camera : null,
  scene : null,
  renderer : null,
  controls : null,
  num : 1
}
view2 = {
  camera : null,
  scene : null,
  renderer : null,
  controls : null,
  num : 2
}

/*
 * デバッグ情報を表示するかを切り替える
 */
var isDebug = true;

/*
 * 3Dモデルの描画
 */
init(view1);
init(view2);
loadOBJ(view1, assetsPath, modelname1, mtlName);
loadOBJ(view2, assetsPath, modelname2, mtlName);
render();

/*
 * 描画の準備とモデルの読み込み
 */
function init(view) {
  // renderer ------------------------------
  view.renderer = new THREE.WebGLRenderer({ antialias: true });
  view.renderer.setClearColor( 0x222222 ); // 背景色
  view.renderer.setSize( width, height );
  document.getElementById('model-viewer' + view.num).appendChild( view.renderer.domElement );
  // scene ------------------------------
  view.scene = new THREE.Scene();

  // camera ------------------------------
  var perscamera = new THREE.PerspectiveCamera( 45, width / height, 1, 10000 ); // fov(視野角),aspect,near,far
  var orthocamera = new THREE.OrthographicCamera( width / -2, width / 2, height / 2, height / -2, 1, 10000 );
  view.camera = perscamera;
  view.camera.position.set(1000, 1000, 1000);
  view.camera.up.set(0, 1, 0);
  view.camera.lookAt({ x:0, y:0, z:0 });

  // add light ３点光にしてあげる
  var light = new THREE.DirectionalLight( 0xffffff );
  light.position.set( 1, 1, 1 );
  view.scene.add( light );

  var light = new THREE.DirectionalLight( 0xffffff );
  light.position.set( -1, -1, -1 );
  view.scene.add( light );

  var light = new THREE.AmbientLight( 0x444444 );
  view.scene.add( light );

  // controls ------------------------------
  view.controls = new THREE.OrbitControls(view.camera);
  view.controls.enabled = false;

  // axis ------------------------------
  var axis = new THREE.AxisHelper(1000);
  axis.position.set(0,0,0);
  view.scene.add(axis);
}

/*
 * 描画をthree.jsに依頼
 */
function render(){
  requestAnimationFrame( render );
  view1.renderer.render( view1.scene, view1.camera );
  view2.renderer.render( view2.scene, view2.camera );
}

/*
 * 3Dモデルの読み込み
 */
function loadOBJ(view, assetsPath, objName, mtlName) {
  // obj mtl を読み込んでいる時の処理
  var onProgress = function ( xhr ) {
    if ( xhr.lengthComputable ) {
      var percentComplete = xhr.loaded / xhr.total * 100;
      var text = Math.round(percentComplete, 2) + '% downloaded';
      printLog( text );
      if(Math.round(percentComplete, 2)==100) {
        document.getElementById('viewer-message' + view.num + '_3').textContent = '';
      } else {
        document.getElementById('viewer-message' + view.num + '_3').textContent = text;
      }
    }
  };

  // obj mtl が読み込めなかったときのエラー処理
  var onError = function ( xhr ) {
    document.getElementById('viewer-message3').textContent = '読み込みエラーが発生しました．<br>実験監督者に連絡してください．';
  };

  // obj mtlの読み込み
  var mtlLoader = new THREE.MTLLoader();
  mtlLoader.setPath( assetsPath  );              // this/is/obj/path/
  mtlLoader.load( mtlName, function( materials ) {
    materials.preload();
    var objLoader = new THREE.OBJLoader();
    objLoader.setMaterials( materials );
    objLoader.setPath( assetsPath );            // this/is/obj/path/
    objLoader.load( objName, function ( object ) {
      objmodel = object.clone();

      //大きさを調節
      normalizeObjModel(objmodel, 800, [0, 0, 0]);

      //objmodel.scale.set(scale, scale, scale);// 縮尺の初期化
      objmodel.rotation.set(0, 0, 0);         // 角度の初期化
      //objmodel.position.set(0, 0, 0);         // 位置の初期化

      // objをObject3Dで包む
      obj = new THREE.Object3D();
      obj.add(objmodel);

      view.scene.add(obj);                         // sceneに追加
    }, onProgress, onError );
  });
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
    volume += volumei;
    cog = add3(cog, mul3(cogi, volumei));
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
onResize();
window.addEventListener('resize', onResize);
function onResize(){
  //サイズを取得
  var width = window.innerWidth / 3;
  var height = document.getElementById('area2-1').clientHeight;

  //レンダラーのサイズを調整
  view1.renderer.setPixelRatio(window.devicePixelRatio);
  view1.renderer.setSize(width, height);

  //カメラのアスペクト比を正す
  view1.camera.aspect = width / height;
  view1.camera.updateProjectionMatrix();

  //レンダラーのサイズを調整
  view2.renderer.setPixelRatio(window.devicePixelRatio);
  view2.renderer.setSize(width, height);

  //カメラのアスペクト比を正す
  view2.camera.aspect = width / height;
  view2.camera.updateProjectionMatrix();
}

//キーイベントの設定
var isFullScreen = false;
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
    if(isMouseOnView1)
    {
      view1.controls.reset();
    }
    if(isMouseOnView2)
    {
      view2.controls.reset();
    }
  }
  if(code == 70)  //Fが押されたとき
  {
    //テキストエリアにフォーカスが当たっているときは無視
    if(textAreaIsAcrive)
    {
      return;
    }

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

//マウスイベントリスナー（2つの3Dモデルのカメラを操作する切り替え動作の定義）
var isMouseDown = false;
var isMouseOnView1 = false;
var isMouseOnView2 = false;
addEventListener('mousedown', function(){
  isMouseDown = true;
}, false);

addEventListener('mouseup', function(){
  isMouseDown = false;
}, false);

document.getElementById('model-viewer1').addEventListener('mouseenter', function() {
  changeFocus(true, false);
}, false);

document.getElementById('model-viewer1').addEventListener('mouseleave', function() {
  changeFocus(false, isMouseOnView2);
}, false);

document.getElementById('model-viewer2').addEventListener('mouseenter', function(){
  changeFocus(false, true);
}, false);

document.getElementById('model-viewer2').addEventListener('mouseleave', function(){
  changeFocus(isMouseOnView1, false);
}, false);

document.getElementById('area2-2').addEventListener('mouseover', function(){
  changeFocus(false, false);
}, false);

//テキストエリアがアクティブかどうかを監視する．
var textAreaIsAcrive = false;

document.getElementsByName('q3')[0].addEventListener('focus',
function(){
  textAreaIsAcrive = true;
}, false);

document.getElementsByName('q3')[0].addEventListener('blur',
function(){
  textAreaIsAcrive = false;
}, false);

/*
 * imov1=true : 描画1の視点のコントロールをONに
 * imov2=true : 描画2の視点のコントロールをONに
 * imov1, imov2=false : 視点のコントロールをOFFに
 */
function changeFocus(imov1, imov2){
  isMouseOnView1 = imov1;
  isMouseOnView2 = imov2;
  if(!isMouseDown)
  {
    view1.controls.enabled = imov1;
    view2.controls.enabled = imov2;
    return;
  }
  window.addEventListener('mouseup', onMouseUp, false);
  function onMouseUp(){
    changeFocus(imov1, imov2);
    window.removeEventListener('mouseup', onMouseUp);
  }
}

//デバッグ時，ログを表示させる
function printLog(str)
{
  if(isDebug)
  {
    console.log(str);
  }
}
