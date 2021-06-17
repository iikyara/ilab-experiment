/*
 * login.phpで使用する被験者番号があっているかの確認をするスクリプト
 * 1つの関数なのでわざわざ分ける必要はなかったなぁ…
 */
function check()
{
  var form = document.forms.intro_form;
  console.log(form);
  var username = form.username.value;
  var password = form.password.value;
  var challenge = document.getElementsByName("challenge")[0].value;

  alert(username + ", " + password + ", " + challenge);
  
  if(username == "" || password == "")
  {
    alert("ユーザー名またはパスワードを入力してください．");
  }

  form.password.value = SHA256Hash(SHA256Hash(password) + challenge);
}
