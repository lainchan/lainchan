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
				link[i].onclick = function(e) {
					var img;
					var loadImage = function(img, thumb) {
						if (img.naturalWidth) {
							thumb.style.display = 'none';
							img.style.display = '';
						}
						else {
							return thumb.parentNode.timeout = setTimeout(loadImage, 30, img, thumb);
						}
					};

					if (this.childNodes[0].className == 'hidden')
						return false;
					if (e.which == 2 || e.ctrlKey) //open in new tab
						return true;
					if (!this.dataset.expanded) {
						this.parentNode.removeAttribute('style');
						this.dataset.expanded = 'true';

						if (this.childNodes[0].tagName === 'CANVAS') {
							this.removeChild(this.childNodes[0]);
							this.childNodes[0].style.display = 'block';
						}

						this.childNodes[0].style.opacity = '0.4';
						this.childNodes[0].style.filter = 'alpha(opacity=40)';

						img = document.createElement('img');
						img.className = 'full-image';
						img.setAttribute('src', this.href);
						img.setAttribute('alt', 'Fullsized image');
						img.style.display = 'none';
						this.appendChild(img);

						this.timeout = loadImage(img, this.childNodes[0]);
					} else {
						clearTimeout(this.timeout);
						if (~this.parentNode.className.indexOf('multifile'))
							this.parentNode.style.width = (parseInt(this.dataset.width)+40)+'px';

						this.childNodes[0].style.opacity = '';
						this.childNodes[0].style.display = '';
						this.removeChild(this.childNodes[1]);
						delete this.dataset.expanded;
						delete this.childNodes[0].style.filter;

						if (localStorage.no_animated_gif === 'true' && typeof unanimate_gif === 'function') {
							unanimate_gif(this.childNodes[0]);
						}
					}
					return false;
				};
			}
		}
	};

	if (window.jQuery) {
		$('div[id^="thread_"]').each(inline_expand_post);

		// allow to work with auto-reload.js, etc.
		$(document).on('new_post', function(e, post) {
			inline_expand_post.call(post);
		});
	} else {
		inline_expand_post.call(document);
	}
});
