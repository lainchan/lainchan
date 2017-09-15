if (active_page === "thread" || active_page === "index" ||  active_page === "ukko") {

$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend>Editor Dialog </legend>"
	+ ("<label class='tinymcec' id='tinymce'><input type='checkbox' /> Enable TinyMCE WYSIWYG Editor</label>")
	+ "</fieldset>");
}

$('.tinymcec').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.tinymce) {
	localStorage.tinymce = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('tinymce')) $('#tinymce>input').prop('checked', 'checked');

function inittinymce() { 
	if (!getSetting("tinymce")) {return;}
	tinymce.init({
	    selector: 'body'
	});
}
inittinymce();
});
}
