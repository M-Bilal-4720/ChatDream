<?php

return [

    'paths' => ['api/*', 'broadcasting/auth'], // Add 'broadcasting/auth' to paths

    'allowed_methods' => ['*'], // Allow all HTTP methods

    'allowed_origins' => ['http://localhost:8080','*'], // Specify your frontend URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Allow cookies and credentials to be sent
];
