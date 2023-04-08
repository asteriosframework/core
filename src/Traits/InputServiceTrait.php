<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\InputService;

trait InputServiceTrait
{
    /** @var InputService|null */
    protected $inputService;

    public function setInputService(InputService $inputService): self
    {
        $this->inputService = $inputService;

        return $this;
    }

    public function getInputService(): InputService
    {
        return $this->inputService ?? new InputService;
    }
}