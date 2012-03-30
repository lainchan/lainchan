/*
 * smartphone-spoiler.js
 * https://github.com/savetheinternet/Tinyboard-Tools/blob/master/js/smartphone-spoiler.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/smartphone-spoiler.js';
 *
 */

$(document).ready(function(){
	/* This needs to be expanded upon: */
	var is_mobile = navigator.userAgent.match(/iPhone|iPod|iPad|Android/i);
	
	if(is_mobile) {
		$('span.spoiler').each(function() {
			$(this).click(function() {
				if($(this).hasClass('show'))
					$(this).css('color', 'black').removeClass('show');
				else
					$(this).css('color', 'white').addClass('show');
			});
		});
	}
});

