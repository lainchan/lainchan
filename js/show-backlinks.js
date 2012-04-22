/*
 * show-backlinks.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/show-backlinks.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/show-backlinks.js';
 *
 */

$(document).ready(function(){
	$('div.post.reply').each(function() {
		var reply_id = $(this).attr('id').replace(/^reply_/, '');
		
		$(this).find('p.body a:not([rel="nofollow"])').each(function() {
			var id, post, mentioned;
		
			if(id = $(this).text().match(/^>>(\d+)$/))
				id = id[1];
			else
				return;
		
			post = $('#reply_' + id);
			if(post.length == 0)
				return;
		
			mentioned = post.find('p.intro span.mentioned');
			if(mentioned.length == 0)
				mentioned = $('<span class="mentioned"></span>').appendTo(post.find('p.intro'));
		
			mentioned.append('<a onclick="highlightReply(\'' + reply_id + '\');" href="#' + reply_id + '">&gt;&gt;' + reply_id + '</a>');
		});
	});
});

