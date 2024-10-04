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
            self::IMAGE => 'image',
            self::GALLERY => 'gallery',
            self::DOCUMENT => 'document',
            self::STUDIO => 'studio',
        };
    }

    /**
     * @return string[]
     */
    public function availableModes(): array
    {
        return [
            self::IMAGE->mode(),
            self::GALLERY->mode(),
            self::DOCUMENT->mode(),
            self::STUDIO->mode(),
        ];
    }
}
