<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

enum MediaTypeEnum
{
    case JPG;
    case JPEG;
    case PNG;
    case GIF;
    case PDF;

    public function type(): string
    {
        return match ($this)
        {
            self::JPG => 'jpg',
            self::JPEG => 'jpeg',
            self::PNG => 'png',
            self::GIF => 'gif',
            self::PDF => 'pdf',
        };
    }
}
