# Tinyboard - A lightweight PHP imageboard.

## About
Tinyboard is an imageboard software package written in PHP. It aims to maintain a stable, fast, clean and user-friendly engine for imageboards. Development for Tinyboard started in October 2010 and the project is being lead by [OmegaSDG][o] ("Omega Software Develop Group"). You can contact the development team over IRC at irc.n0v4.com #tinyboard.

Tinyboard is not currently at a stable state.

[o]: http://omegadev.org/

## Installation
 1. Tinyboard requires a MySQL database and a user to work. Create one.
 2. Import 'install.sql' into the database. There are several ways to do this.
	- using phpMyAdmin
	- `mysql -uUSERNAME -pPASSWORD DATABASE < install.sql`
 3. Edit '[inc/config.php][c]' to suit your installation
 4. Make sure that the directories used by Tinyboard are writable. Depending on your setup, you may need to `chmod` the directories to 777.
  The default directories are:
	- ./res
	- ./src
	- ./thumb
	- . (document root)
 5. Ensure everything is okay by running [test.php][t] in a browser. The script will try and help you correct your errors.
 6. Run the [post.php][p] script. It should create an index.html and redirect you to it if everything is okay.
 7. Optional (highly recommended): Delete [test.php][t] and perhaps [install.sql][i] and this [README][r].

[t]: http://github.com/savetheinternet/Tinyboard/blob/master/test.php
[p]: http://github.com/savetheinternet/Tinyboard/blob/master/post.php
[c]: http://github.com/savetheinternet/Tinyboard/blob/master/inc/config.php
[i]: http://github.com/savetheinternet/Tinyboard/blob/master/install.sql
[r]: http://github.com/savetheinternet/Tinyboard/blob/master/README.md

## License
Copyright (c) 2010 by Omega Software Development Group

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above copyright
notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.