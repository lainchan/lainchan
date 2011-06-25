<?php
/* Config */
$kusabaxc = array('db' => array('timeout' => 5, 'persistent' => false));
$kusabaxc['db']['type']     = 'mysql';
$kusabaxc['db']['server']   = 'localhost';
$kusabaxc['db']['user']     = 'root';
$kusabaxc['db']['password'] = '';
$kusabaxc['db']['database'] = 'wakaba';
// KusabaX table prefix
$kusabaxc['db']['prefix']   = 'comments';
// Anything more to add to the DSN string (eg. port=xxx;foo=bar)
$kusabaxc['db']['dsn']      = '';

$kusabaxc['root'] = '/var/www/imgboard/';

$board = '';


/* End Config */

if (empty($kusabaxc['db']['user']))
    die('Did you forget to configure the script?');

// Infinite timeout
set_time_limit(0);

// KusabaX functions
function md5_decrypt($enc_text, $password, $iv_len = 16)
{
    $enc_text   = base64_decode($enc_text);
    $n          = strlen($enc_text);
    $i          = $iv_len;
    $plain_text = '';
    $iv         = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
    while ($i < $n) {
        $block = substr($enc_text, $i, 16);
        $plain_text .= $block ^ pack('H*', md5($iv));
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }
    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}

// KusabaX -> Tinyboard HTML
function convert_markup($body)
{
    global $config;
    $body = stripslashes($body);
    
    // >quotes
    $body = str_replace('"unkfunc"', '"quote"', $body);
    
    // >>cites
    $body = preg_replace('/<a href="[^"]+?\/(\w+)\/res\/(\d+).html#(\d+)" onclick="return highlight\(\'\d+\', true\);" class="[^"]+">/', '<a onclick="highlightReply(\'$3\');" href="' . $config['root'] . '$1/res/$2.html#$3">', $body);
    
    // Public bans
    $body = preg_replace('/<br \/><font color="#FF0000"><b>\((.+?)\)<\/b><\/font>/', '<span class="public_ban">($1)</span>', $body);
    
    return $body;
}

require 'inc/functions.php';
require 'inc/display.php';
require 'inc/template.php';
require 'inc/database.php';
require 'inc/user.php';
$step = isset($_GET['step']) ? round($_GET['step']) : 0;
$page = array(
    'config' => $config,
    'title' => 'KusabaX Database Migration',
    'body' => ''
);

$log = array();

// Trick Tinyboard into opening the KusabaX databse instead
$__temp       = $config['db'];
$config['db'] = $kusabaxc['db'];
sql_open();
// Get databse link
$kusabax = $pdo;
// Clear
unset($pdo);

// Open Tinyboard database
$config['db'] = $__temp;
unset($__temp);
sql_open();

