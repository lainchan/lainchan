/*
 * post-hover.js
 * https://github.com/savetheinternet/Tinyboard-Tools/blob/master/js/post-hover.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/post-hover.js';
 *
 */

$(document).ready(function(){
	$('p.body a:not([rel="nofollow"])').each(function() {
		var id;
		
		if(id = $(this).text().match(/^>>(\d+)$/)) {
			id = id[1];
		}
		
		var post = $('div.post#reply_' + id);
		$(this).hover(function() {
			post.attr('style', 'border-style: none dashed dashed none; background: ' + post.css('border-color'));
		}, function() {
			post.attr('style', '');
		});
	});
});

