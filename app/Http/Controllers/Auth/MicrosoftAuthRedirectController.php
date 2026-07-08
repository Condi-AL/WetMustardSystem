<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MicrosoftAuthRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->serve(\App\Features\Auth\RedirectToMicrosoftLoginFeature::class, $request);
    }
}
