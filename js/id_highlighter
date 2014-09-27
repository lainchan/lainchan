Array.prototype.remove = function(v) { this.splice(this.indexOf(v) == -1 ? this.length : this.indexOf(v), 1); }

var idshighlighted = [];

function getPostsById(id){
	return $(".poster_id").filter(function(i){
		return $(this).text() == id;
	});
}

function getMasterPosts(parents){
	if(!parents.hasClass("post")) return;
	
	var toRet = [];
	
	$(parents).each(function(){
		if($(this).hasClass("post"))
			toRet.push($(this));
	});
	
	return toRet;
}

$(".poster_id").click(function(){
	var id = $(this).text();
	
	if($.inArray(id, idshighlighted) !== -1){
		idshighlighted.remove(id);
		
		$(getMasterPosts(getPostsById(id).parents())).each(function(i){
			$(this).removeClass("highlighted");
		});
	}else{
		idshighlighted.push(id);
		
		$(getMasterPosts(getPostsById(id).parents())).each(function(i){
			$(this).addClass("highlighted");
		});
	}
});
