<?php declare(strict_types=1);

namespace Asterios\Core\Http;

final class Disposition
{
    private function __construct()
    {
    }

    public const string INLINE = 'inline';
    public const string ATTACHMENT = 'attachment';
}