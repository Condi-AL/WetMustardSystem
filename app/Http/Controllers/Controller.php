<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function serve(string $feature, mixed ...$arguments): mixed
    {
        return app($feature)(...$arguments);
    }
}
