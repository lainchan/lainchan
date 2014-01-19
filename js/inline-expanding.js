/*
 * inline-expanding.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/inline-expanding.js
 *
 * Released under the MIT license
 * Copyright (c) 2012-2013 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   // $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/inline-expanding.js';
 *
 */

onready(function(){
	var inline_expand_post = function() {
		var link = this.getElementsByTagName('a');

		for (var i = 0; i < link.length; i++) {
			if (typeof link[i] == "object" && link[i].childNodes && typeof link[i].childNodes[0] !== 'undefined' && link[i].childNodes[0].src && link[i].childNodes[0].className.match(/post-image/) && !link[i].className.match(/file/)) {
				link[i].childNodes[0].style.maxWidth = '95%';
				link[i].onclick = function(e) {
					if (this.childNodes[0].className == 'hidden')
						return false;
					if (e.which == 2 || e.metaKey)
						return true;
					if (!this.dataset.src) {
						this.dataset.expanded = 'true';
						this.dataset.src= this.childNodes[0].src;
						this.dataset.width = this.childNodes[0].style.width;
						this.dataset.height = this.childNodes[0].style.height;
						this.childNodes[0].src = this.href;
						this.childNodes[0].style.width = 'auto';
						this.childNodes[0].style.height = 'auto';
						this.childNodes[0].style.opacity = '0.4';
						this.childNodes[0].style.filter = 'alpha(opacity=40)';
						this.childNodes[0].onload = function() {
							this.style.opacity = '';
							delete this.style.filter;
						}
					} else {
						this.childNodes[0].src = this.dataset.src;
						this.childNodes[0].style.width = this.dataset.width;
						this.childNodes[0].style.height = this.dataset.height;
						delete this.dataset.expanded;
						delete this.dataset.src;
						delete this.childNodes[0].style.opacity;
						delete this.childNodes[0].style.filter;
					}
					return false;
				}
			}
		}
	}

	if (window.jQuery) {
		$('div[id^="thread_"]').each(inline_expand_post);

		// allow to work with auto-reload.js, etc.
		$(document).bind('new_post', function(e, post) {
			inline_expand_post.call(post);
		});
	} else {
		inline_expand_post.call(document);
	}

});
