if (active_page === "thread" || active_page === "index" ||  active_page === "ukko") {

$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend>Editor Dialog </legend>"
	+ ("<label class='quillc' id='quill'><input type='checkbox' /> Enable Quill WYSIWYG Editor</label>")
	+ "</fieldset>");
}

$('.quillc').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.quill) {
	localStorage.quill = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('quill')) $('#quill>input').prop('checked', 'checked');

function initquill() { 
	if (!getSetting("quill")) {return;}
	var quill = new Quill('#body', {
		  modules: {
			      toolbar: [
				            [{ header: [1, 2, false] }],
					          ['bold', 'italic', 'underline'],
					          ['image', 'code-block']
							      ]
							        },
		  placeholder: 'Compose an epic...',
			    theme: 'snow'  // or 'bubble'
	});
}
initquill();
});
}
