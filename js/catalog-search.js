/*
 * catalog-search.js
 * https://github.com/mgrabovsky/lainchan/blob/catalog-search/js/catalog-search.js
 *
 * Released under the MIT license
 * Copyright (c) 2015 Matěj Grabovský <matej.grabovsky@gmail.com>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/catalog-search.js';
 */

(function() {

var catalogSearch = function() {
	var $controls = $('.controls'),
		$threads = $('.threads .thread'),
		$searchLabel = $('<label for="catalog_search">Search: </label>'),
		$searchBox = $('<input id="catalog_search" type="search" placeholder="Search" />');

	$controls.append($searchLabel)
			.append($searchBox);

	$searchBox.keyup(function() {
		var $found = searchThreads($threads, this.value);
		$threads.hide();
		$found.show();
	});

	var m = location.hash.match(/[#&]s=([^&]+)/);
	if(m) {
		$searchBox.val(decodeURIComponent(m[1])).keyup();
	}
};

// Filter threads by their content, given a regex. Can be extended to load data
// remotely and filter by multiple fields
var searchThreads = function($threads, query) {
	var re = new RegExp(query, 'mi');
	return $threads.filter(function() {
		return re.test($('.replies', this).text());
	});
};

// Only load in the catalog
if (active_page == 'catalog') {
	onready(catalogSearch);
}

}());