$k_query = $kusabax->query('SELECT * FROM `' . $kusabaxc['db']['prefix'] . '` ORDER BY num ASC');
while ($post = $k_query->fetch()) {
    $log[] = 'Replicating post <strong>' . $post['num'] . '</strong> on /' . $board . '/';
    
    $query = prepare(sprintf("INSERT INTO `posts_%s` VALUES 
(:id, :thread, :subject, :email, :name, :trip, :capcode, :body, :time, :bump, :thumb, :thumbwidth,
 :thumbheight, :file, :width, :height, :filesize, :filename, :filehash, :password, :ip, :sticky, :locked, :embed)", $board));
    
    // Post ID
    $query->bindValue(':id', $post['num'], PDO::PARAM_INT);
    
    // Thread (`parentid`)
    if ($post['parent'] == 0)
        $query->bindValue(':thread', null, PDO::PARAM_NULL);
    else
        $query->bindValue(':thread', (int) $post['parent'], PDO::PARAM_INT);
    
    // Name
    if (empty($post['name']))
        $post['name'] = $config['anonymous'];
    $query->bindValue(':name', $post['name'], PDO::PARAM_INT);
    
    // Trip
    if (empty($post['tripcode']))
        $query->bindValue(':trip', null, PDO::PARAM_NULL);
    else
        $query->bindValue(':trip', $post['tripcode'], PDO::PARAM_STR);
    
    // Email
    $query->bindValue(':email', $post['email'], PDO::PARAM_STR);
    
    // Subject
    $query->bindValue(':subject', $post['subject'], PDO::PARAM_STR);
    
    // Body (`message`)
    $query->bindValue(':body', convert_markup($post['comment']), PDO::PARAM_STR);
    
    // File
    
    if (empty($post['image'])) {
        if (empty($post['parent'])) {
            $query->bindValue(':file', 'deleted', PDO::PARAM_NULL);
        } else {
            $query->bindValue(':file', null, PDO::PARAM_NULL);
        }
        $query->bindValue(':width', null, PDO::PARAM_NULL);
        $query->bindValue(':height', null, PDO::PARAM_NULL);
        $query->bindValue(':filesize', null, PDO::PARAM_NULL);
        $query->bindValue(':filename', null, PDO::PARAM_NULL);
        $query->bindValue(':filehash', null, PDO::PARAM_NULL);
        $query->bindValue(':thumb', null, PDO::PARAM_NULL);
        $query->bindValue(':thumbwidth', null, PDO::PARAM_NULL);
        $query->bindValue(':thumbheight', null, PDO::PARAM_NULL);
    } else {
        $fileA  = str_replace('src/', '', $post['image']);
        $thumbA = str_replace('thumb/', '', $post['thumbnail']);
        
        
        
        
        // Copy file
        $file_path = $kusabaxc['root'] . 'src/' . $fileA;
        
        if (strstr($thumbA, 'src')) {
            $thumbA     = str_replace('src/', '', $thumbA);
            $thumb_path = $kusabaxc['root'] . 'src/' . $thumbA;
            $log[]      = $thumb_path;
        } else {
            $thumb_path = $kusabaxc['root'] . 'thumb/' . $thumbA;
        }
        
        $to_file_path  = sprintf($config['board_path'], $board) . $config['dir']['img'] . $fileA;
        $to_thumb_path = sprintf($config['board_path'], $board) . $config['dir']['thumb'] . $thumbA;
        
        
        if (!file_exists($to_file_path)) {
            $log[] = 'Copying file: <strong>' . $file_path . '</strong>';
            if (!@copy($file_path, $to_file_path)) {
                $err   = error_get_last();
                $log[] = 'Could not copy <strong>' . $file_path . '</strong>: ' . $err['message'];
            }
        }
        
        if (!file_exists($to_thumb_path)) {
            $log[] = 'Copying file: <strong>' . $thumb_path . '</strong>';
            if (!@copy($thumb_path, $to_thumb_path)) {
                $err   = error_get_last();
                $log[] = 'Could not copy <strong>' . $thumb_path . '</strong>: ' . $err['message'];
            }
        }
        
        if (!file_exists($to_file_path)) {
            $query->bindValue(':file', 'deleted', PDO::PARAM_STR);
            $query->bindValue(':width', null, PDO::PARAM_NULL);
            $query->bindValue(':height', null, PDO::PARAM_NULL);
            $query->bindValue(':filesize', null, PDO::PARAM_NULL);
            $query->bindValue(':filename', null, PDO::PARAM_NULL);
            $query->bindValue(':filehash', null, PDO::PARAM_NULL);
            $query->bindValue(':thumb', null, PDO::PARAM_NULL);
            $query->bindValue(':thumbwidth', null, PDO::PARAM_NULL);
            $query->bindValue(':thumbheight', null, PDO::PARAM_NULL);
        } else {
            $query->bindValue(':file', $fileA, PDO::PARAM_STR);
            $query->bindValue(':width', $post['width'], PDO::PARAM_INT);
            $query->bindValue(':height', $post['height'], PDO::PARAM_INT);
            $query->bindValue(':filesize', $post['size'], PDO::PARAM_INT);
            $query->bindValue(':filename', $fileA, PDO::PARAM_STR);
            $query->bindValue(':filehash', null, PDO::PARAM_NULL);
            $query->bindValue(':thumb', $thumbA, PDO::PARAM_STR);
            $query->bindValue(':thumbwidth', $post['tn_width'], PDO::PARAM_INT);
            $query->bindValue(':thumbheight', $post['tn_height'], PDO::PARAM_INT);
        }
    }
    
    // IP
    //    $ip = md5_decrypt($post['ip'], $kusabaxc['randomseed']);
    $ip = long2ip($post['ip']);
    if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip)) {
        // Invalid IP address. Wrong KU_RANDOMSEED?
        
        $log[] = 'Invalid IP address returned after decryption. Wrong KU_RANDOMSEED?';
        // just set it to something valid and continue
        $ip    = '0.0.0.0';
    }
    $query->bindValue(':ip', $ip, PDO::PARAM_STR);
    
    // Time (`timestamp`)
    $query->bindValue(':time', $post['timestamp'], PDO::PARAM_INT);
    
    // Bump (`bumped`)
    //    $query->bindValue(':bump', $post['bumped'], PDO::PARAM_INT);
    //      $query->bindValue(':bump', '0', PDO::PARAM_INT);
    $query->bindValue(':bump', $post['timestamp'], PDO::PARAM_INT);
    
    
    // Locked
    //    $query->bindValue(':locked', $post['locked'], PDO::PARAM_INT);
    $query->bindValue(':locked', '0', PDO::PARAM_INT);
    
    // Sticky
    //    $query->bindValue(':sticky', $post['stickied'], PDO::PARAM_INT);
    $query->bindValue(':sticky', '0', PDO::PARAM_INT);
    
    // Stuff we can't do (yet)
    $query->bindValue(':embed', null, PDO::PARAM_NULL);
    $query->bindValue(':password', null, PDO::PARAM_NULL);
    $query->bindValue(':capcode', null, PDO::PARAM_NULL);
    
    // Insert post
    $query->execute() or $log[] = 'Error: ' . db_error($query);
    //$log[] = print $query;
    //$log[] = $query->debugDumpParams();
    //$log[]= $ip;
}

$page['body'] = '<div class="ban"><h2>Migrating…</h2><p>';
foreach ($log as &$l) {
    $page['body'] .= $l . '<br/>';
}
$page['body'] .= '</p></div>';

echo Element('page.html', $page);
?>
