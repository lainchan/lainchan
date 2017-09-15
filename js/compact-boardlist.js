/*
 * compact-boardlist.js - a compact boardlist implementation making it
 *                        act more like a menubar
 * https://github.com/vichan-devel/Tinyboard/blob/master/js/compact-boardlist.js
 *
 * Released under the MIT license
 * Copyright (c) 2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['boards'] = array(
 *     "icon_vichan" => array('*'), # would refer to /static/icons/vichan.png
 *     "Regular" => array('b', 'cp', 'r+oc', 'id', 'waifu'),
 *     "Topical" => array('sci', "Offsite board name" => '//int.vichan.net/s/'),
 *     "fa_search" => array("search" => "/search.php") # would refer to a search 
 *                                                     # font-awesome icon
 *   )
 *
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/mobile-style.js';
 *   $config['additional_javascript'][] = 'js/compact-boardlist.js';
 *   //$config['additional_javascript'][] = 'js/watch.js';
 *
 */
$(document).on("ready", function() {
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab("general", 
	"<fieldset><legend> Compact Board List </legend>"
	+ ("<label class='compact-boardlist' id='compactboardlist' style='padding:0px;'><input type='checkbox' /> Enable Compact Board List </label>")
	+ ("<label class='compact-boardlisttinyalias' id='compactboardlisttinyalias'><input type='checkbox' /> Tiny Alias for Compact Board List </label>")
	+ ("<label class='compact-boardlistshortalias' id='compactboardlistshortalias'><input type='checkbox' /> Short Alias for Compact Board List </label>")
	+ ("<label class='compact-boardlistunicodealias' id='compactboardlistunicodealias'><input type='checkbox' /> Unicode Alias for Compact Board List </label>")
	+ "</fieldset>");
}

$('.compact-boardlist').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.compact-boardlisttinyalias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.compact-boardlistshortalias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});
$('.compact-boardlistunicodealias').on('change', function(){
	var setting = $(this).attr('id');

	localStorage[setting] = $(this).children('input').is(':checked');
	location.reload();
});

if (!localStorage.compactboardlist) {
	localStorage.compactboardlist = 'false';
}
if (!localStorage.compactboardlisttinyalias) {
	localStorage.compactboardlistshortalias = 'false';
}
if (!localStorage.compactboardlistshortalias) {
	localStorage.compactboardlistshortalias = 'false';
}
if (!localStorage.compactboardlistunicodealias) {
	localStorage.compactboardlistunicodealias = 'false';
}

function getSetting(key) {
	return (localStorage[key] == 'true');
}

if (getSetting('compactboardlist')) $('#compactboardlist>input').prop('checked', 'checked');
if (getSetting('compactboardlisttinyalias')) $('#compactboardlisttinyalias>input').prop('checked', 'checked');
if (getSetting('compactboardlistshortalias')) $('#compactboardlistshortalias>input').prop('checked', 'checked');
if (getSetting('compactboardlistunicodealias')) $('#compactboardlistunicodealias>input').prop('checked', 'checked');

