<?php
declare(strict_types=1);

return [
    'logger' => [
        'path'     => 'logs',
        'filename' => 'asterios',
    ],
    'mail'   => [
        'server'    => [
            'port' => 993,
            'host' => 'example.tld',
        ],
        'templates' => [
            'login' => [
                'de' => [
                    'name'    => 'login.twig',
                    'content' => [
                        'data' => [
                            'title'   => 'Titel',
                            'content' => 'Hello World',
                        ],
                    ],
                ],
            ],
        ],
    ],
];