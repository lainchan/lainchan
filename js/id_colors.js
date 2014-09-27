$.hash = function(str) {
	var i, j, msg = 0;
	
	for (i = 0, j = str.length; i < j; ++i) {
		msg = ((msg << 5) - msg) + str.charCodeAt(i);
	}
	
	return msg;
};

function stringToRGB(str){
	var rgb, hash;
	
	rgb = [];
	hash = $.hash(str);
	
	rgb[0] = (hash >> 24) & 0xFF;
	rgb[1] = (hash >> 16) & 0xFF;
	rgb[2] = (hash >> 8) & 0xFF;
	
	return rgb;
}

$(".poster_id").each(function(){
	var rgb = stringToRGB($(this).text());
	
	$(this).css({
		"background-color": "rgb("+rgb[0]+", "+rgb[1]+", "+rgb[2]+")",
		"padding": "3px 5px",
		"border-radius": "8px",
		"color": "white"
	});
});

