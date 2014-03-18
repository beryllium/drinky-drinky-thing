var refreshCounter = 0;
window.refreshFlag = false;

$(document).ready(function() {
    if (navigator.geolocation) {
        $('#nearby').html('<p class="lead">GeoLocating ... Please wait. <small>Unless you\'re on a desktop, in which case you will be waiting a long, long time.</small></p>');
        navigator.geolocation.watchPosition(
            function (pos) {
                if (undefined === window.currentCoords) {
                    window.currentCoords = pos.coords;
                    window.refreshFlag = true;
                }
                if (undefined === window.previousCoords) {
                    window.previousCoords = pos.coords;
                    window.refreshFlag = true;
                }

                window.previousCoords = window.currentCoords;
                window.currentCoords  = pos.coords;

                // if the user has moved approximately 20 feet from their current location, we trigger a refresh
                // otherwise, we just go with the current data & restore the original location
                // (if we didn't restore the original location, smaller distance increments would preclude a refresh)
                if (Math.abs(window.currentCoords.latitude - window.previousCoords.latitude) > 0.00005 ||
                    Math.abs(window.currentCoords.longitude - window.previousCoords.longitude) > 0.00005
                ) {
                    window.refreshFlag = true;
                } else {
                    window.currentCoords = window.previousCoords;
                }
            },
            function (error) {
                window.currentCoords = false;
                window.refreshFlag   = false;
            }
        );
    }
    else
    {
        $('#nearby').replaceAll('<p class="lead">GeoLocation is not supported by your browser. Abandon all hope, ye who enter here.</p>');
    }
    setTimeout(function() {
        lookupLocations();
    }, 800);
    setInterval(function() {
        lookupLocations();
    }, 5000);
});

function lookupLocations() {
    if (null === window.currentCoords) {
        $('#nearby').html('<p class="lead">GeoLocation is currently disabled or not responding properly in your browser.</p>');
        return;
    }
    if (false === window.currentCoords) {
        $('#nearby').html('<p class="lead">Unable to pinpoint your location. Perhaps location services are disabled?</p>');
        return;
    }
    if (false === refreshFlag) {
        return;
    }

    $.post(
        "/nearby",
        {'lat':window.currentCoords.latitude, 'long':window.currentCoords.longitude},
        function(data, textStatus) {
            var items = [];
            $.each( data, function( key, val ) {
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
                "class": "populated-list" + refreshCounter++,
                "id": "nearby",
                html: items.join( "" )
            }).replaceAll( "#nearby" );
            refreshFlag = false;
        },
        "json"
    );
}