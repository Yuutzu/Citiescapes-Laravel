# White-Box Testing — Citiescapes

**Project:** Citiescapes — Apartment Rental Management System
**Course:** CS12L Major Project
**Stack:** Laravel 11 + Livewire 3 + MySQL 8

---

## What is White-Box Testing?

White-box testing (a.k.a. structural testing) examines the **internal logic** of the
code. The tester knows the source and designs test cases that exercise specific
**statements, branches, and paths**.

This document uses **Branch Coverage** as the primary technique: every `if`,
`elseif`, `else`, ternary, and `match` arm must be executed at least once across
the test suite. A test case is added for each decision outcome (true/false) of
every conditional in the modules under test.

### Modules covered

| #   | Module                         | Source                                                                                     | Subsystem |
| --- | ------------------------------ | ------------------------------------------------------------------------------------------ | --------- |
| 1   | Billing penalty cascade        | [ApplyBillingPenalties.php](../app/Console/Commands/ApplyBillingPenalties.php)             | SS3       |
| 2   | Account lockout / auto-unlock  | [User.php](../app/Models/User.php)                                                         | SS6       |
| 3   | Contract timer badge           | [Contract.php](../app/Models/Contract.php)                                                 | SS4       |
| 4   | Login flow                     | [Login.php](../app/Livewire/Auth/Login.php)                                                | SS6       |
| 5   | Auto-archive expired contracts | [AutoArchiveExpiredContracts.php](../app/Console/Commands/AutoArchiveExpiredContracts.php) | SS4 → SS5 |

---

## 1. Billing Penalty Cascade — `ApplyBillingPenalties::handle()`

### Source under test

