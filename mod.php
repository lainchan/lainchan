<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	// Check if banned
	checkBan();
			
	require 'inc/mod.php';
	
	if (get_magic_quotes_gpc()) {
		function strip_array($var) {
			return is_array($var) ? array_map("strip_array", $var) : stripslashes($var);
		}
		
		$_GET = strip_array($_GET);
		$_POST = strip_array($_POST);
	}
	
	$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
	
	// If not logged in
	if(!$mod) {
		if(isset($_POST['login'])) {
			// Check if inputs are set and not empty
			if(	!isset($_POST['username']) ||
				!isset($_POST['password']) ||
				empty($_POST['username']) ||
				empty($_POST['password'])
				) loginForm($config['error']['invalid'], $_POST['username'], '?' . $query);
			
			
			if(!login($_POST['username'], $_POST['password'])) {
				if($config['syslog'])
					_syslog(LOG_WARNING, 'Unauthorized login attempt!');
				loginForm($config['error']['invalid'], $_POST['username'], '?' . $query);
			}
			
			modLog("Logged in.");
			
			// Login successful
			// Set cookies
			setCookies();
			
			// Redirect
			if(isset($_POST['redirect']))
				header('Location: ' . $_POST['redirect'], true, $config['redirect_http']);
			else
				header('Location: ?' . $config['mod']['default'], true, $config['redirect_http']);
		} else {
			loginForm(false, false, '?' . $query);
		}
	} else {
		// Redirect (for index pages)
		if(count($_GET) == 2 && isset($_GET['status']) && isset($_GET['r'])) {
			header('Location: ' . $_GET['r'], true, $_GET['status']);
			exit;
		}
		
		// A sort of "cache"
		// Stops calling preg_quote and str_replace when not needed; only does it once
		$regex = Array(
			'board' => str_replace('%s', '(\w{1,8})', preg_quote($config['board_path'], '/')),
			'page' => str_replace('%d', '(\d+)', preg_quote($config['file_page'], '/')),
			'img' => preg_quote($config['dir']['img'], '/'),
			'thumb' => preg_quote($config['dir']['thumb'], '/'),
			'res' => preg_quote($config['dir']['res'], '/'),
			'index' => preg_quote($config['file_index'], '/')
		);
		
		if(preg_match('/^\/?$/', $query)) {
			// Dashboard
			$fieldset = Array(
				'Boards' => '',
				'Noticeboard' => '',
				'Administration' => '',
				'Themes' => '',
				'Search' => '',
				'Update' => '',
				'Logout' => ''
			);
			
			// Boards
			$fieldset['Boards'] .= ulBoards();
			
			if(hasPermission($config['mod']['noticeboard'])) {
				if(!$config['cache']['enabled'] || !($fieldset['Noticeboard'] = cache::get('noticeboard_preview'))) {
				
					$query = prepare("SELECT `noticeboard`.*, `username` FROM `noticeboard` LEFT JOIN `mods` ON `mods`.`id` = `mod` ORDER BY `id` DESC LIMIT :limit");
					$query->bindValue(':limit', $config['mod']['noticeboard_dashboard'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				
					$fieldset['Noticeboard'] .= '<li>';
				
					$_body = '';
					while($notice = $query->fetch()) {					
						$_body .= '<li><a href="?/noticeboard#' .
							$notice['id'] .
						'">' .
						($notice['subject'] ?
							$notice['subject']
						:
							'<em>' . _('no subject') . '</em>'
						) .
					'</a><span class="unimportant"> &mdash; by ' .
						(isset($notice['username']) ?
							utf8tohtml($notice['username'])
						: '<em>???</em>') .
					' at ' .
						strftime($config['post_date'], $notice['time']) .
					'</span></li>';
					}
					if(!empty($_body)) {
						$fieldset['Noticeboard'] .= '<ul>' . $_body . '</ul></li><li>';
					}
				
					$fieldset['Noticeboard'] .= '<a href="?/noticeboard">' . _('View all entries') . '</a></li>';
				
					$query = prepare("SELECT COUNT(*) AS `count` FROM `pms` WHERE `to` = :id AND `unread` = 1");
					$query->bindValue(':id', $mod['id']);
					$query->execute() or error(db_error($query));
					$count = $query->fetch();
					$count = $count['count'];
				
					$fieldset['Noticeboard'] .= '<li><a href="?/inbox">' . _('PM Inbox') . 
						($count > 0
						?
							' <strong>(' . $count . ' unread)</strong>'
						: '') .
					'</a></li>';
				
					$fieldset['Noticeboard'] .= '<li><a href="?/news">' . _('News') . '</a></li>';
				
					if($config['cache']['enabled'])
						cache::set('noticeboard_preview', $fieldset['Noticeboard']);
				}
			}
			
			
			if(hasPermission($config['mod']['reports'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/reports">' . _('Report queue') . '</a></li>';
			}
			if(hasPermission($config['mod']['view_banlist'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/bans">' . _('Ban list') . '</a></li>';
			}
			if(hasPermission($config['mod']['manageusers'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/users">' . _('Manage users') . '</a></li>';
			} elseif(hasPermission($config['mod']['change_password'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/users/' . $user['id'] . '">' . _('Change own password') . '</a></li>';
			}
			if(hasPermission($config['mod']['modlog'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/log">' . _('Moderation log') . '</a></li>';
			}
			if(hasPermission($config['mod']['rebuild'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/rebuild">' . _('Rebuild static files') . '</a></li>';
			}
			if(hasPermission($config['mod']['rebuild']) && $config['cache']['enabled']) {
				$fieldset['Administration'] .= 	'<li><a href="?/flush">' . _('Clear cache') . '</a></li>';
			}
			if(hasPermission($config['mod']['show_config'])) {
				$fieldset['Administration'] .= 	'<li><a href="?/config">' . _('Show configuration') . '</a></li>';
			}
			
			if(hasPermission($config['mod']['themes'])) {
				$fieldset['Themes'] .= 	'<li><a href="?/themes">' . _('Manage themes') . '</a></li>';
			}
			
			if(hasPermission($config['mod']['search'])) {
				$fieldset['Search'] .= 	'<li><form style="display:inline" action="?/search" method="post">' .
				'<label style="display:inline" for="search">' . _('Phrase:') . '</label> ' .
					'<input id="search" name="search" type="text" size="35" />' .
					'<input type="submit" value="' . _('Search') . '" />' .
				'</form>' .
					'<p class="unimportant">' . _('(Search is case-insensitive, and based on keywords. To match exact phrases, use "quotes". Use an asterisk (*) for wildcard.)') . '</p>' .
				'</li>';
			}
			
			if($mod['type'] >= ADMIN && $config['check_updates']) {
				if(!$config['version'])
					error('Could not find current version! (Check .installed)');
				if(isset($_COOKIE['update'])) {
					$latest = unserialize($_COOKIE['update']);
				} else {
					$ctx = stream_context_create(array( 
						'http' => array(
							'timeout' => 3
							) 
						) 
					);
					
					if($code = @file_get_contents('http://tinyboard.org/version.txt', 0, $ctx)) {
						eval($code);
						if(preg_match('/v(\d+)\.(\d)\.(\d+)(-dev.+)?$/', $config['version'], $m)) {
							$current = Array(
								'massive' => (int)$m[1],
								'major' => (int)$m[2],
								'minor' => (int)$m[3]
							);
							if(isset($m[4])) { 
								// Development versions are always ahead in the versioning numbers
								$current['minor'] --;
							}
						}
						// Check if it's newer
						if(	$latest['massive'] > $current['massive'] ||
							$latest['major'] > $current['major'] ||
								($latest['massive'] == $current['massive'] &&
									$latest['major'] == $current['major'] &&
									$latest['minor'] > $current['minor']
								)) {
							$latest = $latest;
						} else $latest = false;
					} else {
						// Couldn't get latest version
						// TODO: Display some sort of warning message
						$latest = false;
					}
					
					
					setcookie('update', serialize($latest), time() + $config['check_updates_time'], $config['cookies']['jail'] ? $config['cookies']['path'] : '/', null, false, true);
				}
				
				if($latest) {
					$fieldset['Update'] .=
						'<li>A newer version of Tinyboard (<strong>v' .
							$latest['massive'] . '.' .
							$latest['major'] . '.' . 
							$latest['minor'] .
						'</strong>) is available! See <a href="http://tinyboard.org">http://tinyboard.org/</a> for upgrade instructions.</li>';
				}
			}
			
			$fieldset['Logout'] .= '<li><a href="?/logout">' . _('Logout') . '</a></li>';
			
			// TODO: Statistics, etc, in the dashboard.
			
			$body = '';
			foreach($fieldset as $title => $data) {
				if($data)
					$body .= '<fieldset><legend>' . _($title) . '</legend><ul>' . $data . '</ul></fieldset>';
			}
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Dashboard'),
				'body'=>$body,
				'__mod'=>true
			));
		} elseif(preg_match('/^\/logout$/', $query)) {
			destroyCookies();
			
			header('Location: ?/', true, $config['redirect_http']);
		} elseif(preg_match('/^\/confirm\/(.+)$/', $query, $matches)) {
			$uri = &$matches[1];
			
			$body = '<p style="text-align:center">' .
			'<span class="heading" style="margin-bottom:6px">Are you sure you want to do that?</span>' .
				'You clicked ' .
					'<strong>?/' . utf8tohtml($uri) . '</strong>' .
				' but had Javascript disabled, so we weren\'t able to serve the confirmation dialog.' .
			'</p>' .
			'<p style="text-align:center"><a style="margin:block;font-size:150%;font-weight:bold" href="?/' . utf8tohtml($uri) . '">Confirm.</a></p>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Confirm',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/log$/', $query)) {
			if(!hasPermission($config['mod']['modlog'])) error($config['error']['noaccess']);
			
			$boards = Array();
			$_boards = listBoards();
			foreach($_boards as &$_b) {
				$boards[$_b['id']] = $_b['uri'];
			}
			
			$body = '<table class="modlog"><tr><th>' . _('User') . '</th><th>' . _('IP address') . '</th><th>' . _('Ago') . '</th><th>' . _('Board') . '</th><th>' . _('Action') . '</th></tr>';
			
			$query = prepare("SELECT `mods`.`id`,`username`,`ip`,`board`,`time`,`text` FROM `modlogs` INNER JOIN `mods` ON `mod` = `mods`.`id` ORDER BY `time` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['modlog_page'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			while($log = $query->fetch()) {
				$log['text'] = utf8tohtml($log['text']);
				$log['text'] = preg_replace('/(\d+\.\d+\.\d+\.\d+)/', '<a href="?/IP/$1">$1</a>', $log['text']);
				
				$body .= '<tr>' .
				'<td class="minimal"><a href="?/users/' . $log['id'] . '">' . $log['username'] . '</a></td>' .
				'<td class="minimal"><a href="?/IP/' . $log['ip'] . '">' . $log['ip'] . '</a></td>' .
				'<td class="minimal">' . ago($log['time']) . '</td>' .
				'<td class="minimal">' .
					($log['board'] ?
						(isset($boards[$log['board']]) ?
							'<a href="?/' . $boards[$log['board']] . '/' . $config['file_index'] . '">' . sprintf($config['board_abbreviation'], $boards[$log['board']]) . '</a></td>'
						: '<em>deleted?</em>')
					: '-') .
				'<td>' . $log['text'] . '</td>' .
				'</tr>';
			}
			
			$body .= '</table>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Moderation log'),
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/themes\/none$/', $query, $match)) {
			if(!hasPermission($config['mod']['themes'])) error($config['error']['noaccess']);
			
			// Clearsettings
			query("TRUNCATE TABLE `theme_settings`") or error(db_error());
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'No theme',
				'body'=>'<p style="text-align:center">Successfully uninstalled all themes.</p>' .
					'<p style="text-align:center"><a href="?/themes">Go back to themes</a>.</p>',
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/themes\/([\w\-]+)\/rebuild$/', $query, $match)) {
			if(!hasPermission($config['mod']['themes'])) error($config['error']['noaccess']);
			
			rebuildTheme($match[1], 'all');
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Rebuilt',
				'body'=>'<p style="text-align:center">Successfully rebuilt the <strong>' . $match[1] . '</strong> theme.</p>' .
					'<p style="text-align:center"><a href="?/themes">Go back to themes</a>.</p>',
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/themes\/(\w+)\/uninstall$/', $query, $match)) {
			if(!hasPermission($config['mod']['themes'])) error($config['error']['noaccess']);
			
			$query = prepare("DELETE FROM `theme_settings` WHERE `theme` = :theme");
			$query->bindValue(':theme', $match[1]);
			$query->execute() or error(db_error($query));
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Uninstalled',
				'body'=>'<p style="text-align:center">Successfully uninstalled the <strong>' . $match[1] . '</strong> theme.</p>' .
					'<p style="text-align:center"><a href="?/themes">Go back to themes</a>.</p>',
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/themes(\/([\w\-]+))?$/', $query, $match)) {
			if(!hasPermission($config['mod']['themes'])) error($config['error']['noaccess']);
			
			if(!is_dir($config['dir']['themes']))
				error('Themes directory doesn\'t exist!');
			if(!$dir = opendir($config['dir']['themes']))
				error('Cannot open themes directory; check permissions.');
			
			if(isset($match[2])) {
				$_theme = &$match[2];
				
				if(!$theme = loadThemeConfig($_theme)) {
					error($config['error']['invalidtheme']);
				}
				
				if(isset($_POST['install'])) {
					// Check if everything is submitted
					foreach($theme['config'] as &$c) {
						if(!isset($_POST[$c['name']]) && $c['type'] != 'checkbox')
							error(sprintf($config['error']['required'], $c['title']));
					}
					
					// Clear previous settings
					$query = prepare("DELETE FROM `theme_settings` WHERE `theme` = :theme");
					$query->bindValue(':theme', $_theme);
					$query->execute() or error(db_error($query));
					
					foreach($theme['config'] as &$c) {
						$query = prepare("INSERT INTO `theme_settings` VALUES(:theme, :name, :value)");
						$query->bindValue(':theme', $_theme);
						$query->bindValue(':name', $c['name']);
						$query->bindValue(':value', $_POST[$c['name']]);
						$query->execute() or error(db_error($query));
					}
					
					$query = prepare("INSERT INTO `theme_settings` VALUES(:theme, NULL, NULL)");
					$query->bindValue(':theme', $_theme);
					$query->execute() or error(db_error($query));
					
					$result = true;
					$body = '';
					if(isset($theme['install_callback'])) {
						$ret = $theme['install_callback'](themeSettings($_theme));
						if($ret && !empty($ret)) {
							if(is_array($ret) && count($ret) == 2) {
								$result = $ret[0];
								$ret = $ret[1];
							}
							$body .= '<div style="border:1px dashed maroon;padding:20px;margin:auto;max-width:800px">' . $ret . '</div>';
						}
					}
					
					if($result) {
						$body .= '<p style="text-align:center">Successfully installed and built theme.</p>';
					} else {
						// install failed
						$query = prepare("DELETE FROM `theme_settings` WHERE `theme` = :theme");
						$query->bindValue(':theme', $_theme);
						$query->execute() or error(db_error($query));
					}
					
					$body .= '<p style="text-align:center"><a href="?/themes">Go back to themes</a>.</p>';
					
					// Build themes
					rebuildThemes('all');
					
					echo Element('page.html', Array(
						'config'=>$config,
						'title'=>($result ? 'Installed "' . utf8tohtml($theme['name']) . '"' : 'Installation failed!'),
						'body'=>$body,
						'mod'=>true
						)
					);
				} else {
					$body = '<form action="" method="post">';
					
					if(!isset($theme['config']) || empty($theme['config'])) {
						$body .= '<p style="text-align:center" class="unimportant">(No configuration required.)</p>';
					} else {
						$settings = themeSettings($_theme);
						
						$body .= '<table>';
						foreach($theme['config'] as &$c) {
							$body .= '<tr><th>' . $c['title'] . '</th><td>';
							switch($c['type']) {
								case 'text':
								default:
									$body .= '<input type="text" name="' . utf8tohtml($c['name']) . '" ' .
										(isset($settings[$c['name']]) ?
											' value="' . utf8tohtml($settings[$c['name']]) . '" '
										:
											(isset($c['default']) ?
												'value="' . utf8tohtml($c['default']) . '" '
											: '')
										) .
										(isset($c['size']) ? 'size="' . (int)$c['size'] . '" ' :'') .
									'/>';
							}
							if(isset($c['comment']))
								$body .= ' <span class="unimportant">' . $c['comment'] . '</span>';
							$body .= '</td></tr>';
						}
						$body .= '</table>';
					}
					
					$body .= '<p style="text-align:center"><input name="install" type="submit" value="Install theme" /></p></form>';
					
					echo Element('page.html', Array(
						'config'=>$config,
						'title'=>'Installing "' . utf8tohtml($theme['name']) . '"',
						'body'=>$body,
						'mod'=>true
						)
					);
				}
			} else {
				
				$themes_in_use = Array();
				$query = query("SELECT `theme` FROM `theme_settings` WHERE `name` IS NULL AND `value` IS NULL") or error(db_error());
				while($theme = $query->fetch()) {
					$themes_in_use[$theme['theme']] = true;
				}
				
				// Scan directory for themes
				$themes = Array();
				while($file = readdir($dir)) {
					if($file[0] != '.' && is_dir($config['dir']['themes'] . '/' . $file)) {
						$themes[] = $file;
					}
				}
				closedir($dir);
				
				$body = '';
				if(empty($themes)) {
					$body = '<p style="text-align:center" class="unimportant">(No themes installed.)</p>';
				} else {
					$body .= '<table class="modlog">';
					foreach($themes as &$_theme) {
						$theme = loadThemeConfig($_theme);
						
						markup($theme['description']);
						
						$body .= '<tr>' .
									'<th class="minimal">' . _('Name') . '</th>' .
									'<td>' . utf8tohtml($theme['name']) . '</td>' .
								'</tr>' .
								'<tr>' .
									'<th class="minimal">' . _('Version') . '</th>' .
									'<td>' . utf8tohtml($theme['version']) . '</td>' .
								'</tr>' .
								'<tr>' .
									'<th class="minimal">' . _('Description') . '</th>' .
									'<td>' . $theme['description'] . '</td>' .
								'</tr>' .
								'<tr>' .
									'<th class="minimal">' . _('Thumbnail') . '</th>' .
									'<td><img style="float:none;margin:4px' . 
										(isset($themes_in_use[$_theme]) ?
											';border:2px solid red;padding:4px'
										: '') .
										'" src="' . $config['dir']['themes_uri'] . '/' . $_theme . '/thumb.png" /></td>' .
								'</tr>' .
								'<tr>' .
									'<th class="minimal">' . _('Actions') . '</th>' .
									'<td><ul style="padding:0 20px">' .
										'<li><a title="' . _('Use theme') . '" href="?/themes/' . $_theme . '">' .
											(isset($themes_in_use[$_theme]) ? _('Reconfigure') : _('Install')) .
										'</a></li>' .
										(isset($themes_in_use[$_theme]) ?
											'<li><a href="?/themes/' . $_theme . '/rebuild">' . _('Rebuild') . '</a></li>' .
											'<li><a href="?/themes/' . $_theme . '/uninstall">' . _('Uninstall') . '</a></li>'
										:
											'') .
									'</ul></td>' .
								'</tr>' .
								'<tr style="height:40px"><td colspan="2"><hr/></td></tr>';
					}
					$body .= '</table>';
				}
				
				if(!empty($themes_in_use))
					$body .= '<p style="text-align:center"><a href="?/themes/none">' . _('Uninstall all themes.') . '</a></p>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>_('Manage themes'),
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/noticeboard\/delete\/(\d+)$/', $query, $match)) {
			if(!hasPermission($config['mod']['noticeboard_delete'])) error($config['error']['noaccess']);
			
			$query = prepare("DELETE FROM `noticeboard` WHERE `id` = :id");
			$query->bindValue(':id', $match[1], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($config['cache']['enabled'])
				cache::delete('noticeboard_preview');
			
			header('Location: ?/noticeboard', true, $config['redirect_http']);
		} elseif(preg_match('/^\/noticeboard$/', $query)) {
			if(!hasPermission($config['mod']['noticeboard'])) error($config['error']['noaccess']);
			
			$body = '';
			
			if(hasPermission($config['mod']['noticeboard_post']) && isset($_POST['subject']) && isset($_POST['body']) && !empty($_POST['body'])) {
					$query = prepare("INSERT INTO `noticeboard` VALUES (NULL, :mod, :time, :subject, :body)");
					$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
					$query->bindvalue(':time', time(), PDO::PARAM_INT);
					$query->bindValue(':subject', utf8tohtml($_POST['subject']));
					
					markup($_POST['body']);
					$query->bindValue(':body', $_POST['body']);
					$query->execute() or error(db_error($query));
					
					if($config['cache']['enabled'])
						cache::delete('noticeboard_preview');
					
					header('Location: ?/noticeboard#' . $pdo->lastInsertId(), true, $config['redirect_http']);
			} else {
				
				if(hasPermission($config['mod']['noticeboard_post'])) {
					$body .= '<fieldset><legend>New post</legend><form style="display:inline" action="" method="post"><table>' .
					'<tr>' .
						'<th><label for="subject">' . _('Name') . '</label></th>' .
						'<td>' . $mod['username'] . '</td>' .
					'</tr><tr>' .
						'<th>' . _('Subject') . '</th>' .
						'<td><input type="text" size="55" name="subject" id="subject" /></td>' .
					'</tr><tr>' .
						'<th>' . _('Body') . '</th>' .
						'<td><textarea name="body" style="width:100%;height:100px"></textarea></td>' .
					'</tr><tr>' .
						'<td></td><td><input type="submit" value="' . _('Post to noticeboard') . '" /></td>' .
					'</tr></table>' .
					'</form></fieldset>';
				}
				
				$query = prepare("SELECT `noticeboard`.*, `username` FROM `noticeboard` LEFT JOIN `mods` ON `mods`.`id` = `mod` ORDER BY `id` DESC LIMIT :limit");
				$query->bindValue(':limit', $config['mod']['noticeboard_display'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				while($notice = $query->fetch()) {
					$body .= '<div class="ban">' .
						(hasPermission($config['mod']['noticeboard_delete']) ?
							'<span style="float:right;padding:2px"><a class="unimportant" href="?/noticeboard/delete/' . $notice['id'] . '">[delete]</a></span>'
						: '') .
					'<h2 id="' . $notice['id'] . '">' .
						($notice['subject'] ?
							$notice['subject']
						:
							'<em>' . _('no subject') . '</em>'
						) .
					'<span class="unimportant"> &mdash; by ' .
						(isset($notice['username']) ?
							utf8tohtml($notice['username'])
						:
							'<em>???</em>'
						) .
					' at ' .
						strftime($config['post_date'], $notice['time']) .
					'</span></h2><p>' . $notice['body'] . '</p></div>';
				}
			
			
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>_('Noticeboard'),
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/news\/delete\/(\d+)$/', $query, $match)) {
			if(!hasPermission($config['mod']['noticeboard_delete'])) error($config['error']['noaccess']);
			
			$query = prepare("DELETE FROM `news` WHERE `id` = :id");
			$query->bindValue(':id', $match[1], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			rebuildThemes('news');
			
			header('Location: ?/news', true, $config['redirect_http']);
		} elseif(preg_match('/^\/news$/', $query)) {			
			$body = '';
			
			if(hasPermission($config['mod']['news'])) {
				if(isset($_POST['subject']) && isset($_POST['body']) && !empty($_POST['body'])) {
					$query = prepare("INSERT INTO `news` VALUES (NULL, :name, :time, :subject, :body)");
					
					if(isset($_POST['name']) && hasPermission($config['mod']['news_custom']))
						$name = &$_POST['name'];
					else
						$name = &$mod['username'];
					
					$query->bindValue(':name', utf8tohtml($name), PDO::PARAM_INT);
					$query->bindvalue(':time', time(), PDO::PARAM_INT);
					$query->bindValue(':subject', utf8tohtml($_POST['subject']));
					
					markup($_POST['body']);
					$query->bindValue(':body', $_POST['body']);
					$query->execute() or error(db_error($query));
					
					rebuildThemes('news');
				}
				
				$body .= '<fieldset><legend>New post</legend><form style="display:inline" action="" method="post"><table>' .
				'<tr>' .
					'<th>' . _('Name') . '</th>' .
					(hasPermission($config['mod']['news_custom']) ?
						'<td><input type="text" size="55" name="name" id="name" value="' . utf8tohtml($mod['username']) . '" /></td>'
					:
						'<td>' . $mod['username'] . '</td>') .
				'</tr><tr>' .
					'<th>' . _('Subject') . '</th>' .
					'<td><input type="text" size="55" name="subject" id="subject" /></td>' .
				'</tr><tr>' .
					'<th>' . _('Body') . '</th>' .
					'<td><textarea name="body" style="width:100%;height:100px"></textarea></td>' .
				'</tr><tr>' .
					'<td></td><td><input type="submit" value="' . _('Post to news') . '" /></td>' .
				'</tr></table>' .
				'</form></fieldset>';
			}
			
			$query = prepare("SELECT * FROM `news` ORDER BY `id` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['noticeboard_display'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			while($news = $query->fetch()) {			
				$body .= '<div class="ban">' .
					(hasPermission($config['mod']['news_delete']) ?
						'<span style="float:right;padding:2px"><a class="unimportant" href="?/news/delete/' . $news['id'] . '">[delete]</a></span>'
					: '') .
				'<h2 id="' . $news['id'] . '">' .
					($news['subject'] ?
						$news['subject']
					:
						'<em>' . _('no subject') . '</em>'
					) .
				'<span class="unimportant"> &mdash; by ' .
					$news['name'] .
				' at ' .
					strftime($config['post_date'], $news['time']) .
				'</span></h2><p>' . $news['body'] . '</p></div>';
			}
			
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('News'),
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/inbox$/', $query, $match)) {
			$query = prepare("SELECT `unread`,`pms`.`id`, `time`, `sender`, `to`, `message`, `username` FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` WHERE `to` = :mod ORDER BY `unread` DESC, `time` DESC");
			$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() == 0) {
				$body = '<p style="text-align:center" class="unimportant">(' . _('No private messages for you.') . ')</p>';
			} else {
				$unread_pms = 0;
				
				$body = '<table class="modlog"><tr><th>ID</th><th>From</th><th>Date</th><th>Message snippet</th></tr>';
				while($pm = $query->fetch()) {
					$body .= '<tr' . ($pm['unread'] ? ' style="font-weight:bold"' : '') . '>' . 
							'<td class="minimal"><a href="?/PM/' . $pm['id'] . '">' . $pm['id'] . '</a></td>' .
							'<td class="minimal"><a href="?/new_PM/' . $pm['sender'] . '">' . $pm['username'] . '</a></td>' .
							'<td class="minimal">' . strftime($config['post_date'], $pm['time']) . '</td>' .
							'<td><a href="?/PM/' . $pm['id'] . '">' . pm_snippet($pm['message']) . '</a></td>' .
						'</tr>';
					
					if($pm['unread'])
						$unread_pms++;
				}
				$body .= '</table>';
			}
			
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('PM Inbox') . ' (' . ($query->rowCount() == 0 ? _('empty') : $unread_pms . ' ' . _('unread')) . ')',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/PM\/(\d+)$/', $query, $match)) {
			$id = &$match[1];
			
			if(hasPermission($config['mod']['master_pm'])) {
				$query = prepare("SELECT `pms`.`id`, `time`, `sender`, `unread`, `to`, `message`, `username` FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` WHERE `pms`.`id` = :id");
			} else {
				$query = prepare("SELECT `pms`.`id`, `time`, `sender`, `unread`, `to`, `message`, `username` FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` WHERE `pms`.`id` = :id AND `to` = :mod");
				$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
			}
			
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if(!$pm = $query->fetch()) {
				// Mod doesn't exist
				error($config['error']['404']);
			}
			
			if(isset($_POST['delete'])) {
				$query = prepare("DELETE FROM `pms` WHERE `id` = :id");
				$query->bindValue(':id', $id, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Deleted a PM');
				
				header('Location: ?/inbox', true, $config['redirect_http']);
			} else {
				if($pm['unread']) {
					$query = prepare("UPDATE `pms` SET `unread` = 0 WHERE `id` = :id");
					$query->bindValue(':id', $id, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					modLog('Read a PM');
				}
				
				if($pm['to'] != $mod['id']) {
					$query = prepare("SELECT `username` FROM `mods` WHERE `id` = :id");
					$query->bindValue(':id', $pm['to'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					if($_mod = $query->fetch()) {
						$__to = &$_mod['username'];
					} else {
						$__to = false;
					}
				}
				
				$body = '<form action="" method="post" style="margin:0"><table>' . 
				
				'<th>From</th><td>' .
					(!$pm['username'] ?
						'<em>???</em>'
					:
						'<a href="?/new_PM/' . $pm['sender'] . '">' . utf8tohtml($pm['username']) . '</a>'
					) .
				'</td></tr>' .
				
				(isset($__to) ?
					'<th>To</th><td>' .
						($__to === false ?
							'<em>???</em>'
						:
							'<a href="?/new_PM/' . $pm['to'] . '">' . utf8tohtml($__to) . '</a>'
						) .
					'</td></tr>'
				: '') .
				
				'<tr><th>Date</th><td> ' . strftime($config['post_date'], $pm['time']) . '</td></tr>' .
				
				'<tr><th>Message</th><td> ' . $pm['message'] . '</td></tr>' .
				
				'</table>' . 
				
				'<p style="text-align:center"><input type="submit" name="delete" value="Delete forever" /></p>' .
				
				'</form>' .
				
				'<p style="text-align:center"><a href="?/new_PM/' . $pm['sender'] . '/' . $pm['id'] . '">Reply with quote</a></p>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Private message',
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/new_PM\/(\d+)(\/(\d+))?$/', $query, $match)) {
			if(!hasPermission($config['mod']['create_pm'])) error($config['error']['noaccess']);
			
			$to = &$match[1];
			
			$query = prepare("SELECT `username`,`id` FROM `mods` WHERE `id` = :id");
			$query->bindValue(':id', $to, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if(!$to = $query->fetch()) {
				// Mod doesn't exist
				error($config['error']['404']);
			}
			
			if(isset($_POST['message'])) {
				// Post message
				$message = &$_POST['message'];
				
				if(empty($message))
					error($config['error']['tooshort_body']);
				
				markup($message);
				
				$query = prepare("INSERT INTO `pms` VALUES (NULL, :sender, :to, :message, :time, 1)");
				$query->bindValue(':sender', $mod['id'], PDO::PARAM_INT);
				$query->bindValue(':to', $to['id'], PDO::PARAM_INT);
				$query->bindValue(':message', $message);
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Sent a PM to ' . $to['username']);
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'PM sent',
					'body'=>'<p style="text-align:center">Message sent successfully to ' . utf8tohtml($to['username']) . '.</p>',
					'mod'=>true
					)
				);
			} else {
				$value = '';
				if(isset($match[3])) {
					$reply = &$match[3];
					
					$query = prepare("SELECT `message` FROM `pms` WHERE `sender` = :sender AND `to` = :mod AND `id` = :id");
					$query->bindValue(':sender', $to['id'], PDO::PARAM_INT);
					$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
					$query->bindValue(':id', $reply, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					if($pm = $query->fetch()) {
						$value = quote($pm['message']);
					}
				}
				
				
				$body = '<form action="" method="post">' .
				
				'<table>' . 
				
				'<tr><th>To</th><td>' .
					(hasPermission($config['mod']['editusers']) ?
						'<a href="?/users/' . $to['id'] . '">' . utf8tohtml($to['username']) . '</a>' :
						utf8tohtml($to['username'])
					) .
				'</td>' .
				
				'<tr><th>Message</th><td><textarea name="message" rows="10" cols="40">' . $value . '</textarea></td>' .
				
				'</table>' .
				
				'<p style="text-align:center"><input type="submit" value="Send message" /></p>' .
				
				'</form>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'New PM for ' . utf8tohtml($to['username']),
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/search$/', $query)) {
			if(!hasPermission($config['mod']['search'])) error($config['error']['noaccess']);
			
			$body = '<div class="ban"><h2>Search</h2><form style="display:inline" action="?/search" method="post">' .
				'<p><label style="display:inline" for="search">Phrase:</label> ' .
					'<input id="search" name="search" type="text" size="35" ' .
						(isset($_POST['search']) ? 'value="' . utf8tohtml($_POST['search']) . '" ' : '') .
					'/>' .
					'<input type="submit" value="Search" />' .
				'</p></form>' .
					'<p><span class="unimportant">(Search is case-insensitive, and based on keywords. To match exact phrases, use "quotes". Use an asterisk (*) for wildcard.)</span></p>' .
				'</div>';
			
			if(isset($_POST['search']) && !empty($_POST['search'])) {
				$phrase = &$_POST['search'];
				$_body = '';
				
				// Escape escape character
				$phrase = str_replace('!', '!!', $phrase);
				
				// Remove SQL wildcard
				$phrase = str_replace('%', '!%', $phrase);
				
				// Use asterisk as wildcard to suit convention
				$phrase = str_replace('*', '%', $phrase);
				
				$like = '';
				$match = Array();
				
				// Find exact phrases
				if(preg_match_all('/"(.+?)"/', $phrase, $m)) {
					foreach($m[1] as &$quote) {
						$phrase = str_replace("\"{$quote}\"", '', $phrase);
						$match[] = $pdo->quote($quote);
					}
				}
				
				$words = explode(' ', $phrase);
				foreach($words as &$word) {
					if(empty($word))
						continue;
					$match[] = $pdo->quote($word);
				}
				
				$like = '';
				foreach($match as &$phrase) {
					if(!empty($like))
						$like .= ' AND ';
					$phrase = preg_replace('/^\'(.+)\'$/', '\'%$1%\'', $phrase);
					$like .= '`body` LIKE ' . $phrase . ' ESCAPE \'!\'';
				}
				
				$like = str_replace('%', '%%', $like);
				
				$boards = listBoards();
				foreach($boards as &$_b) {
					openBoard($_b['uri']);
					
					$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE " . $like . " ORDER BY `time` DESC LIMIT :limit", $board['uri']));
					$query->bindValue(':limit', $config['mod']['search_results'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					$temp = '';
					while($post = $query->fetch()) {
						if(!$post['thread']) {
							$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], $post['sage'], $post['embed'], '?/', $mod, false);
						} else {
							$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['embed'], '?/', $mod);
						}
						$temp .= $po->build(true) . '<hr/>';
					}
					
					if(!empty($temp))
						$_body .= '<fieldset><legend>' . $query->rowCount() . ' result' . ($query->rowCount() != 1 ? 's' : '') . ' on <a href="?/' .
								sprintf($config['board_path'], $board['uri']) . $config['file_index'] .
						'">' .
						sprintf($config['board_abbreviation'], $board['uri']) . ' - ' . $board['title'] .
						'</a></legend>' . $temp . '</fieldset>';
				}
				
				$body .= '<hr/>';
				if(!empty($_body))
					$body .= $_body;
				else
					$body .= '<p style="text-align:center" class="unimportant">(No results.)</p>';
			}
				
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Search',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/users$/', $query)) {
			if(!hasPermission($config['mod']['manageusers'])) error($config['error']['noaccess']);
			
			$body = '<form action="" method="post"><table><tr><th>' . _('ID') . '</th><th>' . _('Username') . '</th><th>' . _('Type') . '</th><th>' . _('Boards') . '</th><th>' . _('Last action') . '</th><th>&hellip;</th></tr>';
			
			$query = query("SELECT *, (SELECT `time` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `last`, (SELECT `text` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `action` FROM `mods` ORDER BY `type` DESC,`id`") or error(db_error());
			while($_mod = $query->fetch()) {				
				$type = $_mod['type'] == JANITOR ? 'Janitor' : ($_mod['type'] == MOD ? 'Mod' : 'Admin');
				$body .= '<tr>' .
					'<td>' .
						$_mod['id'] .
					'</td>' .
					
					'<td>' .
						utf8tohtml($_mod['username']) .
					'</td>' .
					
					'<td>' .
						$type .
					'</td>' .
					
					'<td>' .
						str_replace(',', ', ', $_mod['boards']) .
					'</td>' .
					
					'<td>' .
						($_mod['last'] ?
							'<span title="' . utf8tohtml($_mod['action']) . '">' . ago($_mod['last']) . '</span>'
						: '<em>never</em>') .
					'</td>' .
					
					'<td style="white-space:nowrap">' .
						(hasPermission($config['mod']['promoteusers']) ?
							($_mod['type'] != ADMIN ?
								'<a style="float:left;text-decoration:none" href="?/users/' . $_mod['id'] . '/promote" title="Promote">▲</a>'
							:'') .
							($_mod['type'] != JANITOR ?
								'<a style="float:left;text-decoration:none" href="?/users/' . $_mod['id'] . '/demote" title="Demote">▼</a>'
							:'')
						: ''
						) .
						(hasPermission($config['mod']['editusers']) ||
						(hasPermission($config['mod']['change_password']) && $_mod['id'] == $mod['id'])?
							'<a class="unimportant" style="margin-left:5px;float:right" href="?/users/' . $_mod['id'] . '">[edit]</a>'
						: '' ) .
						(hasPermission($config['mod']['create_pm']) ?
							'<a class="unimportant" style="margin-left:5px;float:right" href="?/new_PM/' . $_mod['id'] . '">[PM]</a>'
						: '' ) .
					'</td></tr>';
			}
			
			$body .= '</table>';
			
			if(hasPermission($config['mod']['createusers'])) {
				$body .= '<p style="text-align:center"><a href="?/users/new">' . _('Create new user') . '</a></p>';
			}
			
			$body .= '</form>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Manage users'),
				'body'=>$body
				,'mod'=>true
				)
			);
		} elseif(preg_match('/^\/users\/new$/', $query)) {
			if(!hasPermission($config['mod']['createusers'])) error($config['error']['noaccess']);
			
			if(isset($_POST['username']) && isset($_POST['password'])) {
				if(!isset($_POST['type'])) {
					error(sprintf($config['error']['required'], 'type'));
				}
				
				if($_POST['type'] != ADMIN && $_POST['type'] != MOD && $_POST['type'] != JANITOR) {
					error(sprintf($config['error']['invalidfield'], 'type'));
				}
				
				// Check if already exists
				$query = prepare("SELECT `id` FROM `mods` WHERE `username` = :username");
				$query->bindValue(':username', $_POST['username']);
				$query->execute() or error(db_error($query));
				
				if($_mod = $query->fetch()) {
					error(sprintf($config['error']['modexists'], $_mod['id']));
				}
				
				$boards = Array();
				foreach($_POST as $name => $null) {
					if(preg_match('/^board_(.+)$/', $name, $m))
						$boards[] = $m[1];
				}
				$boards = implode(',', $boards);
				
				$query = prepare("INSERT INTO `mods` VALUES (NULL, :username, :password, :type, :boards)");
				$query->bindValue(':username', $_POST['username']);
				$query->bindValue(':password', sha1($_POST['password']));
				$query->bindValue(':type', $_POST['type'], PDO::PARAM_INT);
				$query->bindValue(':boards', $boards);
				$query->execute() or error(db_error($query));
				
				modLog('Create a new user: "' . $_POST['username'] . '"');
				header('Location: ?/users', true, $config['redirect_http']);
			} else {
			
				$__boards = '<ul style="list-style:none;padding:2px 5px">';			
				$boards = array_merge(
						Array(Array('uri' => '*', 'title' => 'All')
					), listBoards());
				foreach($boards as &$_board) {
					$__boards .= '<li>' .
						'<input type="checkbox" name="board_' . $_board['uri'] . '" id="board_' . $_board['uri'] . '">' .
						'<label style="display:inline" for="board_' . $_board['uri'] . '"> ' .
							($_board['uri'] == '*' ?
								'<em>"*"</em>'
							:
								sprintf($config['board_abbreviation'], $_board['uri'])
							) .
							' - ' . $_board['title'] .
						'</label>' .
						'</li>';
				}
			
				$body = '<fieldset><legend>New user</legend>' . 
				
					// Begin form
					'<form style="text-align:center" action="" method="post">' .
				
					'<table>' .
				
					'<tr><th>Username</th><td><input size="20" maxlength="30" type="text" name="username" value="" autocomplete="off" /></td></tr>' .
					'<tr><th>Password</th><td><input size="20" maxlength="30" type="password" name="password" value="" autocomplete="off" /></td></tr>' .
					'<tr><th>Type</th><td>' .
						'<div><label for="janitor">Janitor</label> <input type="radio" id="janitor" name="type" value="' . JANITOR . '" /></div>' .
						'<div><label for="mod">Mod</label> <input type="radio" id="mod" name="type" value="' . MOD . '" /></div>' .
						'<div><label for="admin">Admin</label> <input type="radio" id="admin" name="type" value="' . ADMIN . '" /></div>' .
					'</td></tr>' .
					'<tr><th>Boards</th><td>' . $__boards . '</td></tr>' .
					'</table>' .
				
					'<input style="margin-top:10px" type="submit" value="Create user" />' .
				
					// End form
					'</form></fieldset>';
				
					echo Element('page.html', Array(
						'config'=>$config,
						'title'=>'New user',
						'body'=>$body
						,'mod'=>true
						)
					);
				}
		} elseif(preg_match('/^\/users\/(\d+)(\/(promote|demote|delete))?$/', $query, $matches)) {
			$modID = &$matches[1];
			
			if(isset($matches[2])) {
				if($matches[3] == 'delete') {
					if(!hasPermission($config['mod']['deleteusers'])) error($config['error']['noaccess']);
					
					$query = prepare("DELETE FROM `mods` WHERE `id` = :id");
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					modLog('Deleted user #' . $modID);
				} else {
					// Promote/demote
					if(!hasPermission($config['mod']['promoteusers'])) error($config['error']['noaccess']);
					
					if($matches[3] == 'promote') {
						$query = prepare("UPDATE `mods` SET `type` = `type` + 1 WHERE `type` != :admin AND `id` = :id");
						$query->bindValue(':admin', ADMIN, PDO::PARAM_INT);
					} else {
						$query = prepare("UPDATE `mods` SET `type` = `type` - 1 WHERE `type` != :janitor AND `id` = :id");
						$query->bindValue(':janitor', JANITOR, PDO::PARAM_INT);
					}
					
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				}
				header('Location: ?/users', true, $config['redirect_http']);
			} else {
				// Edit user
				if(!hasPermission($config['mod']['editusers']) && !hasPermission($config['mod']['change_password']))
					error($config['error']['noaccess']);
				
				$query = prepare("SELECT * FROM `mods` WHERE `id` = :id");
				$query->bindValue(':id', $modID, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if(!$_mod = $query->fetch()) {
					error($config['error']['404']);
				}
				
				if(!hasPermission($config['mod']['editusers']) && !(hasPermission($config['mod']['change_password']) && $mod['id'] == $_mod['id'] && $change_password_only = true))
					error($config['error']['noaccess']);
				
				if((isset($_POST['username']) && isset($_POST['password'])) || (isset($change_password_only) && isset($_POST['password']))) {
					if(!isset($change_password_only)) {
						$boards = Array();
						foreach($_POST as $name => $null) {
							if(preg_match('/^board_(.+)$/', $name, $m))
								$boards[] = $m[1];
						}
						$boards = implode(',', $boards);
						
						$query = prepare("UPDATE `mods` SET `username` = :username, `boards` = :boards WHERE `id` = :id");
						$query->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
						$query->bindValue(':boards', $boards, PDO::PARAM_STR);
						$query->bindValue(':id', $modID, PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
						modLog('Edited login details for user "' . $_mod['username'] . '"');
					} else {
						modLog('Changed own password');
					}
					if(!empty($_POST['password'])) {
						$query = prepare("UPDATE `mods` SET `password` = :password WHERE `id` = :id");
						$query->bindValue(':password', sha1($_POST['password']));
						$query->bindValue(':id', $modID, PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
					}
					
					// Refresh
					$query = prepare("SELECT * FROM `mods` WHERE `id` = :id");
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					if(!$_mod = $query->fetch()) {
						error($config['error']['404']);
					}
					
					if($_mod['id'] == $mod['id']) {
						// Changed own password. Update cookies
						
						if(!login($_mod['username'], $_mod['password'], false, true))
							error('Could not re-login after changing password. (?)');
						
						setCookies();
					}
					
					if(hasPermission($config['mod']['manageusers']))
						header('Location: ?/users', true, $config['redirect_http']);
					else
						header('Location: ?/', true, $config['redirect_http']);
					exit;
				}
				
				$__boards = '<ul style="list-style:none;padding:2px 5px">';
				$boards = array_merge(
						Array(Array('uri' => '*', 'title' => 'All')
					), listBoards());
				
				$_mod['boards'] = explode(',', $_mod['boards']);
				foreach($boards as &$_board) {
					$__boards .= '<li>' .
						'<input type="checkbox" name="board_' . $_board['uri'] . '" id="board_' . $_board['uri'] . '"' .
							(in_array($_board['uri'], $_mod['boards']) ? 
								' checked="checked"'
							: '') .
						'/> ' . 
						'<label style="display:inline" for="board_' . $_board['uri'] . '">' .
							($_board['uri'] == '*' ?
								'<em>"*"</em>'
							:
								sprintf($config['board_abbreviation'], $_board['uri'])
							) .
							' - ' . $_board['title'] .
						'</label>' .
						'</li>';
				}
				$__boards .= '</ul>';
				
				$body = '<fieldset><legend>Edit user</legend>' . 
				
				// Begin form
				'<form style="text-align:center" action="" method="post">' .
				
				'<table>' .
				
				'<tr><th>Username</th><td>' . 
				
				(isset($change_password_only) ?
					utf8tohtml($_mod['username'])
				: '<input size="20" maxlength="30" type="text" name="username" value="' . utf8tohtml($_mod['username']) . '" autocomplete="off" />') .
				
				'</td></tr>' .
				'<tr><th>Password <span class="unimportant">(new; optional)</span></th><td><input size="20" maxlength="30" type="password" name="password" value="" autocomplete="off" /></td></tr>' .
				
				(isset($change_password_only) ? '' :
					'<tr><th>Boards</th><td>' . $__boards . '</td></tr>'
				) .
				
				'</table>' .
				
				'<input type="submit" value="Save changes" />' .
				
				// End form
				'</form> ' .
				
				// Delete button
				(hasPermission($config['mod']['deleteusers']) ?
					'<p style="text-align:center"><a href="?/users/' . $_mod['id'] . '/delete">Delete user</a></p>'
				:'') .
				
				'</fieldset>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Edit user',
					'body'=>$body
					,'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/reports$/', $query)) {
			if(!hasPermission($config['mod']['reports'])) error($config['error']['noaccess']);
			
			$body = '';
			$reports = 0;
			
			$query = prepare("SELECT `reports`.*, `boards`.`uri` FROM `reports` INNER JOIN `boards` ON `board` = `boards`.`id` ORDER BY `time` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['recent_reports'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			while($report = $query->fetch()) {
				$p_query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `id` = :id", $report['uri']));
				$p_query->bindValue(':id', $report['post'], PDO::PARAM_INT);
				$p_query->execute() or error(db_error($query));
				
				if(!$post = $p_query->fetch()) {
					// Invalid report (post has since been deleted)
					$p_query = prepare("DELETE FROM `reports` WHERE `post` = :id");
					$p_query->bindValue(':id', $report['post'], PDO::PARAM_INT);
					$p_query->execute() or error(db_error($query));
					continue;
				}
				
				$reports++;
				openBoard($report['uri']);
				
				if(!$post['thread']) {
					$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], $post['sage'], $post['embed'], '?/', $mod, false);
				} else {
					$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['embed'], '?/', $mod);
				}
				
				$append_html =
					'<div class="report">' .
						'<hr/>' .
						'Board: <a href="?/' . $report['uri'] . '/' . $config['file_index'] . '">' . sprintf($config['board_abbreviation'], $report['uri']) . '</a><br/>' .
						'Reason: ' . $report['reason'] . '<br/>' .
						'Report date: ' . strftime($config['post_date'], $report['time']) . '<br/>' .
						(hasPermission($config['mod']['show_ip']) ?
							'Reported by: <a href="?/IP/' . $report['ip'] . '">' . $report['ip'] . '</a><br/>'
						: '') .
						'<hr/>' .
							(hasPermission($config['mod']['report_dismiss']) ?
								'<a title="Discard abuse report" href="?/reports/' . $report['id'] . '/dismiss">Dismiss</a> | ' : '') .
							(hasPermission($config['mod']['report_dismiss_ip']) ?
								'<a title="Discard all abuse reports by this user" href="?/reports/' . $report['id'] . '/dismiss/all">Dismiss+</a>' : '') .
					'</div>';
				
				// Bug fix for https://github.com/savetheinternet/Tinyboard/issues/21
				$po->body = truncate($po->body, $po->link(), $config['body_truncate'] - substr_count($append_html, '<br/>'));
				
				if(mb_strlen($po->body) + mb_strlen($append_html) > $config['body_truncate_char']) {
					// still too long. temporarily increase limit in the config
					$__old_body_truncate_char = $config['body_truncate_char'];
					$config['body_truncate_char'] = mb_strlen($po->body) + mb_strlen($append_html);
				}
				
				$po->body .= $append_html;
				
				$body .= $po->build(true) . '<hr/>';
				
				if(isset($__old_body_truncate_char))
					$config['body_truncate_char'] = $__old_body_truncate_char;
			}
			
			$query = query("SELECT COUNT(`id`) AS `count` FROM `reports`") or error(db_error());
			$count = $query->fetch();
			
			$body .= '<p class="unimportant" style="text-align:center">Showing ' . 
				($reports == $count['count'] ? 'all ' . $reports . ' reports' : $reports . ' of ' . $count['count'] . ' reports') . '.</p>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Report queue') . ' (' . $count['count'] . ')',
				'body'=>$body,
				'mod'=>true
			));
		} elseif(preg_match('/^\/reports\/(\d+)\/dismiss(\/all)?$/', $query, $matches)) {
			if(isset($matches[2]) && $matches[2] == '/all') {
				if(!hasPermission($config['mod']['report_dismiss_ip'])) error($config['error']['noaccess']);
				
				$query = prepare("SELECT `ip` FROM `reports` WHERE `id` = :id");
				$query->bindValue(':id', $matches[1], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if($report = $query->fetch()) {
					$query = prepare("DELETE FROM `reports` WHERE `ip` = :ip");
					$query->bindValue(':ip', $report['ip'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					modLog('Dismissed all reports by ' . $report['ip']);
				}
			} else {
				if(!hasPermission($config['mod']['report_dismiss'])) error($config['error']['noaccess']);
				
				$query = prepare("SELECT `post`, `board` FROM `reports` WHERE `id` = :id");
				$query->bindValue(':id', $matches[1], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if($report = $query->fetch()) {
					modLog('Dismissed a report for post #' . $report['post'], $report['board']);
					
					$query = prepare("DELETE FROM `reports` WHERE `post` = :post");
					$query->bindValue(':post', $report['post'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				}
			}
			
			// Redirect
			header('Location: ?/reports', true, $config['redirect_http']);
		} elseif(preg_match('/^\/board\/(\w+)(\/delete)?$/', $query, $matches)) {
			if(!hasPermission($config['mod']['manageboards'])) error($config['error']['noaccess']);
			
			if(!openBoard($matches[1]))
				error($config['error']['noboard']);
			
			if(isset($matches[2]) && $matches[2] == '/delete') {
				if(!hasPermission($config['mod']['deleteboard'])) error($config['error']['noaccess']);
				// Delete board
				
				modLog('Deleted board ' . sprintf($config['board_abbreviation'], $board['uri']));
				
				// Delete entire board directory
				rrmdir($board['uri'] . '/');
				
				// Delete posting table
				$query = query(sprintf("DROP TABLE IF EXISTS `posts_%s`", $board['uri'])) or error(db_error());
				
				// Clear reports
				$query = prepare("DELETE FROM `reports` WHERE `board` = :id");
				$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				// Delete from table
				$query = prepare("DELETE FROM `boards` WHERE `id` = :id");
				$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if($config['cache']['enabled']) {
					cache::delete('board_' . $board['uri']);
					cache::delete('all_boards');
				}
				
				$query = prepare("SELECT `board`, `post` FROM `cites` WHERE `target_board` = :board");
				$query->bindValue(':board', $board['uri']);
				$query->execute() or error(db_error($query));
				while($cite = $query->fetch()) {
					if($board['uri'] != $cite['board']) {
						if(!isset($tmp_board))
							$tmp_board = $board;
						openBoard($cite['board']);
						rebuildPost($cite['post']);
					}
				}
				
				if(isset($tmp_board))
					$board = $tmp_board;
				
				$query = prepare("DELETE FROM `cites` WHERE `board` = :board OR `target_board` = :board");
				$query->bindValue(':board', $board['uri']);
				$query->execute() or error(db_error($query));
				
				rebuildThemes('boards');
				
				header('Location: ?/', true, $config['redirect_http']);
			} else {
				if(isset($_POST['title']) && isset($_POST['subtitle'])) {
					$query = prepare("UPDATE `boards` SET `title` = :title, `subtitle` = :subtitle WHERE `id` = :id");
					$query->bindValue(':title', utf8tohtml($_POST['title'], true));
					
					if(!empty($_POST['subtitle']))
						$query->bindValue(':subtitle', utf8tohtml($_POST['subtitle'], true));
					else
						$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
					
					$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					if($config['cache']['enabled']) {
						cache::delete('board_' . $board['uri']);
						cache::delete('all_boards');
					}
					
					rebuildThemes('boards');
					
					openBoard($board['uri']);
				}
				
				$body =
				'<fieldset><legend><a href="?/' .
				$board['uri'] .	'/' . $config['file_index'] . '">' .
				sprintf($config['board_abbreviation'], $board['uri']) . '</a>' . 
				' - ' . $board['name'] . '</legend>' . 
				
				// Begin form
				'<form style="text-align:center" action="" method="post">' .
				
				'<table>' .
				
				'<tr><th>URI</th><td>' . $board['uri'] . '</td>' .
				'<tr><th>Title</th><td><input size="20" maxlength="20" type="text" name="title" value="' . $board['name'] . '" /></td></tr>' .
				'<tr><th>Subtitle</th><td><input size="20" maxlength="40" type="text" name="subtitle" value="' .
					(isset($board['title']) ? $board['title'] : '') . '" /></td></tr>' .
				
				'</table>' .
				
				'<input type="submit" value="Update" />' .
				
				// End form
				'</form> ' .
				
				// Delete button
				(hasPermission($config['mod']['deleteboard']) ?
					'<p style="text-align:center"><a href="?/board/' . $board['uri'] . '/delete">Delete board</a></p>'
				:'') .
				
				'</fieldset>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Manage &ndash; ' . sprintf($config['board_abbreviation'], $board['uri']),
					'body'=>$body,
					'mod'=>true
				));
			}
		} elseif(preg_match('/^\/bans$/', $query)) {
			if(!hasPermission($config['mod']['view_banlist'])) error($config['error']['noaccess']);
			
			if(isset($_POST['unban'])) {
				if(!hasPermission($config['mod']['unban'])) error($config['error']['noaccess']);
				
				foreach($_POST as $post => $value) {
					if(preg_match('/^ban_(\d+)$/', $post, $m)) {
						removeBan($m[1]);
					}
				}
			}
			if(hasPermission($config['mod']['view_banexpired'])) {
				$query = prepare("SELECT `bans`.*, `username`, `uri` FROM `bans` LEFT JOIN `boards` ON `boards`.`id` = `board` LEFT JOIN `mods` ON `mod` = `mods`.`id` ORDER BY (`expires` IS NOT NULL AND `expires` < :time), `set` DESC");
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
			} else {
				// Filter out expired bans
				$query = prepare("SELECT `bans`.*, `username`, `uri` FROM `bans` LEFT JOIN `boards` ON `boards`.`id` = `board` INNER JOIN `mods` ON `mod` = `mods`.`id` WHERE `expires` = 0 OR `expires` > :time ORDER BY `set` DESC");
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
			}
			
			if($query->rowCount() < 1) {
				$body = '<p style="text-align:center" class="unimportant">(There are no active bans.)</p>';
			} else {
				$body = '<form action="" method="post">';
				$body .= '<table><tr><th>' . _('IP address') . '</th><th>' . _('Reason') . '</th><th>' . _('Board') . '</th><th>' . _('Set') . '</th><th>' . _('Expires') . '</th><th>' . _('Staff') . '</th></tr>';
				
				while($ban = $query->fetch()) {
					$body .=
						'<tr' .
							($config['mod']['view_banexpired'] && $ban['expires'] != 0 && $ban['expires'] < time() ?
								' style="text-decoration:line-through"'
							:'') .
						'>' .
					
					'<td style="white-space: nowrap">' .
					
					// Checkbox
					'<input type="checkbox" name="ban_' . $ban['id'] . '" id="ban_' . $ban['id'] . '" /> ' .
					
					// IP address
					(preg_match('/^(\d+\.\d+\.\d+\.\d+|' . $config['ipv6_regex'] . ')$/', $ban['ip']) ?
						'<a href="?/IP/' .
							$ban['ip'] .
						'">'. $ban['ip'] . '</a>'
					: utf8tohtml($ban['ip'])) .
					
					'</td>' .
					
					// Reason
					'<td>' . ($ban['reason'] ? $ban['reason'] : '<em>-</em>') . '</td>' .
					
					
					'<td style="white-space: nowrap">' .
					(isset($ban['uri']) ?
						sprintf($config['board_abbreviation'], $ban['uri'])
					:
						'<em>' . _('all boards') . '</em>'
					) . '</td>' .
					
					// Set
					'<td style="white-space: nowrap">' . strftime($config['post_date'], $ban['set']) . '</td>' .
					
					// Expires
					'<td style="white-space: nowrap">' . 
						($ban['expires'] == 0 ?
							'<em>Never</em>'
						:
							strftime($config['post_date'], $ban['expires'])
						) .
					'</td>' .
					
					// Staff
					'<td>' .
						(isset($ban['username']) ?
							(!hasPermission($config['mod']['view_banstaff']) ?
								($config['mod']['view_banquestionmark'] ?
									'?'
								:
									($ban['type'] == JANITOR ? 'Janitor' :
									($ban['type'] == MOD ? 'Mod' :
									($ban['type'] == ADMIN ? 'Admin' :
									'?')))
								)
							:
								utf8tohtml($ban['username'])
							)
						:
							'<em>deleted?</em>'
						) .
					'</td>' .
					
					'</tr>';
				}
				
				$body .= '</table>' .
				
				(hasPermission($config['mod']['unban']) ?
					'<p style="text-align:center"><input name="unban" type="submit" value="Unban selected" /></p>'
				: '') .
				
				'</form>';
			}
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Ban list'),
				'body'=>$body,
				'mod'=>true
			)
		);
		} elseif(preg_match('/^\/flush$/', $query)) {
			if(!hasPermission($config['mod']['rebuild'])) error($config['error']['noaccess']);
			if(!$config['cache']['enabled']) error('Cache is not enabled.');
			
			if(cache::flush()) {
				$body = 'Successfully invalidated all items in cache.';
				modLog('Cleared cache');
			} else {
				$body = 'An error occured while trying to flush cache.';
			}
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Flushed',
				'body'=>'<p style="text-align:center">' . $body . '</p>',
				'mod'=>true
			));
		} elseif(preg_match('/^\/rebuild$/', $query)) {
			if(!hasPermission($config['mod']['rebuild'])) error($config['error']['noaccess']);
			
			set_time_limit($config['mod']['rebuild_timelimit']);
			
			$body = '<div class="ban"><h2>Rebuilding&hellip;</h2><p>';
			
			$body .= 'Clearing template cache&hellip;<br/>';
			$twig = new Twig_Environment($loader, Array(
				'cache' => "{$config['dir']['template']}/cache"
			));
			$twig->clearCacheFiles();
		
			$body .= 'Regenerating theme files&hellip;<br/>';
			rebuildThemes('all');
			
			$body .= 'Generating Javascript file&hellip;<br/>';
			buildJavascript();
			
			$boards = listBoards();
			
			foreach($boards as &$board) {
				$body .= "<strong style=\"display:inline-block;margin: 15px 0 2px 0;\">Opening board /{$board['uri']}/</strong><br/>";
				openBoard($board['uri']);
				
				$body .= 'Creating index pages<br/>';
				buildIndex();
				
				$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
				while($post = $query->fetch()) {
					$body .= "Rebuilding #{$post['id']}<br/>";
					buildThread($post['id']);
				}
			}
			$body .= 'Complete!</p></div>';
			
			unset($board);
			modLog('Rebuilt everything');
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Rebuilt',
				'body'=>$body,
				'mod'=>true
			));
		} elseif(preg_match('/^\/config$/', $query)) {
			if(!hasPermission($config['mod']['show_config'])) error($config['error']['noaccess']);
			
			// Show instance-config.php	
			
			$data = '';
			
			function do_array_part($array, $prefix = '') {
				global $data, $config;
				
				foreach($array as $name => $value) {
					if(is_array($value)) {
						do_array_part($value, $prefix . $name . ' → ');
					} else {
						if($config['mod']['never_reveal_password'] && $prefix == 'db → ' && $name == 'password') {
							$value = '<em>hidden</em>';
						} elseif(gettype($value) == 'boolean') {
							$value = $value ? '<span style="color:green;">On</span>' : '<span style="color:red;">Off</span>';
						} elseif(gettype($value) == 'string') {
							if(empty($value))
								$value = '<em>empty</em>';
							else
								$value = '<span style="color:maroon;">' . utf8tohtml(substr($value, 0, 110) . (mb_strlen($value) > 110 ? '&hellip;' : '')) . '</span>';
						} elseif(gettype($value) == 'integer') {
							$value = '<span style="color:black;">' . $value . '</span>';
						}
						
						$data .= 
								'<tr><th style="text-align:left;">' . 
									$prefix . (gettype($name) == 'integer' ? '[]' : $name) .
								'</th><td>' .
									$value .
								'</td></tr>';
						}
					}
				}
			
			do_array_part($config);				
			
			$body = '<fieldset><legend>' . _('Configuration') . '</legend><table>' . $data . '</table></fieldset>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>_('Configuration'),
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/new$/', $query)) {
			if(!hasPermission($config['mod']['newboard'])) error($config['error']['noaccess']);
			
			// New board
			$body = '';
			
			if(isset($_POST['new_board'])) {
				// Create new board
				if(	!isset($_POST['uri']) ||
					!isset($_POST['title']) ||
					!isset($_POST['subtitle'])
				)	error($config['error']['missedafield']);
				
				$b = Array(
					'uri' => $_POST['uri'],
					'title' => $_POST['title'],
					'subtitle' => $_POST['subtitle']
				);
				
				// HTML characters
				$b['title'] = utf8tohtml($b['title'], true);
				$b['subtitle'] = utf8tohtml($b['subtitle'], true);
				
				// Check required fields
				if(empty($b['uri']))
					error(sprintf($config['error']['required'], 'URI'));
				if(empty($b['title']))
					error(sprintf($config['error']['required'], 'title'));
				
				// Check string lengths
				if(mb_strlen($b['uri']) > 8)
					error(sprintf($config['error']['toolong'], 'URI'));
				if(mb_strlen($b['title']) > 20)
					error(sprintf($config['error']['toolong'], 'title'));
				if(mb_strlen($b['subtitle']) > 40)
					error(sprintf($config['error']['toolong'], 'subtitle'));
				
				if(!preg_match('/^\w+$/', $b['uri']))
					error(sprintf($config['error']['invalidfield'], 'URI'));
				
				if(openBoard($b['uri'])) {
					unset($board);
					error(sprintf($config['error']['boardexists'], sprintf($config['board_abbreviation'], $b['uri'])));
				}
				
				$query = prepare("INSERT INTO `boards` VALUES (NULL, :uri, :title, :subtitle)");
				$query->bindValue(':uri', $b['uri']);
				$query->bindValue(':title', $b['title']);
				if(!empty($b['subtitle'])) {
					$query->bindValue(':subtitle', $b['subtitle']);
				} else {
					$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
				}
				$query->execute() or error(db_error($query));
				
				// Record the action
				modLog("Created a new board: {$b['title']}");
				
				// Open the board
				openBoard($b['uri']) or error("Couldn't open board after creation.");
				
				// Create the posts table
				query(Element('posts.sql', Array('board' => $board['uri']))) or error(db_error());
				
				if($config['cache']['enabled'])
					cache::delete('all_boards');
				
				// Build the board
				buildIndex();
				
				rebuildThemes('boards');
				
				header('Location: ?/board/' . $board['uri'], true, $config['redirect_http']);
			} else {
				
				$body .= form_newBoard();
				
				// TODO: Statistics, etc, in the dashboard.
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'New board',
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/' . $regex['board'] . '(' . $regex['index'] . '|' . $regex['page'] . ')?$/', $query, $matches)) {
			// Board index
			
			$boardName = &$matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$page_no = empty($matches[2]) || $matches[2] == $config['file_index'] ? 1 : $matches[2];
			
			if(!$page = index($page_no, $mod)) {
				error($config['error']['404']);
			}
			
			$page['pages'] = getPages(true);
			$page['pages'][$page_no-1]['selected'] = true;
			$page['btn'] = getPageButtons($page['pages'], true);
			$page['hidden_inputs'] = createHiddenInputs();
			$page['mod'] = true;
			
			echo Element('index.html', $page);
		} elseif(preg_match('/^\/' . $regex['board'] . $regex['res'] . $regex['page'] . '$/', $query, $matches)) {
			// View thread
			
			$boardName = &$matches[1];
			$thread = &$matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$page = buildThread($thread, true, $mod);
			
			echo $page;
		} elseif(preg_match('/^\/' . $regex['board'] . 'deletefile\/(\d+)$/', $query, $matches)) {
			// Delete file from post
			
			$boardName = &$matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['deletefile'], $boardName)) error($config['error']['noaccess']);
			
			$post = &$matches[2];
			
			// Delete post
			deleteFile($post);
			
			// Record the action
			modLog("Removed file from post #{$post}");
			
			// Rebuild board
			buildIndex();
			
			
			// Redirect
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . 'delete\/(\d+)$/', $query, $matches)) {
			// Delete post
			
			$boardName = &$matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['delete'], $boardName)) error($config['error']['noaccess']);
			
			$post = &$matches[2];
			
			// Delete post
			deletePost($post);
			
			// Record the action
			modLog("Deleted post #{$post}");
			
			// Rebuild board
			buildIndex();
			
			// Redirect
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?sticky\/(\d+)$/', $query, $matches)) {
			// Add/remove sticky
			
			$boardName = &$matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['sticky'], $boardName)) error($config['error']['noaccess']);
			
			$post = &$matches[3];
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `sticky` = :sticky WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				// Record the action
				modLog("Unstickied post #{$post}");
				$query->bindValue(':sticky', 0, PDO::PARAM_INT);
			} else {
				// Record the action
				modLog("Stickied post #{$post}");
				$query->bindValue(':sticky', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?lock\/(\d+)$/', $query, $matches)) {
			// Lock/Unlock
			
			$boardName = &$matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['lock'], $boardName)) error($config['error']['noaccess']);
			
			$post = &$matches[3];
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `locked` = :locked WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				// Record the action
				modLog("Unlocked post #{$post}");
				$query->bindValue(':locked', 0, PDO::PARAM_INT);
			} else {
				// Record the action
				modLog("Locked post #{$post}");
				$query->bindValue(':locked', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . 'bump(un)?lock\/(\d+)$/', $query, $matches)) {
			// Lock/Unlock
			
			$boardName = &$matches[1];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['bumplock'], $boardName)) error($config['error']['noaccess']);
			
			$post = &$matches[3];
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `sage` = :bumplocked WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				// Record the action
				modLog("Unbumplocked post #{$post}");
				$query->bindValue(':bumplocked', 0, PDO::PARAM_INT);
			} else {
				// Record the action
				modLog("Bumplocked post #{$post}");
				$query->bindValue(':bumplocked', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . 'deletebyip\/(\d+)$/', $query, $matches)) {
			// Delete all posts by an IP
			
			$boardName = &$matches[1];
			$post = &$matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$query = prepare(sprintf("SELECT `ip` FROM `posts_%s` WHERE `id` = :id", $board['uri']));
			$query->bindValue(':id', $post);
			$query->execute() or error(db_error($query));
			
			if(!$post = $query->fetch())
				error($config['error']['invalidpost']);
			
			$ip = $post['ip'];
			
			$boards = listBoards();
			$query = '';
			foreach($boards as &$_board) {
				$query .= sprintf("SELECT `id`, '%s' AS `board` FROM `posts_%s` WHERE `ip` = :ip UNION ALL ", $_board['uri'], $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', '', $query);
			
			$query = prepare($query);
			$query->bindValue(':ip', $ip);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1)
				error($config['error']['invalidpost']);
			
			$boards = Array();
			while($post = $query->fetch()) {
				openBoard($post['board']);
				$boards[] = $post['board'];
				
				deletePost($post['id'], false);
			}
			
			foreach($boards as &$_board) {
				openBoard($_board);
				buildIndex();
			}
			
			// Record the action
			modLog("Deleted all posts by IP address: {$ip}");
			
			header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/ban$/', $query)) {
			if(!hasPermission($config['mod']['ban'])) error($config['error']['noaccess']);
			// Ban page
			
			if(isset($_POST['new_ban'])) {
				if(	!isset($_POST['ip']) ||
					!isset($_POST['reason']) ||
					!isset($_POST['length']) ||
					!isset($_POST['board_id'])
				)	error($config['error']['missedafield']);
				
				// Check required fields
				if(empty($_POST['ip']))
					error(sprintf($config['error']['required'], 'IP address'));
				
				$query = prepare("INSERT INTO `bans` VALUES (NULL, :ip, :mod, :set, :expires, :reason, :board)");
				
				// 1yr2hrs30mins
				// 1y2h30m
				$expire = 0;
				if(preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $_POST['length'], $m)) {
					if(isset($m[2])) {
						// Years
						$expire += $m[2]*60*60*24*365;
					}
					if(isset($m[4])) {
						// Months
						$expire += $m[4]*60*60*24*30;
					}
					if(isset($m[6])) {
						// Weeks
						$expire += $m[6]*60*60*24*7;
					}
					if(isset($m[8])) {
						// Days
						$expire += $m[8]*60*60*24;
					}
					if(isset($m[10])) {
						// Hours
						$expire += $m[10]*60*60;
					}
					if(isset($m[12])) {
						// Minutes
						$expire += $m[12]*60;
					}
					if(isset($m[14])) {
						// Seconds
						$expire += $m[14];
					}
				}
				if($expire) {
					$query->bindValue(':expires', time()+$expire, PDO::PARAM_INT);
				} else {
					// Never expire
					$query->bindValue(':expires', null, PDO::PARAM_NULL);
				}
				
				$query->bindValue(':ip', $_POST['ip'], PDO::PARAM_STR);
				$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
				$query->bindValue(':set', time(), PDO::PARAM_INT);
				
				
				
				if(!empty($_POST['reason'])) {
					$reason = $_POST['reason'];
					markup($reason);
					$query->bindValue(':reason', $reason, PDO::PARAM_STR);
				} else {
					$query->bindValue(':reason', null, PDO::PARAM_NULL);
				}
				
				if($_POST['board_id'] < 0) {
					$query->bindValue(':board', null, PDO::PARAM_NULL);
				} else {
					$query->bindValue(':board', (int)$_POST['board_id'], PDO::PARAM_INT);
				}
				
				// Record the action
				modLog('Created a ' . ($expire ? $expire . ' second' : 'permanent') . " ban for {$_POST['ip']} with " . (!empty($_POST['reason']) ? "reason \"${reason}\"" : 'no reason'));
				
				$query->execute() or error(db_error($query));
				
				if(isset($_POST['board']))
					openBoard($_POST['board']);
				
				// Delete too
				if(isset($_POST['delete']) && isset($_POST['board']) && hasPermission($config['mod']['delete'], $_POST['board'])) {					
					$post = round($_POST['delete']);
					
					deletePost($post);
					
					// Record the action
					modLog("Deleted post #{$post}");
					
					// Rebuild board
					buildIndex();
				}
				
				if(hasPermission($config['mod']['public_ban']) && isset($_POST['post']) && isset($_POST['board']) && isset($_POST['public_message']) && isset($_POST['message'])) {					
					$post = round($_POST['post']);
					
					$query = prepare(sprintf("UPDATE `posts_%s` SET `body` = CONCAT(`body`, :body) WHERE `id` = :id", $board['uri']));
					$query->bindValue(':id', $post, PDO::PARAM_INT);
					$query->bindValue(':body', sprintf($config['mod']['ban_message'], utf8tohtml($_POST['message'])));
					$query->execute() or error(db_error($query));
					
					// Rebuild thread
					$query = prepare(sprintf("SELECT `thread` FROM `posts_%s` WHERE `id` = :id", $board['uri']));
					$query->bindValue(':id', $post, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					$thread = $query->fetch();
					if($thread['thread'])
						buildThread($thread['thread']);
					else
						buildThread($post);
					
					// Rebuild board
					buildIndex();
					
					// Record the action
					modLog("Attached a public ban message for post #{$post}: " . $_POST['message']);
				}
				
				// Redirect
				if(isset($_POST['continue']))
					header('Location: ' . $_POST['continue'], true, $config['redirect_http']);
				elseif(isset($board))
					header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
				else
					header('Location: ?/', true, $config['redirect_http']);
			}
		} elseif(preg_match('/^\/' . $regex['board'] . 'move\/(\d+)$/', $query, $matches)) {
			
			$boardName = &$matches[1];
			$postID = $matches[2];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['move'], $boardName)) error($config['error']['noaccess']);
			
			if(isset($_POST['board'])) {
				$targetBoard = $_POST['board'];
				$shadow = isset($_POST['shadow']);
				
				if($targetBoard == $boardName)
					error("Target and source board are the same.");
				
				// copy() if leaving a shadow thread behind. otherwise, rename().
				$clone = $shadow ? 'copy' : 'rename';
				
				$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `thread` IS NULL AND `id` = :id", $board['uri']));
				$query->bindValue(':id', $postID, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				if(!$post = $query->fetch()) {
					error($config['error']['nonexistant']);
				}
				
				if($post['file']) {
					$post['has_file'] = true;
					$post['width'] = &$post['filewidth'];
					$post['height'] = &$post['fileheight'];
					
					$file_src = sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file'];
					$file_thumb = sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb'];
				} else $post['has_file'] = false;
				
				// allow thread to keep its same traits (stickied, locked, etc.)
				$post['mod'] = true;
				
				if(!openBoard($targetBoard))
					error($config['error']['noboard']);
				
				$newID = post($post, true);
				
				if($post['has_file']) {
					$clone($file_src, sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file']);
					$clone($file_thumb, sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb']);
				}
				
				// move replies too...
				openBoard($boardName);
				
				$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `thread` = :id ORDER BY `id`", $board['uri']));
				$query->bindValue(':id', $postID, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				$replies = Array();
				while($post = $query->fetch()) {
					$post['mod'] = true;
					$post['thread'] = $newID;
					
					if($post['file']) {
						$post['has_file'] = true;
						$post['width'] = &$post['filewidth'];
						$post['height'] = &$post['fileheight'];
						
						$post['file_src'] = sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file'];
						$post['file_thumb'] = sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb'];
					} else $post['has_file'] = false;
					
					$replies[] = $post;
				}
				
				openBoard($targetBoard);
				foreach($replies as &$post) {
					post($post, false);
					if($post['has_file']) {
						$clone($post['file_src'], sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file']);
						$clone($post['file_thumb'], sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb']);
					}
				}
				
				// build thread
				buildThread($newID);
				buildIndex();
				
				// trigger themes
				rebuildThemes('post');
				
				openBoard($boardName);
				
				if($shadow) {
					// lock thread
					$query = prepare(sprintf("UPDATE `posts_%s` SET `locked` = 1 WHERE `id` = :id", $board['uri']));
					$query->bindValue(':id', $postID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					$post = Array(
						'mod' => true,
						'subject' => '',
						'email' => '',
						'name' => $config['mod']['shadow_name'],
						'capcode' => $config['mod']['shadow_capcode'],
						'trip' => '',
						'body' => sprintf($config['mod']['shadow_mesage'], '>>>/' . $targetBoard . '/' . $newID),
						'password' => '',
						'has_file' => false,
						// attach to original thread
						'thread' => $postID
					);
					
					markup($post['body']);
					
					$botID = post($post, false);
					
					header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['dir']['res'] . sprintf($config['file_page'], $postID) . '#' . $botID, true, $config['redirect_http']);
				} else {
					deletePost($postID);
					buildIndex();
					
					openBoard($targetBoard);					
					header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['dir']['res'] . sprintf($config['file_page'], $newID), true, $config['redirect_http']);
				}
			} else {
			
				$body = '<fieldset><legend>Move thread</legend>' .
					'<form action="?/' . $boardName . '/move/' . $postID . '" method="post">' .
						'<table>'
					;
			
				$boards = listBoards();
				if(count($boards) <= 1)
					error('No board to move to; there is only one.');
				
				$__boards = '';
				foreach($boards as &$_board) {
					if($_board['uri'] == $board['uri'])
						continue;
					$__boards .= '<li>' .
						'<input type="radio" name="board" id="board_' . $_board['uri'] . '" value="' . $_board['uri'] . '">' .
						'<label style="display:inline" for="board_' . $_board['uri'] . '"> ' .
								sprintf($config['board_abbreviation'], $_board['uri']) .
							' - ' . $_board['title'] .
						'</label>' .
						'</li>';
				}
			
				$body .= '<tr>' .
							'<th>Thread ID</th>' .
							'<td><input type="text" size="7" value="' . $postID . '" disabled /></td>' .
						'</tr>' .
					
						'<tr>' . 
							'<th><label for="message">Leave shadow thread</label></th>' .
							'<td>' .
								'<input type="checkbox" id="shadow" name="shadow" checked/>' .
								' <span class="unimportant">(locks thread; replies to it with a link.)</span>' .
							'</td>' .
						'</tr>' .
					
						'<tr>' .
							'<th>Target board</th>' .
							'<td><ul style="list-style:none;padding:2px 5px">' . $__boards . '</tl></td>' .
						'</tr>' .
					
						'<tr>' . 
							'<td></td>' . 
							'<td><input type="submit" value="Move thread" /></td>' . 
						'</tr>' . 
					'</table>' .
				'</form></fieldset>';
		
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Move #' . $postID,
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/' . $regex['board'] . 'ban(&delete)?\/(\d+)$/', $query, $matches)) {
			
			// Ban by post
			
			$boardName = &$matches[1];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			if(!hasPermission($config['mod']['ban'], $boardName)) error($config['error']['noaccess']);
			
			$delete = isset($matches[2]) && $matches[2] == '&delete';
			if($delete && !hasPermission($config['mod']['delete'], $boardName)) error($config['error']['noaccess']);
			
			$post = $matches[3];
			
			$query = prepare(sprintf("SELECT `ip`,`id` FROM `posts_%s` WHERE `id` = :id LIMIT 1", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1) {
				error($config['error']['invalidpost']);
			}
		
			$post = $query->fetch();
			
			$body = form_newBan($post['ip'], null, '?/' . sprintf($config['board_path'], $board['uri']) . $config['file_index'], $post['id'], $boardName, !$delete);
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'New ban',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/IP\/(\d+\.\d+\.\d+\.\d+|' . $config['ipv6_regex'] . ')\/deletenote\/(?P<id>\d+)$/', $query, $matches)) {
			if(!hasPermission($config['mod']['remove_notes'])) error($config['error']['noaccess']);
			
			$ip = $matches[1];
			$id = $matches['id'];

			$query = prepare("DELETE FROM `ip_notes` WHERE `ip` = :ip AND `id` = :id");
			$query->bindValue(':ip', $ip);
			$query->bindValue(':id', $id);
			$query->execute() or error(db_error($query));
			
			header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
		} elseif(preg_match('/^\/IP\/(\d+\.\d+\.\d+\.\d+|' . $config['ipv6_regex'] . ')$/', $query, $matches)) {
			// View information on an IP address
			
			$ip = $matches[1];
			$host = $config['mod']['dns_lookup'] ? rDNS($ip) : false;
			
			if(hasPermission($config['mod']['unban']) && isset($_POST['unban']) && isset($_POST['ban_id'])) {
				removeBan($_POST['ban_id']);
				header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
			} elseif(hasPermission($config['mod']['create_notes']) && isset($_POST['note'])) {
				$query = prepare("INSERT INTO `ip_notes` VALUES(NULL, :ip, :mod, :time, :body)");
				$query->bindValue(':ip', $ip);
				$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				markup($_POST['note']);
				$query->bindValue(':body', $_POST['note']);
				$query->execute() or error(db_error($query));
				
				header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
			} else {
				$body = '';
				$boards = listBoards();
				foreach($boards as &$_board) {
					openBoard($_board['uri']);
				
					$temp = '';
					$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `ip` = :ip ORDER BY `sticky` DESC, `time` DESC LIMIT :limit", $_board['uri']));
					$query->bindValue(':ip', $ip);
					$query->bindValue(':limit', $config['mod']['ip_recentposts'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				
					while($post = $query->fetch()) {
						if(!$post['thread']) {
							$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], $post['sage'], $post['embed'], '?/', $mod, false);
						} else {
							$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'],  $post['embed'], '?/', $mod);
						}
						$temp .= $po->build(true) . '<hr/>';
					}
				
					if(!empty($temp))
						$body .= '<fieldset><legend>Last ' . $query->rowCount() . ' posts on <a href="?/' .
								sprintf($config['board_path'], $_board['uri']) . $config['file_index'] .
							'">' .
							sprintf($config['board_abbreviation'], $_board['uri']) . ' - ' . $_board['title'] .
							'</a></legend>' . $temp . '</fieldset>';
				}
			
				if(hasPermission($config['mod']['view_notes'])) {
					$query = prepare("SELECT * FROM `ip_notes` WHERE `ip` = :ip ORDER BY `id` DESC");
					$query->bindValue(':ip', $ip);
					$query->execute() or error(db_error($query));
				
					if($query->rowCount() > 0 || hasPermission($config['mod']['create_notes'])) {
						$body .= '<fieldset><legend>' .
								$query->rowCount() . ' note' . ($query->rowCount() == 1 ?'' : 's') . ' on record' . 
							'</legend>';
						if($query->rowCount() > 0) {
							$body .= '<table class="modlog">' .
							'<tr><th>Staff</th><th>Note</th><th>Date</th>' .
								(hasPermission($config['mod']['remove_notes']) ? '<th>Actions</th>' : '') .
							'</td>';
							while($note = $query->fetch()) {
							
								if($note['mod']) {
									$_query = prepare("SELECT `username` FROM `mods` WHERE `id` = :id");
									$_query->bindValue(':id', $note['mod']);
									$_query->execute() or error(db_error($_query));
									if($_mod = $_query->fetch()) {
										$staff = '<a href="?/new_PM/' . $note['mod'] . '">' . utf8tohtml($_mod['username']) . '</a>';
									} else {
										$staff = '<em>???</em>';
									}
								} else {
									$staff = '<em>system</em>';
								}
								$body .= '<tr>' .
									'<td class="minimal">' .
										$staff .
									'</td><td>' .
										$note['body'] .
									'</td><td class="minimal">' .
										strftime($config['post_date'], $note['time']) .
									'</td>' .
									(hasPermission($config['mod']['remove_notes']) ?
										'<td class="minimal"><a class="unimportant" href="?/IP/' . $ip . '/deletenote/' . $note['id'] . '">[delete]</a></td>'
									: '') .
								'</tr>';
							}
							$body .= '</table>';
						}
			
						if(hasPermission($config['mod']['create_notes'])) {
							$body .= '<form action="" method="post" style="text-align:center;margin:0">' . 
									'<table>' .
										'<tr>' .
											'<th>Staff</th>' .
											'<td>' . $mod['username'] . '</td>' .
										'</tr>' .
										'<tr>' .
											'<th><label for="note">Note</label></th>' .
											'<td><textarea id="note" name="note" rows="5" cols="30"></textarea></td>' .
										'</tr>' .
										'<tr>' .
											'<td></td>' .
											'<td><input type="submit" value="New note" /></td>' .
										'</tr>' .
									'</table>' .
								'</form>';
						}
					
						$body .= '</fieldset>';
					}
				}
			
				if(hasPermission($config['mod']['view_ban'])) {
					$query = prepare("SELECT `bans`.*, `username`, `uri` FROM `bans` LEFT JOIN `boards` ON `boards`.`id` = `board` LEFT JOIN `mods` ON `mod` = `mods`.`id` WHERE `ip` = :ip");
					$query->bindValue(':ip', $ip);
					$query->execute() or error(db_error($query));
				
					if($query->rowCount() > 0) {
						$body .= '<fieldset><legend>Ban' . ($query->rowCount() == 1 ? '' : 's') . ' on record</legend>';
					
						while($ban = $query->fetch()) {
							$body .= '<form action="" method="post" style="text-align:center">' .
							'<table style="width:400px;margin-bottom:10px;border-bottom:1px solid #ddd;padding:5px"><tr><th>Status</th><td>' . 
								($config['mod']['view_banexpired'] && $ban['expires'] != 0 && $ban['expires'] < time() ?
									'Expired'
								: 'Active') .
							'</td></tr>' .
						
							// IP
							'<tr><th>IP</th><td>' . $ban['ip'] . '</td></tr>' .
						
							// Reason
							'<tr><th>Reason</th><td>' . $ban['reason'] . '</td></tr>' .
						
							// Board
							'<tr><th>Board</th><td>' .
							(isset($ban['board']) ?
								(isset($ban['uri']) ?
									sprintf($config['board_abbreviation'], $ban['uri'])
								:
									'<em>deleted?</em>'
								)
							:
								'<em>' . _('all boards') . '</em>'
							) .
							'</td></tr>' .
						
							// Set
							'<tr><th>Set</th><td>' . strftime($config['post_date'], $ban['set']) . '</td></tr>' .
						
							// Expires
							'<tr><th>Expires</th><td>' . 
								($ban['expires'] == 0 ?
									'<em>Never</em>'
								:
									strftime($config['post_date'], $ban['expires'])
								) .
							'</td></tr>' .
						
							// Staff
							'<tr><th>Staff</th><td>' .
								(isset($ban['username']) ?
									(!hasPermission($config['mod']['view_banstaff']) ?
										($config['mod']['view_banquestionmark'] ?
											'?'
										:
											($ban['type'] == JANITOR ? 'Janitor' :
											($ban['type'] == MOD ? 'Mod' :
											($ban['type'] == ADMIN ? 'Admin' :
											'?')))
										)
									:
										utf8tohtml($ban['username'])
									)
									: '<em>deleted?</em>'
								) .
							'</td></tr></table>' .
							
							'<input type="hidden" name="ban_id" value="' . $ban['id'] . '" />' .
							
							'<input type="submit" name="unban" value="Remove ban" ' .
								(!hasPermission($config['mod']['unban']) ? 'disabled' : '') .
							'/></form>';
						}
						
						$body .= '</fieldset>';
						
					}
				}
			
				if(hasPermission($config['mod']['ip_banform']))
					$body .= form_newBan($ip, null, '?/IP/' . $ip);
			
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'IP: ' . $ip,
					'subtitle' => $host,
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} else {
			error($config['error']['404']);
		}
	}

?>
