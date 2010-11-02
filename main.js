function highlightReply(id)
{
	var divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++)
	{
		if (divs[i].className.indexOf('post') != -1)
			divs[i].className = divs[i].className.replace(/highlighted/, '');
	}

	console.log('reply_'+id);
	if (id)
		document.getElementById('reply_'+id).className += ' highlighted';
}
function focusId(id)
{
	document.getElementById(id).focus();
	init();
}
function dopost(form) {
	localStorage.name = form.name.value;
	localStorage.email = form.email.value;
	
	return form.body.value != "" || (typeof form.thread != "undefined" && form.file.value != "");
}
function citeReply(id) {
	document.getElementById('body').value += '>>' + id + '\n';
}

function init()
{
	if (window.location.hash.indexOf('q') == 1)
		citeReply(window.location.hash.substring(2));
	else if (window.location.hash.substring(1))
		highlightReply(window.location.hash.substring(1));
	if(localStorage.name)
		document.getElementsByTagName('form')[0].name.value = localStorage.name;
	if(localStorage.email)
		document.getElementsByTagName('form')[0].email.value = localStorage.email;
	
	link = document.getElementsByTagName('a');
	for ( i in link ) {
		if(typeof link[i] == "object" && link[i].childNodes[0].src) {
			
			link[i].onclick = function() {
				if(!this.tag) {
					this.tag = this.childNodes[0].src;
					this.childNodes[0].src = this.href;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height='auto';
				} else {
					this.childNodes[0].src = this.tag;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height='auto';
					this.tag = '';
				}
				return false;
			}
			
			console.log(link[i].childNodes[0]);
			console.log(link[i].onclick);
		}
	}
}

window.onload = init;