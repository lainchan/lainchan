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
		if(post.length == 0)
			return;
		
		$(this).hover(function(e) {
			if($(window).scrollTop() <= post.offset().top + post.height()) {
				// post is in view
				post.attr('style', 'border-style: none dashed dashed none; background: ' + post.css('border-right-color'));
			} else {
				post.clone()
					.attr('id', 'post-hover-' + id)
					.addClass('post-hover')
					.css('position', 'absolute')
					.css('border-style', 'solid')
					.css('box-shadow', '1px 1px 1px #999')
					.insertAfter($(this).parent());
				$(this).trigger('mousemove');
			}
		}, function() {
			post.attr('style', '');
			$('.post-hover').remove();
		}).mousemove(function(e) {
			var top = e.pageY - post.height() - 15;
			$('#post-hover-' + id)
				.css('left', $(this).width() + e.pageX)
				.css('top', top > $(window).scrollTop() ? top : $(window).scrollTop());
		});
	});
});