File: [ApplyBillingPenalties.php:33-83](../app/Console/Commands/ApplyBillingPenalties.php#L33-L83)

```php
if ($daysOverdue <= $graceDays) {                       // B1
    $bill->status = 'grace';
    if ($daysOverdue === 1 || $daysOverdue === $graceDays) {   // B1a
        // send grace_reminder
    }
} elseif ($daysOverdue < $delinquentDay) {              // B2
    $bill->penalty_amount = $penaltyRate * ($daysOverdue - $graceDays);
    $bill->status = 'overdue';
} elseif ($daysOverdue < $evictionDay) {                // B3
    $bill->penalty_amount = $penaltyRate * ($daysOverdue - $graceDays);
    $bill->status = 'delinquent';
    if ($daysOverdue === $delinquentDay) { /* notice */ }       // B3a
} else {                                                // B4
    $bill->penalty_amount = $penaltyRate * ($daysOverdue - $graceDays);
    $bill->status = 'eviction';
    if ($daysOverdue === $evictionDay) { /* urgent */ }         // B4a
}
```

**Configured constants** (from [config/citiescapes.php](../config/citiescapes.php)):
`grace_days = 3`, `delinquent_day = 14`, `eviction_day = 30`, `default_daily_rate = 100.00`.

### Branches identified

| Branch | Decision                     | Outcome covered                 |
| ------ | ---------------------------- | ------------------------------- |
| B1     | `daysOverdue <= 3`           | Grace status set                |
| B1a    | `daysOverdue === 1 OR === 3` | Grace reminder sent             |
| B2     | `4 ≤ daysOverdue < 14`       | Overdue status, penalty applied |
| B3     | `14 ≤ daysOverdue < 30`      | Delinquent status               |
| B3a    | `daysOverdue === 14`         | Delinquent notice sent          |
| B4     | `daysOverdue ≥ 30`           | Eviction status                 |
| B4a    | `daysOverdue === 30`         | Eviction notice sent            |

### Test cases

| Test Case ID | Feature/Module          | Testing Technique                      | Preconditions                                  | Test Input (`daysOverdue`) | Expected Output                                                                            | Actual Output                                           | Status |
| ------------ | ----------------------- | -------------------------------------- | ---------------------------------------------- | -------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------- | ------ |
| WBT_BILL_001 | Billing penalty cascade | Branch Coverage (B1)                   | Bill `unpaid`, base_rent=10000, utilities=2000 | 1                          | status=`grace`, penalty=0, total=12000, **grace_reminder logged**                          | status=`grace`, penalty=0, total=12000, reminder logged | Pass   |
| WBT_BILL_002 | Billing penalty cascade | Branch Coverage (B1)                   | Same                                           | 2                          | status=`grace`, penalty=0, total=12000, no reminder                                        | status=`grace`, penalty=0, no reminder                  | Pass   |
| WBT_BILL_003 | Billing penalty cascade | Branch Coverage (B1, B1a — boundary)   | Same                                           | 3                          | status=`grace`, penalty=0, total=12000, **grace_reminder logged** (last day)               | as expected                                             | Pass   |
| WBT_BILL_004 | Billing penalty cascade | Branch Coverage (B2 — entry boundary)  | Same, penalty_rate=100                         | 4                          | status=`overdue`, penalty=100·(4−3)=**100.00**, total=12100                                | 100.00, total=12100                                     | Pass   |
| WBT_BILL_005 | Billing penalty cascade | Branch Coverage (B2 — middle)          | Same                                           | 10                         | status=`overdue`, penalty=100·7=**700.00**, total=12700                                    | 700.00                                                  | Pass   |
| WBT_BILL_006 | Billing penalty cascade | Branch Coverage (B2 — exit boundary)   | Same                                           | 13                         | status=`overdue`, penalty=100·10=**1000.00**, total=13000                                  | 1000.00                                                 | Pass   |
| WBT_BILL_007 | Billing penalty cascade | Branch Coverage (B3, B3a — boundary)   | Same                                           | 14                         | status=`delinquent`, penalty=100·11=**1100.00**, total=13100, **delinquent_notice logged** | as expected                                             | Pass   |
| WBT_BILL_008 | Billing penalty cascade | Branch Coverage (B3 — middle)          | Same                                           | 20                         | status=`delinquent`, penalty=100·17=**1700.00**, no extra notice                           | as expected                                             | Pass   |
| WBT_BILL_009 | Billing penalty cascade | Branch Coverage (B3 — exit boundary)   | Same                                           | 29                         | status=`delinquent`, penalty=100·26=**2600.00**                                            | 2600.00                                                 | Pass   |
| WBT_BILL_010 | Billing penalty cascade | Branch Coverage (B4, B4a — boundary)   | Same                                           | 30                         | status=`eviction`, penalty=100·27=**2700.00**, **eviction_notice logged**                  | as expected                                             | Pass   |
| WBT_BILL_011 | Billing penalty cascade | Branch Coverage (B4 — beyond)          | Same                                           | 45                         | status=`eviction`, penalty=100·42=**4200.00**, no duplicate notice                         | as expected                                             | Pass   |
| WBT_BILL_012 | Billing penalty cascade | Branch Coverage (loop skip)            | Bill already `paid`                            | n/a                        | bill is **excluded** from query, untouched                                                 | excluded                                                | Pass   |
| WBT_BILL_013 | Billing penalty cascade | Branch Coverage (custom grace)         | contract.penalty_grace_days=5                  | 5                          | status=`grace`, penalty=0 (override path)                                                  | grace, 0                                                | Pass   |
| WBT_BILL_014 | Billing penalty cascade | Branch Coverage (null contract values) | contract.penalty_rate=null                     | 7                          | falls back to default 100, penalty=400                                                     | 400                                                     | Pass   |

---

## 2. Account Lockout / Auto-Unlock — `User`

### Source under test

File: [User.php:87-116](../app/Models/User.php#L87-L116)

```php
public function incrementFailedLogin(): void
{
    $this->increment('failed_login_attempts');
    $threshold = config('citiescapes.auth.lockout_threshold', 5);
    if ($this->failed_login_attempts >= $threshold) {       // B1
        $this->update(['status' => 'locked',
            'locked_until' => now()->addMinutes(...)]);
    }
}

public function autoUnlockIfExpired(): bool
{
    if ($this->status === 'locked' && $this->locked_until?->isPast()) {  // B2 (compound)
        $this->update(['status' => 'active', 'locked_until' => null,
            'failed_login_attempts' => 0]);
        return true;
    }
    return false;
}

public function isLocked(): bool {
    return $this->status === 'locked' && $this->locked_until?->isFuture();   // B3 (compound)
}
```

### Test cases

| Test Case ID | Feature/Module         | Testing Technique                          | Preconditions                      | Test Input | Expected Output                                         | Actual Output             | Status |
| ------------ | ---------------------- | ------------------------------------------ | ---------------------------------- | ---------- | ------------------------------------------------------- | ------------------------- | ------ |
| WBT_AUTH_001 | `incrementFailedLogin` | Branch Coverage (B1=false)                 | user.failed_attempts=0             | call once  | attempts=1, status unchanged                            | attempts=1, status=active | Pass   |
| WBT_AUTH_002 | `incrementFailedLogin` | Branch Coverage (B1=false, near threshold) | attempts=3                         | call once  | attempts=4, status=active                               | as expected               | Pass   |
| WBT_AUTH_003 | `incrementFailedLogin` | Branch Coverage (B1=true — boundary)       | attempts=4                         | call once  | attempts=5, **status=locked**, locked_until ≈ now+15m   | locked, +15m              | Pass   |
| WBT_AUTH_004 | `incrementFailedLogin` | Branch Coverage (B1=true — beyond)         | attempts=5 (already locked)        | call once  | attempts=6, status=locked, locked_until refreshed       | as expected               | Pass   |
| WBT_AUTH_005 | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧T)                  | status=locked, locked_until=now−1m | call       | returns **true**, status=active, attempts=0             | true, active, 0           | Pass   |
| WBT_AUTH_006 | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧F)                  | status=locked, locked_until=now+5m | call       | returns **false**, no change                            | false                     | Pass   |
| WBT_AUTH_007 | `autoUnlockIfExpired`  | Branch Coverage (B2: F∧·)                  | status=active                      | call       | returns **false**, no change                            | false                     | Pass   |
| WBT_AUTH_008 | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧null)               | status=locked, locked_until=null   | call       | null-safe `?->isPast()` → false; returns **false**      | false                     | Pass   |
| WBT_AUTH_009 | `isLocked`             | Branch Coverage (B3: T∧T)                  | status=locked, locked_until=now+5m | call       | returns **true**                                        | true                      | Pass   |
| WBT_AUTH_010 | `isLocked`             | Branch Coverage (B3: T∧F)                  | status=locked, locked_until=now−5m | call       | returns **false** (treated as eligible for auto-unlock) | false                     | Pass   |
| WBT_AUTH_011 | `isLocked`             | Branch Coverage (B3: F∧·)                  | status=active                      | call       | returns **false**                                       | false                     | Pass   |

