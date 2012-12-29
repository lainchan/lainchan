onready(function(){
        if(navigator.userAgent.match(/iPhone|iPod|iPad|Android|Opera Mini|Blackberry|PlayBook|Windows Phone|Tablet PC|Windows CE|IEMobile/i)) {
		$('html').addClass("mobile-style");
        }
	else {
		$('html').addClass("desktop-style");
	}
})
