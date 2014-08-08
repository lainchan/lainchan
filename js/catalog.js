if (active_page == 'catalog') $(function(){

	$("#sort_by").change(function(){
		var value = this.value;
		$("#sort-"+value).trigger("click");
	});

	$("#image_size").change(function(){
		var value = this.value, old;
		if (value == "small") {
			old = "large";
		} else {
			old = "small";
		}
		$(".grid-li").removeClass("grid-size-"+old);
		$(".grid-li").addClass("grid-size-"+value);
	});

	$('#Grid').mixitup({
	});

});
