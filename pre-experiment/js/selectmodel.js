/*
 * selectmodel.php（3Dモデル選択ページ）のスクリプト
 */

//初期設定
document.getElementById('submitButton').disabled = true;

//3Dモデルが使用可能かチェックするボタンのリスナー
document.getElementById('checkButton').addEventListener('click', function(){
  check();
});

//ファイル追加の動作
var filenum = 1;
document.getElementById('addFileButton').addEventListener('click', function(){
  filenum += 1;
  var elem = document.getElementById('modelfiles');
  var addelem = document.createElement('input');
  addelem.type = 'file';
  addelem.name = 'modelFile' + filenum;
  elem.appendChild(addelem);
  document.getElementsByName('modelFileNum')[0].value = filenum;
});

//ファイル送信の動作
document.getElementById('subFileButton').addEventListener('click', function(){
  if(filenum > 1){
    var elem = document.getElementById('modelfiles');
    var targetname = 'modelFile' + filenum;
    var target = document.getElementsByName(targetname);
    console.log(targetname, target, filenum);
    elem.removeChild(target[0]);
    filenum -= 1;
    document.getElementsByName('modelFileNum')[0].value = filenum;
  }
});

//3Dモデルが使用可能か，実際に3Dモデルを読み込んでみるメソッド
function check(){
  var url = '/pre-experiment/saveselectmodel.php';
  var form = document.getElementById('selectModelForm');
  var formdata = new FormData();
  console.log(formdata);
  for(var i = 1; i <= filenum; i++)
  {
    var modelfile = document.getElementsByName('modelFile' + i)[0];
    var files = modelfile.files;
    console.log(files);
    formdata.append('file' + i, files[0]);
    /*
    $.each(files, function(i, file){
  		formdata.append('file', file);
      console.log(file);
  	});
    */
  }

  formdata.append('modelFileNum', document.getElementsByName('modelFileNum')[0].value);
  formdata.append('id', document.getElementsByName('id')[0].value);

  console.log(formdata);

  //ファイルをサーバへ一旦送信
  $.ajax({
    type : 'POST',
    url : url,
    cache: false,
    data : formdata,
    processData: false,
		contentType: false,
    dataType : 'html',
    async: false,
    success : function(data) {
      //成功なら，3Dモデルを別ウィンドウで表示させる
      console.log('success');
      var nwin = window.open('about:blank', null, 'width=800,height=800');
      nwin.document.open();
      nwin.document.write(data);
      nwin.document.close();
      document.getElementById('submitButton').disabled = false;
    },
    error : function() {
      // Error
      console.log('false');
    }
  });
}

//3Dモデルを送信
function onsubmit()
{
  var url = '/pre-experiment/saveselectmodel.php';
  var form = document.getElementById('selectModelForm');
  var formdata = new FormData();
  console.log(formdata);
  for(var i = 1; i <= filenum; i++)
  {
    var modelfile = document.getElementsByName('modelFile' + i)[0];
    var files = modelfile.files;
    console.log(files);
    formdata.append('file' + i, files[0]);
    /*
    $.each(files, function(i, file){
  		formdata.append('file', file);
      console.log(file);
  	});
    */
  }

  formdata.append('modelFileNum', document.getElementsByName('modelFileNum')[0].value);
  formdata.append('id', document.getElementsByName('id')[0].value);

  formdata.submit();
}
