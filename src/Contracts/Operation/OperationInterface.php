<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Operation;

interface OperationInterface
{
    public function run(): void;
}