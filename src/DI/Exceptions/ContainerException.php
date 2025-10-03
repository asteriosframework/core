<?php

namespace Asterios\Core\DI\Exceptions;

use Asterios\Core\DI\Psr\Container\ContainerExceptionInterface;
use Exception;

class ContainerException extends Exception implements ContainerExceptionInterface
{
}