---

## 3. Contract Timer Badge — `Contract::getTimerBadgeAttribute()`

### Source under test

File: [Contract.php:63-71](../app/Models/Contract.php#L63-L71)

```php
public function getTimerBadgeAttribute(): string
{
    if ($this->status !== 'active') return 'gray';   // B1
    $d = $this->days_remaining;
    if ($d <= 0)   return 'gray';                    // B2
    if ($d <= 7)   return 'red';                     // B3
    if ($d <= 30)  return 'amber';                   // B4
    return 'green';                                  // B5
}
```

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input (status, end_date) | Expected Output | Actual Output | Status |
|--------------|----------------------|---------------------------------------| ————————————— |---------------------------------------|---------------------------|---------------|--------|
| WBT_CONT_001 | Contract timer badge | Branch Coverage (B1) | ————————————— | status=`draft`, end_date=now+60d | `gray` | gray | Pass |
| WBT_CONT_002 | Contract timer badge | Branch Coverage (B1) | ————————————— | status=`terminated`, end_date=now+60d | `gray` | gray | Pass |
| WBT_CONT_003 | Contract timer badge | Branch Coverage (B2 — boundary) | ————————————— | status=`active`, end_date=today | days_remaining=0 → `gray` | gray | Pass |
| WBT_CONT_004 | Contract timer badge | Branch Coverage (B2 — past) | ————————————— | status=`active`, end_date=now−5d | days_remaining=0 → `gray` | gray | Pass |
| WBT_CONT_005 | Contract timer badge | Branch Coverage (B3 — entry) | ————————————— | status=`active`, end_date=now+1d | `red` | red | Pass |
| WBT_CONT_006 | Contract timer badge | Branch Coverage (B3 — exit boundary) | ————————————— | status=`active`, end_date=now+7d | `red` | red | Pass |
| WBT_CONT_007 | Contract timer badge | Branch Coverage (B4 — entry boundary) | ————————————— | status=`active`, end_date=now+8d | `amber` | amber | Pass |
| WBT_CONT_008 | Contract timer badge | Branch Coverage (B4 — exit boundary) | ————————————— | status=`active`, end_date=now+30d | `amber` | amber | Pass |
| WBT_CONT_009 | Contract timer badge | Branch Coverage (B5 — entry boundary) | ————————————— | status=`active`, end_date=now+31d | `green` | green | Pass |
| WBT_CONT_010 | Contract timer badge | Branch Coverage (B5 — far) | ————————————— | status=`active`, end_date=now+365d | `green` | green | Pass |

---

## 4. Login Flow — `Login::login()`

### Source under test

File: [Login.php:23-95](../app/Livewire/Auth/Login.php#L23-L95)

```php
$user = User::where('email', $this->email)->first();
if (!$user) { /* B1 */ return error; }

$user->autoUnlockIfExpired();
if ($user->isLocked())              { /* B2 */ return error; }
if ($user->status === 'archived')   { /* B3 */ return error; }

if (!Hash::check($this->password, $user->password)) {        // B4
    $user->incrementFailedLogin();
    if ($user->status === 'locked') { /* B4a */ notify GMs; return error; }
    return error;
}
$user->resetFailedLogin();
Auth::login($user, true);

if ($user->status === 'pending_activation') { /* B5 */ return redirect otp; }
if ($user->must_change_password)            { /* B6 */ return redirect change-password; }

return redirect $user->isGm() ? admin : tenant;              // B7
```

### Test cases

| Test Case ID  | Feature/Module | Testing Technique                     | Preconditions                             | Test Input (email, password)      | Expected Output                                                                       | Actual Output     | Status |
| ------------- | -------------- | ------------------------------------- | ----------------------------------------- | --------------------------------- | ------------------------------------------------------------------------------------- | ----------------- | ------ |
| WBT_LOGIN_001 | Login flow     | Branch Coverage (B1)                  | DB has no user `ghost@x.com`              | `ghost@x.com`, `password`         | error "Invalid credentials"; AuditLog `failed_login` (no user_id)                     | as expected       | Pass   |
| WBT_LOGIN_002 | Login flow     | Branch Coverage (B2)                  | user locked, locked_until=now+10m         | correct creds                     | error "Account locked. Try again in 10 minute(s)"                                     | as expected       | Pass   |
| WBT_LOGIN_003 | Login flow     | Branch Coverage (B2 — auto-unlock)    | user locked, locked_until=now−1m          | correct creds                     | auto-unlock fires (B2=false), proceeds to success                                     | redirected        | Pass   |
| WBT_LOGIN_004 | Login flow     | Branch Coverage (B3)                  | status=`archived`                         | correct creds                     | error "Account archived…"                                                             | as expected       | Pass   |
| WBT_LOGIN_005 | Login flow     | Branch Coverage (B4 — wrong password) | active user, attempts=0                   | correct email, **wrong** password | failed_login++, attempts=1, error "Invalid credentials"                               | attempts=1, error | Pass   |
| WBT_LOGIN_006 | Login flow     | Branch Coverage (B4a)                 | active user, attempts=4                   | wrong password                    | attempts=5, status=`locked`, **GMs notified**, error "Account locked after too many…" | as expected       | Pass   |
| WBT_LOGIN_007 | Login flow     | Branch Coverage (B5)                  | status=`pending_activation`               | correct creds                     | redirect to `otp.verify`, OtpRecord created, OtpMail sent                             | as expected       | Pass   |
| WBT_LOGIN_008 | Login flow     | Branch Coverage (B6)                  | active user, must_change_password=true    | correct creds                     | redirect to `password.change`                                                         | as expected       | Pass   |
| WBT_LOGIN_009 | Login flow     | Branch Coverage (B7 — GM)             | role=`gm`, must_change_password=false     | correct creds                     | redirect to `admin.dashboard`                                                         | as expected       | Pass   |
| WBT_LOGIN_010 | Login flow     | Branch Coverage (B7 — Tenant)         | role=`tenant`, must_change_password=false | correct creds                     | redirect to `tenant.dashboard`                                                        | as expected       | Pass   |
| WBT_LOGIN_011 | Login flow     | Branch Coverage (validation)          | —                                         | empty email, empty password       | validation errors on `email` & `password`; no DB query                                | as expected       | Pass   |

---

## 5. Auto-Archive Expired Contracts — `AutoArchiveExpiredContracts::handle()`

### Source under test

File: [AutoArchiveExpiredContracts.php:24-65](../app/Console/Commands/AutoArchiveExpiredContracts.php#L24-L65)

```php
foreach ($contracts as $contract) {
    $contract->update(['status' => 'expired']);
    Archive::create([... record_type => 'contract' ...]);

    if ($contract->tenant && $contract->tenant->status === 'active') {  // B1
        $contract->tenant->update(['status' => 'archived', ...]);
        Archive::create([... record_type => 'tenant_account' ...]);
    }

    if ($contract->room && $contract->room->current_tenant_id === $contract->tenant_id) {  // B2
        $contract->room->update(['current_tenant_id' => null,
            'status' => 'available', ...]);
    }
}
```

### Test cases

| Test Case ID | Feature/Module                 | Testing Technique                               | Preconditions                                                     | Test Input (contract end_date / tenant status / room state) | Expected Output                                                           | Actual Output | Status |
| ------------ | ------------------------------ | ----------------------------------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------------------- | ------------- | ------ |
| WBT_ARCH_001 | Auto-archive expired contracts | Branch Coverage (loop skip)                     | contract.status=`active`, end_date=tomorrow                       | n/a                                                         | contract **not** archived                                                 | not archived  | Pass   |
| WBT_ARCH_002 | Auto-archive expired contracts | Branch Coverage (B1=T, B2=T)                    | end_date=yesterday; tenant active; room.current_tenant_id matches | run command                                                 | contract→`expired`, tenant→`archived`, room→`available`, two Archive rows | as expected   | Pass   |
| WBT_ARCH_003 | Auto-archive expired contracts | Branch Coverage (B1=F: tenant null)             | tenant_id orphaned (deleted)                                      | run command                                                 | contract archived, **tenant block skipped**, no second Archive            | as expected   | Pass   |
| WBT_ARCH_004 | Auto-archive expired contracts | Branch Coverage (B1=F: tenant already archived) | tenant.status=`archived`                                          | run command                                                 | contract archived, tenant block skipped (no second Archive row)           | as expected   | Pass   |
| WBT_ARCH_005 | Auto-archive expired contracts | Branch Coverage (B2=F: room null)               | contract.room_id stale                                            | run command                                                 | contract archived, **room block skipped**                                 | as expected   | Pass   |
| WBT_ARCH_006 | Auto-archive expired contracts | Branch Coverage (B2=F: tenant mismatch)         | room.current_tenant_id ≠ contract.tenant_id                       | run command                                                 | room left untouched (different tenant)                                    | as expected   | Pass   |
| WBT_ARCH_007 | Auto-archive expired contracts | Branch Coverage (multi-loop)                    | 3 expired contracts queued                                        | run command                                                 | command output `Auto-archived 3 expired contracts.`                       | as expected   | Pass   |

---

## Coverage summary

| Module                              | Branches     | Branches Covered | Coverage |
| ----------------------------------- | ------------ | ---------------- | -------- |
| ApplyBillingPenalties::handle       | 7            | 7                | 100%     |
| User::incrementFailedLogin          | 1            | 1                | 100%     |
| User::autoUnlockIfExpired           | 2 (compound) | 2                | 100%     |
| User::isLocked                      | 2 (compound) | 2                | 100%     |
| Contract::getTimerBadgeAttribute    | 5            | 5                | 100%     |
| Login::login                        | 9            | 9                | 100%     |
| AutoArchiveExpiredContracts::handle | 4            | 4                | 100%     |
| **Total**                           | **30**       | **30**           | **100%** |

> All test cases follow the **Branch Coverage** technique. Each conditional in
> the modules under test is exercised for both true and false outcomes
> (or every arm of a `match`/cascading `elseif`), yielding 100% branch
> coverage on the SUT.

---

## Findings — defects surfaced by branch coverage

Branch-coverage testing on Module 2 surfaced a real defect in
[config/citiescapes.php](../config/citiescapes.php) that would have caused
**every account lockout in production to crash** with a `TypeError`.

| ID         | Module                          | Defect                                                                                                                                       | Resolution                                                                       |
| ---------- | ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| WBT-FIND-1 | Module 2 (`incrementFailedLogin`) | `now()->addMinutes(config('citiescapes.auth.lockout_minutes', 15))` received a string `'15'` from `env()`; Carbon 3 strictly requires `int\|float`. | Cast all `env()` numerics in `config/citiescapes.php` to `int` at the config layer. |

The same defect pattern threatened `Login::sendOtp()` (OTP expiry) and the
`ApplyBillingPenalties` numeric thresholds. Casting once at the config layer
remediated all three call sites without touching business logic.

This is the kind of defect that Black-Box testing would not have surfaced —
the branch (`failed_login_attempts >= threshold`) only fires on the
**5th** failed attempt, which is exactly the boundary case `WBT_AUTH_003` was
written to exercise.

---

## How to execute

These tests are encoded as PHPUnit tests under `tests/Unit/Models/` and
`tests/Feature/`. Run with:

```bash
php artisan test
# or with coverage (requires Xdebug / PCOV)
php artisan test --coverage --min=85
```

> **Note:** This project's `vendor/composer/platform_check.php` requires PHP
> ≥ 8.3. On the dev machine the Laragon-bundled `php-8.3.30` binary is used.
