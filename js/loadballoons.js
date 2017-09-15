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
	localStorage.eventeffect = 'true';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('eventeffect')) $('#eventeffect>input').prop('checked', 'checked');

function initBalloons() { //Pashe, influenced by tux, et al, WTFPL
	if (!getSetting("eventeffect")) {return;}

floatingLove({
        'minSpeed': 1.5,        //Minimum vertical speed
        'maxSpeed': 2,          //Maximum vertical speed
        'minAmplitude': 0.5,    //Minimum amplitude (>0)
        'maxAmplitude': 1.5,    //Maximum amplitude (>0)
        'minFrequency': 0.08,    //Maximum Frequency (>0)
        'maxFrequency': 0.1,    //Maximum Frequency (>0)
        'minAlpha': 0.7,        //Minimum opacity (0-1)
        'maxAlpha': 0.8,        //Maximum opacity (0-1)
        'minScale': 0.2,        //Minimum size multiplier (0-1)
        'maxScale': 0.8,        //Maximum size multiplier (0-1)
        'interval': 1000,       //Time gap between each heart
        'delay': 1000           //Starting delay from initialization
    }).init();
}
initBalloons();
});
}
