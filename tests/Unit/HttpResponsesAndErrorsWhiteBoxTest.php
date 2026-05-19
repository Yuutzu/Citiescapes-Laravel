<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureAccountActivated;
use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * HTTP Responses & Error Handling (White-Box)
 *
 * Branch coverage of the two middleware classes that gate every authenticated
 * route in the app:
 *   - EnsureUserHasRole         (app/Http/Middleware/EnsureUserHasRole.php)
 *   - EnsureAccountActivated    (app/Http/Middleware/EnsureAccountActivated.php)
 *
 * Each test directly invokes ->handle() and asserts the produced Response /
 * thrown HttpException — no HTTP kernel round-trip.
 *
 * IDs: WBT_HTTP_001..010
 */
class HttpResponsesAndErrorsWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    /* ── EnsureUserHasRole ──────────────────────────── */

    #[Test] // WBT_HTTP_001 — B1a: no authenticated user → 403
    public function role_middleware_aborts_403_when_unauthenticated(): void
    {
        $mw = new EnsureUserHasRole();
        $req = Request::create('/admin/dashboard');

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        $mw->handle($req, fn () => new Response('ok'), 'gm');
    }

    #[Test] // WBT_HTTP_002 — B1b: authenticated but wrong role → 403
    public function role_middleware_aborts_403_on_role_mismatch(): void
    {
        $tenant = $this->makeUser('t@x.com', 'tenant', 'active');
        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $tenant);

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);

        (new EnsureUserHasRole())->handle($req, fn () => new Response('ok'), 'gm');
    }

    #[Test] // WBT_HTTP_003 — B1c: correct role → next() returned, no abort
    public function role_middleware_passes_through_on_match(): void
    {
        $gm = $this->makeUser('g@x.com', 'gm', 'active');
        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $gm);

        $res = (new EnsureUserHasRole())->handle($req, fn () => new Response('ok', 200), 'gm');

        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('ok', $res->getContent());
    }

    /* ── EnsureAccountActivated ─────────────────────── */

    #[Test] // WBT_HTTP_004 — B2a: no user → redirect to login
    public function activated_middleware_redirects_when_no_user(): void
    {
        $req = Request::create('/admin/dashboard');
        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok'));

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertSame(route('login'), $res->getTargetUrl());
    }

    #[Test] // WBT_HTTP_005 — B2b: locked status → logout + redirect to login w/ flash
    public function activated_middleware_logs_out_locked_user(): void
    {
        $u = $this->makeUser('locked@x.com', 'gm', 'locked');
        $u->forceFill(['locked_until' => now()->addMinutes(15)])->save();
        auth()->login($u);

        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $u);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok'));

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertSame(route('login'), $res->getTargetUrl());
        $this->assertGuest();
    }

    #[Test] // WBT_HTTP_006 — B2c: archived status → logout + redirect to login
    public function activated_middleware_logs_out_archived_user(): void
    {
        $u = $this->makeUser('arch@x.com', 'gm', 'archived');
        auth()->login($u);

        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $u);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok'));

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertSame(route('login'), $res->getTargetUrl());
        $this->assertGuest();
    }

    #[Test] // WBT_HTTP_007 — B2d: pending_activation → redirect to otp.verify
    public function activated_middleware_redirects_pending_user_to_otp(): void
    {
        $u = $this->makeUser('p@x.com', 'gm', 'pending_activation');
        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $u);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok'));

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertSame(route('otp.verify'), $res->getTargetUrl());
    }

    #[Test] // WBT_HTTP_008 — B2e: must_change_password + not on change route → redirect
    public function activated_middleware_forces_password_change(): void
    {
        $u = $this->makeUser('pw@x.com', 'gm', 'active');
        $u->forceFill(['must_change_password' => true])->save();

        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $u);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok'));

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertSame(route('password.change'), $res->getTargetUrl());
    }

    #[Test] // WBT_HTTP_009 — B2f: must_change_password + already on change route → passes through
    public function activated_middleware_allows_through_when_on_password_change_route(): void
    {
        $u = $this->makeUser('pw2@x.com', 'gm', 'active');
        $u->forceFill(['must_change_password' => true])->save();

        // Build a request that resolves the named route 'password.change'.
        $req = Request::create(route('password.change'));
        $req->setUserResolver(fn () => $u);
        $route = app('router')->getRoutes()->getByName('password.change');
        $req->setRouteResolver(fn () => $route);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok', 200));

        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('ok', $res->getContent());
    }

    #[Test] // WBT_HTTP_010 — B2g: fully active + activated → passes through
    public function activated_middleware_allows_through_active_user(): void
    {
        $u = $this->makeUser('a@x.com', 'gm', 'active');
        $req = Request::create('/admin/dashboard');
        $req->setUserResolver(fn () => $u);

        $res = (new EnsureAccountActivated())->handle($req, fn () => new Response('ok', 200));

        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('ok', $res->getContent());
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
