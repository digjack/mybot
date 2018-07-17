<?php

return [
    // Default app name
    'default_app' => 'default',

    // Base configuration
    'base' => [
        'debug' => true,
        'log' => [
            'name' => 'youzan',
            'file' => __DIR__.'/youzan.log',
            'level'      => 'debug',
            'permission' => 0777,
        ]
    ],

    // Applications
    'apps' => [
        'default' => [
            'client_id' => '3e504e69c383fed1ff',
            'client_secret' => '2af4f6acbd75ad47a9d09205f0864df4',
            'type' => \Hanson\Youzan\Youzan::PERSONAL, // 自用型应用
            'kdt_id' => '40935361', // store_id
        ],
        // 'another_app' => [
        //     'client_id' => 'XXXXXXXXX',
        //     'client_secret' => 'XXXXXXXXX',
        //     'redirect_uri' => 'http://YOURSITE.com/',
        // ],
        //
        // 'platform_app' => [
        //     'client_id' => '',
        //     'client_secret' => '',
        //     'type' => \Hanson\Youzan\Youzan::PLATFORM,
        // ],
    ]
];
