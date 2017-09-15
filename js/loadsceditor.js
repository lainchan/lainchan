if (active_page === "thread" || active_page === "index" ||  active_page === "ukko") {

$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend>Editor Dialog </legend>"
	+ ("<label class='sceditorc' id='sceditor'><input type='checkbox' /> Enable SCEditor WYSIWYG Editor</label>")
	+ "</fieldset>");
}

$('.sceditorc').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.sceditor) {
	localStorage.sceditor = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('sceditor')) $('#sceditor>input').prop('checked', 'checked');

function initsceditor() { 
	if (!getSetting("sceditor")) {return;}
	$('.format-text').toggle();		
		
	$.sceditor.plugins.bbcode.bbcode.set('spoiler', {
		    tags: {
			            'span': {
					                'class': ['spoiler']
								        }
				        },
		    format: '[spoiler]{0}[/spoiler]',
			        html: '<span class="spoiler">{0}</span>'
	});
	$.sceditor.plugins.bbcode.bbcode.set('t', {
		    tags: {
			            'span': {
					                'class': ['heading']
								        }
				        },
		    format: '\t=={0}==\t',
			        html: '<span class="heading">{0}</span>'
	});
	$.sceditor.command.set("spoiler", {
	    exec: function() {
    	    this.insert("[spoiler]", "[/spoiler]");
			},
	    txtExec: ["[spoiler]", "[/spoiler]"],
	    tooltip: "Spoiler (CTRL+S)",
	});
	$.sceditor.command.set("t", {
	    exec: function() {
    	    this.insert("\t==", "==\t");
			},
	    txtExec: ["\t==", "==\t"],
	    tooltip: "Heading (CTRL+T)",
        });
	$('#body').sceditor({
	  plugins: 'bbcode',
	  style: $('#stylesheet').attr("href"), 
	  //style: '/stylesheets/sceditor/jquery.sceditor.default.min.css',
          toolbar: "bold,italic,t,spoiler,code|source",
	  emoticonsEnabled : false,
          autoUpdate : true,
	});
	var backgroundcolor = $('#body').css('background-color');
	$('.sceditor-container').css('background-color',backgroundcolor);
	$('.sceditor-toolbar').css('background-color',backgroundcolor);
	$('#body').sceditor('instance').css('body { background-color: ' + backgroundcolor +'; }');
	
	/*$(document).on('ajax_before_post', function (e, formData) {
		formData.set(body, $("#body").sceditor('instance').val());
	});

	$(document).on('ajax_after_post', function () {
		$("#body").sceditor('instance').val("");
	});*/
}
initsceditor();
});
}
