<?php

namespace spec\Asterios\Core\DI;

use Asterios\Core\DI\Container;
use DateTimeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DateTime;

class ContainerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Container::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement('Asterios\Core\DI\Psr\Container\ContainerInterface');
    }

    public function it_can_register_simple_classes()
    {
        $this->set(DateTime::class, DateTime::class)->shouldReturnAnInstanceOf(Container::class);
    }

    public function it_has_simple_classes()
    {
        $this->set('DateTime', DateTime::class);
        $this->has('DateTime')->shouldReturn(true);
    }

    public function it_does_not_have_unknown_classes()
    {
        $this->has('UnknownClass')->shouldReturn(false);
    }

    public function it_can_get_simple_class()
    {
        $this->set('DateTime', DateTime::class);
        $this->get('DateTime')->shouldReturnAnInstanceOf(DateTime::class);
    }

    public function it_returns_not_found_exception_if_class_cannont_be_found()
    {
        $this->shouldThrow('Asterios\Core\DI\Exceptions\NotFoundException')
            ->duringGet('UnknownClass');
    }

    public function it_can_register_dependencies()
    {
        $toResolve = new class () {
        };

        $this->set('Foo\Bar', $toResolve)->shouldReturn($this);
    }

    public function it_can_resolve_registered_dependencies()
    {
        $toResolve = new class () {
        };

        $this->set('Foo\Bar', $toResolve);
        $this->get('Foo\Bar')->shouldReturnAnInstanceOf($toResolve);
    }

    public function it_can_resolve_registered_invokable()
    {
        $toResolve = new class () {
            public function __invoke()
            {
                return new DateTime();
            }
        };

        $this->set('Foo\Bar', $toResolve);
        $this->get('Foo\Bar')->shouldReturnAnInstanceOf('DateTime');
    }

    public function it_can_resolve_registered_callable()
    {
        $toResolve = function () {
            return new DateTime();
        };

        $this->set('Foo\Bar', $toResolve);
        $this->get('Foo\Bar')->shouldReturnAnInstanceOf('DateTime');
    }

    public function it_can_resolve_if_registered_dependencies_instantiable()
    {
        $toResolve = new class () {
        };

        $this->set('Foo\Bar', $toResolve);
        $this->has('Foo\Bar')->shouldReturn(true);
    }

    public function it_can_resolve_dependencies()
    {
        $toResolve = get_class(new class (new DateTime()) {
            public $datetime;

            public function __construct(DateTime $datetime)
            {
                $this->datetime = $datetime;
            }
        });

        $this->set('Foo\Bar', $toResolve);
        $this->get('Foo\Bar')->shouldReturnAnInstanceOf($toResolve);
    }

    public function it_can_inject_constructor_parameter()
    {
        $expected = new DateTime('2024-10-20T14:13:45+02:00');

        $this->set(DateTime::class, DateTime::class, ['datetime' => '2024-10-20T14:13:45+02:00']);
        $this->get(DateTime::class)
            ->format(DateTimeInterface::ATOM)
            ->shouldEqual($expected->format(DateTimeInterface::ATOM));
    }

    public function it_can_set_constructor_default_parameter()
    {
        $toResolve = get_class(new class (true) {
            public function __construct(protected bool $cust, protected string $ask = 'Hello, Welt!')
            {
                //
            }

            public function ask(): string
            {
                return $this->ask;
            }
        });

        $this->set('Foo\Bar', $toResolve);
        $this->get('Foo\Bar')->ask()->shouldEqual('Hello, Welt!');
    }
}
