var elem_list = document.getElementById('list');

loadUser();

$('#testButton').on('click', loadUser);

function loadUser()
{
  console.log('通信中');
  // Ajax通信を開始
  $.ajax({
    url: 'usermanagementapi.php',
    type: 'POST',
    dataType: 'json',
    data: {"op" : 'load'},
    timeout: 5000,
  })
  .done(function(data) {
      console.log("success");
      if(data['success'])
      {
        _loadUser(data['result']);
      }
      else
      {
        console.log('server error');
        console.log(data['message']);
      }
  })
  .fail(function() {
      console.log("failed");
  });
}

function _loadUser(users)
{
  //テンプレート
  var template = ['id', 'name', 'r-date', 'u-date', 'question'];

  //表の全要素を削除
  while( elem_list.firstChild ){
    elem_list.removeChild( elem_list.firstChild );
  }

  //ユーザーを表に追加
  for(var i = 0; i < users.length; i++)
  {
    //TR要素を作成
    var tr = document.createElement('tr');
    var td;   //tdタグ
    var btn;  //input[type=button]タグ

    //trに要素を追加
    for(var j = 0; j < template.length; j++)
    {
      td = document.createElement('td');
      td.className = template[j];
      td.innerHTML = users[i][template[j]];
      tr.appendChild(td);
    }

    //初期化を追加
    td = document.createElement('td');
    btn = document.createElement('input');
    btn.type = 'button';
    btn.value = '初期化';
    btn.id = 'initPass' + users[i]['id'];
    btn.addEventListener('click', initPass, false);
    td.appendChild(btn);
    tr.appendChild(td);

    //削除を追加
    td = document.createElement('td');
    btn = document.createElement('input');
    btn.type = 'button';
    btn.value = '削除';
    btn.id = 'deleteUser' + users[i]['id'];
    btn.addEventListener('click', deleteUser, false);
    td.appendChild(btn);
    tr.appendChild(td);

    //表に追加
    elem_list.appendChild(tr);
  }
}

function initPass( event )
{
  var id = parseID(event.target.id, 'initPass');

  if(!window.confirm('警告\nUserID:' + id + 'のパスワードを初期化します．'))
  {
    return;
  }

  console.log('通信中');
  // Ajax通信を開始
  $.ajax({
    url: 'usermanagementapi.php',
    type: 'POST',
    dataType: 'json',
    data: {"op" : 'initPass', "id" : id},
    timeout: 5000,
  })
  .done(function(data) {
      console.log("success");
      if(data['success'])
      {
        alert(data['result']['password']);
      }
      else
      {
        console.log('server error');
        console.log(data['message']);
      }
  })
  .fail(function() {
      console.log("failed");
  });
}

function deleteUser( event )
{
  var id = parseID(event.target.id, 'deleteUser');

  if(!window.confirm('警告\nUserID:' + id + 'を削除します．'))
  {
    return;
  }

  console.log('通信中');
  // Ajax通信を開始
  $.ajax({
    url: 'usermanagementapi.php',
    type: 'POST',
    dataType: 'json',
    data: {"op" : 'deleteUser', "id" : id},
    timeout: 5000,
  })
  .done(function(data) {
      console.log("success");
      console.log(data);
      if(data['success'])
      {
        _loadUser(data['result']['users']);
      }
      else
      {
        console.log('server error');
        console.log(data['message']);
      }
  })
  .fail(function() {
      console.log("failed");
  });
}

//[head+id]の形式の文字列からheadを取り除いて整数にして返す．
function parseID(str, head)
{
  return Number(str.slice(head.length));
}
