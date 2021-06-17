/*
 * registration.htmlで使用するスクリプト
 * 工事の音めっちゃうるせえ（関係ない）
 */
function check()
{
  var form = document.forms.intro_form;
  var s_num = form.subject_number.value;
  var m_name = form.model_name.value;

  if(m_name == "")
  {
    alert("モデルを選択してください．");
    return false;
  }

  var message = "被験者番号：" + s_num
              + "\nモデル名：" + m_name
              + "\nで送信します．よろしいですか？";
  if(!window.confirm(message))
  {
    return false;
  }
}
