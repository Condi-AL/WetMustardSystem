<?php

namespace App\Domains\Auth\Jobs;

class BuildMicrosoftAuthorizationUrlJob
{
    public function __invoke(string $state): string
    {
        $query = http_build_query([
            'client_id' => config('services.microsoft.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.microsoft.redirect_uri'),
            'response_mode' => 'query',
            'scope' => implode(' ', config('services.microsoft.scopes', [])),
            'state' => $state,
        ]);

        return 'https://login.microsoftonline.com/'.config('services.microsoft.tenant_id').'/oauth2/v2.0/authorize?'.$query;
    }
}
