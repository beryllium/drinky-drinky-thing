DrinkyDrinkyThing
=================

DrinkyDrinkyThing is a geocoding and geolocation demonstration app built in Silex using the [Geocoder-PHP library](http://geocoder-php.org/) and a dataset of liquor-licensed establishments from the government of British Columbia, Canada.

**Blog Post:** http://whateverthing.com/blog/2014/03/18/search-nearby-in-silex/

**Live Demo Site:** http://drinkydrinky.grubthing.com/ (Note: only contains data for the Vancouver and Victoria area, so if you're not loading it from there, don't expect any results :) )

### Configuration

Create a file called config.php:

    // config.php
    <?php
    $db_settings = array(
        'driver'   => 'pdo_mysql',
        'dbname'   => DB_NAME,
        'user'     => DB_USER,
        'password' => DB_PASS,
        'host'     => DB_HOST,
        'port'     => DB_PORT,
    );

    // optional, to enable debugging information:
    // $debug = true;

### Database

The DB schema is provided in schema/db\_schema.sql - it's based on the CSV headers for the BC liquor licence dataset, but it could be easily cleaned up for other data as well.

---
A [whateverthing](http://whateverthing.com) project.
