/*
 * quick-reply.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/quick-reply.js
 *
 * Released under the MIT license
 * Copyright (c) 2013 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/jquery-ui.custom.min.js'; // Optional; if you want the form to be draggable.
 *   $config['additional_javascript'][] = 'js/quick-reply.js';
 *
 */

var do_css = function() {
	$('#quick-reply-css').remove();
	
	// Find background of reply posts
	var dummy_reply = $('<div class="post reply"></div>').appendTo($('body'));
	var reply_background = dummy_reply.css('background');
	dummy_reply.remove();
	
	$('<style type="text/css" id="quick-reply-css">\
	#quick-reply {\
		position: fixed;\
		right: 0;\
		top: 5%;\
		float: right;\
		display: block;\
		padding: 0 0 0 0;\
		width: 350px;\
	}\
	#quick-reply table {\
		border-collapse: collapse;\
		background: ' + reply_background + ';\
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
};

var show_quick_reply = function(){
	if($('div.banner').length == 0)
		return;
	if($('#quick-reply').length != 0)
		return;
	
	do_css();
	
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
	
	$postForm.appendTo($('body')).hide();
	
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

	if (typeof $postForm.draggable != undefined) {
		if (localStorage.quickReplyPosition) {
			var offset = JSON.parse(localStorage.quickReplyPosition);
			if (offset.right > $(window).width() - $postForm.width())
				offset.right = $(window).width() - $postForm.width();
			if (offset.top > $(window).height() - $postForm.height())
				offset.top = $(window).height() - $postForm.height();
			$postForm.css('right', offset.right).css('top', offset.top);
		}
		$postForm.draggable({
			handle: 'th',
			containment: 'window',
			distance: 10,
			opacity: 0.9,
			scroll: false,
			stop: function() {
				var offset = {
					top: $(this).offset().top - $(window).scrollTop(),
					right: $(window).width() - $(this).offset().left - $(this).width(),
				};
				localStorage.quickReplyPosition = JSON.stringify(offset);
				
				$postForm.css('right', offset.right).css('top', offset.top).css('left', 'auto');
			}
		});
		$postForm.find('th').css('cursor', 'move');
	}

	$postForm.show();
	$origPostForm = $('form[name="post"]');

	$(window).ready(function() {
		$(window).scroll(function() {
			if ($(this).width() <= 800)
				return;
			if ($(this).scrollTop() < $origPostForm.offset().top + $origPostForm.height() - 100)
				$postForm.fadeOut(100);
			else
				$postForm.fadeIn(100);
		}).on('stylesheet', function() {
			do_css();
			if ($('link#stylesheet').attr('href')) {
				$('link#stylesheet')[0].onload = do_css;
			}
		});
	});
};

$(window).on('cite', function(e, id, with_link) {
	if ($(this).width() <= 800)
		return;
	show_quick_reply();
	$('#quick-reply textarea').focus();
	if (with_link) {
		$(window).ready(function() {
			if ($('#' + id).length) {
				highlightReply(id);
				$(window).scrollTop($('#' + id).offset().top);
			}
		});
	}
});
