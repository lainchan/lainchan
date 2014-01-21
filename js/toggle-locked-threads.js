/*
 * toggle-locked-threads.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net> 
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/toggle-locked-threads.js';
 *
 */

if (active_page == 'ukko' || active_page == 'index')
$(document).ready(function(){
	if($('div.banner').length != 0)
		return; // not index
	
	var hide_locked_threads = localStorage['hidelockedthreads'] ? true : false;

	$('<style type="text/css"> img.hidden{ opacity: 0.1; background: grey; border: 1px solid #000; } </style>').appendTo($('head'));
	
	var hideLockedThread = function($thread) {
		$thread
			.hide()
			.addClass('hidden');
	};
	
	var restoreLockedThread = function($thread) {
		$thread
			.show()
			.removeClass('hidden');
	};
	
	var getThreadFromIcon = function($icon) {
		return $icon.parent().parent().parent()
	};
	
	$('hr:first').before('<div id="toggle-locked-threads" style="text-align:right"><a class="unimportant" href="javascript:void(0)">-</a></div>');
	$('div#toggle-locked-threads a')
		.text(hide_locked_threads ? _('Show locked threads') : _('Hide locked threads'))
		.click(function() {
			hide_locked_threads = !hide_locked_threads;
			if (hide_locked_threads) {
				$('img.icon[title="Locked"], i.fa-lock.fa').each(function() {
					hideLockedThread(getThreadFromIcon($(this)));
				});
				localStorage.hidelockedthreads = true;
			} else {
				$('img.icon[title="Locked"], i.fa-lock.fa').each(function() {
					restoreLockedThread(getThreadFromIcon($(this)));
				});
				delete localStorage.hidelockedthreads;
			}
			
			$(this).text(hide_locked_threads ? _('Show locked threads') : _('Hide locked threads'))
		});
	
	if (hide_locked_threads) {
		$('img.icon[title="Locked"], i.fa-lock.fa').each(function() {
			hideLockedThread(getThreadFromIcon($(this)));
		});
	}
        $(document).on('new_post', function(e, post) {
		if (hide_locked_threads) {
			$(post).find('img.icon[title="Locked"], i.fa-lock.fa').each(function() {
	                        hideLockedThread(getThreadFromIcon($(this)));
       		        });
		}
	});
});

