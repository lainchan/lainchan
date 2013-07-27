/*
 * inline-expanding.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/inline-expanding.js
 *
 * Released under the MIT license
 * Copyright (c) 2012-2013 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/inline-expanding.js';
 *
 */

onready(function(){
	var link = document.getElementsByTagName('a');
	
	for (var i = 0; i < link.length; i++) {
		if (typeof link[i] == "object" && link[i].childNodes && link[i].childNodes[0].src && link[i].className != 'file') {
			link[i].childNodes[0].style.maxWidth = '95%';
			link[i].childNodes[0].style.maxHeight = '95%';
			link[i].onclick = function(e) {
				if (this.childNodes[0].className == 'hidden')
					return false;
				if (e.which == 2)
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
});

