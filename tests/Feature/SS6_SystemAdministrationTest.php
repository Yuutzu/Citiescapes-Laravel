<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS6 — System Administration (Black-Box)
 *
 * Tests the Login Livewire component (auth + lockout + role routing).
 * Technique: Equivalence Partitioning (EP) + Boundary Value Analysis (BVA).
 *
 * IDs: BBT_SS6_LOGIN_001..008
 */
class SS6_SystemAdministrationTest extends TestCase
{
    use RefreshDatabase;

    /* ── EP: valid logins ──────────────────────────── */

    #[Test] // BBT_SS6_LOGIN_001 — EP: valid GM login redirects to admin dashboard
    public function valid_gm_login_redirects_to_admin_dashboard(): void
    {
        $this->makeUser('gm@x.com', 'gm', 'active');

        Livewire::test(Login::class)
            ->set('email', 'gm@x.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));
    }

    #[Test] // BBT_SS6_LOGIN_002 — EP: valid tenant login redirects to tenant dashboard
    public function valid_tenant_login_redirects_to_tenant_dashboard(): void
    {
        $this->makeUser('tenant@x.com', 'tenant', 'active');

        Livewire::test(Login::class)
            ->set('email', 'tenant@x.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('tenant.dashboard'));
    }

    /* ── EP: validation ─────────────────────────────── */

    #[Test] // BBT_SS6_LOGIN_003 — EP: empty email fails validation
    public function empty_email_fails_validation(): void
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email' => 'required']);
    }

    #[Test] // BBT_SS6_LOGIN_004 — EP: malformed email fails validation
    public function malformed_email_fails_validation(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'not-an-email')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email' => 'email']);
    }

    #[Test] // BBT_SS6_LOGIN_005 — BVA: password 5 chars (just below 6 min)
    public function password_below_minimum_length_fails(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'a@b.com')
            ->set('password', 'short')   // 5 chars
            ->call('login')
            ->assertHasErrors(['password' => 'min']);
    }

    /* ── EP: failure paths ─────────────────────────── */

    #[Test] // BBT_SS6_LOGIN_006 — EP: unknown email returns generic error (no enumeration)
    public function unknown_email_returns_generic_credential_error(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'nobody@x.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    #[Test] // BBT_SS6_LOGIN_007 — EP: wrong password increments failed_login_attempts
    public function wrong_password_increments_failed_attempts(): void
    {
        $u = $this->makeUser('attempt@x.com', 'tenant', 'active');

        Livewire::test(Login::class)
            ->set('email', 'attempt@x.com')
            ->set('password', 'WRONG-PASSWORD')
            ->call('login');

        $this->assertSame(1, $u->fresh()->failed_login_attempts);
    }

    #[Test] // BBT_SS6_LOGIN_008 — EP: archived user is blocked from logging in
    public function archived_user_is_blocked(): void
    {
        $this->makeUser('archived@x.com', 'tenant', 'archived');

        Livewire::test(Login::class)
            ->set('email', 'archived@x.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    private function makeUser(string $email, string $role, string $status): User
    {
        return User::create([
            'full_name'    => 'User ' . $email,
            'email'        => $email,
            'password'     => Hash::make('password'),
            'role'         => $role,
            'status'       => $status,
            'activated_at' => now(),
            'must_change_password' => false,
        ]);
    }
}
