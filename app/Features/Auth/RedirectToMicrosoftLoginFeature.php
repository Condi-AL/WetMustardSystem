<?php

namespace App\Features\Auth;

use App\Domains\Auth\Jobs\BuildMicrosoftAuthorizationUrlJob;
use App\Domains\Auth\Jobs\GenerateOauthStateJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectToMicrosoftLoginFeature
{
    public function __invoke(Request $request): RedirectResponse
    {
        $state = app(GenerateOauthStateJob::class)();

        $request->session()->put('oauth_state', $state);

        $url = app(BuildMicrosoftAuthorizationUrlJob::class)($state);

        return redirect()->away($url);
    }
}
