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

The Database program works with XML configuration files (.XML) and is transparent, which means, it basically knows nothing about the Database and Table structure.
Given a configuration, the software can initialize a database, or given a database, you should be able to write the corresponding configuration.

Most functionality is build into a special include file and it features:
- Special task functions, activated from the menu
- Field functions
- Reports

The Database created is just a start and can be opened by: ``index1.php?create``

It is a nearly empty database for a start.

Instructions

- install Ampps on Mac, LAMP for Linux or XAMP for Windows (which includes Apache2 webserver and MySQL server or a light version)
- create a sub-folder in the htdocs/html folder from which you want to work, for example taxasoft (it could be the html root htdocs/html)
- copy the files taxasoftBG files into this folder
- create the subfolders log and img (Apache2 does not have the rights to create the folders) and set ownership of these two folders to www-data/apache (depending the username Apache2 uses)
- also set ownership of the .jpg files to www-data/apache, these will be moved to the img folder
- create a database user with the proper rights with phpmyadmin and set the credentials in the file: guest.pass.php (see below what should be included in this file)
- start the the program localhost/path/index1.php?create in your browser, to create the database and tables and load ITF, WGS, Family and Genus data (this can take some minutes)

Notes:

Genera.xml includes
- Taxon Author Combinations
- Family names
- Genara names and authors

Content of guest.pass.php (with your credentials for the database):

<?php
$cfgUser = "database_user";
$cfgPass = "password";
$cfgHost = "localhost";		//* normally "localhost" depending on your ISP
?>
