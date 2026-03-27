<?php
return [
    'strategies' => [
        'light' => [
            'requests' => 1000,
            'window' => 3600,
        ],
        'moderate' => [
            'requests' => 100,
            'window' => 3600,
        ],
        'strict' => [
            'requests' => 10,
            'window' => 3600,
        ],
        'auth' => [
            'requests' => 5,
            'window' => 900,
        ],
    ],
];
