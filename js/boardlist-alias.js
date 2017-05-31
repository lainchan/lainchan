$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend> Board List Alias </legend>"
	+ ("<label class='boardlist-tinyalias' id='boardlisttinyalias' style='padding:0px;'><input type='checkbox' /> Enable Tiny  Board List Alias </label>")
	+ ("<label class='boardlist-legacyalias' id='boardlistlegacyalias' style='padding:0px;'><input type='checkbox' /> Enable Legacy Names  Board List Alias </label>")
	+ ("<label class='boardlist-unicodealias' id='boardlistunicodealias' style='padding:0px;'><input type='checkbox' /> Enable Unicode Board List Alias </label>")
	+ ("<label class='boardlist-hideoverboards' id='boardlisthideoverboards' style='padding:0px;'><input type='checkbox' /> Enable Overboard hiding </label>")
	+ ("<label class='boardlist-hideunderboards' id='boardlisthideunderboards' style='padding:0px;'><input type='checkbox' /> Enable Underboard hiding </label>")
	+ "</fieldset>");
}

$('.boardlist-tinyalias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.boardlist-legacyalias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.boardlist-unicodealias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.boardlist-hideoverboards').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.boardlist-hideunderboards').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.boardlisttinyalias) {
	localStorage.boardlistshortalias = 'false';
}
if (!localStorage.boardlistlegacyalias) {
	localStorage.boardlistlegacyalias = 'false';
}
if (!localStorage.boardlistunicodealias) {
	localStorage.boardlistunicodealias = 'false';
}
if (!localStorage.boardlisthideoverboards) {
	localStorage.boardlisthideoverboards = 'false';
}
if (!localStorage.boardlisthideunderboards) {
	localStorage.boardlisthideunderboards = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('boardlisttinyalias')) $('#boardlisttinyalias>input').prop('checked', 'checked');
if (getSetting('boardlistlegacyalias')) $('#boardlistshortalias>input').prop('checked', 'checked');
if (getSetting('boardlistunicodealias')) $('#boardlistunicodealias>input').prop('checked', 'checked');
if (getSetting('boardlisthideoverboards')) $('#boardlisthideoverboards>input').prop('checked', 'checked');
if (getSetting('boardlisthideunderboards')) $('#boardlisthideunderboards>input').prop('checked', 'checked');

function initBoardListAlias() {

  do_boardlist_alias = function() {
    var categories = [];
    var topbl = $('.boardlist:first');

    topbl.find('>.sub').each(function() {
      var cat = {name: $(this).data('description'), boards: []};
      if (getSetting("boardlisthideoverboards")){
	if (cat.name === "Overboards 1"){
		$(this).hide();
	}
      }
      if (getSetting("boardlisthideunderboards")){
	if (cat.name === "People"){
		$(this).hide();
	}
      
      }
      $(this).find('a').each(function() {
      var board = $(this).html(); 
      var menuitemname = board;

      var tinyalias = {"$$$" : "$", "rules" : "law" , "faq" : "?" , "news" : "n" , "diy" : "Δ", "sec" : "s", "tech" : "Ω", "inter" : 'i', "lit" : "l", "music" : "mu" , "vis" : "v" , "hum" : "h", "drg" : "d" , "zzz" : "z" , "layer" : "ddt" ,"cult" : "c" , "psy" : "p", "mega" : "me" , "random" : "ra", "radio" : "rad", "stream" : "mov"};
      var legacyalias = {  "Δ" : "diy",  "Ω" : "tech", "drug" : "drg", "hum" : "feels"};
      var unicodealias = {"$$$": "&#x1F4B8", "rules" : "&#x2696&#xFE0F" , "faq" : "&#x2049&#xFE0F" , "news" : "&#x1F4F0" , "diy" : "&#x1F527" , "Δ" : "&#x1F527", "sec" : "&#x1F512", "tech" : "&#x1F4BB", "Ω" : "&#x1F4BB", "inter" : "&#x1F3AE", "lit" : "&#x270D&#xFE0F", "music" : "&#x1F3BC" , "vis" : "&#x1F3A8" , "hum" : "&#x1F465", "drg" : "&#x1F48A" , "drug" :  "&#x1F48A" , "zzz" : "&#x1F4A4" , "layer" : "&#x3299&#xFE0F" ,"cult" : "&#x1F3AD" , "psy" : "&#x1F386", "mega" : "&#x1F4E3" , "random" : "&#x1F3B2", "radio" : "&#x1F4FB", "stream" : "&#x1F4FA", "zine" : "&#x1F4D3", "irc" : "&#x1F4DD", "q" : "&#x2753", "r" : "&#x1F3B2"};

        if (getSetting("boardlisttinyalias")) {
		if (board in tinyalias){
			menuitemname = tinyalias[board];
		} 
        }
	else if (getSetting("boardlistlegacyalias")){
		if (board in legacyalias){
			menuitemname = legacyalias[board];
		} 
	  
	}
 	else if (getSetting("boardlistunicodealias")){
		if (board in unicodealias){
			menuitemname = unicodealias[board];
		} 
	}
    $(this).html(menuitemname);
    });
    do_boardlist_alias = undefined;

  });
  if (typeof twemoji.parse !== 'undefined') {
	if (!getSetting("emojiimagefallback")) {return;}
	var twemoji_opts = {
	    callback: function(icon, options, variant) {
		switch ( icon ) {
		    case 'a9':      // copyright
		    case 'ae':      // (R)
		    case '2122':    // TM
                    case '25b6':    // post filter 
			return false;
		}
		return ''.concat(options.base, options.size, '/', icon, options.ext);
	    }
	}
	twemoji.parse(document.body, twemoji_opts);
  }
  }
  do_boardlist_alias();
}
initBoardListAlias();
});

