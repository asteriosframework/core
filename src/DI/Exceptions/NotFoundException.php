<?php

namespace Asterios\Core\DI\Exceptions;

use Asterios\Core\DI\Psr\Container\NotFoundExceptionInterface;
use Exception;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
