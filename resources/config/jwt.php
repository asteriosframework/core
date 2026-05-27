<?php

declare(strict_types=1);

use Asterios\Core\Asterios;
use Asterios\Core\Env;

$env = (new Env(Asterios::getBasePath() . DIRECTORY_SEPARATOR . '.env'));

return [
    'secret' => $env->get('JWT_SECRET', 'db'),
];
