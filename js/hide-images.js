/*
 * hide-images.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/hide-images.js
 *
 * Hide individual images.
 *
 * Released under the MIT license
 * Copyright (c) 2013 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/hide-images.js';
 *
 */

$(document).ready(function(){
	$('<style type="text/css"> img.hidden{ opacity: 0.1; background: grey; border: 1px solid #000; } </style>').appendTo($('head'));
	
	if (!localStorage.hiddenimages)
		localStorage.hiddenimages = '{}';

	// Load data from HTML5 localStorage
	var hidden_data = JSON.parse(localStorage.hiddenimages);

	var store_data = function() {
		localStorage.hiddenimages = JSON.stringify(hidden_data);
	};

	// Delete old hidden images (30+ days old)
	for (var key in hidden_data) {
		for (var id in hidden_data[key]) {
			if (hidden_data[key][id] < Math.round(Date.now() / 1000) - 60 * 60 * 24 * 30) {
				delete hidden_data[key][id];
				store_data();
			}
		}
	}

	var handle_images = function() {
		var img = this;
		var fileinfo = $(this).parent().prev();
		var id = $(this).parent().parent().find('>p.intro>a.post_no:eq(1),>div.post.op>p.intro>a.post_no:eq(1)').text();

		var board = $(this).parents('[id^="thread_"]').data("board");

		if (!hidden_data[board]) {
			hidden_data[board] = {}; // id : timestamp
		}
		
		var replacement = $('<span>'+_('File')+' <small>(<a class="hide-image-link" href="javascript:void(0)">'+_('hide')+'</a>)</small>: </span>');
				
		replacement.find('a').click(function() {
			hidden_data[board][id] = Math.round(Date.now() / 1000);
			store_data();
			
			var show_link = $('<a class="show-image-link" href="javascript:void(0)">'+_('show')+'</a>').click(function() {
				delete hidden_data[board][id];
				store_data();
				
				$(img)
					.removeClass('hidden')
					.attr('src', $(img).data('orig'));
				$(this).prev().show();
				$(this).remove();
			});
			
			$(this).hide().after(show_link);
			
			if ($(img).parent()[0].dataset.expanded == 'true') {
				$(img).parent().click();
			}
			$(img)
				.data('orig', img.src)
				.attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
				.addClass('hidden');
		});
		
		$(this).parent().prev().contents().first().replaceWith(replacement);
		
		if (hidden_data[board][id])
			$(this).parent().prev().find('.hide-image-link').click();
	};

	$('div.post > a > img.post-image, div > a > img.post-image').each(handle_images);

        $(document).on('new_post', function(e, post) {
                $(post).find('> a > img.post-image').each(handle_images);
        });
});
