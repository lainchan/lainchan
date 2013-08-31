/*
 * download-original.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/download-original.js
 *
 * Makes image filenames clickable, allowing users to download and save files as their original filename.
 * Only works in newer browsers. http://caniuse.com/#feat=download
 *
 * Released under the MIT license
 * Copyright (c) 2012-2013 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/download-original.js';
 *
 */

onready(function(){
	$('.postfilename').each(function() {
		var filename, truncated;
		if ($(this).attr('title')) {
			filename = $(this).attr('title');
			truncated = true;
		} else {
			filename = $(this).text();
		}
		
		$(this).replaceWith(
			$('<a></a>')
				.attr('download', filename)
				.append($(this).contents())
				.attr('href', $(this).parent().parent().find('a').attr('href'))
				.attr('title', _('Save as original filename') + (truncated ? ' (' + filename + ')' : ''))
			);
	});
});
