/*
 * show-op
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/show-op.js
 *
 * Adds "(OP)" to >>X links when the OP is quoted.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/show-op.js';
 *
 */

$(document).ready(function(){
	var showOPLinks = function() {
		var OP;
		
		if ($('div.banner').length == 0) {
			OP = $(this).parent().find('div.post.op a.post_no:eq(1)').text();
		} else {
			OP = $('div.post.op a.post_no:eq(1)').text();
		}
		
		$(this).find('div.body a:not([rel="nofollow"])').each(function() {
			var postID;
			
			if(postID = $(this).text().match(/^>>(\d+)$/))
				postID = postID[1];
			else
				return;
			
			if (postID == OP) {
				$(this).after(' <small>(OP)</small>');
			}
		});
	};
	
	$('div.post.reply').each(showOPLinks);
	
	// allow to work with auto-reload.js, etc.
	$(document).on('new_post', function(e, post) {
		$(post).each(showOPLinks);
	});
});

