<?php

declare(strict_types=1);

namespace Asterios\Core\Athene\Exception;

use Exception;

class ModelValidateException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Model validation exception';
}