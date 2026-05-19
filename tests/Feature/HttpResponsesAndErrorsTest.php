<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * HTTP Responses & Error Handling (Black-Box)
 *
 * Exercises the full HTTP lifecycle through routes + middleware and asserts
 * the status code contract that the rest of the app depends on.
 *
 * Status codes covered: 200, 302, 403, 404, 419, 500.
 *
 * IDs: BBT_HTTP_001..012
 */
class HttpResponsesAndErrorsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::create([
            'full_name' => 'Seed GM',
            'email'     => 'gm-seed@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'gm',
            'status'    => 'active',
        ]);
    }

    /* ── 200 OK ─────────────────────────────────────── */

    #[Test] // BBT_HTTP_001 — public home renders 200
    public function public_home_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    #[Test] // BBT_HTTP_002 — guest login page renders 200
    public function guest_login_page_returns_200(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    #[Test] // BBT_HTTP_003 — authenticated GM hits admin dashboard with 200
    public function gm_can_reach_admin_dashboard_200(): void
    {
        $gm = $this->makeUser('gm@x.com', 'gm', 'active');
        $this->actingAs($gm)->get('/admin/dashboard')->assertStatus(200);
    }

    /* ── 302 Redirect ───────────────────────────────── */

    #[Test] // BBT_HTTP_004 — unauthenticated admin route redirects to login
    public function unauthenticated_admin_route_redirects_to_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    #[Test] // BBT_HTTP_005 — pending_activation user is bounced to OTP screen
    public function pending_activation_user_is_redirected_to_otp(): void
    {
        $u = $this->makeUser('pending@x.com', 'gm', 'pending_activation');
        $this->actingAs($u)->get('/admin/dashboard')
            ->assertStatus(302)
            ->assertRedirect(route('otp.verify'));
    }

    #[Test] // BBT_HTTP_006 — must-change-password user is bounced to password.change
    public function must_change_password_user_is_redirected(): void
    {
        $u = $this->makeUser('pw@x.com', 'gm', 'active');
        $u->forceFill(['must_change_password' => true])->save();

        $this->actingAs($u)->get('/admin/dashboard')
            ->assertStatus(302)
            ->assertRedirect(route('password.change'));
    }

    /* ── 403 Forbidden ──────────────────────────────── */

    #[Test] // BBT_HTTP_007 — tenant hitting admin route gets 403
    public function tenant_hitting_admin_route_returns_403(): void
    {
        $t = $this->makeUser('t@x.com', 'tenant', 'active');
        $this->actingAs($t)->get('/admin/dashboard')->assertStatus(403);
    }

    #[Test] // BBT_HTTP_008 — gm hitting tenant route gets 403
    public function gm_hitting_tenant_route_returns_403(): void
    {
        $gm = $this->makeUser('gm2@x.com', 'gm', 'active');
        $this->actingAs($gm)->get('/tenant/dashboard')->assertStatus(403);
    }

    /* ── 404 Not Found ──────────────────────────────── */

    #[Test] // BBT_HTTP_009 — unknown route returns 404
    public function unknown_route_returns_404(): void
    {
        $this->get('/this-route-does-not-exist-' . uniqid())->assertStatus(404);
    }

    /* ── 419 CSRF / Page Expired ────────────────────── */

    #[Test] // BBT_HTTP_010 — POST without CSRF token returns 419
    public function post_without_csrf_returns_419(): void
    {
        // Re-enable CSRF middleware (TestCase disables it by default via Laravel's testing helpers
        // for POSTs, so we explicitly include it for this check).
        $this->withMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $gm = $this->makeUser('csrf@x.com', 'gm', 'active');
        $this->actingAs($gm)
            ->post('/logout')
            ->assertStatus(419);
    }

    /* ── 500 Server Error ───────────────────────────── */

    #[Test] // BBT_HTTP_011 — uncaught exception in a route surfaces as 500
    public function uncaught_exception_renders_500(): void
    {
        Route::get('/__http_test/boom', function () {
            throw new \RuntimeException('synthetic explosion');
        })->middleware('web');

        $this->get('/__http_test/boom')->assertStatus(500);
    }

    #[Test] // BBT_HTTP_012 — 500 response body does not leak the raw exception message in prod-style render
    public function server_error_does_not_leak_exception_message(): void
    {
        config(['app.debug' => false]);

        Route::get('/__http_test/boom2', function () {
            throw new \RuntimeException('SECRET_LEAKED_MARKER');
        })->middleware('web');

        $response = $this->get('/__http_test/boom2');
        $response->assertStatus(500);
        $this->assertStringNotContainsString('SECRET_LEAKED_MARKER', $response->getContent() ?? '');
    }

    /* ── helpers ────────────────────────────────────── */

    private function makeUser(string $email, string $role, string $status): User
    {
        return User::create([
            'full_name'            => 'User ' . $email,
            'email'                => $email,
            'password'             => Hash::make('password'),
            'role'                 => $role,
            'status'               => $status,
            'activated_at'         => $status === 'active' ? now() : null,
            'must_change_password' => false,
        ]);
    }
}
