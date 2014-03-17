<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = require_once __DIR__ . '/../bootstrap.php';

$app->get(
    '/',
    function (Request $request) use ($app) {
        $EXTRA  = $app['extra'];
        $output = <<<DRINKYDRINKY_OUT
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Drinky Drinky Thing, a whateverthing project</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">

    <link href="/components/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="/css/style.css" rel="stylesheet"/>
    <style type="text/css">
    div.place {
        padding: 8px 8px 8px 8px;
        background-color: #aaa;
    }

    div.place:nth-child(odd) {
        background-color: #ddd;
    }

    .footer {
        text-align: center;
    }
    </style>
</head>
<body>
<div class="container">
<h1>Drinky Drinky Thing</h1>
<h5><small>Accuracy is not guaranteed. Tailored for Victoria, BC, and surrounding areas.</small></h5>
<h4>Please don't drink and drive. Call a cab (#TAXI / #8294 on any cellphone) or use BC Transit.</h4>
<h4>Nearby liquor establishments of many types:</h4>
<div id="nearby"></div>
</div>
<div class="footer">
a <a href="http://whateverthing.com">whateverthing</a> project
</div>
<script src="/components/jquery/jquery.min.js"          type="text/javascript"></script>
<script src="/components/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

<script type="text/javascript">
    function success(pos) {
        crd = pos.coords;
        /*document.getElementById('loc').innerHTML = crd.latitude + ', ' + crd.longitude;*/
        $.post( "/nearby", {'lat':crd.latitude, 'long':crd.longitude}, function(data, textStatus) {
            var items = [];
            $.each( data, function( key, val ) {
                /*items.push( "<li id='res" + val.id + "'><a href='/restaurant/" + val.id + "'>" + val.name + "</a> <small>" + Math.round(val.distance * 100) / 100 + " km</small></li>" );*/
                items.push(
                    '<div class="place"><div class="row">' +
                    '<div class="col-xs-8 col-md-8"><strong>' + val.name + '</strong></div>' +
                    '<div class="col-xs-4 col-md-4">' + Math.round(val.distance * 100) / 100 + 'km</div>' +
                    '</div><div class="row">' +
                    '<div class="col-xs-8 col-md-8"><small>' + val.address1 + " " + val.address2 + ", " + val.city + '</small></div>' +
                    '<div class="col-xs-4 col-md-4"><small>' + val.type + '</small></div>' +
                    '</div></div>'
                );
            });
            $( "<div/>", {
                "class": "my-new-list",
                html: items.join( "" )
            }).replaceAll( "#nearby" );
        }, "json");
        console.log(crd.latitude + ', ' + crd.longitude);
    }

if (navigator.geolocation) {
    $('#nearby').html('<p class="lead">GeoLocating ... Please wait. <small>Unless you said no to the popup, in which case, you will be waiting a long, long time.</small></p>');
    navigator.geolocation.watchPosition(success);
}
else
{
    $('#nearby').replaceAll('<p class="lead">GeoLocation is not supported by your browser. Abandon all hope, ye who enter here.</p>');
}
</script>
$EXTRA
</body>
</html>
DRINKYDRINKY_OUT;

        return new Response($output);
    }
);

$app->post(
    '/nearby',
    function (Request $request) use ($app) {
        // prepare things
        $db     = $app['db'];
        $query  = <<<SQL
SELECT
  *,
  (
    6371
    *
    acos(
      cos(radians(:lat))
      *
      cos(radians(latitude))
      *
      cos(radians(longitude)-radians(:long))
      +
      sin(radians(:lat))
      *
      sin(radians(latitude))
    )
  ) AS distance
FROM places
HAVING distance < :radius
ORDER BY distance
LIMIT 0 , 25
SQL;

        // grab parameters
        $lat    = $request->get('lat');
        $long   = $request->get('long');
        $radius = $request->get('radius');

        // rudimentary filtering of parameters
        $radius = in_array((int)$radius, array(1,5,20,40)) ? (int)$radius : 5;

        // lets do this
        $result = $db->executeQuery(
            $query,
            array(
                'lat'    => $lat,
                'long'   => $long,
                'radius' => $radius
            )
        );

        // we like arrays, although we're not necessarily fans of this notation
        $locations = $result->fetchAll(\PDO::FETCH_ASSOC);

        return new JsonResponse($locations);
    }
);

$app->run();