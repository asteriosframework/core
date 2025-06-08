<?php declare(strict_types=1);

use Asterios\Core\Asterios;

return [
    'environment' => Asterios::FEATURE,
    'timezone' => 'Europe/Berlin',
    'app' => [
        'name' => 'Test App',
        'copyright' => 'true',
    ],
    'template' => [
        'extension' => 'htm.php',
    ],
];
