/*
 * banner-reload-helper.js
 * https://github.com/savetheinternet/Tinyboard-Tools/blob/master/js/banner-reload-helper.js
 *
 * For 4chon.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = $config['root'] . 'banner-reload-helper.js';
 *
 */

$(document).ready(function(){
	var img = document.getElementsByTagName('img')[0];
	if(img.className != 'banner')
		return;
	img.src = img.src + '?' + new Date().getTime();
});

