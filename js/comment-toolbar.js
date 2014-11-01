/*
 * comment-toolbar.js
 *   - Adds a toolbar above the commenting area containing most of 8Chan's formatting options
 *   - Press Esc to close quick-reply window when it's in focus
 * 
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/comment-toolbar.js';
 */
if (active_page == 'thread' || active_page == 'index') {
	$(document).ready(function () {
		'use strict';
		var formats = {
			bold: {
				displayText: 'B',
				altText: 'bold',
				styleCSS: 'font-weight: bold;',
				options: {
					prefix: "'''",
					suffix: "'''"
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				},
				shortcutKey: 'b'
			},
			italics: {
				displayText: 'i',
				altText: 'italics',
				styleCSS: 'font-style: italic;',
				options: {
					prefix: "''",
					suffix: "''"
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				},
				shortcutKey: 'i'
			},
			under: {
				displayText: 'U',
				altText: 'underline',
				styleCSS: 'text-decoration: underline;',
				options: {
					prefix: '__',
					suffix: '__'
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				},
				shortcutKey: 'u'
			},
			spoiler: {
				displayText: 'spoiler',
				altText: 'mark as spoiler',
				styleCSS: '',
				options: {
					prefix: '[spoiler]',
					suffix: '[/spoiler]'
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				},
				shortcutKey: 's'
			},
			code: {
				displayText: 'code',
				altText: "code formatting",
				styleCSS: 'font-family: "Courier New", Courier, monospace;',
				options: {
					prefix: '[code]',
					suffix: '[/code]',
					multiline: true
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				},
				shortcutKey: 'd'
			},
			strike: {
				displayText: 'strike',
				altText: 'strikethrough',
				styleCSS: 'text-decoration: line-through;',
				options: {
					prefix: '~~',
					suffix: '~~'
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				}
			},
			heading: {
				displayText: 'heading',
				altText: 'redtext',
				styleCSS: 'color: #AF0A0F; font-weight: bold;',
				options: {
					prefix: '==',
					suffix: '==',
					exclusiveLine: true
				},
				edit: function (box, options) {
					wrapSelection(box, options);
				}
			}
		};

		var key, name, altText, ele;
		var strBuilder = [];
		var subStr = '';
		var styleRules = '';

		//not exactly mine
		var wrapSelection = function (box, options) {
			if (box == null) {
				return;
			}
			var prefix = options.prefix;
			var suffix = options.suffix;
			var multiline = options.multiline || false;
			var exclusiveLine = options.exclusiveLine || false;

			//record scroll top to restore it later.
			var scrollTop = box.scrollTop;
			var selectionStart = box.selectionStart;
			var selectionEnd = box.selectionEnd;
			var text = box.value;
			var beforeSelection = text.substring(0, selectionStart);
			var selectedText = text.substring(selectionStart, selectionEnd);
			var afterSelection = text.substring(selectionEnd);

			var breakSpace = ["\r","\n"];
			var trailingSpace = "";
			var cursor = selectedText.length - 1;

			//remove trailing space
			while (cursor > 0 && selectedText[cursor] === " ") {
				trailingSpace += " ";
				cursor--;
			}
			selectedText = selectedText.substring(0, cursor + 1);

			if (!multiline)
				selectedText = selectedText.replace(/(\r|\n|\r\n)/g, suffix +"$1"+ prefix);

			if (exclusiveLine) {
				// buffer the begining of the selection until a linebreak
				cursor = beforeSelection.length -1;
				while (cursor >= 0 && breakSpace.indexOf(beforeSelection.charAt(cursor)) == -1) {
					cursor--;
				}
				selectedText = beforeSelection.substring(cursor +1) + selectedText;
				beforeSelection = beforeSelection.substring(0, cursor +1);
				
				// buffer the end of the selection until a linebreak
				cursor = 0;
				while (cursor < afterSelection.length && breakSpace.indexOf(afterSelection.charAt(cursor)) == -1) {
					cursor++;
				}
				selectedText += afterSelection.substring(0, cursor);
				afterSelection = afterSelection.substring(cursor);
			}

			box.value = beforeSelection + prefix + selectedText + suffix + trailingSpace + afterSelection;

			box.selectionEnd = beforeSelection.length + prefix.length + selectedText.length;
			if (selectionStart === selectionEnd) {
				box.selectionStart = box.selectionEnd;
			} else {
				box.selectionStart = beforeSelection.length + prefix.length;
			}
			box.scrollTop = scrollTop;
		};

		/*	Generate the HTML for the toolbar
		 */
		for (ele in formats) {
			if (formats.hasOwnProperty(ele) && formats[ele].displayText != null) {
				name = formats[ele].displayText;
				altText = formats[ele].altText || '';
				key = formats[ele].shortcutKey;

				//add tooltip text
				if (altText) {
					if (key) {
						altText += ' (ctrl+'+ key +')';
					}
					altText = 'title="'+ altText +'"';
				}

				subStr = '<a href="javascript:void(0)" '+ altText +' id="tf-'+ ele +'">'+ name +'</a>';
				strBuilder.push(subStr);
			} else {
				continue;
			}
		}

		$( 'textarea[name="body"]' ).before( '<div class="tf-toolbar"></div>' );
		$( '.tf-toolbar' ).html( strBuilder.join(' | ') );

		/*	Sets the CSS style
		 */
		styleRules = '\n/* generated by 8chan Formatting Tools */'+
					 '\n.tf-toolbar {padding: 0px 5px 1px 5px;}'+
					 '\n.tf-toolbar :link {text-decoration: none;}';
			for (ele in formats) {
				if (formats.hasOwnProperty(ele) && formats[ele].styleCSS) {
					styleRules += ' \n#tf-' + ele + ' {' + formats[ele].styleCSS + '}';
				}
			}
			//add CSS rule to user's custom CSS if it exist
			if ($( '.user-css' ).length !== 0) {
				$( '.user-css' ).append( styleRules );
			} else {
				$( 'body' ).append( '<style>'+ styleRules +'\n</style>' );
			}

		/*  Attach event listeners
		 */
		$( 'body' ).on( 'keydown', 'textarea[name="body"]', {formats: formats}, function (e) {
			//shortcuts
			if (e.ctrlKey) {
				var ch = String.fromCharCode(e.which).toLowerCase();
				var box = e.target;
				var formats = e.data.formats;
				for (var ele in formats) {
					if (formats.hasOwnProperty(ele) && (ch === formats[ele].shortcutKey)) {
						formats[ele].edit(box, formats[ele].options);
						e.preventDefault();
					}
				}
			}
		});
		$( 'body' ).on( 'keydown', '#quick-reply textarea[name="body"]', {formats: formats}, function (e) {
			//close quick reply when esc is prssed
			if (e.which === 27) {
				$( '.close-btn' ).trigger( 'click' );
			}
		});
		$( 'body' ).on( 'click', '.tf-toolbar a[id]', {formats: formats}, function (e) {
			//toolbar buttons
			var formats = e.data.formats;
			var box = $(e.target).parent().next()[0];

			for (var ele in formats) {
				if (formats.hasOwnProperty(ele) && (e.target.id === 'tf-' + ele)) {
					formats[ele].edit(box, formats[ele].options);
				}
			}
		});
		// $( 'body' ).on( 'keydown', function (e) {
		// 	if (e.which === 67 &&
		// 		e.target.nodeName !== 'INPUT' &&         //The C, the whole C, and nothing but the C
		// 		e.target.nodeName !== 'TEXTAREA' &&
		// 		!(e.ctrlKey || e.altKey || e.shiftKey)) {
		// 			document.location.href = '//'+ document.location.host +'/'+ board_name +'/catalog.html';
		// 		}
		// });
	
	});
}
