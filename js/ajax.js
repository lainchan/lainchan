/*
 * ajax.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/ajax.js
 *
 * Released under the MIT license
 * Copyright (c) 2013 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/ajax.js';
 *
 */

$(window).ready(function() {
	var do_not_ajax = false;
	
	var setup_form = function($form) {
		$form.submit(function() {
			if (do_not_ajax)
				return true;
			var form = this;
			var submit_txt = $(this).find('input[type="submit"]').val();
			if (window.FormData === undefined)
				return true;
			
			var formData = new FormData(this);
			formData.append('json_response', '1');
			formData.append('post', submit_txt);

			var updateProgress = function(e) {
				$(form).find('input[type="submit"]').val(_('Posting... (#%)').replace('#', Math.round(e.position / e.total * 100)));
			};

			$.ajax({
				url: this.action,
				type: 'POST',
				xhr: function() {
					var xhr = $.ajaxSettings.xhr();
					if(xhr.upload) {
						xhr.upload.addEventListener('progress', updateProgress, false);
					}
					return xhr;
				},
				success: function(post_response) {
					if (post_response.error) {
						alert(post_response.error);
						$(form).find('input[type="submit"]').val(submit_txt);
						$(form).find('input[type="submit"]').removeAttr('disabled');
					} else if (post_response.redirect && post_response.id) {
						if (!$(form).find('input[name="thread"]').length) {
							document.location = post_response.redirect;
						} else {
							$.ajax({
								url: document.location,
								success: function(data) {
									$(data).find('div.post.reply').each(function() {
										var id = $(this).attr('id');
										if($('#' + id).length == 0) {
											$(this).insertAfter($('div.post:last').next()).after('<br class="clear">');
											$(document).trigger('new_post', this);
										}
									});
									
									highlightReply(post_response.id);
									window.location.hash = post_response.id;
									$(window).scrollTop($('div.post#reply_' + post_response.id).offset().top);
									
									$(form).find('input[type="submit"]').val(submit_txt);
									$(form).find('input[type="submit"]').removeAttr('disabled');
									$(form).find('input[name="subject"],input[name="file_url"],\
										textarea[name="body"],input[type="file"]').val('').change();
								},
								cache: false,
								contentType: false,
								processData: false
							}, 'html');
						}
						$(form).find('input[type="submit"]').val('Posted...');
					} else {
						alert(_('An unknown error occured when posting!'));
						$(form).find('input[type="submit"]').val(submit_txt);
						$(form).find('input[type="submit"]').removeAttr('disabled');
					}
				},
				error: function(xhr, status, er) {
					// An error occured
					// TODO
					do_not_ajax = true;
					$(form).find('input[type="submit"]').each(function() {
						var $replacement = $('<input type="hidden">');
						$replacement.attr('name', $(this).attr('name'));
						$replacement.val(submit_txt);
						$(this)
							.after($replacement)
							.replaceWith($('<input type="button">').val(submit_txt));
					});
					$(form).submit();
				},
				// Form data
				data: formData,
				cache: false,
				contentType: false,
				processData: false
			}, 'json');
			
			$(form).find('input[type="submit"]').val(_('Posting...'));
			$(form).find('input[type="submit"]').attr('disabled', true);
			
			return false;
		});
	};
	setup_form($('form[name="post"]'));
	$(window).on('quick-reply', function() {
		setup_form($('form#quick-reply'));
	});
});
