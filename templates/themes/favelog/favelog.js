$().ready(function(){

	$("#selectorzilla").change(function(){
		sheit = this.value;
		$("#sort-"+sheit).trigger("click");
	});

	$("#imgurzilla").change(function(){
		sheit = this.value;
		if (sheit == "small") {
			old = "large";
		} else {
			old = "small";
		}
		$(".grid-li").removeClass("grid-size-"+old);
		$(".grid-li").addClass("grid-size-"+sheit);
	});

	$('#Grid').mixitup({
		onMixEnd: function(){
			if(use_tooltipster) {
				buildTooltipster();
			}
		}
	});

	if(use_tooltipster) {
		buildTooltipster();
	}

});

function buildTooltipster(){
	$(".thread-image").each(function(){
		subject = $(this).attr('data-subject');
		name = $(this).attr('data-name');
		muhdifference = $(this).attr('data-muhdifference');
		last_reply = $(this).attr('data-last-reply');
		last_subject = $(this).attr('data-last-subject');
		last_name = $(this).attr('data-last-name');
		last_difference = $(this).attr('data-last-difference');
		muh_body = '<span="poster-span">';
		
		if (subject) {
			muh_body = muh_body + subject + '&nbsp;por';
		} else {
			muh_body = muh_body + 'Postado por';
		};
		muh_body = muh_body + '&nbsp;<span class="poster-name">' + name + '&nbsp;</span>' + muhdifference + '</span>';

		if (last_reply) {
			muh_body = muh_body + '<br><span class="last-reply-span">';
			if (last_subject) {
				muh_body = muh_body + last_subject + '&nbsp;por';
			} else{
				muh_body = muh_body + 'Última resposta por';
			};
			muh_body = muh_body + '&nbsp;<span class="poster-name">' + last_name + '&nbsp;</span>' + last_difference + '</span>';
		}
		$(this).tooltipster({
			content: $(muh_body)
		});
	});
}