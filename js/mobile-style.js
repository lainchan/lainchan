onready(function(){
        if(navigator.userAgent.match(/iPhone|iPod|iPad|Android|Opera Mini|Blackberry|PlayBook/i)) {
		$('html').addClass("mobile-style");
        }
	else {
		$('html').addClass("desktop-style");
	}
})
