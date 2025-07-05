<?php

namespace Asterios\Test\Stubs;

class FakeController
{
    public function index(): string
    {
        echo 'FakeController@index called';
    }
}
