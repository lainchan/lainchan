/*
 * quick-reply.js
 * https://github.com/savetheinternet/Tinyboard-Tools/blob/master/js/quick-reply.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = $config['root'] . 'jquery.min.js';
 *   $config['additional_javascript'][] = $config['root'] . 'quick-reply.js';
 *
 */

$(document).ready(function(){
	if($('div.banner').length != 0)
		return; // not index
	
	txt_new_topic = $('form[name=post] input[type=submit]').val();
	txt_new_reply = 'New Reply';
	
	undo_quick_reply = function() {
		$('div.banner').remove();
		$('form[name=post] input[type=submit]').val(txt_new_topic);
		$('form[name=post] input[name=quick-reply]').remove();
	}
	
	$('div.post.op').each(function() {
		var id = $(this).children('p.intro').children('a.post_no:eq(1)').text();
		$('<a href="?/b/res/69.html">[Quick reply]</a>').insertAfter($(this).children('p.intro').children('a:last')).click(function() {
			$('div.banner').remove();
			$('<div class="banner">Posting mode: Replying to <small>&gt;&gt;' + id + '</small> <a class="unimportant" onclick="undo_quick_reply()" href="javascript:void(0)">[Return]</a></div>')
				.insertBefore('form[name=post]');
			$('form[name=post] input[type=submit]').val(txt_new_reply);
			
			$('<input type="hidden" name="quick-reply" value="' + id + '">').appendTo($('form[name=post]'));
			
			$('form[name=post] textarea').select();
			
			window.scrollTo(0, 0);
			
			return false;
		});		
	});
});

