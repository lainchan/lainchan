/*
 * mute-images.js
 *
 * Hide all images.
 *
 * Released under the MIT license
 * Copyright (c) 2015 boku
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/mute-images.js';
 *
 */

$(document).ready(function(){
	$('head').append('<style type="text/css"> .muteimages .post-image:not(:hover) { opacity: 0.03; } </style>');
	$('hr:first').before('<div style="text-align:right"><a class="unimportant" href="javascript:void(0)" id="mute_images">'+_('Mute all images')+'</a></div>');
	
	if (!localStorage.imagesmuted){
		localStorage.imagesmuted = 'false';
	}

	// Load data from HTML5 localStorage
	var isMuted = JSON.parse(localStorage.imagesmuted),
	    store_data = function() {
			localStorage.imagesmuted = JSON.stringify(isMuted);
		};

	if(isMuted){
		$('body').addClass('muteimages');
		$('#mute_images').text(_('Unmute all images'));
	}   

    $('#mute_images').on('click', function(){
    	if(isMuted){
			$('body').removeClass('muteimages');
			isMuted = false;
			$('#mute_images').text(_('Mute all images'));
		}else{
			$('body').addClass('muteimages');
			isMuted = true;
			$('#mute_images').text(_('Unmute all images'));
		}

		store_data();
		return false; 
    });
});
