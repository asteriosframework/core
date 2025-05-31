<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Logger;

trait LoggerTrait
{
    /** @var Logger|null */
    protected $logger;

    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger(): Logger
    {
        return $this->logger ?? new Logger();
    }

}
