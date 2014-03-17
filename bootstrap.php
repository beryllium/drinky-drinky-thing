<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config.php';

$app = new Silex\Application();

$app['debug'] = isset($debug) ? $debug : false;
$app['extra'] = isset($extra) ? $extra : false;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => $db_settings,
        'db.dbal.class_path' => __DIR__ . '/vendors/dbal/lib',
        'db.common.class_path' => __DIR__ . '/vendors/common/lib',
    ));

$app->register(new \Geocoder\Provider\GeocoderServiceProvider());

// we configure our provider here
$app['geocoder.provider'] = $app->share(function () use ($app) {
        return new \Geocoder\Provider\OpenStreetMapProvider($app['geocoder.adapter']);
    });

return $app;