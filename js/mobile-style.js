if(navigator.userAgent.match(/iPhone|iPod|iPad|Android|Opera Mini|Blackberry|PlayBook|Windows Phone|Tablet PC|Windows CE|IEMobile/i)) {
	$('html').addClass("mobile-style");
	device_type = "mobile";
}
else {
	$('html').addClass("desktop-style");
	device_type = "desktop";
}
