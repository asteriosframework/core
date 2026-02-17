<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Str;

trait StrTrait
{
    /** @var Str|null */
    protected $str;

    public function setStr(Str $str): self
    {
        $this->str = $str;

        return $this;
    }

    public function getStr(): Str
    {
        return $this->str ?? Str::getInstance();
    }
}
