function get_cookie(cookie_name)
{
	var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
	if(results)
		return (unescape(results[2]));
	else
		return null;
}

function highlightReply(id)
{
	var divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++)
	{
		if (divs[i].className.indexOf('post') != -1)
			divs[i].className = divs[i].className.replace(/highlighted/, '');
	}
	if (id) {
		post = document.getElementById('reply_'+id);
		if(post)
			post.className += ' highlighted';
	}
}
function focusId(id)
{
	document.getElementById(id).focus();
	init();
}

function generatePassword() {
	pass = '';
	chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
	for(i=0;i<8;i++) {
		rnd = Math.floor(Math.random() * chars.length);
		pass += chars.substring(rnd,rnd + 1);
	}
	return pass;
}

function dopost(form) {
	localStorage.name = form.name.value.replace(/ ##.+$/, '');
	if(form.email.value != 'sage')
		localStorage.email = form.email.value;
	
	saved[document.location] = form.body.value;
	sessionStorage.body = JSON.stringify(saved);
	
	return form.body.value != "" || (typeof form.thread != "undefined" && form.file.value != "");
}
function citeReply(id) {
	document.getElementById('body').value += '>>' + id + '\n';
}

var selectedstyle = 'Yotsuba B';
var styles = [
	['Yotsuba B', '/board/default.css'],
	['Yotsuba', '/board/yotsuba.css']
];
var saved = {};

function changeStyle(x) {
	localStorage.stylesheet = styles[x][1];
	document.getElementById('stylesheet').href = styles[x][1];
	selectedstyle = styles[x][0];
}

if(localStorage.stylesheet) {
	for(x=0;x<styles.length;x++) {
		if(styles[x][1] == localStorage.stylesheet) {
			changeStyle(x);
			break;
		}
	}
}

function rememberStuff() {
	if(document.forms.post) {
		if(!localStorage.password)
			localStorage.password = generatePassword();
		document.forms.post.password.value = localStorage.password;
		
		if(localStorage.name)
			document.forms.post.name.value = localStorage.name;
		if(localStorage.email)
			document.forms.post.email.value = localStorage.email;
			
		if(sessionStorage.body) {
			saved = JSON.parse(sessionStorage.body);
			if(get_cookie('serv')) {
				// Remove successful posts
				successful = JSON.parse(get_cookie('serv'));
				for (var url in successful) {
					saved[url] = null;
				}
				sessionStorage.body = JSON.stringify(saved);
				
				document.cookie = 'serv={};expires=0;path=/;';
			}
			if(saved[document.location]) {
				document.forms.post.body.value = saved[document.location];
			}
		}
		
		if(localStorage.body) {
			document.forms.post.body.value = localStorage.body;
			localStorage.body = '';
		}
	}
}

function init()
{
	newElement = document.createElement('div');
	newElement.className = 'styles';
	
	for(x=0;x<styles.length;x++) {
		style = document.createElement('a');
		style.innerHTML = '[' + styles[x][0] + ']';
		style.href = 'javascript:changeStyle(' + x + ');';
		if(selectedstyle == styles[x][0])
			style.className = 'selected';
		newElement.appendChild(style);
	}	
	
	document.getElementsByTagName('body')[0].insertBefore(newElement, document.getElementsByTagName('body')[0].lastChild)
	
	if(document.forms.delete) {
		document.forms.delete.password.value = localStorage.password;
	}
	
	if (window.location.hash.indexOf('q') == 1)
		citeReply(window.location.hash.substring(2));
	else if (window.location.hash.substring(1))
		highlightReply(window.location.hash.substring(1));
	
	link = document.getElementsByTagName('a');
	for ( i in link ) {
		if(typeof link[i] == "object" && link[i].childNodes[0].src) {
			link[i].onclick = function() {
				if(!this.tag) {
					this.tag = this.childNodes[0].src;
					this.childNodes[0].src = this.href;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height = 'auto';
					this.childNodes[0].style.opacity = '0.4';
					this.childNodes[0].style.filter = 'alpha(opacity=40)';
					this.childNodes[0].onload = function() {
						this.style.opacity = '1';
						this.style.filter = '';
					}
				} else {
					this.childNodes[0].src = this.tag;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height = 'auto';
					this.tag = '';
				}
				return false;
			}
			
		}
	}
}

window.onload = init;
