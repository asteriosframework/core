<?php

declare(strict_types=1);

namespace Asterios\Core\Provider;

use Asterios\Core\Bootstrap\Bootstrap;

class AppServiceProvider
{
    /**
     * Summary of register
     * @return void
     */
    public function register(): void
    {
        // This method is used to register any application services or bindings.
        // You can use it to bind classes or interfaces to the service container.
        // For example, you could register a database connection or a logger.

        // Example: Registering a service
        // Bootstrap::getContainer()->set(SomeService::class, SomeService::class);
    }

    public function boot(): void
    {
        // This method can be used to perform any actions after the service provider has been registered.
        // For example, you could load configuration files or set up event listeners.
    }
}
