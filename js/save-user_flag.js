onready(function(){
	var uri = window.location.pathname.substr(1);
	var flagStorage = uri.slice(0, -1)+'_flag';
	var item = window.localStorage.getItem(flagStorage);
	$('select[name=user_flag]').val(item);
	$('select[name=user_flag]').change(function() {
		window.localStorage.setItem(flagStorage, $(this).val());
	});
});