This directory contains files being the integration of containerchan with vichan-devel imageboards.

Containerchan allows posting of webm files, like they were the regular images.

An original board using this code can be found at:
http://containerchan.org/tb/demo/

The original repo containing the containerchan (possibly with no Tinyboard integration) can be found here:
https://github.com/ccd0/containerchan


Be aware that this is beta software.  Please report any bugs you find.

Installation
------------

Add these lines to inc/instance-config.php:

    $config['allowed_ext_files'][] = 'webm';
    $config['additional_javascript'][] = 'js/webm-settings.js';
    $config['additional_javascript'][] = 'js/expand-video.js';

License
-------

See [LICENSE.md](https://github.com/ccd0/containerchan/blob/master/LICENSE.md).
