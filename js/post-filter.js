if (active_page === 'thread' || active_page === 'index') {
	$(document).ready(function () {
		'use strict';
		// returns blacklist object from storage
		function getList() {
			return JSON.parse(localStorage.postFilter);
		}

		// stores blacklist into storage and reruns the filter
		function setList(blacklist) {
			localStorage.postFilter = JSON.stringify(blacklist);
			$(document).trigger('filter_page');
		}

		// unit: seconds
		function timestamp() {
			return Math.floor((new Date()).getTime() / 1000);
		}

		function initList(list, boardId, threadId) {
			if (typeof list.postFilter[boardId] == 'undefined')
				list.postFilter[boardId] = {};
				list.nextPurge[boardId] = {};
			if (typeof list.postFilter[boardId][threadId] == 'undefined') {
				list.postFilter[boardId][threadId] = [];
			}
			list.nextPurge[boardId][threadId] = {timestamp: timestamp(), interval: 86400};  // 86400 seconds == 1 day
		}

		var blacklist = {
			add: {
				name: function (posterName) {
					var list = getList();
					var filter = list.nameFilter;

					for (var i in filter) {
						if (filter[i].name == posterName) return;
					}
					filter.push({
						name: posterName
					});
					setList(list);
					drawFilterList();
				},
				trip: function (posterTrip) {
					var list = getList();
					var filter = list.nameFilter;

					for (var i in filter) {
						if (filter[i].trip == posterTrip) return;
					}
					filter.push({
						trip: posterTrip
					});
					setList(list);
					drawFilterList();
				},
				post: function (boardId, threadId, postId, hideReplies) {
					var list = getList();
					var filter = list.postFilter;

					initList(list, boardId, threadId);

					for (var i in filter[boardId][threadId]) {
						if (filter[boardId][threadId][i].post == postId) return;
					}
					filter[boardId][threadId].push({
						post: postId,
						hideReplies: hideReplies
					});
					setList(list);
				},
				uid: function (boardId, threadId, uniqueId, hideReplies) {
					var list = getList();
					var filter = list.postFilter;

					initList(list, boardId, threadId);

					for (var i in filter[boardId][threadId]) {
						if (filter[boardId][threadId][i].uid == uniqueId) return;
					}
					filter[boardId][threadId].push({
						uid: uniqueId,
						hideReplies: hideReplies
					});
					setList(list);
				}
			},
			remove: {
				name: function (posterName) {
					var list = getList();
					var filter = list.nameFilter;

					for (var i=0; i<filter.length; i++) {
						if (filter[i].name == posterName) {
							filter.splice(i, 1);
							break;
						}
					}
					setList(list);
					drawFilterList();
				},
				trip: function (posterTrip) {
					var list = getList();
					var filter = list.nameFilter;

					for (var i=0; i<filter.length; i++) {
						if (filter[i].trip == posterTrip) {
							filter.splice(i, 1);
							break;
						}
					}
					setList(list);
					drawFilterList();
				},
				post: function (boardId, threadId, postId) {
					var list = getList();
					var filter = list.postFilter;

					// thread already pruned
					if (typeof filter[boardId] == 'undefined' || typeof filter[boardId][threadId] == 'undefined')
						return;

					for (var i=0; i<filter[boardId][threadId].length; i++) {
						if (filter[boardId][threadId][i].post == postId) {
							filter[boardId][threadId].splice(i, 1);
							break;
						}
					}

					if ($.isEmptyObject(filter[boardId][threadId])) {
						delete filter[boardId][threadId];
						delete list.nextPurge[boardId][threadId];

						if ($.isEmptyObject(filter[boardId])) {
							delete filter[boardId];
							delete list.nextPurge[boardId];
						}
					}
					setList(list);
				},
				uid: function (boardId, threadId, uniqueId) {
					var list = getList();
					var filter = list.postFilter;

					// thread already pruned
					if (typeof filter[boardId] == 'undefined' || typeof filter[boardId][threadId] == 'undefined')
						return;

					for (var i=0; i<filter[boardId][threadId].length; i++) {
						if (filter[boardId][threadId][i].uid == uniqueId) {
							filter[boardId][threadId].splice(i, 1);
							break;
						}
					}

					if ($.isEmptyObject(filter[boardId][threadId])) {
						delete filter[boardId][threadId];
						delete list.nextPurge[boardId][threadId];

						if ($.isEmptyObject(filter[boardId])) {
							delete filter[boardId];
							delete list.nextPurge[boardId];
						}
					}
					setList(list);
				}
			}
		};

		/* 
		 *  hide/show the specified thread/post
		 */
		function hide(ele) {
			var $ele = $(ele);

			if (ele.dataset.hidden == '1')
				return;

			ele.dataset.hidden = '1';
			if ($ele.hasClass('op')) {
				$ele.parent().find('.body, .files, .video-container').not($ele.children('.reply').children()).hide();

				// hide thread replies on index view
				if (active_page == 'index') $ele.parent().find('.omitted, .reply:not(.hidden), post_no, .mentioned, br').hide();
			} else {
				// normal posts
				$ele.children('.body, .files, .video-container').hide();
			}
		}
		function show(ele) {
			var $ele = $(ele);

			ele.dataset.hidden = '0';
			if ($ele.hasClass('op')) {
				$ele.parent().find('.body, .files, .video-container').show();
				if (active_page == 'index') $ele.parent().find('.omitted, .reply:not(.hidden), post_no, .mentioned, br').show();
			} else {
				// normal posts
				$ele.children('.body, .files, .video-container').show();
			}
		}

		/* 
		 *  create filter menu when the button is clicked
		 */
		function addMenu(e, ele, threadId, pageData) {

			var $ele = $(ele);
			var $buffer;

			var postId = $ele.find('.post_no').not('[id]').text();
			if (pageData.hasUID) {
				var postUid = $ele.find('.poster_id').text();
			}
			var postName;
			var postTrip = '';
			if (!pageData.forcedAnon) {
				postName = (typeof $ele.find('.name').contents()[0] == 'undefined') ? '' : $ele.find('.name').contents()[0].nodeValue.trim();
				postTrip = $ele.find('.trip').text();
			}

			//  get the button's position
			var pos = $(e.target).offset();

			$buffer = $('<div class="filter-menu"></div>').append(
				$('<ul>').append(
					$('<li>', {id: 'filter-menu-unhide'}).text('Unhide post'),
					$('<li>', {id: 'filter-menu-add'}).append(
						$('<ul>').append(
							$('<li>', {class: 'filter-post'}).text('Post'),
							$('<li>', {class: 'filter-post-plus', title: 'Hide post and all replies'}).text('Post +'),
							$('<li>', {class: 'filter-id'}).text('ID'),
							$('<li>', {class: 'filter-id-plus', title: 'Hide ID and all replies'}).text('ID +'),
							$('<li>', {class: 'filter-name'}).text('Name'),
							$('<li>', {class: 'filter-trip'}).text('Tripcode')
						),
						'Add filter',
						$('<span>', {class: 'filter-menu-arrow'}).append('»')
					),
					$('<li>', {id: 'filter-menu-remove'}).append(
						$('<ul>').append(
							$('<li>', {class: 'filter-id'}).text('ID'),
							$('<li>', {class: 'filter-name'}).text('Name'),
							$('<li>', {class: 'filter-trip'}).text('Tripcode')
						),
						'Remove filter',
						$('<span>', {class: 'filter-menu-arrow'}).append('»')
					)
				)
			);

			/*  display logic and bind click handlers
			 */

			 // unhide button
			if (ele.dataset.hidden == '1' && ele.dataset.hiddenByUid == '0' &&
				ele.dataset.hiddenByName == '0' && ele.dataset.hiddenByTrip == '0') {
				$buffer.find('#filter-menu-unhide').click(function () {
					//  if hidden due to post id, remove it from blacklist
					//  otherwise just show this post
					blacklist.remove.post(pageData.boardId, threadId, postId);
					show(ele);
				});
			} else {
				$buffer.find('#filter-menu-unhide').addClass('hidden');
			}

			//  post id
			if (ele.dataset.hiddenByPost == '0') {
				$buffer.find('#filter-menu-add .filter-post').click(function () {
					blacklist.add.post(pageData.boardId, threadId, postId, false);
				});
				$buffer.find('#filter-menu-add .filter-post-plus').click(function () {
					blacklist.add.post(pageData.boardId, threadId, postId, true);
				});
			} else {
				$buffer.find('#filter-menu-add .filter-post').addClass('hidden');
				$buffer.find('#filter-menu-add .filter-post-plus').addClass('hidden');
			}

			// UID
			if (pageData.hasUID && ele.dataset.hiddenByUid == '0') {
				$buffer.find('#filter-menu-add .filter-id').click(function () {
					blacklist.add.uid(pageData.boardId, threadId, postUid, false);
				});
				$buffer.find('#filter-menu-add .filter-id-plus').click(function () {
					blacklist.add.uid(pageData.boardId, threadId, postUid, true);
				});

				$buffer.find('#filter-menu-remove .filter-id').addClass('hidden');
			} else  if (pageData.hasUID) {
				$buffer.find('#filter-menu-remove .filter-id').click(function () {
					blacklist.remove.uid(pageData.boardId, threadId, postUid);
				});

				$buffer.find('#filter-menu-add .filter-id').addClass('hidden');
				$buffer.find('#filter-menu-add .filter-id-plus').addClass('hidden');
			} else {
				// board doesn't use UID
				$buffer.find('#filter-menu-add .filter-id').addClass('hidden');
				$buffer.find('#filter-menu-add .filter-id-plus').addClass('hidden');
				$buffer.find('#filter-menu-remove .filter-id').addClass('hidden');
			}

			//  name
			if (!pageData.forcedAnon && ele.dataset.hiddenByName == '0') {
				$buffer.find('#filter-menu-add .filter-name').click(function () {
					blacklist.add.name(postName);
				});

				$buffer.find('#filter-menu-remove .filter-name').addClass('hidden');
			} else if (!pageData.forcedAnon) {
				$buffer.find('#filter-menu-remove .filter-name').click(function () {
					blacklist.remove.name(postName);
				});

				$buffer.find('#filter-menu-add .filter-name').addClass('hidden');
			} else {
				// board has forced anon
				$buffer.find('#filter-menu-remove .filter-name').addClass('hidden');
				$buffer.find('#filter-menu-add .filter-name').addClass('hidden');
			}

			//  tripcode
			if (!pageData.forcedAnon && ele.dataset.hiddenByTrip == '0' && postTrip !== '') {
				$buffer.find('#filter-menu-add .filter-trip').click(function () {
					blacklist.add.trip(postTrip);
				});

				$buffer.find('#filter-menu-remove .filter-trip').addClass('hidden');
			} else if (!pageData.forcedAnon && postTrip !== '') {
				$buffer.find('#filter-menu-remove .filter-trip').click(function () {
					blacklist.remove.trip(postTrip);
				});

				$buffer.find('#filter-menu-add .filter-trip').addClass('hidden');
			} else {
				// board has forced anon
				$buffer.find('#filter-menu-remove .filter-trip').addClass('hidden');
				$buffer.find('#filter-menu-add .filter-trip').addClass('hidden');
			}

			/*  hide sub menus if all items are hidden
			 */
			if (!$buffer.find('#filter-menu-remove > ul').children().not('.hidden').length) {
				$buffer.find('#filter-menu-remove').addClass('hidden');
			}
			if (!$buffer.find('#filter-menu-add > ul').children().not('.hidden').length) {
				$buffer.find('#filter-menu-add').addClass('hidden');
			}

			/*  set menu position
			 */
			 $buffer.css({top: pos.top + 20, left: pos.left});

			/*  finally append to page
			 */
			$('body').append($buffer);
		}

		/* 
		 *  add menu button to the thread/post
		 */
		function addMenuButton(ele, threadId, pageData) {
			if ($(ele).find('.filter-btn').length)
				$('.filter-btn').remove();

			$(ele).find('.intro')
				.append(
					$('<a>', {href: '#', class: 'filter-btn', title: 'Filter menu'})
						.text('▶')
						.click(function (e) {
							//  button toggle
							e.preventDefault();
							//  remove existing menu
							$('.filter-menu').remove();

							if ($(e.target).hasClass('filter-btn-open')) {
								$('.filter-btn-open').removeClass('filter-btn-open');
							} else {
								//  close previous button
								$('.filter-btn-open').removeClass('filter-btn-open');
								//  mark the menu button as open
								$(ele).find('.filter-btn').addClass('filter-btn-open');
								addMenu(e, ele, threadId, pageData);
							}
						})
				);
		}

		/*
		 *  determine whether the reply post should be hidden
		 *   - applies to all posts on page load or filtering rule change
		 *   - apply to new posts on thread updates
		 *   - must explicitly set the state of each attributes because filter will reapply to all posts after filtering rule change
		 */
		function filter(post, threadId, pageData) {
			var $post = $(post);
			var list = getList();
			var postId = $post.find('.post_no').not('[id]').text();
			var name, trip, uid;
			var i, array;  // temp variables

			var boardId     = pageData.boardId;
			var localList   = pageData.localList;
			var noReplyList = pageData.noReplyList;
			var hasUID      = pageData.hasUID;
			var forcedAnon  = pageData.forcedAnon;

			post.dataset.hidden = '0';
			post.dataset.hiddenByUid = '0';
			post.dataset.hiddenByPost = '0';
			post.dataset.hiddenByName = '0';
			post.dataset.hiddenByTrip = '0';

			// add post with matched UID to localList
			if (hasUID &&
				typeof list.postFilter[boardId] != 'undefined' &&
				typeof list.postFilter[boardId][threadId] != 'undefined') {
				uid = $post.find('.poster_id').text();
				array = list.postFilter[boardId][threadId];

				for (i=0; i<array.length; i++) {
					if (array[i].uid == uid) {
						post.dataset.hiddenByUid = '1';
						localList.push(postId);
						if (array[i].hideReplies) noReplyList.push(postId);
						break;
					}
				}
			}

			// match localList
			if (localList.length) {
				if ($.inArray(postId, localList) != -1) {
					if (post.dataset.hiddenByUid != '1') post.dataset.hiddenByPost = '1';
					hide(post);
				}
			}

			// match poster name and tripcode
			if (!forcedAnon) {
				name = (typeof $post.find('.name').contents()[0] == 'undefined') ? '' : $post.find('.name').contents()[0].nodeValue.trim();
				if ($(list.nameFilter).filter(function(){if (this.name == name) return true;}).length) {
					post.dataset.hiddenByName = '1';
					hide(post);
				}
				if ($post.find('.trip').length) {
					trip = $post.find('.trip').text();
					if ($(list.nameFilter).filter(function(){if (this.trip == trip) return true;}).length) {
						post.dataset.hiddenByTrip = '1';
						hide(post);
					}
				}
			}

			// check for link to filtered posts
			$post.find('.body a').not('[rel="nofollow"]').each(function () {
				var replyId = $(this).text().match(/^>>(\d+)$/);

				if (!replyId)
					return;

				replyId = replyId[1];
				if ($.inArray(replyId, noReplyList) != -1) {
					hide(post);
				}
			});

			// post didn't match any filters
			if ((typeof post.dataset.hidden == 'undefined' || post.dataset.hidden == '0') &&
				post.dataset.hiddenByUid == '0' &&
				post.dataset.hiddenByUid == post.dataset.hiddenByPost == post.dataset.hiddenByName == post.dataset.hiddenByTrip) {
				show(post);
			}
		}
		/*  (re)runs the filter on the entire page
		 */
		 function filterPage(pageData) {
			var list = getList();

			// empty the local and no-reply list
			pageData.localList = [];
			pageData.noReplyList = [];

			$('.thread').each(function () {
				var $thread = $(this);
				// disregard the hidden threads constructed by post-hover.js
				if ($thread.css('display') == 'none')
					return;

				var threadId = $thread.attr('id').replace('thread_', '');
				var op = $thread.children('.op')[0];
				var i, array;  // temp variables

				// add posts to localList and noReplyList
				if (typeof list.postFilter[pageData.boardId] != 'undefined') {
					array = list.postFilter[pageData.boardId][threadId];
					if (typeof array != 'undefined') {
						for (i=0; i<array.length; i++) {
							if ( typeof array[i].post == 'undefined')
								continue;

							pageData.localList.push(array[i].post);
							if (array[i].hideReplies) pageData.noReplyList.push(array[i].post);
						}
					}
				}
				// run filter on OP
				filter(op, threadId, pageData);
				addMenuButton(op, threadId, pageData);

				// iterate filter over each post
				if (op.dataset.hidden != '1' || active_page == 'thread') {
					$thread.find('.reply').not('.hidden').each(function () {
						filter(this, threadId, pageData);
						addMenuButton(this, threadId, pageData);
					});
				}

			});
		 }

		/* 
		 *  
		 */
		function initStyle() {
			var $ele, cssStyle, cssString;

			$ele = $('<div>').addClass('post reply').hide().appendTo('body');
			cssStyle = $ele.css(['background-color', 'border-color']);
			cssStyle.hoverBg = $('body').css('background-color');
			$ele.remove();

			cssString =
				'\n/*** Generated by post-filter ***/\n' +
				'.filter-menu {position: absolute; font-size: 12px; line-height: 1.3em;}\n' +
				'.filter-menu ul {\n' +
				'    background-color: '+ cssStyle['background-color'] +'; border: 1px solid '+ cssStyle['border-color'] +'; border-right-width: 2px;\n' +
				'    list-style: none; padding: 0; margin: 0; white-space: nowrap;\n}\n' +
				'.filter-menu li {cursor: pointer; position: relative; padding: 4px 4px; vertical-align: middle; border-bottom: 1px solid '+ cssStyle['border-color'] +'; white-space: normal; width: 90px;}\n' +
				'.filter-menu li:hover {background-color: '+ cssStyle.hoverBg +';}\n' +
				'.filter-menu ul ul {display: none; position: absolute;}\n' +
				'.filter-menu li:hover ul {display: block; left: 100%; margin-top: -3px;}\n' +
				'.filter-menu li ul li {white-space: nowrap; width: auto;}\n' +
				'.filter-menu-arrow {float: right; margin-left: 10px;}\n' +
				'.filter-menu.hidden, .filter-menu .hidden {display: none;}\n' +
				'.filter-btn {transition: transform 0.1s; width: 15px; text-align: center; font-size: 12pt; opacity: 0.8; text-decoration: none; margin: -6px 0px; display: inline-block;}\n' +
				'.filter-btn:hover {opacity: 1;}\n' +
				'.filter-btn-open {transform: rotate(90deg);}\n';
			cssString +=
				'#filter-control #clear {float: right;}\n' +
				'#filter-container {margin-top: 20px; border: 1px solid; height: 270px; overflow: auto;}\n' +
				'#filter-list {width: 100%; border-collapse: collapse;}\n' +
				'#filter-list th {text-align: center; height: 20px; font-size: 14px; border-bottom: 1px solid;}\n' +
				'#filter-list th:nth-child(1) {text-align: center; width: 70px;}\n' +
				'#filter-list th:nth-child(2) {text-align: left;}\n' +
				'#filter-list th:nth-child(3) {text-align: center; width: 58px;}\n' +
				'#filter-list tr:not(#header) {height: 22px;}\n' +
				'#filter-list tr:nth-child(even) {background-color:rgba(255, 255, 255, 0.5);}\n' +
				'#filter-list td:nth-child(1) {text-align: center; width: 70px;}\n' +
				'#filter-list td:nth-child(3) {text-align: center; width: 58px;}\n' +
				'#confirm {text-align: right; margin-bottom: -18px; padding-top: 2px; font-size: 14px; color: #FF0000;}';

			if (!$('style.generated-css').length) $('<style class="generated-css">').appendTo('head');
			$('style.generated-css').html($('style.generated-css').html() + cssString);
		}

		function drawFilterList() {
			var list = getList().nameFilter;
			var $ele = $('#filter-list');
			var $row;
			var type, val;
			var i, length, obj;

			$ele.empty();

			$ele.append('<tr id="header"><th>Type</th><th>Content</th><th>Remove</th></tr>');
			for (i = 0, length = list.length; i < length; i++) {
				obj = list[i];
				for (type in obj) {
					val = obj[type];
					$row = $('<tr>');
					$row.append(
								'<td>'+ type +'</td>',
								'<td>'+ val +'</td>',
								$('<td>').append(
									$('<a>').html('X')
										.addClass('del-btn')
										.attr('href', '#')
										.attr('data-type', type)
										.attr('data-val', val)
								)
							);
					$ele.append($row);
				}
			}
		}

		function initOptionsPanel() {
			if (window.Options && !Options.get_tab('filter')) {
				Options.add_tab('filter', 'list', 'Filters');
				Options.extend_tab('filter',
					'<div id="filter-control">' +
						'<input id="input" type="text"></input>' +
						'<button id="name">Add Name</button>' +
						'<button id="trip">Add Tripcode</button>' +
						'<button id="clear">Clear all filters</button>' +
						'<div id="confirm" class="hidden">' +
							'This will clear all filtering rules including hidden posts. <a id="confirm-y" href="#">yes</a> | <a id="confirm-n" href="#">no</a>' +
						'</div>' +
					'</div>' +
					'<div id="filter-container"><table id="filter-list"></table></div>'
				);
				drawFilterList();

				// control buttons
				$('#filter-control').on('click', '#name', function () {
					var str = $('#filter-control #input').val().trim();
					$('#filter-control #input').val('');
					blacklist.add.name(str);
				});
				$('#filter-control').on('click', '#trip', function () {
					var str = $('#filter-control #input').val().trim();
					$('#filter-control #input').val('');
					blacklist.add.trip(str);
				});
				$('#filter-control').on('click', '#clear', function () {
					$('#filter-control #clear').addClass('hidden');
					$('#filter-control #confirm').removeClass('hidden');
				});
				$('#filter-control').on('click', '#confirm-y', function (e) {
					e.preventDefault();

					$('#filter-control #clear').removeClass('hidden');
					$('#filter-control #confirm').addClass('hidden');
					setList({
						nameFilter: [],
						postFilter: {},
						nextPurge: {},
						lastPurge: timestamp()
					});
					drawFilterList();
				});
				$('#filter-control').on('click', '#confirm-n', function (e) {
					e.preventDefault();

					$('#filter-control #clear').removeClass('hidden');
					$('#filter-control #confirm').addClass('hidden');
				});


				// remove button
				$('#filter-list').on('click', '.del-btn', function (e) {
					e.preventDefault();

					var $ele = $(e.target);
					var type = $ele.attr('data-type');
					var val = $ele.attr('data-val');
					blacklist.remove[type](val);
				});
			}
		}

		/* 
		 *  clear out pruned threads
		 */
		function purge() {
			var list = getList();
			var board, thread, boardId, threadId;
			var deferred;
			var requestArray = [];

			var successHandler = function (boardId, threadId) {
				return function () {
					// thread still alive, keep it in the list and increase the time between checks.
					var list = getList();
					var thread = list.nextPurge[boardId][threadId];

					thread.timestamp = timestamp();
					thread.interval = Math.floor(thread.interval * 1.5);
					setList(list);
				};
			};
			var errorHandler = function (boardId, threadId) {
				return function (xhr) {
					if (xhr.status == 404) {
						var list = getList();

						delete list.nextPurge[boardId][threadId];
						delete list.postFilter[boardId][threadId];
						if ($.isEmptyObject(list.nextPurge[boardId])) delete list.nextPurge[boardId];
						if ($.isEmptyObject(list.postFilter[boardId])) delete list.postFilter[boardId];
						setList(list);
					}
				};
			};

			if ((timestamp() - list.lastPurge) < 86400)  // less than 1 day
				return;
			
			for (boardId in list.nextPurge) {
				board = list.nextPurge[boardId];
				for (threadId in board) {
					thread = board[threadId];
					if (timestamp() > (thread.timestamp + thread.interval)) {
						// check if thread is pruned
						deferred = $.ajax({
							cache: false,
							url: '/'+ boardId +'/res/'+ threadId +'.json',
							success: successHandler(boardId, threadId),
							error: errorHandler(boardId, threadId)
						});
						requestArray.push(deferred);
					}
				}
			}

			// when all requests complete
			$.when.apply($, requestArray).always(function () {
				var list = getList();
				list.lastPurge = timestamp();
				setList(list);
			});
		}

		function init() {
			if (typeof localStorage.postFilter === 'undefined') {
				localStorage.postFilter = JSON.stringify({
					nameFilter: [],
					postFilter: {},
					nextPurge: {},
					lastPurge: timestamp()
				});
			}

			var pageData = {
				boardId: board_name,  // get the id from the global variable
				localList: [],  // all the blacklisted post IDs or UIDs that apply to the current page
				noReplyList: [],  // any posts that replies to the contents of this list shall be hidden
				hasUID: (document.getElementsByClassName('poster_id').length > 0),
				forcedAnon: ($('th:contains(Name)').length === 0)  // tests by looking for the Name label on the reply form
			};

			initStyle();
			initOptionsPanel();
			filterPage(pageData);

			// menu close click handler
			$(document).on('click', function (e){
				if ($(e.target).hasClass('filter-btn'))
					return;

				$('.filter-menu').remove();
				$('.filter-btn-open').removeClass('filter-btn-open');
			});

			// on new posts
			$(document).on('new_post', function (e, post) {
				var threadId = $(post).parent().attr('id').replace('thread_', '');

				filter(post, threadId, pageData);
				addMenuButton(post, threadId, pageData);
			});

			$(document).on('filter_page', function () {
				filterPage(pageData);
			});

			// clear out the old threads
			purge();
		}
		init();
	});
}
