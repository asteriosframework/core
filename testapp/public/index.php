<?php

declare(strict_types=1);

namespace Asterios\Testapp\Public;

use DateTime;

// bootstrap the application
$bootstrapPath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../../src/Bootstrap/Bootstrap.php');
require_once $bootstrapPath;

use Asterios\Core\Asterios;
use Asterios\Core\Bootstrap\Bootstrap;
use DateTimeInterface;

Bootstrap::init(normalize_path(__DIR__ . '/..'));
$asterios = Bootstrap::$container->get(Asterios::class);

echo ($asterios->isInitialized() === true ? 'Asterios core is initialized.' : 'Asterios core is not initialized.') . '<br>' . PHP_EOL;

echo (config('app.name', 'not found')) . PHP_EOL;
if (config('app.copyright', 'false') === 'true') {
    echo 'X-Powered-By: ' . config('app.name', 'not set') . '<br>' . PHP_EOL;
}

echo 'Project directory: ' . base_path() . '<br>' . PHP_EOL;
echo 'App directory: ' . app_path() . '<br>' . PHP_EOL;

$toResolve = new class() {
    public function __invoke(): DateTime
    {
        return new DateTime();
    }
};

Bootstrap::$container->set('Foo\Bar', $toResolve);
$fooBar = Bootstrap::$container->get('Foo\Bar');
echo '<pre>' . PHP_EOL;
var_dump($fooBar);
echo '</pre>' . PHP_EOL;

$expected = new DateTime('2024-10-20T14:13:45+02:00');

Bootstrap::$container->set(DateTime::class, DateTime::class, ['datetime' => '2024-10-20T14:13:45+02:00']);
$actual = Bootstrap::$container->get(DateTime::class);

echo 'DateTime test:<br>' . PHP_EOL;
echo '<pre>' . PHP_EOL;
echo 'expected: ' . $expected->format(DateTimeInterface::ATOM) . '<br>' . PHP_EOL;
echo 'actual  : ' . $actual->format(DateTimeInterface::ATOM) . '<br>' . PHP_EOL;
echo '</pre>' . PHP_EOL;

$toResolve = get_class(new class(true) {
    public function __construct(protected bool $cust, protected string $ask = 'Hello, Welt!')
    {
        //
    }

    public function ask(): string
    {
        return $this->ask;
    }
});

Bootstrap::$container->set('Foo\Bar', $toResolve, ['cust' => true]);
echo Bootstrap::$container->get('Foo\Bar')->ask() . '<br>' . PHP_EOL;
