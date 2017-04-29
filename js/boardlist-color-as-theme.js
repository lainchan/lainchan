/*
 * boardlist-color-as-theme.js - to have a slightly more consistent look
 * 		This library changes theme color of top bar in supported 
 * 		browsers to the background color of board list.
 *
 * rgb2hex function is taken from http://jsfiddle.net/Mottie/xcqpF/1/light/ (https://github.com/Mottie/)
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/boardlist-color-as-theme.js';
 *
 */

onready(function(){

	function rgb2hex(rgb){
		rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
		return (rgb && rgb.length === 4) ? "#" +
		("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
	}

	// Add the line with specified hex value to the head part of document
	function changeTheme(color_hex) {
		$('meta[name=theme-color]').remove();
		$('head').append( '<meta name="theme-color" content="' + color_hex + '">' );
	}

	// Some styles do not have background-color property in their 'boardlist' class
	// so color of bar is inherited from 'bar' class, obviously.
	var el_boardlist = document.getElementsByClassName('bar')[0];
	var el_boardlist = document.getElementsByClassName('boardlist')[0];

	var el_style = window.getComputedStyle(el_boardlist);
	var hex_val = rgb2hex(el_style.getPropertyValue('background-color'));

	changeTheme(hex_val);
});
