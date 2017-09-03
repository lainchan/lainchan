if (active_page === "thread" || active_page === "index" ||  active_page === "ukko") {

$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend>Editor Dialog </legend>"
	+ ("<label class='summer-note' id='summernote'><input type='checkbox' /> Enable Summernote WYSIWYG Editor</label>")
	+ "</fieldset>");
}

$('.summer-note').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.summernote) {
	localStorage.summernote = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('summernote')) $('#summernote>input').prop('checked', 'checked');

function initsummernote() { 
	if (!getSetting("summernote")) {return;}
	$('#body').summernote();
}
initsummernote();
});
}
