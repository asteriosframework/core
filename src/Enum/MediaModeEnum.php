<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

enum MediaModeEnum
{
    case BASE;
    case IMAGE;
    case GALLERY;
    case DOCUMENT;
    case STUDIO;

    /**
     * @return string
     */
    public function mode(): string
    {
        return match ($this)
        {
            self::BASE => 'base',
            self::IMAGE => 'images',
            self::GALLERY => 'gallery',
            self::DOCUMENT => 'documents',
            self::STUDIO => 'studio',
        };
    }
}
