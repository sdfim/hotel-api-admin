<?php

return [
    /*
     | Your Google Maps API key, usually set in .env (but see 'keys' section below).
     */

    'key' => env('GOOGLE_API_DEVELOPER_KEY'),

    /*
     | If you need to use both a browser key (restricted by HTTP Referrer) for use in the Javascript API on the
     | front end, and a server key (restricted by IP address) for server side API calls, you will need to set those
     | keys here (or preferably set the appropriate env keys).  You may also set a signing key here for use with
     | static map generation.
     */

    'keys' => [
        'web_key'     => env('FILAMENT_GOOGLE_MAPS_WEB_API_KEY', env('GOOGLE_API_DEVELOPER_KEY')),
        'server_key'  => env('FILAMENT_GOOGLE_MAPS_SERVER_API_KEY', env('GOOGLE_API_DEVELOPER_KEY')),
        'signing_key' => env('FILAMENT_GOOGLE_MAPS_SIGNING_KEY', null),
    ],
];
