/*
 * catalog-link.js - This script puts a link to the catalog below the board
 *                   subtitle and next to the board list.
 * https://github.com/vichan-devel/Tinyboard/blob/master/js/catalog-link.js
 *
 * Released under the MIT license
 * Copyright (c) 2013 copypaste <wizardchan@hush.com>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/catalog-link.js';
 */

(function($) {
var catalog = function() {
	var board = $('input[name="board"]');

	if (!board.length) {
		return;
	}

	var catalog_url = configRoot + board.first().val() + '/catalog.html';

	var pages = $('.pages');
	var bottom = $('.boardlist.bottom');
	var subtitle = $('.subtitle');

	var link = $('<a class="catalog" />')
			.attr('href', catalog_url);

	if (pages.length) {
		link.text(_('Catalog'))
			.css({
				color: '#F10000',
				padding: '4px',
				textDecoration: 'underline',
				display: 'table-cell'
		});
		link.insertAfter(pages);
	} else if (bottom.length) {
		link.text('['+_('Catalog')+']')
			.css({
				paddingLeft: '10px',
				textDecoration: 'underline'
		});
		link.insertBefore(bottom);
	}

	if (subtitle.length) {
		subtitle.append('<br />');
		$('<a class="catalog" />')
			.text(_('Catalog'))
			.attr('href', catalog_url)
			.appendTo(subtitle);
	}
}

if (active_page == 'thread' || active_page == 'index' || active_page == 'ukko') {
	$(document).ready(catalog);
}
})(jQuery);
