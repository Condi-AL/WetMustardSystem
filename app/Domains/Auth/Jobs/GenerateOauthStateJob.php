<?php

namespace App\Domains\Auth\Jobs;

use Illuminate\Support\Str;

class GenerateOauthStateJob
{
    public function __invoke(): string
    {
        return Str::random(40);
    }
}
