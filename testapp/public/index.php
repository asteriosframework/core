<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

// bootstrap the application
$bootstrapPath = realpath(__DIR__ . '/../vendor/asterios/core/src');
require_once $bootstrapPath . '/Bootstrap/Bootstrap.php';
require_once $bootstrapPath . '/Helper/CoreHelper.php';

$loadedClasses = get_declared_classes();
$expectedClasses = array_filter($loadedClasses, function ($class) {
    return str_starts_with($class, 'Asterios\\');
});
var_dump($expectedClasses);

use Asterios\Core\Asterios;
use Asterios\Core\Bootstrap\Bootstrap;

Bootstrap::init(realpath(__DIR__ . '/..'));
$asterios = Bootstrap::getContainer()->get(Asterios::class);

echo ($asterios->isInitialized() === true ? 'Asterios core is initialized.' : 'Asterios core is not initialized.') . '<br>' . PHP_EOL;
echo '(Asterios) App Environment: ' . Asterios::getEnvironment() . '<br>' . PHP_EOL;

echo 'Project directory: ' . base_path() . '<br>' . PHP_EOL;
echo 'App directory: ' . app_path() . '<br>' . PHP_EOL;
echo 'App Environment: ' . config('environment') . '<br>' . PHP_EOL;

$toResolve = new class () {
    public function __invoke(): DateTime
    {
        return new DateTime();
    }
};

Bootstrap::getContainer()->set('Foo\Bar', $toResolve);
$fooBar = Bootstrap::getContainer()->get('Foo\Bar');
echo '<pre>' . PHP_EOL;
var_dump($fooBar);
echo '</pre>' . PHP_EOL;

$expected = new DateTime('2024-10-20T14:13:45+02:00');

Bootstrap::getContainer()->set(DateTime::class, DateTime::class, ['datetime' => '2024-10-20T14:13:45+02:00']);
$actual = Bootstrap::getContainer()->get(DateTime::class);

echo 'DateTime test:<br>' . PHP_EOL;
echo '<pre>' . PHP_EOL;
echo 'expected: ' . $expected->format(DateTimeInterface::ATOM) . '<br>' . PHP_EOL;
echo 'actual  : ' . $actual->format(DateTimeInterface::ATOM) . '<br>' . PHP_EOL;
echo '</pre>' . PHP_EOL;

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

Bootstrap::getContainer()->set('Foo\Bar', $toResolve, ['cust' => true]);
echo Bootstrap::getContainer()->get('Foo\Bar')->ask() . '<br>' . PHP_EOL;
