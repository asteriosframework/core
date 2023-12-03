<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Dto\AssetsDto;

class Assets
{
    /**
     * @var  string
     */
    public const EXTENSION_CSS = 'css';
    public const EXTENSION_JS = 'js';
    public const EXTENSION_JPG = 'jpg';
    public const EXTENSION_JPEG = 'jpeg';
    public const EXTENSION_PNG = 'png';
    public const EXTENSION_GIF = 'gif';
    public const EXTENSION_SVG = 'svg';
    public const EXTENSION_ICO = 'ico';

    public const DOCTYPE_HTML4 = 'html4';
    public const DOCTYPE_HTML5 = 'html5';
    public const DOCTYPE_XHTML = 'xhtml';

    /**
     * Private member variable for the asset file
     */
    private static $asset_file;

    private static $allowed_document_types = [
        self::DOCTYPE_HTML4,
        self::DOCTYPE_XHTML,
        self::DOCTYPE_HTML5,
    ];

    private static $allowed_image_extensions = [
        self::EXTENSION_JPG,
        self::EXTENSION_JPEG,
        self::EXTENSION_PNG,
        self::EXTENSION_GIF,
        self::EXTENSION_SVG,
        self::EXTENSION_ICO,
    ];

    /**
     * This method render automatically valid html code for css, js or image files in condition of file extension.
     * @param AssetsDto $dto
     * @return mixed boolean|string
     */
    public static function forge(AssetsDto $dto)
    {
        $file = $dto->get_path() . $dto->get_file();
        $extension = self::get_file_extension($file);

        switch ($extension)
        {
            case self::EXTENSION_CSS:
                $return = self::css($dto);
                break;
            case self::EXTENSION_JS:
                $return = self::js($dto);
                break;
            case self::EXTENSION_ICO:
                $return = self::favicon($dto);
                break;
            default:
                $return = self::img($dto);
        }

        return $return;
    }

    /**
     * This method returns css html code for given css file
     * @param AssetsDto $dto
     * @return string|false
     */
    public static function css(AssetsDto $dto)
    {
        $file = $dto->get_path() . $dto->get_file();

        $extension = self::get_file_extension($file);

        if (self::EXTENSION_CSS !== $extension)
        {
            return false;
        }

        self::set_asset_file($file);

        if (!self::check_file())
        {
            return false;
        }

        return '<link rel="stylesheet" type="text/css" href="' . self::get_asset_file() . '?' . self::get_file_time() . '"' . self::set_proper_close_tag($dto->get_document_type()) . PHP_EOL;
    }

    /**
     * This method returns js html code for given css file
     * @param AssetsDto $dto
     * @return false|string
     */
    public static function js(AssetsDto $dto)
    {
        $file = $dto->get_path() . $dto->get_file();

        $extension = self::get_file_extension($file);

        if (self::EXTENSION_JS !== $extension)
        {
            return false;
        }

        self::set_asset_file($file);

        if (!self::check_file())
        {
            return false;
        }

        return sprintf('<script type="text/javascript" src="%s?%d"></script>' . PHP_EOL, self::get_asset_file(), self::get_file_time());
    }

    /**
     * This method returns css html code for given css file
     * @param AssetsDto $dto
     * @return bool|string
     */
    public static function img(AssetsDto $dto)
    {
        $file = $dto->get_path() . $dto->get_file();
        $extension = self::get_file_extension($file);

        if (self::is_allowed_image_extension($extension))
        {
            self::set_asset_file($file);

            if (false === self::check_file())
            {
                return false;
            }

            $css_classname = (!is_null($dto->get_css_classname())) ? $dto->get_css_classname() : '';

            return sprintf('<img src="%s?%d" class="%s"%s' . PHP_EOL, self::get_asset_file(), self::get_file_time(), $css_classname, self::set_proper_close_tag($dto->get_document_type()));
        }

        return false;
    }

    /**
     * @param AssetsDto $dto
     * @return false|string
     */
    public static function favicon(AssetsDto $dto)
    {
        $file = $dto->get_path() . $dto->get_file();

        $extension = self::get_file_extension($file);

        if (self::EXTENSION_ICO !== $extension)
        {
            return false;
        }

        self::set_asset_file($file);

        if (false === self::check_file())
        {
            return false;
        }

        return '<link rel="shortcut icon" type="image/x-icon" href=' . self::get_asset_file() . '"' . self::set_proper_close_tag($dto->get_document_type()) . PHP_EOL;
    }

    /**
     * Private method to set asset file
     * @param string $file
     */
    private static function set_asset_file(string $file): void
    {
        self::$asset_file = $file;
    }

    /**
     * Private method to get asset file
     * @return string|false
     */
    private static function get_asset_file()
    {
        return self::$asset_file ?? false;
    }

    /**
     * Private method to check if given file exists in filesystem
     * @return boolean
     */
    private static function check_file(): bool
    {
        $file_exists = File::forge()
            ->file_exists(self::get_doc_root() . self::get_asset_file());

        return !(false === self::get_asset_file() || !$file_exists);
    }

    /**
     * Private method to get the file time of given file
     * @return integer
     */
    private static function get_file_time(): int
    {
        return filemtime(self::get_doc_root() . self::get_asset_file());
    }

    /**
     * Check if given extension is allowed image extension
     * @param string $extension
     * @return boolean
     */
    private static function is_allowed_image_extension(string $extension): bool
    {
        return in_array($extension, self::$allowed_image_extensions);
    }

    /**
     * Get file extension of given file
     * @param string $file
     * @return mixed boolean|string|null
     */
    private static function get_file_extension(string $file)
    {
        return pathinfo(self::get_doc_root() . $file, PATHINFO_EXTENSION);
    }

    /**
     * Set proper closing tag for given document type
     * @param string $document_type
     * @return string
     */
    private static function set_proper_close_tag(string $document_type): string
    {
        if ($document_type === 'xhtml' && in_array($document_type, self::$allowed_document_types))
        {
            return '/>';
        }

        return '>';
    }

    /**
     * @return null|string
     */
    private static function get_doc_root(): ?string
    {
        return Config::get_memory('DOCROOT');
    }
}