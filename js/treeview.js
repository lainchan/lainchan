/*
 * treeview.js
 * https://github.com/vichan-devel/vichan/blob/master/js/treeview.js
 *
 * Released under the MIT license
 * Copyright (c) 2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/treeview.js';
 *
 */

if (active_page == 'thread' || active_page == 'ukko' || active_page == 'index')
$(function() {
	if (window.Options && Options.get_tab('general')) {
		var selector = '#treeview-global>input';
		Options.extend_tab("general", "<label id='treeview-global'><input type='checkbox' /> "+_('Use tree view by default')+"</label>");
	}
	$(selector).on('change', function () {
		if (localStorage.treeview === 'true') {
			localStorage.treeview = 'false';
		} else {
			localStorage.treeview = 'true';
		}
	});
	if (localStorage.treeview === 'true') {
		$(selector).attr('checked', 'checked');
	}
});

if (active_page == 'thread')
$(function() {
	var treeview_on = false;
	var treeview = function() {
		if (!treeview_on) {
			treeview_on = true;
			$('.post.reply').each(function(){
				var references = [];
				$(this).find('.body a').each(function(){
					if ($(this).html().match('^&gt;&gt;[0-9]+$')) {
						references.push(parseInt($(this).html().replace('&gt;&gt;', '')));
					}
				});
				var maxref = references.reduce(function(a,b) { return a > b ? a : b; }, 0);

				var parent_post = $("#reply_"+maxref);
				if (parent_post.length == 0) return;

				var margin = parseInt(parent_post.css("margin-left"))+32;

				var post = $(this);
				var br = post.next();

				post.detach().css("margin-left", margin).insertAfter(parent_post.next());
				br.detach().insertAfter(post);
			});
		} else {
			treeview_on = false;
			$('.post.reply').sort(function(a,b) {
				return parseInt(a.id.replace('reply_', '')) - parseInt(b.id.replace('reply_', ''));
			}).each(function () {
				var post = $(this);
				var br = post.next();
				post.detach().css('margin-left', '0').appendTo('.thread');
				br.detach().insertAfter(post);
			});
		}
	}
	if (localStorage.treeview === 'true') {
		treeview();
	}
	
        $('hr:first').before('<div id="treeview" style="text-align:right"><a class="unimportant" href="javascript:void(0)"></a></div>');
        $('div#treeview a')
                .text(_('Tree view'))
                .click(function(e) { treeview(); e.preventDefault(); });
});
