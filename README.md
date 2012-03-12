Tinyboard -- A lightweight PHP imageboard.
==========================================

Tinyboard is a light-weight, fast, highly configurable and user-friendly
imageboard software package released under a non-restrictive open-source
license. It is written in PHP and has few dependencies.

Requirements
------------
1.	PHP >= 5.2.5
2.	[mbstring](http://www.php.net/manual/en/mbstring.installation.php) 
	(--enable-mbstring)
3.	[PHP-GD](http://php.net/manual/en/book.image.php)
4.	[PHP-PDO](http://php.net/manual/en/book.pdo.php) with appropriate <del>[driver for your database](http://www.php.net/manual/en/pdo.drivers.php)</del> (only MySQL is supported at the moment)

We try to make sure Tinyboard is compatible with all major web servers and
operating systems. Tinyboard does not include an Apache ```.htaccess``` file nor does
it need one.

Contributing
------------
Use GitHub to submit a pull request. If you need help developing a patch, join
our IRC channel.

Installation
-------------
1.	Download and extract Tinyboard to your web directory or get the latest
	development version with:
	
	 git clone git://github.com/savetheinternet/Tinyboard.git
	
2.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
3.	Tinyboard should now be installed. Log in to ```mod.php``` with the
	default username and password combination: **admin / password**. (You
	should probably change that.)

Support
--------
Tinyboard is still beta software -- there are bound to be bugs. If you find a
bug, please report it.

If you need assistance with installing, configuring or using Tinyboard, you may
find support from a variety of sources:

*	If you're unsure about how to enable or configure certain features, make
	sure you have read the comments in ```inc/config.php```.
*	Documentation can be found [here](http://tinyboard.org/docs/).
*	You can join Tinyboard's IRC channel for support and general queries: 
	[irc.datnode.net #tinyboard](irc://irc.datnode.net/tinyboard).

License
--------
See [LICENSE.md](http://github.com/savetheinternet/Tinyboard/blob/master/LICENSE.md).

