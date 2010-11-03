# Tinyboard - A lightweight PHP imageboard.

## Installation
 1. Tinyboard requires a MySQL database and a user to work. Create one.
 2. Import 'install.sql' into the database
  using phpMyAdmin
   OR
  mysql -uUSERNAME -pPASSWORD DATABASE < install.sql
 3. Edit 'inc/config.php' to suit your installation
 4. Make sure that the directories used by TinyBoard are writable. Depending on your setup, you may need to chmod the directories to 777.
  The default directories are
	- /res
	- /src
	- /thumb
  You will also need to chmod the root directory so that static HTML files like index.html can be made.
 5. Ensure everything is okay by running test.php in a browser. The script will try and help you correct your errors.
 6. Run the post.php script. It should create an index.html and redirect you to it if everything is okay.
 7. Optional (highly recommended): Delete test.php and perhaps install.sql and this README

