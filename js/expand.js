/*
 * expand.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/expand.js
 *
 * Released under the MIT license
 * Copyright (c) 2012-2013 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013 Czterooki <czterooki1337@gmail.com>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/expand.js';
 *
 */

$(document).ready(function(){
	if($('span.omitted').length == 0)
		return; // nothing to expand

	var do_expand = function() {
		$(this)
			.html($(this).text().replace(_("Click reply to view."), '<a href="javascript:void(0)">'+_("Click to expand")+'</a>.'))
			.find('a').click(function() {
				var thread = $(this).parent().parent().parent();
				var id = thread.attr('id').replace(/^thread_/, '');
				$.ajax({
					url: thread.find('p.intro a.post_no:first').attr('href'),
					context: document.body,
					success: function(data) {
						var last_expanded = false;
						$(data).find('div.post.reply').each(function() {
							thread.find('div.hidden').remove();
							var post_in_doc = thread.find('#' + $(this).attr('id'));
							if(post_in_doc.length == 0) {
								if(last_expanded) {
									$(this).addClass('expanded').insertAfter(last_expanded).before('<br class="expanded">');
								} else {
									$(this).addClass('expanded').insertAfter(thread.find('div.post:first')).after('<br class="expanded">');
								}
								last_expanded = $(this);
								$(document).trigger('new_post', this);
							} else {
								last_expanded = post_in_doc;
							}
						});
						$('<span class="omitted"><a href="javascript:void(0)">' + _('Hide expanded replies') + '</a>.</span>')
							.insertAfter(thread.find('span.omitted').css('display', 'none'))
							.click(function() {
								thread.find('.expanded').remove();
								$(this).prev().css('display', '');
								$(this).remove();
							});
					}
				});
			});
	}

	$('div.post.op span.omitted').each(do_expand);

	$(document).on("new_post", function(e, post) {
		if (!$(post).hasClass("reply")) {
			$(post).find('div.post.op span.omitted').each(do_expand);
		}
	});
});
