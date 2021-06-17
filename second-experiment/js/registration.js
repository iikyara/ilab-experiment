/*
 * registration.htmlで使用するスクリプト
 * 工事の音めっちゃうるせえ（関係ない）
 */

function check()
{
  var form = document.forms.regist_form;
  var username = form.username.value;
  var password = form.password.value;
  var password2 = form.password2.value;

  if(username.length < 5 || password.length < 5)
  {
    alert("ユーザー名またはパスワードが短いです．<br>共に5文字以上にしてください．");
    return false;
  }

  if(password != password2)
  {
    alert("再入力したパスワードが異なっています．");
    return false;
  }

  var message = username + "," + password + "で登録します．\nよろしいですか？";
  if(!window.confirm(message))
  {
    return false;
  }

  form.password.value = SHA256Hash(password);
}
