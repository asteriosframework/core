<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

enum MediaEnum
{
    case BASE;
    case IMAGE;
    case GALLERY;
    case DOCUMENT;
    case STUDIO;

    public function type(): string
    {
        return match ($this)
        {
            self::BASE => 'base',
            self::IMAGE => 'image',
            self::GALLERY => 'gallery',
            self::DOCUMENT => 'document',
            self::STUDIO => 'studio',
        };
    }
}
