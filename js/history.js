$(document).ready(function(){
	$('.history_item').hide();
	$('.edit_history').prepend('This post was edited by the user. <a href="#" class="view_history">Click here to toggle the edit history.</a> ');
	$('.view_history').click(function(element) {
		$(this).parent().find('.history_item').toggle();
		return false;
	});
});