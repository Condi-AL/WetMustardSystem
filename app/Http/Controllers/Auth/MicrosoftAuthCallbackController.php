<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class MicrosoftAuthCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            return $this->serve(\App\Features\Auth\HandleMicrosoftAuthCallbackFeature::class, $request);
        } catch (AuthenticationException $exception) {
            return redirect()->route('home')->with('auth_error', $exception->getMessage());
        }
    }
}
