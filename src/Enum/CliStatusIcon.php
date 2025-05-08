<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

enum CliStatusIcon: string
{
    case Pending = '⏳';
    case Success = '✅';
    case Warning = '⚠️';
    case Danger = '❗';
    case Error = '❌';
    case Unknown = '❓';

    public function icon(bool $withSpace = true): string
    {
        return $this->value . ($withSpace ? '  ' : '');
    }
}
