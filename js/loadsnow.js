if (active_page === "catalog" || active_page === "thread" || active_page === "index" ||  active_page === "ukko") {

$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend>Special Event Effects </legend>"
	+ ("<label class='event-effect' id='eventeffect'><input type='checkbox' /> Enable Special Event Effects</label>")
	+ "</fieldset>");
}

$('.event-effect').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.eventeffect) {
	localStorage.eventeffect = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('eventeffect')) $('#eventeffect>input').prop('checked', 'checked');

function initBalloons() { //Pashe, influenced by tux, et al, WTFPL
	if (!getSetting("eventeffect")) {return;}
	snowStorm.start();
}
initBalloons();
});
}
