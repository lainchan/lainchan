/*
 * auto-reload.js
 * https://github.com/savetheinternet/Tinyboard-Tools/blob/master/js/auto-reload.js
 *
 * Brings AJAX to Tinyboard.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = $config['root'] . 'auto-reload.js';
 *
 */

$(document).ready(function(){
	if($('div.banner').length == 0)
		return; // not index
	
	setInterval(function() {
		$.ajax({
			url: document.location,
			success: function(data) {
				$(data).find('div.post.reply').each(function() {
					var id = $(this).attr('id');
					if($('#' + id).length == 0) {
						$(this).insertAfter($('div.reply:last').next()).after('<br class="clear">');
					}
				});
			}
		});
	}, 6000);
});

