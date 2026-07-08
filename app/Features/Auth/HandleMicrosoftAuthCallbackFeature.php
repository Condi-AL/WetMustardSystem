<?php

namespace App\Features\Auth;

use App\Domains\Auth\Jobs\CreateOrUpdateUserFromMicrosoftProfileJob;
use App\Domains\Auth\Jobs\ExchangeMicrosoftCodeForTokenJob;
use App\Domains\Auth\Jobs\FetchMicrosoftUserProfileJob;
use App\Domains\Auth\Jobs\SyncUserRolesJob;
use App\Domains\Auth\Jobs\ValidateMicrosoftOauthCallbackJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleMicrosoftAuthCallbackFeature
{
    public function __invoke(Request $request): RedirectResponse
    {
        app(ValidateMicrosoftOauthCallbackJob::class)($request);

        $token = app(ExchangeMicrosoftCodeForTokenJob::class)($request->string('code')->toString());

        $profile = app(FetchMicrosoftUserProfileJob::class)($token['access_token']);

        $user = app(CreateOrUpdateUserFromMicrosoftProfileJob::class)($profile);

        app(SyncUserRolesJob::class)($user);

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget('oauth_state');

        return redirect()->intended(route('dashboard'));
    }
}
