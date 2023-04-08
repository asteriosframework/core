<?php
declare(strict_types=1);

return [
    '/v1/foo/(\w+)' => [
        ['GET', 'ControllerFoo/current_one'],
    ],
    'v1'            => [
        'foo/bar/(\w+)' => [
            ['GET', 'ControllerFoo/bar'],
        ],
    ],
    'v2'            => [
        'foo/bar/(\d+)' => [
            ['GET', 'ControllerFoo/bar_one'],
        ],
    ],
];