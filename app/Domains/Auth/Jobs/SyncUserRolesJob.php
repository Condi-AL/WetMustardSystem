<?php

namespace App\Domains\Auth\Jobs;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Synchronises a user's DBMTS roles on sign-in. Every authenticated tenant user
 * gets the default "operator" role; addresses listed in config('dbmts.admin_emails')
 * additionally receive (or retain) the "administrator" role.
 */
class SyncUserRolesJob
{
    public function __invoke(User $user): void
    {
        Role::findOrCreate('operator', 'web');
        Role::findOrCreate('administrator', 'web');

        if (! $user->hasRole('operator')) {
            $user->assignRole('operator');
        }

        $adminEmails = array_map(
            fn (string $email): string => Str::lower(trim($email)),
            (array) config('dbmts.admin_emails', []),
        );

        $isAdmin = in_array(Str::lower((string) $user->email), $adminEmails, true);

        if ($isAdmin && ! $user->hasRole('administrator')) {
            $user->assignRole('administrator');
        } elseif (! $isAdmin && $user->hasRole('administrator')) {
            $user->removeRole('administrator');
        }
    }
}
