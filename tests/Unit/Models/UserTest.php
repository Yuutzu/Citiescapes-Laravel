<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White-Box Testing — Module 2: Account Lockout / Auto-Unlock
 *
 * Branch coverage of:
 *   - User::incrementFailedLogin()  (app/Models/User.php:87-98)
 *   - User::autoUnlockIfExpired()   (app/Models/User.php:105-116)
 *   - User::isLocked()              (app/Models/User.php:42)
 *
 * Each test case maps to a row in docs/WHITE_BOX_TESTING.md (WBT_AUTH_001..011).
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 5, 7, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'full_name'             => 'Test User',
            'email'                 => 'test'.uniqid().'@example.com',
            'password'              => 'secret-pass',
            'role'                  => 'tenant',
            'status'                => 'active',
            'failed_login_attempts' => 0,
        ], $overrides));
    }

    /* ── incrementFailedLogin() ───────────────── */

    #[Test]
    public function wbt_auth_001_increment_from_zero_does_not_lock(): void
    {
        $user = $this->makeUser(['failed_login_attempts' => 0]);

        $user->incrementFailedLogin();

        $this->assertSame(1, (int) $user->failed_login_attempts);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->locked_until);
    }

    #[Test]
    public function wbt_auth_002_increment_below_threshold_does_not_lock(): void
    {
        $user = $this->makeUser(['failed_login_attempts' => 3]);

        $user->incrementFailedLogin();

        $this->assertSame(4, (int) $user->failed_login_attempts);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->locked_until);
    }

    #[Test]
    public function wbt_auth_003_increment_to_threshold_locks_account(): void
    {
        $user = $this->makeUser(['failed_login_attempts' => 4]);

        $user->incrementFailedLogin();

        $this->assertSame(5, (int) $user->failed_login_attempts);
        $this->assertSame('locked', $user->status);
        $this->assertNotNull($user->locked_until);
        $this->assertEquals(now()->addMinutes(15), $user->locked_until);
    }

    #[Test]
    public function wbt_auth_004_increment_beyond_threshold_refreshes_lockout(): void
    {
        $user = $this->makeUser([
            'failed_login_attempts' => 5,
            'status'                => 'locked',
            'locked_until'          => now()->subMinutes(2),
        ]);
        $oldLockedUntil = $user->locked_until->copy();

        $user->incrementFailedLogin();

        $this->assertSame(6, (int) $user->failed_login_attempts);
        $this->assertSame('locked', $user->status);
        $this->assertTrue($user->locked_until->greaterThan($oldLockedUntil));
        $this->assertEquals(now()->addMinutes(15), $user->locked_until);
    }

    /* ── autoUnlockIfExpired() ────────────────── */

    #[Test]
    public function wbt_auth_005_auto_unlock_when_locked_and_expired(): void
    {
        $user = $this->makeUser([
            'status'                => 'locked',
            'locked_until'          => now()->subMinute(),
            'failed_login_attempts' => 5,
        ]);

        $result = $user->autoUnlockIfExpired();

        $this->assertTrue($result);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->locked_until);
        $this->assertSame(0, (int) $user->failed_login_attempts);
    }

    #[Test]
    public function wbt_auth_006_no_unlock_when_locked_and_not_expired(): void
    {
        $user = $this->makeUser([
            'status'                => 'locked',
            'locked_until'          => now()->addMinutes(5),
            'failed_login_attempts' => 5,
        ]);

        $result = $user->autoUnlockIfExpired();

        $this->assertFalse($result);
        $this->assertSame('locked', $user->status);
        $this->assertSame(5, (int) $user->failed_login_attempts);
    }

    #[Test]
    public function wbt_auth_007_no_unlock_when_status_is_active(): void
    {
        $user = $this->makeUser(['status' => 'active']);

        $result = $user->autoUnlockIfExpired();

        $this->assertFalse($result);
        $this->assertSame('active', $user->status);
    }

    #[Test]
    public function wbt_auth_008_no_unlock_when_locked_until_is_null(): void
    {
        $user = $this->makeUser([
            'status'       => 'locked',
            'locked_until' => null,
        ]);

        $result = $user->autoUnlockIfExpired();

        $this->assertFalse($result);
        $this->assertSame('locked', $user->status);
    }

    /* ── isLocked() ───────────────────────────── */

    #[Test]
    public function wbt_auth_009_is_locked_true_when_locked_and_future(): void
    {
        $user = $this->makeUser([
            'status'       => 'locked',
            'locked_until' => now()->addMinutes(5),
        ]);

        $this->assertTrue($user->isLocked());
    }

    #[Test]
    public function wbt_auth_010_is_locked_false_when_locked_but_past(): void
    {
        $user = $this->makeUser([
            'status'       => 'locked',
            'locked_until' => now()->subMinutes(5),
        ]);

        $this->assertFalse($user->isLocked());
    }

    #[Test]
    public function wbt_auth_011_is_locked_false_when_status_is_active(): void
    {
        $user = $this->makeUser(['status' => 'active']);

        $this->assertFalse($user->isLocked());
    }
}
