<<<<<<< HEAD
# Tinyboard Tools
This repository is a collection of management scripts, javascript addons, stylesheets and miscellaneous tools for [Tinyboard](http://github.com/savetheinternet/Tinyboard).

## Directories
*	```tools/``` -- Command-line management scripts for Tinyboard. These should not be publicly executable.
*	```stylesheets/``` -- Additional stylesheets for Tinyboard, mainly user contributions.
*	```js/``` -- Useful Javascript addons for Tinyboard, such as "quick reply" and client-side "forced anonymous". Most of these can be included using ```$config['additional_javascript']```.


## License
The contents of this repository are licensed under the terms of [Tinyboard's license](https://github.com/savetheinternet/Tinyboard/blob/master/LICENSE.md) unless stated otherwise.
=======
# Kusaba X Database Migration

## About
This script pulls board information, posts and images from an already existing [Kusaba X][k] installation and replicates them in [Tinyboard][o]. It should be helpful for those who already use Kusaba X][k], and want to switch over to Tinyboard.
[o]: http://tinyboard.org/
[k]: http://kusabax.cultnet.net/

## Requirements
 1. [Tinyboard][o] >= v0.9.4

## Use
 1. Install Tinyboard (>= v0.9.4) normally.
 2. Download and place `kusabax.php` in the root folder of Tinyboard.
 3. Edit the script and fill in your Kusaba X configuration. You can find KU_RANDOMSEED from Kusaba X's config.php file.
 4. Run the script in a web browser.

## What's copied? (in the future, more will be added.)
 1. Basic board information
 2. Posts
 3. News

## Documentation
Visit the Tinyboard development wiki at <http://tinyboard.org/wiki/> for help.

## License
See [LICENSE.md][l] for the license.
[l]: http://github.com/savetheinternet/Tinyboard/blob/master/LICENSE.md
>>>>>>> cce8b3955d5df03a6adca5e5e11d2bef039cc917
