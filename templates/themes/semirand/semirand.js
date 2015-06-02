$(document).ready(function() {
	var cachedPages = [],
		loading = false,
		timer = null;

	// Load data from HTML5 localStorage
	var hiddenBoards = JSON.parse(localStorage.getItem('hiddenboards'));

	var storeHiddenBoards = function() {
		localStorage.setItem('hiddenboards', JSON.stringify(hiddenBoards));
	};

	// No board are hidden by default
	if (!hiddenBoards) {
		hiddenBoards = {};
		storeHiddenBoards();
	}

	// Hide threads from the same board and remember for next time
	var onHideClick = function(e) {
		e.preventDefault();
		var board = $(this).parent().next().data('board'),
			threads = $('[data-board="'+board+'"]:not([data-cached="yes"])'),
			btns = threads.prev().find('.threads-toggle'),
			hrs = btns.next();

		if (hiddenBoards[board]) {
			threads.show();
			btns.find('.threads-toggle').html(_('(hide threads from this board)'));
			hrs.hide();
		} else {
			threads.hide();
			btns.html(_('(show threads from this board)'));
			hrs.show();
		}

		hiddenBoards[board] = !hiddenBoards[board];
		storeHiddenBoards();
	};

	// Add a hiding link and horizontal separator to each thread
	var addHideButton = function() {
		var board  = $(this).next().data('board'),
			// Create the link and separator
			button = $('<a href="#" class="unimportant threads-toggle"></a>')
				.click(onHideClick),
			myHr   = $('<hr />');

		// Insert them after the board name
		$(this).append(' ').append(button).append(myHr);

		if (hiddenBoards[board]) {
			button.html(_('(show threads from this board)'));
			$(this).next().hide();
		} else {
			button.html(_('(hide threads from this board)'));
			myHr.hide();
		}
	};

	$('h2').each(addHideButton);

	var appendThread = function(elem, data) {
		var boardLink = $('<h2><a href="' + modRoot + data.board + '/">/' +
							data.board + '/</a></h2>');

		// Push the thread after the currently last one
		$('div[id*="thread_"]').last()
			.after(elem.data('board', data.board)
				   .data('cached', 'no')
				   .show());
		// Add the obligatory board link
		boardLink.insertBefore(elem);
		// Set up the hiding link
		addHideButton.call(boardLink);
		// Trigger an event to let the world know that we have a new thread aboard
		$(document).trigger('new_post', elem);
	};

	var attemptLoadNext = function() {
		if (!ukko_overflow.length) {
			$('.pages').show().html(_('No more threads to display'));
			return;
		}

		var viewHeight = $(window).scrollTop() + $(window).height(),
			pageHeight = $(document).height();
		// Keep loading deferred threads as long as we're close to the bottom of the
		// page and there are threads remaining
		while(viewHeight + 1000 > pageHeight && !loading && ukko_overflow.length > 0) {
			// Take the first unloaded post
			var post = ukko_overflow.shift(),
				page = modRoot + post.board + '/' + post.page;

			var thread = $('div#thread_' + post.id + '[data-board="' + post.board + '"]');
			// Check that the thread hasn't been inserted yet
			if (thread.length && thread.data('cached') !== 'yes') {
				continue;
			}

			// Check if we've already downloaded the index page on which this thread
			// is located
			if ($.inArray(page, cachedPages) !== -1) {
				if (thread.length) {
					appendThread(thread, post);
				}
			// Otherwise just load the page and cache its threads
			} else {
				// Make sure that no other thread does the job that we're about to do
				loading = true;
				$('.pages').show().html(_('Loading…'));

				// Retrieve the page from the server
				$.get(page, function(data) {
					cachedPages.push(page);

					// Cache each retrieved thread
					$(data).find('div[id*="thread_"]').each(function() {
						var thread_id = $(this).attr('id').replace('thread_', '');

						// Check that this thread hasn't already been loaded somehow
						if ($('div#thread_' + thread_id + '[data-board="' +
							  post.board + '"]').length)
						{
							return;
						}

						// Hide the freshly loaded threads somewhere at the top
						// of the page for now
						$('form[name="postcontrols"]')
							.prepend($(this).hide()
									 .data('cached', 'yes')
									 .data('data-board', post.board));
					});

					// Find the current thread in the newly retrieved ones
					thread = $('div#thread_' + post.id + '[data-board="' +
							   post.board + '"][data-cached="yes"]');

					if (thread.length) {
						appendThread(thread, post);
					}

					// Release the lock
					loading = false;
					$('.pages').hide().html('');
				});
				break;
			}
		}

		clearTimeout(timer);
		// Check again in one second
		timer = setTimeout(attemptLoadNext, 1000);
	};

	attemptLoadNext();
});
