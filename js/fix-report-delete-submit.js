/*
 * fix-report-delete-submit.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/fix-report-delete-submit.js
 *
 * Fixes a known bug regarding the delete/report submit buttons.
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/fix-report-delete-submit.js';
 *
 */

$(document).ready(function(){
	$('form[name="postcontrols"] div.delete input:not([type="checkbox"]):not([type="submit"]):not([type="hidden"])').keypress(function(e) {
		if(e.which == 13) {
			e.preventDefault();
			$(this).next().click();
			return false;
		}
		return true;
	});
});

