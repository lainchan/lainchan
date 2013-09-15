/*
 * quick-reply.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/quick-reply.js
 *
 * Released under the MIT license
 * Copyright (c) 2013 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/quick-reply.js';
 *
 */

var show_quick_reply = function(){
	if($('div.banner').length == 0)
		return;
	if($('#quick-reply').length != 0)
		return;

	$('<style type="text/css">\
	#quick-reply {\
		position: fixed;\
		right: 0;\
		top: 5%;\
		float: right;\
		background: #D6DAF0;\
		display: block;\
		padding: 0 0 0 0;\
		width: 350px;\
	}\
	#quick-reply table {\
		border-collapse: collapse;\
		margin: 0;\
		width: 100%;\
	}\
	#quick-reply tr td:nth-child(2) {\
		white-space: nowrap;\
		text-align: right;\
		padding-right: 4px;\
	}\
	#quick-reply tr td:nth-child(2) input[type="submit"] {\
		width: 100%;\
	}\
	#quick-reply th, #quick-reply td {\
		margin: 0;\
		padding: 0;\
	}\
	#quick-reply th {\
		text-align: center;\
		padding: 2px 0;\
		border: 1px solid #222;\
	}\
	#quick-reply input[type="text"] {\
		width: 100%;\
		padding: 2px;\
		font-size: 10pt;\
		box-sizing: border-box;\
		-webkit-box-sizing:border-box;\
		-moz-box-sizing: border-box;\
	}\
	#quick-reply textarea {\
		width: 100%;\
		box-sizing: border-box;\
		-webkit-box-sizing:border-box;\
		-moz-box-sizing: border-box;\
		font-size: 10pt;\
	}\
	#quick-reply input, #quick-reply select, #quick-reply textarea {\
		margin: 0 0 1px 0;\
	}\
	#quick-reply input[type="file"] {\
		padding: 5px 2px;\
	}\
	#quick-reply .nonsense {\
		display: none;\
	}\
	#quick-reply td.submit {\
		width: 1%;\
	}\
	@media screen and (max-width: 800px) {\
		#quick-reply {\
			display: none !important;\
		}\
	}\
	</style>').appendTo($('head'));
	
	var $postForm = $('form[name="post"]').clone();
	
	$postForm.clone();
	
	$dummyStuff = $('<div class="nonsense"></div>').appendTo($postForm);
	
	$postForm.find('table tr').each(function() {
		var $th = $(this).children('th:first');
		var $td = $(this).children('td:first');		
		if ($th.length && $td.length) {
			$td.attr('colspan', 2);

			if ($td.find('input[type="text"]').length) {
				// Replace <th> with input placeholders
				$td.find('input[type="text"]')
					.removeAttr('size')
					.attr('placeholder', $th.clone().children().remove().end().text());
			}

			// Move anti-spam nonsense and remove <th>
			$th.contents().appendTo($dummyStuff);
			$th.remove();

			if ($td.find('input[name="password"]').length) {
				// Hide password field
				$(this).hide();
			}

			// Fix submit button
			if ($td.find('input[type="submit"]').length) {
				$td.removeAttr('colspan');
				$('<td class="submit"></td>').append($td.find('input[type="submit"]')).insertAfter($td);
			}

			if ($td.find('input[type="file"]').length) {
				if ($td.find('input[name="file_url"]').length) {
					$file_url = $td.find('input[name="file_url"]');
					
					// Make a new row for it
					$newRow = $('<tr><td colspan="2"></td></tr>');
					
					$file_url.clone().attr('placeholder', _('Upload URL')).appendTo($newRow.find('td'));
					$file_url.parent().remove();
					
					$newRow.insertBefore(this);
					
					$td.find('label').remove();
					$td.contents().filter(function() {
						return this.nodeType == 3; // Node.TEXT_NODE
					}).remove();
					$td.find('input[name="file_url"]').removeAttr('id');
				}
				
				if ($(this).find('input[name="spoiler"]').length) {
					$td.removeAttr('colspan');
				}
			}
			
			// Remove mod controls, because it looks shit.
			if ($td.find('input[type="checkbox"]').length) {
				var tr = this;
				$td.find('input[type="checkbox"]').each(function() {
					if ($(this).attr('name') == 'spoiler') {
						$td.find('label').remove();
						$(this).attr('id', 'q-spoiler-image');
						$postForm.find('input[type="file"]').parent()
							.removeAttr('colspan')
							.after($('<td class="spoiler"></td>').append(this, ' ', $('<label for="q-spoiler-image">').text(_('Spoiler Image'))));
					} else {
						$(tr).remove();
					}
				});
			}
		}
	});
	
	$postForm.find('textarea[name="body"]').removeAttr('id').removeAttr('cols').attr('placeholder', _('Comment'));
	
	$postForm.find('br').remove();
	$postForm.find('table').prepend('<tr><th colspan="2">' + _('Quick Reply') + '</th></tr>');
	
	$postForm.attr('id', 'quick-reply');
	
	$postForm.appendTo($('body'));
	
	// Synchronise body text with original post form
	$('#body').bind('change input propertychange', function() {
		$postForm.find('textarea[name="body"]').val($(this).val());
	});
	$postForm.find('textarea[name="body"]').bind('change input propertychange', function() {
		$('#body').val($(this).val());
	});
	// Synchronise other inputs
	$('form[name="post"]:first input[type="text"],select').bind('change input propertychange', function() {
		$postForm.find('[name="' + $(this).attr('name') + '"]').val($(this).val());
	});
	$postForm.find('input[type="text"],select').bind('change input propertychange', function() {
		$('form[name="post"]:first [name="' + $(this).attr('name') + '"]').val($(this).val());
	});
	
	$origPostForm = $('form[name="post"]');

	$(window).scroll(function() {
		if ($(this).width() <= 800)
			return;
		if ($(this).scrollTop() < $origPostForm.offset().top + $origPostForm.height() - 100)
			$postForm.fadeOut(100);
		else
			$postForm.fadeIn(100);
	});
};

$(window).on('cite', function(e, id, with_link) {
	if ($(this).width() <= 800)
		return;
	show_quick_reply();
	$('#quick-reply textarea').focus();
	if (with_link) {
		setTimeout(function() {
			highlightReply(id);
			$(window).scrollTop($('#' + id).offset().top);
		}, 10);
	}
});
