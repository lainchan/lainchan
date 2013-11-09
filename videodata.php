<?php
require dirname(__FILE__) . '/matroska.php';

// Header for single VPx keyframe
function vpxFrameHeader($size, $width, $height, $codecID) {
    return "\x1A\x45\xDF\xA3\x9F\x42\x86\x81\x01\x42\xF7\x81\x01\x42\xF2\x81"
        . "\x04\x42\xF3\x81\x08\x42\x82\x84\x77\x65\x62\x6D\x42\x87\x81\x02"
        . "\x42\x85\x81\x02\x18\x53\x80\x67\x08" . pack('N', $size + 173) . "\x11\x4D\x9B"
        . "\x74\xB8\x4D\xBB\x8B\x53\xAB\x84\x15\x49\xA9\x66\x53\xAC\x81\x3D"
        . "\x4D\xBB\x8B\x53\xAB\x84\x16\x54\xAE\x6B\x53\xAC\x81\x58\x4D\xBB"
        . "\x8B\x53\xAB\x84\x1C\x53\xBB\x6B\x53\xAC\x81\x85\x4D\xBB\x8B\x53"
        . "\xAB\x84\x1F\x43\xB6\x75\x53\xAC\x81\x97\x15\x49\xA9\x66\x96\x2A"
        . "\xD7\xB1\x83\x0F\x42\x40\x44\x89\x84\x41\x20\x00\x00\x4D\x80\x81"
        . "\x66\x57\x41\x81\x66\x16\x54\xAE\x6B\xA8\xAE\xA6\xD7\x81\x01\x73"
        . "\xC5\x81\x01\x83\x81\x01\x23\xE3\x83\x83\x98\x96\x80\x86\x85" . $codecID
        . "\xE0\x8C\xB0\x84" . pack('N', $width) . "\xBA\x84" . pack('N', $height)
        . "\x1C\x53\xBB\x6B\x8D\xBB\x8B\xB3\x81\x00\xB7\x86\xF7\x81"
        . "\x01\xF1\x81\x97\x1F\x43\xB6\x75\x08" . pack('N', $size + 13) . "\xE7\x81\x00"
        . "\xA3\x08" . pack('N', $size + 4) . "\x81\x00\x00\x80";
}

// Locate first VPx keyframe of track $trackNumber after timecode $skip
function firstVPxFrame($segment, $trackNumber, $skip=0) {
    foreach($segment as $x1) {
        if ($x1->name() == 'Cluster') {
            $cluserTimecode = $x1->Get('Timecode');
            foreach($x1 as $x2) {
                $blockRaw = NULL;
                if ($x2->name() == 'SimpleBlock') {
                    $blockRaw = $x2->value();
                } elseif ($x2->name() == 'BlockGroup') {
                    $blockRaw = $x2->get('Block');
                }
                if (isset($blockRaw)) {
                    $block = new MatroskaBlock($blockRaw);
                    if ($block->trackNumber == $trackNumber) {
                        $frame = $block->frames[0];
                        if ($block->keyframe) {
                            if (!isset($cluserTimecode) || $cluserTimecode + $block->timecode >= $skip) {
                                return $frame;
                            } elseif (!isset($frame1)) {
                                $frame1 = $frame;
                            }
                        }
                    }
                }
            }
        }
    }
    return isset($frame1) ? $frame1 : NULL;
}

function videoData($filename) {
    $data = array();

    // Open file
    $fileHandle = fopen($filename, 'rb');
    if (!$fileHandle) {
        error_log('could not open file');
        return $data;
    }

    try {
        $root = readMatroska($fileHandle);

        // Locate segment information and tracks
        $segment = $root->get('Segment');
        if (!isset($segment)) throw new Exception('missing Segment element');

        // Get segment information
        $info = $segment->get('Info');
        if (isset($info)) {
            $timecodeScale = $info->get('TimecodeScale');
            $duration = $info->get('Duration');
            if (isset($timecodeScale) && isset($duration)) {
                $data['duration'] = 1e-9 * $timecodeScale * $duration;
            }
        }

        // Locate video track
        $tracks = $segment->get('Tracks');
        if (!isset($tracks)) throw new Exception('missing Tracks element');
        foreach($tracks as $trackEntry) {
            if ($trackEntry->name() == 'TrackEntry' && $trackEntry->get('TrackType') == 1) {
                $videoTrack = $trackEntry;
                break;
            }
        }
        if (!isset($videoTrack)) throw new Exception('no video track');

        // Get track information
        $videoAttr = $videoTrack->get('Video');
        if (isset($videoAttr)) {
            $pixelWidth = $videoAttr->get('PixelWidth');
            $pixelHeight = $videoAttr->get('PixelHeight');
            if ($pixelWidth == 0 || $pixelHeight == 0) {
                error_log('bad PixelWidth/PixelHeight');
                $pixelWidth = NULL;
                $pixelHeight = NULL;
            }
            $data['width'] = $videoAttr->get('DisplayWidth', $pixelWidth);
            $data['height'] = $videoAttr->get('DisplayHeight', $pixelHeight);
            if ($data['width'] == 0 || $data['height'] == 0) {
                error_log('bad DisplayWidth/DisplayHeight');
                $data['width'] = $pixelWidth;
                $data['height'] = $pixelHeight;
            }
        }

        // Extract frame to use as thumbnail
        $trackNumber = $videoTrack->get('TrackNumber');
        if (!isset($trackNumber)) throw new Exception('missing track number');
        $codecID = $videoTrack->get('CodecID');
        if ($codecID != 'V_VP8' && $codecID != 'V_VP9') throw new Exception('codec is not VP8 or VP9');
        if (!isset($pixelWidth) || !isset($pixelHeight)) throw new Exception('no width or height');
        if (isset($data['duration']) && $data['duration'] >= 5) {
            $skip = 1e9 / $timecodeScale;
        } else {
            $skip = 0;
        }
        $frame = firstVPxFrame($segment, $trackNumber, $skip);
        if (!isset($frame)) throw new Exception('no keyframes');
        $data['frame'] = vpxFrameHeader($frame->size(), $pixelWidth, $pixelHeight, $codecID) . $frame->readAll();

    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    fclose($fileHandle);
    return $data;
}
