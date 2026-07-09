<?php

declare(strict_types=1);

namespace Asterios\Core\Http;

use ReflectionClass;

final class ContentType
{
    /**
     * @var array<int, string>|null
     */
    private static ?array $contentTypes = null;

    private function __construct()
    {
    }

    public const string PLAIN = 'text/plain';
    public const string HTML = 'text/html';
    public const string CSS = 'text/css';
    public const string CSV = 'text/csv';
    public const string CALENDAR = 'text/calendar';
    /**
     * @deprecated Use ContentType::APPLICATION_JAVASCRIPT instead.
     */
    public const string JAVASCRIPT = 'text/javascript';
    public const string JSON = 'application/json';
    public const string XML = 'application/xml';
    public const string PDF = 'application/pdf';
    public const string ZIP = 'application/zip';
    public const string GZIP = 'application/gzip';
    public const string OCTET_STREAM = 'application/octet-stream';
    public const string FORM_URLENCODED = 'application/x-www-form-urlencoded';
    public const string PHP_SERIALIZED = 'application/vnd.php.serialized';
    public const string APPLICATION_JAVASCRIPT = 'application/javascript';
    public const string MULTIPART_FORM_DATA = 'multipart/form-data';
    public const string JPEG = 'image/jpeg';
    public const string PNG = 'image/png';
    public const string GIF = 'image/gif';
    public const string WEBP = 'image/webp';
    public const string SVG = 'image/svg+xml';
    public const string BMP = 'image/bmp';
    public const string ICO = 'image/x-icon';
    public const string TIFF = 'image/tiff';
    public const string AVIF = 'image/avif';
    public const string MP3 = 'audio/mpeg';
    public const string WAV = 'audio/wav';
    public const string OGG_AUDIO = 'audio/ogg';
    public const string AAC = 'audio/aac';
    public const string FLAC = 'audio/flac';
    public const string MP4 = 'video/mp4';
    public const string MPEG = 'video/mpeg';
    public const string WEBM = 'video/webm';
    public const string OGG_VIDEO = 'video/ogg';
    public const string AVI = 'video/x-msvideo';
    public const string QUICKTIME = 'video/quicktime';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        if (self::$contentTypes === null)
        {
            self::$contentTypes = array_values(
                (new ReflectionClass(self::class))->getConstants()
            );
        }

        return self::$contentTypes;
    }
}