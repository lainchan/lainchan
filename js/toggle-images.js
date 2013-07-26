/*
 * toggle-images.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/toggle-images.js';
 *
 */

$(document).ready(function(){
	var hide_images = localStorage['hideimages'] ? true : false;

	$('<style type="text/css"> img.hidden{ opacity: 0.1; background: grey; border: 1px solid #000; } </style>').appendTo($('head'));

	var hideImage = function() {
		$(this)
			.attr('data-orig', this.src)
			.attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
			.addClass('hidden');
	};

	var restoreImage = function() {
		$(this)
			.attr('src', $(this).attr('data-orig'))
			.removeClass('hidden');
	};

	$('hr:first').before('<div id="toggle-images" style="text-align:right"><a class="unimportant" href="javascript:void(0)">-</a></div>');
	$('div#toggle-images a')
		.text((hide_images ? 'Show' : 'Hide') + ' images')
		.click(function() {
			hide_images = !hide_images;
			if (hide_images) {
				$('div > a > img').each(hideImage);
				localStorage.hideimages = true;
			} else {
				$('div > a > img').each(restoreImage);
				delete localStorage.hideimages;
			}

			$(this).text((hide_images ? 'Show' : 'Hide') + ' images')
		});

	if (hide_images) {
		$('div > a > img').each(hideImage);
	}
});
