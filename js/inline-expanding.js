/*
 * inline-expanding.js
 * https://github.com/savetheinternet/Tinyboard/blob/master/js/inline-expanding.js
 *
 * Released under the MIT license
 * Copyright (c) 2012 Michael Save <savetheinternet@tinyboard.org>
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/inline-expanding.js';
 *
 */

onready(function(){
	var link = document.getElementsByTagName('a');
	
	for(var i = 0; i < link.length; i++) {
		if(typeof link[i] == "object" && link[i].childNodes[0].src && link[i].className != 'file') {
			link[i].onclick = function(e) {
				if(e.which == 2) {
					return true;
				}
				if(!this.tag) {
					this.tag = this.childNodes[0].src;
					this.childNodes[0].src = this.href;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height = 'auto';
					this.childNodes[0].style.opacity = '0.4';
					this.childNodes[0].style.filter = 'alpha(opacity=40)';
					this.childNodes[0].onload = function() {
						this.style.opacity = '1';
						this.style.filter = '';
					}
				} else {
					this.childNodes[0].src = this.tag;
					this.childNodes[0].style.width = 'auto';
					this.childNodes[0].style.height = 'auto';
					this.tag = '';
				}
				return false;
			}
			
		}
	}
});

