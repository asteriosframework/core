<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

use Asterios\Core\Contracts\Cli\Builder\ColorBuilderInterface;

class ColorBuilder implements ColorBuilderInterface
{
    /** @var string[] */
    private array $codes = [];

    /**
     * @inheritDoc
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public static function grayText(string $text): string
    {
        return self::create()
            ->gray()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function greenText(string $text): string
    {
        return self::create()
            ->green()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldGreenText(string $text): string
    {
        return self::create()
            ->bold()
            ->green()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function redText(string $text): string
    {
        return self::create()
            ->red()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldRedText(string $text): string
    {
        return self::create()
            ->bold()
            ->red()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldText(string $text): string
    {
        return self::create()
            ->bold()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function yellowText(string $text): string
    {
        return self::create()
            ->yellow()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldYellowText(string $text): string
    {
        return self::create()
            ->bold()
            ->yellow()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function cyanText(string $text): string
    {
        return self::create()
            ->cyan()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldCyanText(string $text): string
    {
        return self::create()
            ->bold()
            ->cyan()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function magentaText(string $text): string
    {
        return self::create()
            ->magenta()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldMagentaText(string $text): string
    {
        return self::create()
            ->bold()
            ->magenta()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function blackText(string $text): string
    {
        return self::create()
            ->black()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function boldBlackText(string $text): string
    {
        return self::create()
            ->bold()
            ->black()
            ->apply($text);
    }

    /**
     * @inheritDoc
     */
    public static function successText(string $text): string
    {
        return self::boldGreenText($text);
    }

    /**
     * @inheritDoc
     */
    public static function errorText(string $text): string
    {
        return self::boldRedText($text);
    }

    /**
     * @inheritDoc
     */
    public function black(): self
    {
        $this->codes[] = '90';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function gray(): self
    {
        $this->codes[] = '33';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function white(): self
    {
        $this->codes[] = '97';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function green(): self
    {
        $this->codes[] = '92';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function red(): self
    {
        $this->codes[] = '91';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function yellow(): self
    {
        $this->codes[] = '93';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cyan(): self
    {
        $this->codes[] = '96';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function magenta(): self
    {
        $this->codes[] = '95';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function bold(): self
    {
        $this->codes[] = '1';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(string $text): string
    {
        $prefix = "\033[" . implode(';', $this->codes) . "m";
        $suffix = "\033[0m";

        return $prefix . $text . $suffix;
    }
}