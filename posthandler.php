<?php
// Glue code for handling a Tinyboard post.
// Portions of this file are derived from Tinyboard code.

function postHandler($post) {
    global $board, $config;

    if ($post->has_file && $post->extension == 'webm') {
        require_once dirname(__FILE__) . '/videodata.php';
        $videoDetails = videoData($post->file_path);
        if (!isset($videoDetails['container']) || $videoDetails['container'] != 'webm') return "not a WebM file";

        // Set thumbnail
        $thumbName = $board['dir'] . $config['dir']['thumb'] . $post->file_id . '.webm';
        if ($config['spoiler_images'] && isset($_POST['spoiler'])) {
            // Use spoiler thumbnail
            $post->thumb = 'spoiler';
            $size = @getimagesize($config['spoiler_image']);
            $post->thumbwidth = $size[0];
            $post->thumbheight = $size[1];
        } elseif (isset($videoDetails['frame']) && $thumbFile = fopen($thumbName, 'wb')) {
            // Use single frame from video as pseudo-thumbnail
            fwrite($thumbFile, $videoDetails['frame']);
            fclose($thumbFile);
            $post->thumb = $post->file_id . '.webm';
        } else {
            // Fall back to file thumbnail
            $post->thumb = 'file';
        }
        unset($videoDetails['frame']);

        // Set width and height
        if (isset($videoDetails['width']) && isset($videoDetails['height'])) {
            $post->width = $videoDetails['width'];
            $post->height = $videoDetails['height'];
            if ($post->thumb != 'file' && $post->thumb != 'spoiler') {
                $thumbMaxWidth = $post->op ? $config['thumb_op_width'] : $config['thumb_width'];
                $thumbMaxHeight = $post->op ? $config['thumb_op_height'] : $config['thumb_height'];
                if ($videoDetails['width'] > $thumbMaxWidth || $videoDetails['height'] > $thumbMaxHeight) {
                    $post->thumbwidth = min($thumbMaxWidth, intval(round($videoDetails['width'] * $thumbMaxHeight / $videoDetails['height'])));
                    $post->thumbheight = min($thumbMaxHeight, intval(round($videoDetails['height'] * $thumbMaxWidth / $videoDetails['width'])));
                } else {
                    $post->thumbwidth = $videoDetails['width'];
                    $post->thumbheight = $videoDetails['height'];
                }
            }
        }
    }
}
