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

    /**
     * @param string $language
     * @return string
     */
    public function translate(string $language = 'de'): string
    {
        if ($language === 'de')
        {
            return match ($this)
            {
                self::BASE => 'Basis',
                self::IMAGE => 'Bilder',
                self::GALLERY => 'Galerie',
                self::DOCUMENT => 'Dokumente',
                self::STUDIO => 'Studio',
            };
        }

        return match ($this)
        {
            self::BASE => 'Base',
            self::IMAGE => 'Images',
            self::GALLERY => 'Gallery',
            self::DOCUMENT => 'Documents',
            self::STUDIO => 'Studio',
        };

    }
}
