<?php

namespace App\Domains\Auth\Jobs;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class ValidateMicrosoftOauthCallbackJob
{
    public function __invoke(Request $request): void
    {
        $expectedState = $request->session()->pull('oauth_state');
        $providedState = $request->query('state');

        if (! $providedState || ! $expectedState || ! hash_equals($expectedState, $providedState)) {
            throw new AuthenticationException('Invalid state. Please try signing in again.');
        }

        if ($request->filled('error')) {
            $message = $request->query('error_description', $request->query('error', 'Microsoft sign-in failed.'));

            throw new AuthenticationException((string) $message);
        }

        if (! $request->filled('code')) {
            throw new AuthenticationException('No authorisation code was received from Microsoft.');
        }
    }
}
