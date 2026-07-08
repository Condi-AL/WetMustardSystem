<?php

namespace App\Domains\Auth\Jobs;

use App\Models\User;
use Illuminate\Support\Str;

class CreateOrUpdateUserFromMicrosoftProfileJob
{
    public function __invoke(array $profile): User
    {
        $email = Str::lower((string) ($profile['mail'] ?? $profile['userPrincipalName'] ?? ''));
        $name = (string) ($profile['displayName'] ?? $email);

        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => now(),
                'password' => Str::password(32),
            ]
        );
    }
}