function initCompactBoardList() { //Pashe, influenced by tux, et al, WTFPL
	if (!getSetting("compactboardlist")) {return;}

  do_boardlist = function() {
    var categories = [];
    var topbl = $('.boardlist:first');

    topbl.find('>.sub').each(function() {
      var cat = {name: $(this).data('description'), boards: []};
      $(this).find('a').each(function() {
        var board = {name: $(this).prop('title'), uri: $(this).html(), href: $(this).prop('href') }
        cat.boards.push(board);
      });
      categories.push(cat);
    });

    topbl.addClass("compact-boardlist")
       .html("");

    for (var i in categories) {
      var item = categories[i];

      if (item.name.match(/^icon_/)) {
        var icon = item.name.replace(/^icon_/, '')
        $("<a class='cb-item cb-icon' href='"+categories[i].boards[0].href+"'><img src='/static/icons/"+icon+".png'></a>")
  	  .appendTo(topbl)
      }
      else if (item.name.match(/^fa_/)) {
        var icon = item.name.replace(/^fa_/, '')
        $('<a class="cb-item cb-fa" href="'+categories[i].boards[0].href+'"><i class="fa-'+icon+' fa"></i></a>')
          .appendTo(topbl)
      }
      else if (item.name.match(/^d_/)) {
        var icon = item.name.replace(/^d_/, '')
        $('<a class="cb-item cb-cat" href="'+categories[i].boards[0].href+'">'+icon+'</a>')
          .appendTo(topbl)
      }
      else {
          var menuitemname = item.name;
	  var tinyalias = {"Notices" : "/n/", "STEM" : "/s/" , "People" : "/p/" , "Overboards 1" : "/ob1/" , "Overboards 2" : "/ob2/", "Elsewhere" : "/e/", "Services" : "/s/", "Misc" : '/m/', "Affiliates" : "/af/" };
	  var shortalias = {"Notices" : "/not/", "STEM" : "/stem/" , "People" : "/people/" , "Overboards 1" : "/ob1/" , "Overboards 2" : "/ob2/", "Elsewhere" : "/else/", "Services" : "/serv/", "Misc" : "/misc/", "Affiliates" : "/uboa and sushi/" };
	  var unicodealias = {"Notices" : "&#x2139&#xFE0F", "STEM" : "&#x1F468&#x200D&#x1F4BB " , "People" : "&#x1F465" , "Overboards 1" : "&#x1F4AC" , "Overboards 2" : "&#x1F4AD", "Elsewhere" : "&#x1F50D", "Services" : "&#x1F202", "Misc" : "&#x2049", "Affiliates" : "&#x1F363" };

	  if (getSetting("compactboardlisttinyalias")) {
	  	menuitemname = tinyalias[item.name];
	  }
	  else if (getSetting("compactboardlistshortalias")){
	  	menuitemname = shortalias[item.name];
	  
	  }
	  else if (getSetting("compactboardlistunicodealias")){
	  	menuitemname = unicodealias[item.name];
	  }

	  $("<a class='cb-item cb-cat' href='javascript:void(0)'>"+ menuitemname+"</a>")
 	  .appendTo(topbl)
	  .mouseenter(function() {
	    var list = $("<div class='boardlist top cb-menu'></div>")
	      .css("top", $(this).position().top + 13 + $(this).height())
	      .css("left", $(this).position().left)
	      .css("right", "auto")
	      .appendTo(this);
	    for (var j in this.item.boards) {
	      var board = this.item.boards[j];
            
	      var tag;
              var menuitemname = board.uri;
	      var tinyalias = {"$$$" : "$", "rules" : "law" , "faq" : "?" , "news" : "n" , "diy" : "Δ", "sec" : "s", "tech" : "Ω", "inter" : 'i', "lit" : "l", "music" : "mu" , "vis" : "v" , "hum" : "h", "drg" : "d" , "zzz" : "z" , "layer" : "ddt" ,"cult" : "c" , "psy" : "p", "mega" : "me" , "random" : "ra", "radio" : "rad", "stream" : "mov", "cal" : "ca"};
	      var legacyalias = {  "Δ" : "diy",  "Ω" : "tech", "drug" : "drg", "hum" : "feels"};
	      var unicodealias = {"$$$": "&#x1F4B8", "rules" : "&#x2696&#xFE0F" , "faq" : "&#x2049&#xFE0F" , "news" : "&#x1F4F0" , "diy" : "&#x1F527" , "Δ" : "&#x1F527", "sec" : "&#x1F512", "tech" : "&#x1F4BB", "Ω" : "&#x1F4BB", "inter" : "&#x1F3AE", "lit" : "&#x270D&#xFE0F", "music" : "&#x1F3BC" , "vis" : "&#x1F3A8" , "hum" : "&#x1F465", "drg" : "&#x1F48A" , "drug" :  "&#x1F48A" , "zzz" : "&#x1F4A4" , "layer" : "&#x3299&#xFE0F" ,"cult" : "&#x1F3AD" , "psy" : "&#x1F386", "mega" : "&#x1F4E3" , "random" : "&#x1F3B2", "radio" : "&#x1F4FB", "stream" : "&#x1F4FA", "zine" : "&#x1F4D3", "irc" : "&#x1F4DD", "q" : "&#x2753", "r" : "&#x1F3B2", "cal" : "&#x1f4c5"};

	      if (getSetting("compactboardlisttinyalias")) {
	  	  menuitemname = tinyalias[board.uri];
	      }
	      else if (getSetting("compactboardlistshortalias")){
	  	  menuitemname = shortalias[board.uri];
	      }
	      else if (getSetting("compactboardlistunicodealias")){
	  	  menuitemname = unicodealias[board.uri];
	      }
	      if (typeof menuitemname === "undefined"){
		  menuitemname = board.uri;
	      }
	      
	      if (getSetting("boardlistmegaq")) {
		  if (board === "mega"){
			$(this).attr("href", "https://lainchan.org/megaq/index.html");
		  }
	      }

              if (board.name) {
	        tag = $("<a href='"+board.href+"'><span>"+board.name+"</span><span class='cb-uri'>"+menuitemname+"</span></a>")
	      }
	      else {
	        tag = $("<a href='"+board.href+"'><span>"+board.uri+"</span><span class='cb-uri'><i class='fa fa-globe'></i></span></a>")
	      }
	      tag
		.addClass("cb-menuitem")
                .appendTo(list)
	    }
	  })
	  .mouseleave(function() {
	    topbl.find(".cb-menu").remove();
	  })[0].item = item;
      }
    }
    do_boardlist = undefined;
  };
do_boardlist();
options_handler = $("<div id='options_handler'></div>").css("display", "none");
options_background = $("<div id='options_background'></div>").on("click", Options.hide).appendTo(options_handler);
options_div = $("<div id='options_div'></div>").appendTo(options_handler);
options_close = $("<a id='options_close' href='javascript:void(0)'><i class='fa fa-times'></i></div>")
  .on("click", Options.hide).appendTo(options_div);
options_tablist = $("<div id='options_tablist'></div>").appendTo(options_div);
  options_button = $("<a href='javascript:void(0)' title='"+_("Options")+"'>["+_("Options")+"]</a>");

  if ($(".boardlist.compact-boardlist").length) {
    options_button.addClass("cb-item cb-fa").html("<i class='fa fa-gear'></i>");
  }

  if ($(".boardlist:first").length) {
    options_button.css('float', 'right').appendTo($(".boardlist:first"));
  }
  else {
    var optsdiv = $('<div style="text-align: right"></div>');
    options_button.appendTo(optsdiv);
    optsdiv.prependTo($(document.body));
  }

  options_button.on("click", Options.show);

  options_handler.appendTo($(document.body));
  if (typeof watchlist.render !== 'undefined') {
	 $('.boardlist.compact-boardlist').append(' <a class="watchlist-toggle cb-item cb-cat" href="#"><span>['+_('watchlist')+']</span></a>');
	  watchlist.render();
  }
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
initCompactBoardList();
});
