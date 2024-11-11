<?php

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_PUBLIC_KEY'),
        'index-settings' => [
            'articles' => [
                'filterableAttributes'=> ['category_id', 'user_id', 'created_at'],
                'sortableAttributes' => ['created_at'],
            ],
        ],
    ],

    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', false),
    'after_commit' => false,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),
];
