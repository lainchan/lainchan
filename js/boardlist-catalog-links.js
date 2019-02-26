/*
 * boardlist-catalog-links.js
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/boardlist-catalog-links.js';
 *
 */

$(document).ready(function() {
	
    function replace_index_links() {
        $('.boardlist').children('span[class="sub"]').children('a').each(function() {
	        this.href = this.href.replace('index.html', 'catalog.html');
        });
    }

	if (window.Options && Options.get_tab('general')) {
		Options.extend_tab("general", "<fieldset><legend> Board List Catalog Links </legend><label><input type='checkbox' id='boardlist_catalog_links' /> "+_('Use catalog links for the board list')+"</label></fieldset>");

	$('#boardlist_catalog_links').on('change', function(){
		var setting = $(this).attr('id');

		localStorage[setting] = $(this).is(':checked');
		location.reload();
	});
		
		if (localStorage.boardlist_catalog_links === 'true') {
			$('#boardlist_catalog_links').prop('checked', true);
			replace_index_links();
		}
	}
});
