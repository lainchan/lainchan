This project is an effort to enable imageboards to host small video clips.  With modern video compression, it's possible to share much higher-quality videos in a few megabytes than the with animated GIF files.

The software here extends [Tinyboard](http://tinyboard.org/) to display metadata and create pseudo-thumbnails for WebM video files.  It is intended to work on very basic web hosting services, including any hosting service that can run Tinyboard.  In particular, it does not depend on any video conversion software such as FFmpeg.  For this reason, it cannot create true thumbnails, but uses pseudo-thumbnails consisting of a single frame extracted from the video.

A board using this code can be found at:
http://containerchan.org/tb/demo/

Be aware that this is beta software.  Please report any bugs you find.

The modified Tinyboard templates (post_reply.html and post_thread.html) are subject to the Tinyboard licence (see LICENSE.md).  The portions of this software not derived from Tinyboard are released into the public domain.


INSTALLATION
------------

Create a directory named cc at the root of your Tinyboard installation.  Upload these files into that directory.

Replace the files templates/post_thread.html and templates/post_reply.html with the files given here.

Add these lines to inc/instance-config.php:

    $config['allowed_ext_files'][] = 'webm';
    $config['additional_javascript'][] = 'cc/settings.js';
    $config['additional_javascript'][] = 'cc/expandvideo.js';
    require_once 'cc/posthandler.php';
    event_handler('post', 'postHandler');

And add this to stylesheets/style.css:

    video.post-image {display: block; float: left; margin: 10px 20px; border: none;}
