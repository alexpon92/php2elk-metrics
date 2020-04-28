<?php

return [
    'application' => env('APP_NAME'),

    'instance'    => gethostname(),

    /*
    |--------------------------------------------------------------------------
    | Elastic connections config
    |--------------------------------------------------------------------------
    |
    | You may use extended host config or simplified, for more info look at
    | https://www.elastic.co/guide/en/elasticsearch/client/php-api
    |
    |
    */
    'connections' => [
        // Extended hosts configuration example
        'default' => [
            [
                'host'   => env('PHP2ELK_HOST'),
                'port'   => env('PHP2ELK_PORT'),
                'scheme' => env('PHP2ELK_SCHEME'),
                'path'   => env('PHP2ELK_PATH'), //optional
                'user'   => env('PHP2ELK_USER'), //optional
                'pass'   => env('PHP2ELK_PASSWORD') //optional
            ]
        ],

        // Simple host configuration example
        'sample'  => [
            'http://example.com:9200/elastic'
        ]
    ],

    'default_index' => 'monitoring-index',

    /*
    |--------------------------------------------------------------------------
    | Elastic index settings
    |--------------------------------------------------------------------------
    |
    | You should specify this settings if you would like to create monitoring index
    | and mappings automatically by using packet artisan command (php2elk:setup-index)
    | You may use all settings, which is supported by official client
    |
    | More info about index configuration you can find here:
    | https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index_management.html
    |
    */
    'index_settings' => [
        'monitoring-index' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Collectors configuration
    |--------------------------------------------------------------------------
    |
    | Failed Queues:
    | Currently supports only database connection driver for failed queues
    | For failed queues you should specify database connection and failed queue table name
    |
    | Postgres Dead Rows:
    | You should specify database connections to check, minimum rows number to skip small tables,
    | minimum dead rows percent, to skip table with insignificant dead rows percent
    |
    */
    'collectors'    => [
        'failed_queue' => [
            'db_connection' => config('queue.database'),
            'table_name'    => config('queue.failed.table')
        ],

        'postgres' => [
            'dead_rows' => [
                'min_live_tuples'       => 50000,
                'min_percent_threshold' => 3.0,
                'db_connections'        => [
                    config('database.default')
                ]
            ]
        ]
    ],

    'listeners' => [
        'metrics_events' => [
            'connection' => 'redis',
            'queue'      => 'php2elk-metrics'
        ]
    ]
];
