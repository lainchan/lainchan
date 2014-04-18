vichan - A lightweight and full featured PHP imageboard.
========================================================

About
------------
vichan is a free light-weight, fast, highly configurable and user-friendly
imageboard software package. It is written in PHP and has few dependencies.

vichan is a fork of [Tinyboard](http://tinyboard.org/), a great imageboard package, actively building on it
and adding a lot of features and another improvements.

Support and announcements: https://int.vichan.net/devel/

Requirements
------------
1.	PHP >= 5.3
2.	MySQL/MariaDB server
3.	[mbstring](http://www.php.net/manual/en/mbstring.installation.php) 
4.	[PHP GD](http://www.php.net/manual/en/intro.image.php)
5.	[PHP PDO](http://www.php.net/manual/en/intro.pdo.php)

We try to make sure vichan is compatible with all major web servers and
operating systems. vichan does not include an Apache ```.htaccess``` file nor does
it need one.

### Recommended
1.	MySQL/MariaDB server >= 5.5.3
2.	ImageMagick (command-line ImageMagick or GraphicsMagick preferred).
3.	[APC (Alternative PHP Cache)](http://php.net/manual/en/book.apc.php), [XCache](http://xcache.lighttpd.net/) or [Memcached](http://www.php.net/manual/en/intro.memcached.php)

Contributing
------------
You can contribute to vichan by:
*	Developing patches/improvements/translations and using GitHub to submit pull requests
*	Providing feedback and suggestions
*	Writing/editing documentation

If you need help developing a patch, please join our IRC channel.

Installation
-------------
1.	Download and extract Tinyboard to your web directory or get the latest
	development version with:

        git clone git://github.com/vichan-devel/vichan.git
	
2.	Navigate to ```install.php``` in your web browser and follow the
	prompts.
3.	vichan should now be installed. Log in to ```mod.php``` with the
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
*	Check out an [official vichan board](http://int.vichan.net/devel/).
*	You can join vichan's IRC channel for support [irc.6irc.net #vichan-devel](irc://irc.6irc.net/vichan-devel)

Tinyboard support
-----------------
vichan is based on a Tinyboard, so both engines have very much in common. These
links may be helpful for you. 

*	Tinyboard documentation can be found [here](http://tinyboard.org/docs/).
*	You can join Tinyboard's IRC channel for support and general queries: 
	[irc.datnode.net #tinyboard](irc://irc.datnode.net/tinyboard).
*	You may find help at [tinyboard.org](http://tinyboard.org/#help).

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
Wanting to have vichan in your language? You can contribute your translations at this URL:

https://www.transifex.com/organization/6ircnet/dashboard/tinyboard-vichan-devel

License
--------
See [LICENSE.md](http://github.com/vichan-devel/vichan/blob/master/LICENSE.md).

