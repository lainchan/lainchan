if (active_page == 'catalog') $(function(){

	$("#sort_by").change(function(){
		var value = this.value;
		$("#sort-"+value).trigger("click");
	});

	$("#image_size").change(function(){
		var value = this.value, old;
		$(".grid-li").removeClass("grid-size-vsmall");
		$(".grid-li").removeClass("grid-size-small");
		$(".grid-li").removeClass("grid-size-large");
		$(".grid-li").addClass("grid-size-"+value);
	});

	$('#Grid').mixitup({});
});
