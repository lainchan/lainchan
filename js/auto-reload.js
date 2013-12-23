/*
 * auto-reload.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/auto-reload.js
 *
 * Brings AJAX to Tinyboard.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/auto-reload.js';
 *
 */

$(document).ready(function(){
	if($('div.banner').length == 0)
		return; // not index
		
	if($(".post.op").size() != 1)
	return; //not thread page
	
	var poll_interval;

	var end_of_page = false;
	
	var poll = function() {
		$.ajax({
			url: document.location,
			success: function(data) {
				$(data).find('div.post.reply').each(function() {
					var id = $(this).attr('id');
					if($('#' + id).length == 0) {
						$(this).insertAfter($('div.post:last').next()).after('<br class="clear">');
						$(document).trigger('new_post', this);
					}
				});
			}
		});
		
		clearTimeout(poll_interval);
		poll_interval = setTimeout(poll, end_of_page ? 3000 : 10000);
	};
	
	$(window).scroll(function() {
		if($(this).scrollTop() + $(this).height() < $('div.post:last').position().top + $('div.post:last').height()) {
			end_of_page = false;
			return;
		}
		
		clearTimeout(poll_interval);
		poll_interval = setTimeout(poll, 100);
		end_of_page = true;
	}).trigger('scroll');

	poll_interval = setTimeout(poll, 3000);
});

