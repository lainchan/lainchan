onready(function(){
	var flagStorage = "flag_"+window.location.pathname.split('/')[1];
	var item = window.localStorage.getItem(flagStorage);
	$('select[name=user_flag]').val(item);
	$('select[name=user_flag]').change(function() {
		window.localStorage.setItem(flagStorage, $(this).val());
	});
});