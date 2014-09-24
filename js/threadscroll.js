if(active_page == "index" || active_page == "ukko"){
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
			var par = $(ele).parents('div[id^="thread_"]');
			
			if(par.length == 1){
				if(par.prev().attr("id") != null){
					if(par.prev().attr("id").match("^thread")){
						window.location.href = window.location.protocol+"//"+window.location.host+window.location.pathname+"#"+par.prev().attr("id");
					}
				}
			}
		//Down arrow
		}else if(e.which == 40){
			var ele = hoverElem;
			var par = $(ele).parents('div[id^="thread_"]');
			
			if(par.length == 1){
				if(par.next().attr("id") != null){
					if(par.next().attr("id").match("^thread")){
						window.location.href = window.location.protocol+"//"+window.location.host+window.location.pathname+"#"+par.next().attr("id");
					}
				}
			}
		}
	});
}
