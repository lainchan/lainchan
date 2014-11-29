/*
 * inline-expanding.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/inline-expanding.js
 *
 * Released under the MIT license
 * Copyright (c) 2012-2013 Michael Save <savetheinternet@tinyboard.org>
 * Copyright (c) 2013-2014 Marcin Łabanowski <marcin@6irc.net>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/inline-expanding.js';
 *
 */

onready(function(){
	var inline_expand_post = function() {
		var link = this.getElementsByTagName('a');

		for (var i = 0; i < link.length; i++) {
			if (typeof link[i] == "object" && link[i].childNodes && typeof link[i].childNodes[0] !== 'undefined' && link[i].childNodes[0].src && link[i].childNodes[0].className.match(/post-image/) && !link[i].className.match(/file/)) {
				link[i].onclick = function(e) {
					var img, post_body, still_open, canvas;
					var thumb = this.childNodes[0];
					var padding = 5;
					var boardlist = $('.boardlist')[0];
					
					var loadImage = function(img, thumb) {
						if (img.naturalWidth) {
							thumb.style.display = 'none';
							img.style.display = '';
						}
						else {
							return thumb.parentNode.timeout = setTimeout(loadImage, 30, img, thumb);
						}
					};

					if (thumb.className == 'hidden')
						return false;
					if (e.which == 2 || e.ctrlKey) //open in new tab
						return true;
					if (!this.dataset.expanded) {
						this.parentNode.removeAttribute('style');
						this.dataset.expanded = 'true';

						if (thumb.tagName === 'CANVAS') {
							canvas = thumb;
							thumb = thumb.nextSibling;
							this.removeChild(canvas);
							canvas.style.display = 'block';
						}

						thumb.style.opacity = '0.4';
						thumb.style.filter = 'alpha(opacity=40)';

						img = document.createElement('img');
						img.className = 'full-image';
						img.setAttribute('src', this.href);
						img.setAttribute('alt', 'Fullsized image');
						img.style.display = 'none';
						this.appendChild(img);

						this.timeout = loadImage(img, thumb);
					} else {
						clearTimeout(this.timeout);

						//scroll to thumb if not triggered by 'shrink all image'
						if (e.target.className == 'full-image') {
							post_body = $(e.target).parentsUntil('form > div').last();
							still_open = post_body.find('.post-image').filter(function(){return $(this).parent().attr('data-expanded') == 'true'}).length;

							//deal with differnt boards' menu styles
							if ($(boardlist).css('position') == 'fixed')
								padding += boardlist.getBoundingClientRect().height;

							if (still_open > 1) {
								if (e.target.getBoundingClientRect().top - padding < 0)
									$('body').scrollTop($(e.target).parent().parent().offset().top - padding);
							} else {
								if (post_body[0].getBoundingClientRect().top - padding < 0)
									$('body').scrollTop(post_body.offset().top - padding);
							}
						}

						if (~this.parentNode.className.indexOf('multifile'))
							this.parentNode.style.width = (parseInt(this.dataset.width)+40)+'px';

						thumb.style.opacity = '';
						thumb.style.display = '';
						this.removeChild(thumb.nextSibling);
						delete this.dataset.expanded;
						delete thumb.style.filter;

						if (localStorage.no_animated_gif === 'true' && typeof unanimate_gif === 'function') {
							unanimate_gif(thumb);
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
