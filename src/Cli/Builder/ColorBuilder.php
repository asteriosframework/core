<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

final class ColorBuilder
{
    /** @var string[] */
    private array $codes = [];

    public function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public static function grayText(string $text): string
    {
        return self::create()
            ->gray()
            ->apply($text);
    }

    public static function greenText(string $text): string
    {
        return self::create()
            ->green()
            ->apply($text);
    }

    public static function boldGreenText(string $text): string
    {
        return self::create()
            ->bold()
            ->green()
            ->apply($text);
    }

    public static function redText(string $text): string
    {
        return self::create()
            ->red()
            ->apply($text);
    }

    public static function boldRedText(string $text): string
    {
        return self::create()
            ->bold()
            ->red()
            ->apply($text);
    }

    public static function boldText(string $text): string
    {
        return self::create()
            ->bold()
            ->apply($text);
    }

    public static function yellowText(string $text): string
    {
        return self::create()
            ->yellow()
            ->apply($text);
    }

    public static function boldYellowText(string $text): string
    {
        return self::create()
            ->bold()
            ->yellow()
            ->apply($text);
    }

    public static function cyanText(string $text): string
    {
        return self::create()
            ->cyan()
            ->apply($text);
    }

    public static function boldCyanText(string $text): string
    {
        return self::create()
            ->bold()
            ->cyan()
            ->apply($text);
    }

    public static function magentaText(string $text): string
    {
        return self::create()
            ->magenta()
            ->apply($text);
    }

    public static function successText(string $text): string
    {
        return self::boldGreenText($text);
    }

    public static function errorText(string $text): string
    {
        return self::boldRedText($text);
    }

    public function black(): self
    {
        $this->codes[] = '90';
    }

    public function gray(): self
    {
        $this->codes[] = '33';

        return $this;
    }

    public function white(): self
    {
        $this->codes[] = '97';

        return $this;
    }

    public function green(): self
    {
        $this->codes[] = '92';

        return $this;
    }

    public function red(): self
    {
        $this->codes[] = '91';

        return $this;
    }

    public function yellow(): self
    {
        $this->codes[] = '93';

        return $this;
    }

    public function cyan(): self
    {
        $this->codes[] = '96';

        return $this;
    }

    public function magenta(): self
    {
        $this->codes[] = '95';

        return $this;
    }

    public function bold(): self
    {
        $this->codes[] = '1';

        return $this;
    }

    public function apply(string $text): string
    {
        $prefix = "\033[" . implode(';', $this->codes) . "m";
        $suffix = "\033[0m";

        return $prefix . $text . $suffix;
    }
}