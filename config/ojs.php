<?php

return [

    'enabled' => env('OJS_ENABLED', false),

    'base_url' => env('OJS_BASE_URL'),

    'secret_key' => env('OJS_SECRET_KEY'),

    'journals' => [
        // Mapping journal LOA => journal OJS
    ],

];
