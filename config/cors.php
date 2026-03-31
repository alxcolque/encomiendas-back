<?php

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => ['https://encomiendas.culking.com', 'https://kolmox.com.bo', 'http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:5001', 'http://127.0.0.1:5001'],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true, // CAMBIAR A TRUE
];
