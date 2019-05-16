# Taxasoft-BG
Taxasoft Botanic Garden (Collection Management System)

The Database program is Web based and consist 3 main PHP files and uses MySQL
- index1.php
- index1Form.php
- index1showImage.php
 
It can be used on a Linux Server or stand alone on a PC/MAC
- a Linux server has normally Apache2, PHP5 and MySQL running, which are needed for the program
- a MAC, running OS-X is unix (FreeBSD) based and can run similar programs
- a PC need WAMP, a package with Apache2, PHP5 and MySQL running on Windows

The Database program works with XML configuration files (.XML) and is
transparent, which means, it basically knows nothing about the Database and
Table structure.

Given a configuration, the software can initialize a database, or given a
database, you should be able to write the corresponding configuration.

Most functionality is built into a special include file and it features:
- Special task functions, activated from the menu
- Field functions
- Reports

The Database created is just a start and can be opened by: ``index1.php?create``

It is a nearly empty database for a start.

Installation instructions depend on whether you have already a web server on
your machine.  If not, and you're not interested in having a web server for
any other reason than taxasoft-bg, that's the easiest, and simply follow the
following:

Instructions for dedicated Apache

- install Ampps on Mac, LAMP for Linux or XAMP for Windows (which includes
  Apache2 webserver and MySQL server or a light version)
- create a sub-folder in the htdocs/html folder from which you want to work,
  for example taxasoft (it could be the html root htdocs/html)
- copy the files taxasoftBG files into this folder
- create the subfolders log and img (Apache2 does not have the rights to
  create the folders) and set ownership of these two folders to
  www-data/apache (depending the username Apache2 uses)
- also set ownership of the .jpg files to www-data/apache, these will be
  moved to the img folder
- create a database user with the proper rights with phpmyadmin and set the
  credentials in the file: guest.pass.php (see below what should be included
  in this file)
- start the the program localhost/path/index1.php?create in your browser, to
  create the database and tables and load ITF, WGS, Family and Genus data
  (this can take some minutes)

On the other hand, if you already have a web server, or plan to have a web
server doing anything else than serve Taxasoft-BG, you probably know a bit of
what we're talking about here above, so the task is more complex, but the
instructions may make some assumptions on what you know.

More detailed instructions, for unix (OSX|Linux)

- decide the location for your virtual host.  for ease of writing, let's
  assume ``/var/www/taxasoft/``;
- locate your apache configuration directory.  for ease of writing, let's
  assume it's ``/etc/apache2``;
- copy the complete checkout to your new virtual host files location;
- create two extra empty directories ``log`` and ``img``;
- configure the new virtual host;
  - move the ``010-taxasoft.conf`` file to ``/etc/apache2/sites-available``;
  - make a symlink to it from ``/etc/apache/site-enabled``;
  - add the ``Listen 8742`` directive in port.conf, unless you changed the
    port in the site configuration file;
  - reload the new configuration: ``sudo /etc/init.d/apache2 reload``
  - make sure your server serves php4;
- did you enable your new virtual host?
- double check file permissions, they must match your apache user;
  - check your apache user: ``ps aux | egrep '(apache|httpd)'``;
  - change permissions accordingly: ``sudo chown -R www-data.adm /var/www/taxasoft/``;
  - I assume you are in the ``adm`` group on your Unix system;
  - give yourself permissions to edit: ``sudo chmod -R g+w /var/www/taxasoft/``;
- configure your database user in ``guest.pass.php``;
  - this file is not included in the distribution, check further in this text;
- create the corresponding database and database user, with whatever rights you want;
  - ``CREATE DATABASE bg_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;``
  - ``CREATE USER 'guest'@'localhost' IDENTIFIED BY 'password';``
  - ``GRANT ALL PRIVILEGES ON bg_database . * TO 'guest'@'localhost';``
- direct your browser to the page http://localhost:8742/index1.php?create

Notes:

Genera.xml includes
- Taxon Author Combinations
- Family names
- Genara names and authors

Content of guest.pass.php (with your credentials for the database)::

    <?php
    $cfgUser = "database_user";
    $cfgPass = "password";
    $cfgHost = "localhost";		//* normally "localhost" depending on your ISP
    ?>
