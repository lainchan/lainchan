Lainchan - A fork of vichan
========================================================

About
------------
Lainchan is a fork of [vichan](http://github.com/vichan-devel/vichan),
a great imageboard package, actively building on it and adding a lot of features and other
improvements.

Requirements
------------
1.	PHP >= 5.4, <= 7.3 (we still try to keep compatibility with php 5.3 as much as possible)
2.	MySQL/MariaDB server
3.	[mbstring](http://www.php.net/manual/en/mbstring.installation.php) 
4.	[PHP GD](http://www.php.net/manual/en/intro.image.php)
5.	[PHP PDO](http://www.php.net/manual/en/intro.pdo.php)

We try to make sure lainchan is compatible with all major web servers and
operating systems. lainchan does not include an Apache ```.htaccess``` file nor does
it need one.

### Recommended
1.	MySQL/MariaDB server >= 5.5.3
2.	ImageMagick (command-line ImageMagick or GraphicsMagick preferred).
3.	[APC (Alternative PHP Cache)](http://php.net/manual/en/book.apc.php),
	[XCache](http://xcache.lighttpd.net/) or
	[Memcached](http://www.php.net/manual/en/intro.memcached.php)

Contributing
------------
You can contribute to lainchan by:
*	Developing patches/improvements/translations and using GitHub to submit pull requests
*	Providing feedback and suggestions
*	Writing/editing documentation

If you need help developing a patch, please join our IRC channel. 

> irc.lainchan.org:6697 @ #thecage

Installation
-------------
1.	Download and extract lainchan to your web directory or get the latest
	development version with:

        git clone git://github.com/lainchan/lainchan.git
	
2.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
3.	lainchan should now be installed. Log in to ```mod.php``` with the
	default username and password combination: **admin / password**.

Please remember to change the administrator account password.

See also: [Configuration Basics](https://web.archive.org/web/20121003095922/http://tinyboard.org/docs/?p=Config).

Upgrade
-------
To upgrade from any version of Tinyboard or vichan:

Either run ```git pull``` to update your files, if you used git, or
backup your ```inc/instance-config.php```, replace all your files in place
(don't remove boards etc.), then put ```inc/instance-config.php``` back and
finally run ```install.php```.

To migrate from a Kusaba X board, use http://github.com/vichan-devel/Tinyboard-Migration

Support
--------
If you find a bug, please report it.

If you need assistance with installing, configuring, or using lainchan, you may
find support from a variety of sources:

*	If you're unsure about how to enable or configure certain features, make
	sure you have read the comments in ```inc/config.php```.
*	You can join lainchan's IRC channel for support
	[irc.freenode.net #lainchan](irc://irc.freenode.net/lainchan)

### Tinyboard support
vichan, and by extension lainchan, is based on a Tinyboard, so both engines have very much in common. These
links may be helpful for you as well: 

*	Tinyboard documentation can be found [here](https://web.archive.org/web/20121016074303/http://tinyboard.org/docs/?p=Main_Page).

CLI tools
-----------------
There are a few command line interface tools, based on Tinyboard-Tools. These need
to be launched from a Unix shell account (SSH, or something). They are located in a ```tools/```
directory.

You actually don't need these tools for your imageboard functioning, they are aimed
at the power users. You won't be able to run these from shared hosting accounts
(i.e. all free web servers).

Localisation
------------
Wanting to have lainchan in your language? You can contribute your translations to vichan at this URL:

https://www.transifex.com/projects/p/tinyboard-vichan-devel/

Oekaki
------
lainchan makes use of [wPaint](https://github.com/websanova/wPaint) for oekaki. After you pull the repository, however, you will need to download wPaint separately using git's `submodule` feature. Use the following commands:

```
git submodule init
git submodule update
```

To enable oekaki, add all the scripts listed in `js/wpaint.js` to your `instance-config.php`.

WebM support
------------
Read `inc/lib/webm/README.md` for information about enabling webm.

lainchan API
----------
lainchan provides by default a 4chan-compatible JSON API, just like vichan. For documentation on this, see:
https://github.com/vichan-devel/vichan-API/ .

License
--------
See [LICENSE.md](http://github.com/lainchan/lainchan/blob/master/LICENSE.md).

