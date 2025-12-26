<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\QrCode\Builder;

interface QCodeBuilderResultInterface
{
    /**
     * @return string
     */
    public function getString(): string;

    /**
     * @param string $path
     */
    public function saveToFile(string $path): void;
}
