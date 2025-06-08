<?php

namespace spec\Asterios\Core\DI\Exceptions;

use Asterios\Core\DI\Exceptions\ContainerException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerExceptionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ContainerException::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement('Asterios\Core\DI\Psr\Container\ContainerExceptionInterface');
    }

    public function it_implements_throwable()
    {
        $this->shouldImplement('Throwable');
    }
}
