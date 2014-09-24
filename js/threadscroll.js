var hoverElem = null;

$(document).mouseover(function(e){
	var x = e.clientX, y = e.clientY,
		elementOnMouseOver = document.elementFromPoint(x, y);
		hoverElem = $(elementOnMouseOver);
});

$(document).keydown(function(e){
	//Up arrow
	if(e.which == 38){
		var ele = hoverElem;
		
		if(ele.parent().attr("class") == "post op" || ele.attr("class") == "body" || ele.attr("class") == "post-image" || ele.parent().attr("class") == "body" || ele.parent().attr("class") == "body" || (ele.parent().attr("id")!= null && ele.parent().attr("id").match("^thread") != null) || (ele.parent().attr("for")!= null && ele.parent().attr("id").match("delete") != null)){
			var thread = (ele.parent().attr("class") == "post op") ? ele.parent().parent() : ele.parent();
			thread = (thread.attr("class") == "post reply") ? thread.parent() : thread;
			thread = (thread.attr("class") == "body") ? thread.parent().parent() : thread;
			thread = (ele.attr("class") == "post-image") ? thread.parent().parent().parent().parent() : thread; 
			
			if(thread.attr("id") == null) thread = ele.parent().parent().parent().parent(); //op image
			
			if(thread.prev().attr("id") != null){
				if(thread.prev().attr("id").match("^thread")){
					window.location.href = window.location.protocol+"//"+window.location.host+window.location.pathname+"#"+thread.prev().attr("id");
				}
			}
		}
	//Down arrow
	}else if(e.which == 40){
		var ele = hoverElem;
		
		if(ele.parent().attr("class") == "post op" || ele.attr("class") == "body" || ele.attr("class") == "post-image" || ele.parent().attr("class") == "body" || ele.parent().attr("class") == "body" || (ele.parent().attr("id")!= null && ele.parent().attr("id").match("^thread") != null) || (ele.parent().attr("for")!= null && ele.parent().attr("id").match("delete") != null)){
			var thread = (ele.parent().attr("class") == "post op") ? ele.parent().parent() : ele.parent();
			thread = (thread.attr("class") == "post reply") ? thread.parent() : thread;
			thread = (thread.attr("class") == "body") ? thread.parent().parent() : thread;
			thread = (ele.attr("class") == "post-image") ? thread.parent().parent().parent().parent() : thread; 
			
			if(thread.attr("id") == null) thread = ele.parent().parent().parent().parent(); //op image
			
			if(thread.next().attr("id") != null){
				if(thread.next().attr("id").match("^thread")){
					window.location.href = window.location.protocol+"//"+window.location.host+window.location.pathname+"#"+thread.next().attr("id");
				}
			}
		}
	}
});
