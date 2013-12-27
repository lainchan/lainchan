if (device_type == 'desktop') {
  compact_boardlist = true;

  var do_css = function() {
    $('#compact-boardlist-css').remove();
                        
    $('<style type="text/css" id="compact-boardlist-css">\
      .compact-boardlist {\
	padding: 3px;\
	padding-bottom: 0px;\
      }\
      .cb-item {\
        display: inline-block;\
        vertical-align: middle;\
      }\
      .cb-icon {\
        padding-bottom: 1px;\
      }\
      .cb-fa {\
        font-size: 21px;\
        padding: 2px;\
        padding-top: 0;\
      }\
      .cb-cat {\
        padding: 5px 6px 8px 6px;\
      }\
      .cb-menuitem {\
	display: table-row;\
      }\
      .cb-menuitem span {\
        padding: 5px;\
        display: table-cell;\
	text-align: left;\
	border-top: 1px solid rgba(0, 0, 0, 0.5);\
      }\
      .cb-menuitem span.cb-uri {\
	text-align: right;\
        padding-left: 0;\
      }\
    </style>').appendTo($("head"));
  };

  do_boardlist = function() {
    do_css();

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
        $('<a class="cb-item cb-fa" href="'+categories[i].boards[0].href+'"><i class="icon-'+icon+' icon"></i></a>')
          .appendTo(topbl)
      }
      else {
        $("<a class='cb-item cb-cat' href='javascript:void(0)'>"+item.name+"</a>")
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
              if (board.name) {
	        tag = $("<a href='"+board.href+"'><span>"+board.name+"</span><span class='cb-uri'>/"+board.uri+"/</span></a>")
	      }
	      else {
	        tag = $("<a href='"+board.href+"'><span>"+board.uri+"</span><span class='cb-uri'><i class='icon icon-globe'></i></span></a>")
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
}
