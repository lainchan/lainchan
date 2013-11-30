<?php
require dirname(__FILE__) . '/matroska.php';

// Make video from single VPx keyframe
function muxVPxFrame($width, $height, $codecID, $data) {
    $size = strlen($data);
    $lenSeekHead = 61;
    $lenCues = 18;
    $ebml = encodeElement('EBML',
        encodeElement('EBMLVersion', "\x01")
        . encodeElement('EBMLReadVersion', "\x01")
        . encodeElement('EBMLMaxIDLength', "\x04")
        . encodeElement('EBMLMaxSizeLength', "\x08")
        . encodeElement('DocType', "webm")
        . encodeElement('DocTypeVersion', "\x02")
        . encodeElement('DocTypeReadVersion', "\x02")
    );
    $info = encodeElement('Info',
        encodeElement('TimecodeScale', "\x0F\x42\x40")
        . encodeElement('Duration', "\x41\x20\x00\x00")
        . encodeElement('MuxingApp', 'f')
        . encodeElement('WritingApp', 'f')
    );
    $tracks = encodeElement('Tracks',
        encodeElement('TrackEntry',
            encodeElement('TrackNumber', "\x01")
            . encodeElement('TrackUID', "\x01")
            . encodeElement('TrackType', "\x01")
            . encodeElement('DefaultDuration', "\x98\x96\x80")
            . encodeElement('CodecID', $codecID)
            . encodeElement('Video',
                encodeElement('PixelWidth', pack('N', $width))
                . encodeElement('PixelHeight', pack('N', $height))
            )
        )
    );
    $cues = encodeElement('Cues',
        encodeElement('CuePoint',
            encodeElement('CueTime', "\x00")
            . encodeElement('CueTrackPositions',
                encodeElement('CueTrack', "\x01")
                . encodeElement('CueClusterPosition', chr($lenSeekHead + strlen($info) + strlen($tracks) + $lenCues))
            )
        )
    );
    $seekHead = encodeElement('SeekHead',
        encodeElement('Seek',
            encodeElement('SeekID', encodeElementName('Info'))
            . encodeElement('SeekPosition', chr($lenSeekHead))
        )
        . encodeElement('Seek',
            encodeElement('SeekID', encodeElementName('Tracks'))
            . encodeElement('SeekPosition', chr($lenSeekHead + strlen($info)))
        )
        . encodeElement('Seek',
            encodeElement('SeekID', encodeElementName('Cues'))
            . encodeElement('SeekPosition', chr($lenSeekHead + strlen($info) + strlen($tracks)))
        )
        . encodeElement('Seek',
            encodeElement('SeekID', encodeElementName('Cluster'))
            . encodeElement('SeekPosition', chr($lenSeekHead + strlen($info) + strlen($tracks) + $lenCues))
        )
    );
    $cluster = "\x1F\x43\xB6\x75\x08" . pack('N', $size + 13) . (
    //. encodeElement('Cluster',
        encodeElement('Timecode', "\x00")
        . "\xA3\x08" . pack('N', $size + 4) . ("\x81\x00\x00\x80" . $data)
        //. encodeElement('SimpleBlock', "\x81\x00\x00\x80" . $data)
    );
    $segment = "\x18\x53\x80\x67\x08" . pack('N', $size + 173) . (
    // . encodeElement('Segment',
        $seekHead . $info . $tracks . $cues . $cluster
    );
    return $ebml . $segment;
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
        $data['frame'] = muxVPxFrame($pixelWidth, $pixelHeight, $codecID, $frame->readAll());

    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    fclose($fileHandle);
    return $data;
}
