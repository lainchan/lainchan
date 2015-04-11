<?php
require_once("inc/functions.php");

if (!$config['smart_build']) {
  die('You need to enable $config["smart_build"]');
}

$config['smart_build'] = false; // Let's disable it, so we can build the page for real

function after_open_board() { global $config;
  $config['smart_build'] = false;
};

function sb_board($b, $page = 1) { global $config, $build_pages; $page = (int)$page;
  if ($page < 1) return false;
  if (!openBoard($b)) return false;
  if ($page > $config['max_pages']) return false;
  $config['try_smarter'] = true;
  $build_pages = array($page);
  buildIndex("skip");
  return true;
}

function sb_api_board($b, $page = 0) { $page = (int)$page;
  return sb_board($b, $page + 1);
}

function sb_thread($b, $thread, $slugcheck = false) { global $config; $thread = (int)$thread;
  if ($thread < 1) return false;

  if (!preg_match('/^'.$config['board_regex'].'$/u', $b)) return false;

  if (Cache::get("thread_exists_".$b."_".$thread) == "no") return false;

  $query = prepare(sprintf("SELECT MAX(`id`) AS `max` FROM ``posts_%s``", $b));
  if (!$query->execute()) return false;

  $s = $query->fetch(PDO::FETCH_ASSOC);
  $max = $s['max'];

  if ($thread > $max) return false;

  $query = prepare(sprintf("SELECT `id` FROM ``posts_%s`` WHERE `id` = :id AND `thread` IS NULL", $b));
  $query->bindValue(':id', $thread);
  
  if (!$query->execute() || !$query->fetch(PDO::FETCH_ASSOC) ) {
    Cache::set("thread_exists_".$b."_".$thread, "no");
    return false;
  }

  if ($slugcheck && $config['slugify']) {
    global $request;

    $link = link_for(array("id" => $thread), $slugcheck === 50, array("uri" => $b));
    $link = "/".$b."/".$config['dir']['res'].$link;

    if ($link != $request) {
      header("Location: $link", true, 301);
      die();
    }
  }

  if ($slugcheck == 50) { // Should we really generate +50 page? Maybe there are not enough posts anyway
    global $request;
    $r = str_replace("+50", "", $request);
    $r = substr($r, 1); // Cut the slash

    if (file_exists($r)) return false;
  }
  
  if (!openBoard($b)) return false;
  buildThread($thread);
  return true;
}

function sb_thread_slugcheck($b, $thread) {
  return sb_thread($b, $thread, true);
}
function sb_thread_slugcheck50($b, $thread) {
  return sb_thread($b, $thread, 50);
}

function sb_api($b) { global $config, $build_pages;
  if (!openBoard($b)) return false;
  $config['try_smarter'] = true;
  $build_pages = array(-1);
  buildIndex();
  return true;
}

function sb_ukko() {
  rebuildTheme("ukko", "post-thread");
  return true;
}

function sb_catalog($b) {
  if (!openBoard($b)) return false;

  rebuildTheme("catalog", "post-thread", $b); 
  return true;
}

function sb_recent() {
  rebuildTheme("recent", "post-thread");
  return true;
}

function sb_sitemap() {
  rebuildTheme("sitemap", "all");
  return true;
}

$entrypoints = array();

$entrypoints['/%b/']                       = 'sb_board';
$entrypoints['/%b/'.$config['file_index']] = 'sb_board';
$entrypoints['/%b/'.$config['file_page']]  = 'sb_board';
$entrypoints['/%b/%d.json']                = 'sb_api_board';
if ($config['api']['enabled']) {
  $entrypoints['/%b/threads.json']         = 'sb_api';
  $entrypoints['/%b/catalog.json']         = 'sb_api';
}

$entrypoints['/%b/'.$config['dir']['res'].$config['file_page']]          = 'sb_thread_slugcheck';
$entrypoints['/%b/'.$config['dir']['res'].$config['file_page50']]        = 'sb_thread_slugcheck50';
if ($config['slugify']) {
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page_slug']]   = 'sb_thread_slugcheck';
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page50_slug']] = 'sb_thread_slugcheck50';
}
if ($config['api']['enabled']) {
  $entrypoints['/%b/'.$config['dir']['res'].'%d.json']                   = 'sb_thread';
}

$entrypoints['/*/']              = 'sb_ukko';
$entrypoints['/*/index.html']    = 'sb_ukko';
$entrypoints['/recent.html']     = 'sb_recent';
$entrypoints['/%b/catalog.html'] = 'sb_catalog';
$entrypoints['/sitemap.xml']     = 'sb_sitemap';

$reached = false;

$request = $_SERVER['REQUEST_URI'];
list($request) = explode('?', $request);

foreach ($entrypoints as $id => $fun) {
  $id = '@^' . preg_quote($id, '@') . '$@u'; 

  $id = str_replace('%b', '('.$config['board_regex'].')', $id);
  $id = str_replace('%d', '([0-9]+)',                     $id);
  $id = str_replace('%s', '[a-zA-Z0-9-]+',                $id);

  $matches = null;

  if (preg_match ($id, $request, $matches)) {
    array_shift($matches);

    $reached = call_user_func_array($fun, $matches);

    break;
  }
}

function die_404() { global $config;
  if (!$config['page_404']) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Page doesn't exist<hr><address>vichan</address>";
  }
  else {
    header("Location: ".$config['page_404']);
  }
  header("X-Accel-Expires: 120");
  die();
}

if ($reached) {
  if ($request[strlen($request)-1] == '/') {
    $request .= 'index.html';
  }
  $request = '.'.$request;

  if (!file_exists($request)) {
    die_404();
  }

  header("HTTP/1.1 200 OK");
  header("Status: 200 OK");
  if (preg_match('/\.json$/', $request)) {
    header("Content-Type", "application/json");
  }
  elseif (preg_match('/\.js$/', $request)) {
    header("Content-Type", "text/javascript; charset=utf-8");
  }
  elseif (preg_match('/\.xml$/', $request)) {
    header("Content-Type", "application/xml");
  }
  else {
    header("Content-Type", "text/html; charset=utf-8");
  }
  header("Cache-Control: public, nocache, no-cache, max-age=0, must-revalidate");
  header("Expires: Fri, 22 Feb 1991 06:00:00 GMT");
  header("Last-Modified: ".date('r', filemtime($request)));

  //if (isset ($_SERVER['HTTP_ACCEPT_ENCODING']) && preg_match('/gzip/', $_SERVER['HTTP_ACCEPT_ENCODING']) && file_exists($request.".gz")) {
  //  header("Content-Encoding: gzip");
  //  $file = fopen($request.".gz", 'r');
  //}
  //else {
  $file = fopen($request, 'r');
  //}
  fpassthru($file);
  fclose($file);
}
else {
  die_404();
}
