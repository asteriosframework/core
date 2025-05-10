<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface SingletonInterface
{
    public static function getInstance();
}