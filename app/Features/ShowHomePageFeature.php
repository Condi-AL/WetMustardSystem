<?php

namespace App\Features;

use Illuminate\Http\Request;

class ShowHomePageFeature
{
    public function __invoke(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return view('welcome');
    }
}
