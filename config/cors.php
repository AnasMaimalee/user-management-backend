<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Important!

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3001'], // ← Your Nuxt port

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ← THIS IS KEY
];
