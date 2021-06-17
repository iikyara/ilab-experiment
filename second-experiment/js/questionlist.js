var submitbutton = document.getElementsByName('submitbutton')[0];
var isComplete = document.getElementsByName('isComplete')[0].value;

if(isComplete === '1')
{
  submitbutton.disabled = "";
}
else
{
  submitbutton.disabled = "true";
}

submitbutton.addEventListener('click', function(){
  window.location.href = './thankyou.php';
}, false);
