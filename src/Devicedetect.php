<?php
declare(strict_types=1);

namespace Asterios\Core;

use Detection\MobileDetect;

class Devicedetect
{
    /**
     * @var MobileDetect
     */
    protected $mobile_detect;

    protected function __construct(?MobileDetect $mobile_detect = null)
    {
        $this->mobile_detect = $mobile_detect ?? new MobileDetect();
    }

    /**
     * @param MobileDetect|null $mobile_detect
     * @return Devicedetect
     */
    public static function forge(?MobileDetect $mobile_detect = null): Devicedetect
    {
        return new Devicedetect($mobile_detect);
    }

    /**
     * @return bool
     */
    public function is_mobile(): bool
    {
        return $this->mobile_detect->isMobile();
    }

    /**
     * @return bool
     */
    public function is_tablet(): bool
    {
        return $this->mobile_detect->isTablet();
    }

    /**
     * @return bool
     */
    public function is_desktop(): bool
    {
        return !$this->mobile_detect->isMobile() && !$this->mobile_detect->isTablet();
    }

    /**
     * @return array
     */
    public function get_http_headers(): array
    {
        return $this->mobile_detect->getHttpHeaders();
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function get_http_header(string $value): ?string
    {
        return $this->mobile_detect->getHttpHeader($value);
    }

    /**
     * @return string|null
     */
    public function get_user_agent(): ?string
    {
        return $this->mobile_detect->getUserAgent();
    }
}
