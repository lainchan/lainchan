<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = \Stream::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Stream page';
	$theme['version'] = 'v0.9.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Site title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(optional)'];
	
	$theme['config'][] = ['title' => 'File', 'name' => 'file', 'type' => 'text', 'default' => 'stream.html', 'comment' => '(eg. "stream.html")'];
    $theme['config'][] =  ['title'   => 'OGV stream URL', 'name'    => 'ogvurl', 'type'    => 'text', 'default' => 'https://lainchan.org/icecast/lainstream.ogg'];
    $theme['config'][] =  ['title'   => 'RTMP stream URL', 'name'    => 'rtmpurl', 'type'    => 'text', 'default' => 'rtmp://lainchan.org/show/stream'];
    $theme['config'][] =  ['title'   => 'RTMP  Video.JS stream URL', 'name'    => 'rtmpvideojsurl', 'type'    => 'text', 'default' => 'rtmp://lainchan.org/show/&stream'];
    
    $theme['config'][] =  ['title'   => 'RTMP HLS stream URL', 'name'    => 'hlsurl', 'type'    => 'text', 'default' => 'https://lainchan.org:8080/hls/stream.m3u8'];

    $theme['config'][] =  ['title'   => 'OGV Status URL', 'name'    => 'ogvstatus', 'type'    => 'text', 'default' => '/radio_assets/status.xsl'];
    
    $theme['config'][] =  ['title'   => 'RTMP Status URL', 'name'    => 'rtmpstatus', 'type'    => 'text', 'default' => '/live/status?app=live&name=stream'];
    $theme['config'][] =  ['title'   => 'RTMP Viewers URL', 'name'    => 'rtmpviewers', 'type'    => 'text', 'default' => '/live/subs?app=live&name=stream'];
    $theme['config'][] =  ['title'   => 'Formats', 'name'    => 'formats', 'type'    => 'text', 'default' => 'hls ogg rtmp'];
    $theme['config'][] =  ['title'   => 'Default Format', 'name'    => 'defaultformat', 'type'    => 'text', 'default' => 'hls ogg rtmp'];
	
	
	// Unique function name for building everything
	$theme['build_function'] = 'stream_build';
	$theme['install_callback'] = 'stream_install';
	if (!function_exists('stream_install')) {
		function stream_install($settings) {
		}
	}

