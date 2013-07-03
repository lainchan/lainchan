Tinyboard - A lightweight PHP imageboard.
==========================================

Tinyboard + vichan-devel
------------
Tinyboard branch taking lightweightness somewhat more liberally. Running live at
https://pl.vichan.net/ (Polish) and http://vichan.net/ (International; may be outdated).

It contains many changes from original Tinyboard, mainly in frontend area.

About
------------
Tinyboard is a light-weight, fast, highly configurable and user-friendly
imageboard software package released under a non-restrictive open-source
license. It is written in PHP and has few dependencies.

Requirements
------------
1.	PHP >= 5.2.5
2.	[mbstring](http://www.php.net/manual/en/mbstring.installation.php) 
	(--enable-mbstring)
3.	[PHP-GD](http://php.net/manual/en/book.image.php)
4.	[PHP-PDO](http://php.net/manual/en/book.pdo.php) 
	(only MySQL is supported at the moment)

We try to make sure Tinyboard is compatible with all major web servers and
operating systems. Tinyboard does not include an Apache ```.htaccess``` file nor does
it need one.

Contributing
------------
You can contribute to Tinyboard by:
*	Developing patches/improvements/translations and using GitHub to submit pull requests
*	Providing feedback and suggestions
*	Writing/editing documentation

If you need help developing a patch, please join our IRC channel.

Installation
-------------
1.	Download and extract Tinyboard to your web directory or get the latest
	development version with:

        git clone git://github.com/savetheinternet/Tinyboard.git
	
2.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
3.	Tinyboard should now be installed. Log in to ```mod.php``` with the
	default username and password combination: **admin / password**.

Please remember to change the administrator account password.

See also: [Configuration Basics](http://tinyboard.org/docs/?p=Config).

Support
--------
Tinyboard is still beta software -- there are bound to be bugs. If you find a
bug, please report it.

If you need assistance with installing, configuring, or using Tinyboard, you may
find support from a variety of sources:

*	If you're unsure about how to enable or configure certain features, make
	sure you have read the comments in ```inc/config.php```.
*	Documentation can be found [here](http://tinyboard.org/docs/).
*	You can join Tinyboard's IRC channel for support and general queries: 
	[irc.datnode.net #tinyboard](irc://irc.datnode.net/tinyboard).
*	You can find enterprise-grade support at [tinyboard.org](http://tinyboard.org/#support).

License
--------
See [LICENSE.md](http://github.com/savetheinternet/Tinyboard/blob/master/LICENSE.md).

