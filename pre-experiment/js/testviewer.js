/*
 * selectmodel.phpで使用するスクリプト
 * 3Dモデルが使用可能かをチェックするために使用
 */
var mesh, camera, scene, renderer, mouse2D, controls,
radius = 150,
theta = 45,
phi = 0,
width = window.innerWidth,
height = window.innerHeight,
lastAnimTime = window.performance.now(),
RENDER_INTERVAL = 30,
TICK_INTERVAL = 500;
var dataPath = 'freemodeltmp/' + document.getElementById('id').value + '/';
var assetsPath = './showfreemodeldata.php?data=' + encodeURIComponent(dataPath);
var modelName = assetsPath + encodeURIComponent(document.getElementById('mainModelName').value);
var objName = encodeURIComponent(document.getElementById('mainModelName').value);
var mtlName = encodeURIComponent(document.getElementById('mtlFileName').value);
var modelFormat = document.getElementById('fileFormat').value;

init();
// start();
render();

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

  //モデルのロード
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

    for(var i = 0; i < surface_num - 1; i+=1)
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
      if(Number.isNaN(_volume))
      {
        console.log(surface, volumei, cogi);
      }
    }
    console.log(_volume, volume, _cog, cog);
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
}

function cross3(v1, v2)
{
  return [
    v1[1] * v2[2] - v1[2] * v2[1],
    v1[2] * v2[0] - v1[0] * v2[2],
    v1[0] * v2[1] - v1[1] * v2[0]
  ];
}

function dot3(v1, v2)
{
  return v1[0] * v2[0] + v1[1] * v2[1] + v1[2] * v2[2];
}

function add3(v1, v2)
{
  return [
    v1[0] + v2[0],
    v1[1] + v2[1],
    v1[2] + v2[2]
  ]
}

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
  var width = window.innerWidth;
  var height = window.innerHeight;

  //レンダラーのサイズを調整
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(width, height);

  //カメラのアスペクト比を正す
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
}

//キーイベントの設定
addEventListener('keydown',function(event){
  var code = event.keyCode;
  if(code == 32) //スペースキーが押されたとき
  {
    controls.reset();
  }
});
