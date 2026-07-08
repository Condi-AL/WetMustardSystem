<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_route_renders_the_login_form(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('Password');
    }

    public function test_guests_are_redirected_to_microsoft_for_sign_in(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');
        config()->set('services.microsoft.scopes', ['openid', 'profile', 'email', 'User.Read']);

        $response = $this->get(route('auth.microsoft.redirect'));

        $response->assertRedirect();

        $this->assertNotNull(session('oauth_state'));
        $this->assertStringContainsString('login.microsoftonline.com/tenant-id/oauth2/v2.0/authorize', $response->headers->get('Location'));
    }

    public function test_users_can_authenticate_using_microsoft_callback(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');
        config()->set('services.microsoft.scopes', ['openid', 'profile', 'email', 'User.Read']);

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'fake-access-token',
            ], 200),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'displayName' => 'Azure User',
                'mail' => 'azure.user@condimentum.co.uk',
                'userPrincipalName' => 'azure.user@condimentum.co.uk',
            ], 200),
        ]);

        $response = $this->withSession(['oauth_state' => 'known-state'])
            ->get(route('auth.microsoft.callback', [
                'state' => 'known-state',
                'code' => 'oauth-code',
            ]));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'azure.user@condimentum.co.uk',
            'name' => 'Azure User',
        ]);
    }

    public function test_users_outside_the_condimentum_domain_can_not_authenticate(): void
    {
        config()->set('services.microsoft.tenant_id', 'tenant-id');
        config()->set('services.microsoft.client_id', 'client-id');
        config()->set('services.microsoft.client_secret', 'client-secret');
        config()->set('services.microsoft.redirect_uri', 'http://localhost/auth/microsoft/callback');
        config()->set('services.microsoft.scopes', ['openid', 'profile', 'email', 'User.Read']);

        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'fake-access-token',
            ], 200),
            'graph.microsoft.com/v1.0/me*' => Http::response([
                'displayName' => 'External User',
                'mail' => 'external.user@example.com',
                'userPrincipalName' => 'external.user@example.com',
            ], 200),
        ]);

        $response = $this->withSession(['oauth_state' => 'known-state'])
            ->get(route('auth.microsoft.callback', [
                'state' => 'known-state',
                'code' => 'oauth-code',
            ]));

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('auth_error', 'Only @condimentum.co.uk accounts are allowed to sign in.');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'external.user@example.com',
        ]);
    }

    public function test_invalid_callback_state_redirects_back_home_with_error(): void
    {
        $response = $this->withSession(['oauth_state' => 'known-state'])
            ->get(route('auth.microsoft.callback', [
                'state' => 'different-state',
                'code' => 'oauth-code',
            ]));

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('auth_error');

        $this->assertGuest();
    }

    public function test_authenticated_users_are_redirected_from_home_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/');

        $response
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');

        $this->assertGuest();
    }
}
