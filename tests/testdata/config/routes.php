<?php
declare(strict_types=1);

return [
    '/v1/form/current/(\w+)' => [
        ['GET', 'ControllerForm/current_one'],
    ],
];