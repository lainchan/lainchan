/*
 * auto-reload.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/auto-reload.js
 *
 * Brings AJAX to Tinyboard.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 * Copyright (c) 2013 undido <firekid109@hotmail.com>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   //$config['additional_javascript'][] = 'js/titlebar-notifications.js';
 *   $config['additional_javascript'][] = 'js/auto-reload.js';
 *
 */

auto_reload_enabled = true; // for watch.js to interop

$(document).ready(function(){
	if($('div.banner').length == 0)
		return; // not index
		
	if($(".post.op").size() != 1)
	return; //not thread page
	
	var poll_interval;

	// number of ms to wait before reloading
	var poll_interval_delay;

	// If at the bottom of the page, reload more quickly.
	var poll_interval_mindelay_bottom = 3000;
	var poll_interval_mindelay_top    = 10000;

	poll_interval_delay = poll_interval_mindelay_bottom;

	// Don't take longer than this to reload.
	var poll_interval_maxdelay = 600000;

	// Upon scrolling to the bottom, reload very quickly.
	var poll_interval_shortdelay = 100;

	var end_of_page = false;

        var new_posts = 0;
	var first_new_post = null;

	if (typeof update_title == "undefined") {
	   var update_title = function() { };
	}

	if (typeof add_title_collector != "undefined")
	add_title_collector(function(){
	  return new_posts;
	});

	var window_active = true;
	$(window).focus(function() {
		window_active = true;
		recheck_activated();
	});
	$(window).blur(function() {
		window_active = false;
	});
	
	var recheck_activated = function() {
		if (new_posts && window_active &&
			$(window).scrollTop() + $(window).height() >=
			$(first_new_post).position().top) {

			new_posts = 0;
		}
		update_title();
	};

	var poll = function() {
		$.ajax({
			url: document.location,
			success: function(data) {
				$(data).find('div.post.reply').each(function() {
					var id = $(this).attr('id');
					if($('#' + id).length == 0) {
						if (!new_posts) {
							first_new_post = this;
						}
						$(this).insertAfter($('div.post:last').next()).after('<br class="clear">');
						new_posts++;
						$(document).trigger('new_post', this);
						recheck_activated();
					}
				});
				time_loaded = Date.now(); // interop with watch.js
			}
		});
		
		clearTimeout(poll_interval);

		// If there are no new posts, double the delay. Otherwise set it to the min.
		if(new_posts == 0) {
			poll_interval_delay *= 2;

			// Don't increase the delay beyond the maximum
			if(poll_interval_delay > poll_interval_maxdelay) {
				poll_interval_delay = poll_interval_maxdelay;
			}
		} else {
			poll_interval_delay = end_of_page
			    ? poll_interval_mindelay_bottom
			    : poll_interval_mindelay_top;
		}

		poll_interval = setTimeout(poll, poll_interval_delay);
	};
	
	$(window).scroll(function() {
		recheck_activated();

		if($(this).scrollTop() + $(this).height() <
			$('div.post:last').position().top + $('div.post:last').height()) {
			end_of_page = false;
			return;
		}
		
		clearTimeout(poll_interval);
		poll_interval = setTimeout(poll, poll_interval_shortdelay);
		end_of_page = true;
	}).trigger('scroll');

	poll_interval = setTimeout(poll, poll_interval_delay);
});

