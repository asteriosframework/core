<?php

namespace spec\Asterios\Core\DI\Exceptions;

use Asterios\Core\DI\Exceptions\NotFoundException;
use PhpSpec\ObjectBehavior;

class NotFoundExceptionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NotFoundException::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement('Asterios\Core\DI\Psr\Container\NotFoundExceptionInterface');
    }

    public function it_implements_throwable()
    {
        $this->shouldImplement('Throwable');
    }
}
