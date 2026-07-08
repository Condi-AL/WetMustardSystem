<?php

namespace Tests\Feature\Auth;

use App\Domains\Auth\Jobs\SyncUserRolesJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SyncUserRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('dbmts.admin_emails', ['boss@condimentum.co.uk']);
    }

    public function test_admin_email_receives_operator_and_administrator_roles(): void
    {
        $user = User::factory()->create(['email' => 'boss@condimentum.co.uk']);

        app(SyncUserRolesJob::class)($user);

        $this->assertTrue($user->hasRole('operator'));
        $this->assertTrue($user->hasRole('administrator'));
        $this->assertTrue(Gate::forUser($user)->allows('admin'));
    }

    public function test_non_admin_email_receives_operator_only(): void
    {
        $user = User::factory()->create(['email' => 'operator@condimentum.co.uk']);

        app(SyncUserRolesJob::class)($user);

        $this->assertTrue($user->hasRole('operator'));
        $this->assertFalse($user->fresh()->hasRole('administrator'));
        $this->assertFalse(Gate::forUser($user)->allows('admin'));
    }

    public function test_administrator_role_is_revoked_when_email_no_longer_admin(): void
    {
        $user = User::factory()->create(['email' => 'former@condimentum.co.uk']);
        app(SyncUserRolesJob::class)($user); // operator only

        config()->set('dbmts.admin_emails', ['former@condimentum.co.uk']);
        app(SyncUserRolesJob::class)($user->fresh());
        $this->assertTrue($user->fresh()->hasRole('administrator'));

        config()->set('dbmts.admin_emails', []);
        app(SyncUserRolesJob::class)($user->fresh());
        $this->assertFalse($user->fresh()->hasRole('administrator'));
    }
}
