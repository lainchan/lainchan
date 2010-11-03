# Tinyboard - A lightweight PHP imageboard.

## Installation
 1. Tinyboard requires a MySQL database and a user to work. Create one.
 2. Import 'install.sql' into the database. There are several ways to do this.
	- using phpMyAdmin
	- `mysql -uUSERNAME -pPASSWORD DATABASE < install.sql`
 3. Edit '[inc/config.php][c]' to suit your installation
 4. Make sure that the directories used by TinyBoard are writable. Depending on your setup, you may need to `chmod` the directories to 777.
  The default directories are:
	- ./res
	- ./src
	- ./thumb
	- ./
 5. Ensure everything is okay by running [test.php][t] in a browser. The script will try and help you correct your errors.
 6. Run the [post.php][p] script. It should create an index.html and redirect you to it if everything is okay.
 7. Optional (highly recommended): Delete [test.php][t] and perhaps [install.sql][i] and this [README][r]

[t]: http://github.com/savetheinternet/Tinyboard/blob/master/test.php
[p]: http://github.com/savetheinternet/Tinyboard/blob/master/post.php
[c]: http://github.com/savetheinternet/Tinyboard/blob/master/inc/config.php
[i]: http://github.com/savetheinternet/Tinyboard/blob/master/install.sql
[r]: http://github.com/savetheinternet/Tinyboard/blob/master/README.md