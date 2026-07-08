<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seeds the DBMTS role model (scope §12 Roles and Permissions).
 *
 * Permissions are added in later phases as features are built; this seeder
 * establishes the canonical role set so role-based recipients and gates can be
 * wired up as modules land.
 */
class RoleSeeder extends Seeder
{
    /**
     * Canonical DBMTS roles keyed by a stable key => display name.
     */
    public const ROLES = [
        'operator' => 'Operator',
        'production_supervisor' => 'Production Supervisor',
        'qa_technical' => 'QA / Technical',
        'planning' => 'Planning',
        'administrator' => 'Administrator',
        'auditor' => 'Read-only Auditor',
    ];

    public function run(): void
    {
        foreach (array_keys(self::ROLES) as $roleKey) {
            Role::findOrCreate($roleKey, 'web');
        }
    }
}
