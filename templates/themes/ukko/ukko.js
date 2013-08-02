(function(){

var cache = new Array(),
	thread = false,
	loading = false;
$(document).ready(function() {
	$('.pages').hide();
	$(window).on('scroll', function() {
		if (overflow.length == 0) {
			$('.pages').show().html(_("No more threads to display"));
		}
		while($(window).scrollTop() + $(window).height() + 500 > $(document).height() && !loading && overflow.length > 0) {
			var page = '../' + overflow[0].board + '/' + overflow[0].page;
			thread = $('div#thread_' + overflow[0].id + '[data-board="' + overflow[0].board + '"]');
			if (thread.length > 0 && thread.css('display') != 'none') { // already present
				overflow.shift();
				continue;
			}

			if($.inArray(page, cache) != -1 && thread.length > 0) {
				thread.prepend('<h2><a href="/' + overflow[0].board + '/">/' + overflow[0].board + '/</a></h2>');
				$('div[id*="thread_"]').last().after(thread.attr('data-board', overflow[0].board).css('display', 'block'));
				$(document).trigger('new_post', thread);
				overflow.shift();
			} else {
				loading = true;
				$('.pages').show().html(_("Loading..."));
				$.get(page, function(data) {
					cache.push(page);

					$(data).find('div[id*="thread_"]').each(function() {
						$('form[name="postcontrols"]').prepend($(this).css('display', 'none').attr('data-board', overflow[0].board));
					});

					thread = $('div#thread_' + overflow[0].id + '[data-board="' + overflow[0].board + '"]');
					if(thread.length > 0 && thread.css('display') != 'none') {
						thread.prepend('<h2><a href="/' + overflow[0].board + '/">/' + overflow[0].board + '/</a></h2>');
						$('div[id*="thread_"]').last().after(thread.attr('data-board', overflow[0].board).css('display', 'block'));
						$(document).trigger('new_post', thread);
						overflow.shift();
					}
					else {
						overflow.shift(); // We missed it? Or already present...
					}

					loading = false;
					$('.pages').hide().html("");
				});
				break;
			}
		}
	});
});

})();
