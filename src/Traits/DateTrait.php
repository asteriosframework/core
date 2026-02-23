<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Date;

trait DateTrait
{
    /** @var Date|null */
    protected Date|null $date;

    public function setDate(Date $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): Date
    {
        return $this->date ?? new Date();
    }

}
