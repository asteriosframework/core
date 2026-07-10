<?php declare(strict_types=1);

namespace Asterios\Test\Stubs;

class IndexController
{
    public function index(): void
    {
        echo 'IndexController@index';
    }

    public function query_index(): void
    {
        echo 'IndexController@query_index';
    }
}
