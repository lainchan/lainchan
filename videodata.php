<?php
require dirname(__FILE__) . '/matroska.php';

function matroskaSeekElement($name, $pos) {
    return ebmlEncodeElement('Seek',
        ebmlEncodeElement('SeekID', ebmlEncodeElementName($name))
        . ebmlEncodeElement('SeekPosition', pack('N', $pos))
    );
}

// Make video from single VPx keyframe
function muxVPxFrame($trackNumber, $videoTrack, $frame) {
    $lenSeekHead = 73;
    $lenCues = 24;
    $ebml = ebmlEncodeElement('EBML',
        ebmlEncodeElement('DocType', "webm")
        . ebmlEncodeElement('DocTypeVersion', "\x02")
        . ebmlEncodeElement('DocTypeReadVersion', "\x02")
    );
    $info = ebmlEncodeElement('Info',
        ebmlEncodeElement('Duration', "\x41\x20\x00\x00")
        . ebmlEncodeElement('MuxingApp', 'ccframe')
        . ebmlEncodeElement('WritingApp', 'ccframe')
    );
    $tracks = ebmlEncodeElement('Tracks',
        ebmlEncodeElement('TrackEntry', $videoTrack->content()->readAll())
    );
    $cues = ebmlEncodeElement('Cues',
        ebmlEncodeElement('CuePoint',
            ebmlEncodeElement('CueTime', "\x00")
            . ebmlEncodeElement('CueTrackPositions',
                ebmlEncodeElement('CueTrack', pack('N', $trackNumber))
                . ebmlEncodeElement('CueClusterPosition', pack('N', $lenSeekHead + strlen($info) + strlen($tracks) + $lenCues))
            )
        )
    );
    if (strlen($cues) != $lenCues) throw new Exception('length of Cues element wrong');
    $cluster = ebmlEncodeElement('Cluster',
        ebmlEncodeElement('Timecode', "\x00")
        . ebmlEncodeElement($frame->name(), $frame->content()->readAll())
        . ebmlEncodeElement('Void', '')
    );
    $seekHead = ebmlEncodeElement('SeekHead',
        matroskaSeekElement('Info', $lenSeekHead)
        . matroskaSeekElement('Tracks', $lenSeekHead + strlen($info))
        . matroskaSeekElement('Cues', $lenSeekHead + strlen($info) + strlen($tracks))
        . matroskaSeekElement('Cluster', $lenSeekHead + strlen($info) + strlen($tracks) + $lenCues)
    );
    if (strlen($seekHead) != $lenSeekHead) throw new Exception('length of SeekHead element wrong');
    $segment = ebmlEncodeElement('Segment', $seekHead . $info . $tracks . $cues . $cluster);
    return $ebml . $segment;
}

// Locate first VPx keyframe of track $trackNumber after timecode $skip
function firstVPxFrame($segment, $trackNumber, $skip=0) {
    foreach($segment as $x1) {
        if ($x1->name() == 'Cluster') {
            $cluserTimecode = $x1->Get('Timecode');
            foreach($x1 as $blockGroup) {
                $blockRaw = NULL;
                if ($blockGroup->name() == 'SimpleBlock') {
                    $blockRaw = $blockGroup->value();
                } elseif ($blockGroup->name() == 'BlockGroup') {
                    $blockRaw = $blockGroup->get('Block');
                }
                if (isset($blockRaw)) {
                    $block = new MatroskaBlock($blockRaw);
                    if ($block->trackNumber == $trackNumber && $block->keyframe) {
                        if (!isset($cluserTimecode) || $cluserTimecode + $block->timecode >= $skip) {
                            return $blockGroup;
                        } elseif (!isset($frame1)) {
                            $frame1 = $blockGroup;
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
        $data['frame'] = muxVPxFrame($trackNumber, $videoTrack, $frame);

    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    fclose($fileHandle);
    return $data;
}
