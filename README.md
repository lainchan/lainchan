This project is an effort to enable imageboards to host small video clips.  With modern video compression, it's possible to share much higher-quality videos in a few megabytes than what you can do with animated GIF files.

The software here extends [Tinyboard](http://tinyboard.org/) to display metadata and create pseudo-thumbnails for WebM video files.  It is intended to work on very basic web hosting services, including any hosting service that can run Tinyboard.  In particular, it does not depend on any external video conversion software such as FFmpeg or Libav.  Rather, it parses the video container to extract a single frame from the video to use in place of a thumbnail.  If you can run FFmpeg or Libav on your server, it's a good idea to modify this code to use those tools to create true thumbnails; in the future, an option will be added to enable this.

A board using this code can be found at:
http://containerchan.org/tb/demo/

Be aware that this is beta software.  Please report any bugs you find.

Much of the code is not specific to Tinyboard, and you are welcome to use it in your own projects.  See the [core](https://github.com/ccd0/containerchan/tree/core) branch for a version without material from Tinyboard.

Installation
------------

Create a directory named cc at the root of your Tinyboard installation.  Upload these files into that directory.

Replace the files templates/post_thread.html and templates/post_reply.html with the files given here.

Move video.png to the static directory.

Add these lines to inc/instance-config.php:

    $config['allowed_ext_files'][] = 'webm';
    $config['file_icons']['webm'] = 'video.png';
    $config['additional_javascript'][] = 'cc/settings.js';
    $config['additional_javascript'][] = 'cc/expandvideo.js';
    require_once 'cc/posthandler.php';
    event_handler('post', 'postHandler');

And add this to stylesheets/style.css:

    video.post-image {
        display: block;
        float: left;
        margin: 10px 20px;
        border: none;
    }
    div.post video.post-image {
        padding: 0px;
        margin: 10px 25px 5px 5px;
    }
    span.settings {
        position: fixed;
        top: 1em;
        right: 1em;
    }

License
-------

See [LICENSE.md](https://github.com/ccd0/containerchan/blob/master/LICENSE.md).
