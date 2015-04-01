<?php
require_once("inc/functions.php");

if (!$config['smart_build']) {
  die('You need to enable $config["smart_build"]');
}

$config['smart_build'] = false; // Let's disable it, so we can build the page for real

function sb_board($b, $page = 1) { global $config, $build_pages; $page = (int)$page;
  if ($page < 1 && $page != -1) return false;
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

function sb_thread($b, $thread) { global $config; $thread = (int)$thread;
  if (!openBoard($b)) return false;
  buildThread($thread);
  return true;
}

function sb_api($b) { global $config;
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
  rebuildTheme("catalog", "post-thread", $b); 
  return true;
}

function sb_recent() {
  rebuildTheme("recent", "post-thread"); 
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

$entrypoints['/%b/'.$config['dir']['res'].$config['file_page']]          = 'sb_thread';
$entrypoints['/%b/'.$config['dir']['res'].$config['file_page50']]        = 'sb_thread';
if ($config['slugify']) {
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page_slug']]   = 'sb_thread';
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page50_slug']] = 'sb_thread';
}
if ($config['api']['enabled']) {
  $entrypoints['/%b/'.$config['dir']['res'].'%d.json']                   = 'sb_thread';
}

$entrypoints['/*/']              = 'sb_ukko';
$entrypoints['/*/index.html']    = 'sb_ukko';
$entrypoints['/recent.html']     = 'sb_recent';
$entrypoints['/%b/catalog.html'] = 'sb_catalog';

$reached = false;

$request = $_SERVER['REQUEST_URI'];

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

if ($reached) {
  if ($request[strlen($request)-1] == '/') {
    $request .= 'index.html';
  }
  $request = '.'.$request;

  if (!file_exists($request)) {
    header("Location: ".$config['page_404']);
    die();
  }

  header("HTTP/1.1 200 OK");
  header("Status: 200 OK");
  if (preg_match('/\.json$/', $request)) {
    header("Content-Type", "application/json");
  }
  elseif (preg_match('/\.js$/', $request)) {
    header("Content-Type", "text/javascript; charset=utf-8");
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
  header("Location: ".$config['page_404']);
}
