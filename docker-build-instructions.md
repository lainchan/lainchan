# Easy build instructions for the lainchan codebase
Currently the supported method of running lainchan in prod is on bare metal but to keep it simple for development purposes this guide will walk you through setting up a lainchan docker container.
## The guide
1. The software used:
lainchan currently runs on PHP 8.1 with multiple extensions:
``pdo_mysql, gettext, gd, intl, zip``, these will be installed in the Dockerfile along with their dependencies. Additionally in this guide we will use Apache2 as the web server.

2. Clone the repository with ``$ git clone https://github.com/lainchan/lainchan``.

3. Add the Dockerfile into the root directory, you can use the following template: put the link here or smth idk.

4. You may need to change some of the configuration inside the Dockerfile to match your case such as the 000-default site configuration file which you can find on the [vichan wiki](https://vichan.info/index.php?title=Installation_Guide#Apache).

5. Once you are sure that all the important stuff has been put in place, build the Docker image with the ``docker build .`` command.

6. Once your Docker image is built without errors, you can run the image and access your lainchan instance at http://localhost:80/.

7. Once you verify that your instance is working without error you need to either run a MariaDB instance locally or in Docker. If you use Docker be sure to use the container IP address in the lainchan configuration dialog.

### The dependencies for the PHP extensions
- gd: ``libfreetype6-dev, libjpeg62-turbo-dev, libpng-dev``
- intl: ``libicu-dev``
- zip: ``libzip-dev, zip``
Optional:
- xml: ``libxml2-dev``
- pdo_mysql: none
- gettext: none