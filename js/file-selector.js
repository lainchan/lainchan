/*
 * file-selector.js - Add support for drag and drop file selection, and paste from clipbboard on supported browsers.
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/file-selector.js';
 */

if (active_page == 'index' || active_page == 'thread') {
$(document).ready(function () {

// add options panel item
if (window.Options && Options.get_tab('general')) {
	Options.extend_tab('general', '<label id="file-drag-drop"><input type="checkbox">' + _('Drag and drop file selection') + '</label>');

	$('#file-drag-drop>input').on('click', function() {
		if ($('#file-drag-drop>input').is(':checked')) {
			localStorage.file_dragdrop = 'true';
		} else {
			localStorage.file_dragdrop = 'false';
		}
	});

	if (localStorage.file_dragdrop === 'undefined') localStorage.file_dragdrop = 'true';
	if (localStorage.file_dragdrop === 'true') $('#file-drag-drop>input').prop('checked', true);
}

// disabled by user, or incompatible browser.
// fallback to old
if (localStorage.file_dragdrop == 'false' || !(window.FileReader && window.File)) {
	$('.dropzone-wrap').remove();
	$('#upload_file').show();

	return;
}

// multipost not enabled
if (typeof max_images == 'undefined') {
	var max_images = 1;
}

var files = [];

function addFile(file) {
	if (files.length == max_images)
		return;

	files.push(file);
	addThumb(file);
}

function removeFile(file) {
	files.splice(files.indexOf(file), 1);
}

function getThumbElement(file) {
	return $('.tmb-container').filter(function(){return($(this).data('file-ref')==file);});
}

function addThumb(file) {

	var fileName = (file.name.length < 24) ? file.name : file.name.substr(0, 22) + '…';
	var fileType = file.type.split('/')[0];
	var fileExt = file.type.split('/')[1];
	var $fileThumb;

	$('.file-thumbs').append($('<div>')
		.addClass('tmb-container')
		.data('file-ref', file)
		.append(
			$('<div>').addClass('remove-btn').html('✖'),
			$('<div>').addClass('file-tmb'),
			$('<div>').addClass('tmb-filename').html(fileName)
		)
	);

	if (fileType == 'image') {
		// if image file, generate thumbnail
		var reader = new FileReader();

		reader.onloadend = function () {
			var dataURL = reader.result;
			var $fileThumb = getThumbElement(file).find('.file-tmb');
			$fileThumb.css('background-image', 'url('+ dataURL +')');
		};

		reader.readAsDataURL(file);
	} else {
		$fileThumb = getThumbElement(file).find('.file-tmb');
		$fileThumb.html('<span>' + fileExt.toUpperCase() + '</span>');
	}
}

$(document).on('ajax_before_post', function (e, formData) {
	for (var i=0; i<max_images; i++) {
		var key = 'file';
		if (i > 0) key += i + 1;
		formData.append(key, files[i]);
	}
});

// clear file queue and UI on success
$(document).on('ajax_after_post', function () {
	files = [];
	$('.file-thumbs').empty();
});

var dragCounter = 0;
var dropHandlers = {
	dragenter: function (e) {
		e.stopPropagation();
		e.preventDefault();

		if (dragCounter === 0) $(this).addClass('dragover');
		dragCounter++;
	},
	dragover: function (e) {
		// needed for webkit to work
		e.stopPropagation();
		e.preventDefault();
	},
	dragleave: function (e) {
		e.stopPropagation();
		e.preventDefault();

		dragCounter--;
		if (dragCounter === 0) $(this).removeClass('dragover');
	},
	drop: function (e) {
		e.stopPropagation();
		e.preventDefault();

		$(this).removeClass('dragover');
		dragCounter = 0;

		var fileList = e.originalEvent.dataTransfer.files;
		for (var i=0; i<fileList.length; i++) {
			addFile(fileList[i]);
		}
	}
};

$('.dropzone').css('user-select', 'none')  // let jquery add browser specific prefix

// attach handlers
$(document).on(dropHandlers, '.dropzone');

$(document).on('click', '.dropzone .remove-btn', function (e) {
	var file = $(e.target).parent().data('file-ref');

	removeFile(file);
	$(e.target).parent().remove();
});

$(document).on('click', '.dropzone .file-hint', function (e) {
	var $fileSelector = $('<input type="file" multiple>');

	$fileSelector.on('change', function (e) {
		if (this.files.length > 0) {
			for (var i=0; i<this.files.length; i++) {
				addFile(this.files[i]);
			}
		}
		$(this).remove();
	});

	$fileSelector.click();
});

$(document).on('paste', function (e) {
	var clipboard = e.originalEvent.clipboardData;
	if (typeof clipboard.items != 'undefined' && clipboard.items.length != 0) {
		
		//Webkit
		for (var i=0; i<clipboard.items.length; i++) {
			if (clipboard.items[i].kind != 'file')
				continue;

			//convert blob to file
			var file = new File([clipboard.items[i].getAsFile()], 'ClipboardImage.png', {type: 'image/png'});
			addFile(file);
		}
	}
});

});
}
