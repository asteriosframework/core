<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces\Cli\Builder;

interface ColorBuilderInterface
{
    /**
     * @return self
     */
    public static function create(): self;

    /**
     * @param string $text
     * @return string
     */
    public static function grayText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function greenText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldGreenText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function redText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldRedText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function yellowText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldYellowText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function cyanText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldCyanText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function magentaText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldMagentaText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function blackText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function boldBlackText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function successText(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public static function errorText(string $text): string;

    /**
     * @return self
     */
    public function black(): self;

    /**
     * @return self
     */
    public function gray(): self;

    /**
     * @return self
     */
    public function white(): self;

    /**
     * @return self
     */
    public function green(): self;

    /**
     * @return self
     */
    public function red(): self;

    /**
     * @return self
     */
    public function yellow(): self;

    /**
     * @return self
     */
    public function cyan(): self;

    /**
     * @return self
     */
    public function magenta(): self;

    /**
     * @return self
     */
    public function bold(): self;

    /**
     * @param string $text
     * @return string
     */
    public function apply(string $text): string;
}