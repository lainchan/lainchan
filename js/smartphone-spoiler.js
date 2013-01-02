/*
 * smartphone-spoiler.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/smartphone-spoiler.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/mobile-style.js';
 *   $config['additional_javascript'][] = 'js/smartphone-spoiler.js';
 *
 */

onready(function(){
	if(device_type == 'mobile') {
		var spoilers = document.getElementsByClassName('spoiler');
		for(var i = 0; i < spoilers.length; i++) {
			spoilers[i].onmousedown = function() {
				this.style.color = 'white';
			};
		}
	}
});

