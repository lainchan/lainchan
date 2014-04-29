<?php
// Glue code for handling a Tinyboard post.
// Portions of this file are derived from Tinyboard code.

function postHandler($post) {
    global $board, $config;

    if ($post->has_file) foreach ($post->files as &$file) if ($file->extension == 'webm') {
        require_once dirname(__FILE__) . '/videodata.php';
        $videoDetails = videoData($file->file_path);
        if (!isset($videoDetails['container']) || $videoDetails['container'] != 'webm') return "not a WebM file";

        // Set thumbnail
        $thumbName = $board['dir'] . $config['dir']['thumb'] . $file->file_id . '.webm';
        if ($config['spoiler_images'] && isset($_POST['spoiler'])) {
            // Use spoiler thumbnail
            $file->thumb = 'spoiler';
            $size = @getimagesize($config['spoiler_image']);
            $file->thumbwidth = $size[0];
            $file->thumbheight = $size[1];
        } elseif (isset($videoDetails['frame']) && $thumbFile = fopen($thumbName, 'wb')) {
            // Use single frame from video as pseudo-thumbnail
            fwrite($thumbFile, $videoDetails['frame']);
            fclose($thumbFile);
            $file->thumb = $file->file_id . '.webm';
        } else {
            // Fall back to file thumbnail
            $file->thumb = 'file';
        }
        unset($videoDetails['frame']);

        // Set width and height
        if (isset($videoDetails['width']) && isset($videoDetails['height'])) {
            $file->width = $videoDetails['width'];
            $file->height = $videoDetails['height'];
            if ($file->thumb != 'file' && $file->thumb != 'spoiler') {
                $thumbMaxWidth = $post->op ? $config['thumb_op_width'] : $config['thumb_width'];
                $thumbMaxHeight = $post->op ? $config['thumb_op_height'] : $config['thumb_height'];
                if ($videoDetails['width'] > $thumbMaxWidth || $videoDetails['height'] > $thumbMaxHeight) {
                    $file->thumbwidth = min($thumbMaxWidth, intval(round($videoDetails['width'] * $thumbMaxHeight / $videoDetails['height'])));
                    $file->thumbheight = min($thumbMaxHeight, intval(round($videoDetails['height'] * $thumbMaxWidth / $videoDetails['width'])));
                } else {
                    $file->thumbwidth = $videoDetails['width'];
                    $file->thumbheight = $videoDetails['height'];
                }
            }
        }
    }
}
