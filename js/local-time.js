/*
 * local-time.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/local-time.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/local-time.js';
 *
 */

onload(function(){
	var iso8601 = function(s) {
		s = s.replace(/\.\d\d\d+/,""); // remove milliseconds
		s = s.replace(/-/,"/").replace(/-/,"/");
		s = s.replace(/T/," ").replace(/Z/," UTC");
		s = s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"); // -04:00 -> -0400
		return new Date(s);
	};
	var zeropad = function(num, count) {
		return [Math.pow(10, count - num.toString().length), num].join('').substr(1);
	};
	
	var times = document.getElementsByTagName('time');
	
	for(var i = 0; i < times.length; i++) {
		if(!times[i].innerHTML.match(/^\d+\/\d+\/\d+ \(\w+\) \d+:\d+:\d+$/))
			continue;
		
		var t = iso8601(times[i].getAttribute('datetime'));
		
		
		
		times[i].innerHTML =
			// date
			zeropad(t.getMonth() + 1, 2) + "/" + zeropad(t.getDate(), 2) + "/" + t.getFullYear().toString().substring(2) +
			" (" + ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"][t.getDay()]  + ") " +
			// time
			zeropad(t.getHours(), 2) + ":" + zeropad(t.getMinutes(), 2) + ":" + zeropad(t.getSeconds(), 2);
	};
});

