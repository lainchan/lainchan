<?php
/*
* ffmpeg.php
* A barebones ffmpeg based webm implementation for vichan.
*/
function get_webm_info($filename) {
  global $board, $config;
  $filename = escapeshellarg((string) $filename);
  $ffprobe = $config['webm']['ffprobe_path'];
  $ffprobe_out = [];
  $webminfo = [];
  exec("$ffprobe -v quiet -print_format json -show_format -show_streams $filename", $ffprobe_out);
  $ffprobe_out = json_decode(implode("\n", $ffprobe_out), 1, 512, JSON_THROW_ON_ERROR);
  $webminfo['error'] = is_valid_webm($ffprobe_out);
  if(empty($webminfo['error'])) {
    $webminfo['width'] = $ffprobe_out['streams'][0]['width'];
    $webminfo['height'] = $ffprobe_out['streams'][0]['height'];
    $webminfo['duration'] = $ffprobe_out['format']['duration'];
  }
  return $webminfo;
}
function is_valid_webm($ffprobe_out) {
  global $board, $config;
  if (empty($ffprobe_out))
    return ['code' => 1, 'msg' => $config['error']['genwebmerror']];
  $extension = pathinfo((string) $ffprobe_out['format']['filename'], PATHINFO_EXTENSION);
  if ($extension === 'webm') {
    if ($ffprobe_out['format']['format_name'] != 'matroska,webm')
      return ['code' => 2, 'msg' => $config['error']['invalidwebm']];
  } elseif ($extension === 'mp4') {
    if ($ffprobe_out['streams'][0]['codec_name'] != 'h264' && $ffprobe_out['streams'][1]['codec_name'] != 'aac')
      return ['code' => 2, 'msg' => $config['error']['invalidwebm']];
  } else {
    return ['code' => 1, 'msg' => $config['error']['genwebmerror']];  
  }
  if (((is_countable($ffprobe_out['streams']) ? count($ffprobe_out['streams']) : 0) > 1) && (!$config['webm']['allow_audio']))
    return ['code' => 3, 'msg' => $config['error']['webmhasaudio']];
  if (empty($ffprobe_out['streams'][0]['width']) || (empty($ffprobe_out['streams'][0]['height'])))
    return ['code' => 2, 'msg' => $config['error']['invalidwebm']];
  if ($ffprobe_out['format']['duration'] > $config['webm']['max_length'])
    return ['code' => 4, 'msg' => sprintf($config['error']['webmtoolong'], $config['webm']['max_length'])];
}
function make_webm_thumbnail($filename, $thumbnail, $width, $height, $duration) {
  global $board, $config;
  $filename = escapeshellarg((string) $filename);
  $thumbnailfc = escapeshellarg((string) $thumbnail); // Should be safe by default but you
                                           // can never be too safe.
  $width = escapeshellarg((string) $width);
  $height = escapeshellarg((string) $height); // Same as above.
  $ffmpeg = $config['webm']['ffmpeg_path'];
  $ret = 0;
  $ffmpeg_out = [];
  exec("$ffmpeg -strict -2 -ss " . floor($duration / 2) . " -i $filename -v quiet -an -vframes 1 -f mjpeg -vf scale=$width:$height $thumbnailfc 2>&1", $ffmpeg_out, $ret);
  // Work around for https://trac.ffmpeg.org/ticket/4362
  if (filesize($thumbnail) === 0) {
    // try again with first frame
    exec("$ffmpeg -y -strict -2 -ss 0 -i $filename -v quiet -an -vframes 1 -f mjpeg -vf scale=$width:$height $thumbnailfc 2>&1", $ffmpeg_out, $ret);
    clearstatcache();
    // failed if no thumbnail size even if ret code 0, ffmpeg is buggy
    if (filesize($thumbnail) === 0) {
      $ret = 1;
    }
  }
  return $ret;
}
