# White-Box Testing — Citiescapes

**Project:** Citiescapes — Apartment Rental Management System
**Course:** CS12L Major Project
**Stack:** Laravel 11 + Livewire 3 + MySQL 8

---

## What is White-Box Testing?

White-box testing (a.k.a. structural testing) examines the **internal logic**
of the code. The tester knows the source and designs test cases that exercise
specific **statements, branches, and paths**.

This document uses **Branch Coverage** as the primary technique: every `if`,
`elseif`, `else`, ternary, and `match` arm must be executed at least once
across the test suite. A test case is added for each decision outcome
(true / false) of every conditional in the modules under test.

---

## How to plan white-box tests per subsystem

For every subsystem in this project, repeat the same five steps:

1. **Pick one method/function** as the unit under test (a Livewire `submit()`,
   a Console command's `handle()`, a model accessor, a service method).
2. **Number every branch** in that method. Every `if` / `elseif` / `else`,
   every `match` arm, every ternary `? :`, every `??`, every short-circuit
   `&&` / `||` is one branch with two outcomes (true / false). Label them
   `B1`, `B1a`, `B2`, … so each test row references the branch it covers.
3. **For each branch outcome, design the minimum input that forces it.**
   Both the *true* path AND the *false* path must be exercised at least once.
4. **Write a PHPUnit feature/unit test, one method per branch ID.** Use
   `RefreshDatabase`, `Carbon::setTestNow(...)` for date-dependent code,
   small helper methods for fixtures (see [ApplyBillingPenaltiesTest.php](../tests/Feature/Console/ApplyBillingPenaltiesTest.php)
   for the canonical pattern).
5. **Mirror the fixtures into a Seeder** ([WhiteBoxBillingScenarioSeeder.php](../database/seeders/WhiteBoxBillingScenarioSeeder.php))
   so the same scenarios can be inspected in MySQL / the live UI by a grader.

The test-case ID format used throughout this doc is
`WBT_<SUBSYSTEM>_<MODULE>_<NNN>` so cases stay sortable and traceable.

---

## Subsystems covered

| #   | Subsystem                    | Modules under test                                                        | Status      |
| --- | ---------------------------- | ------------------------------------------------------------------------- | ----------- |
| SS1 | Public Listings & Inquiries  | (none yet)                                                                | Planned     |
| SS2 | Tenant Management            | (none yet)                                                                | Planned     |
| SS3 | Billing Management           | `ApplyBillingPenalties::handle()`                                         | Tested      |
| SS4 | Contract Management          | `Contract::getTimerBadgeAttribute()`, `AutoArchiveExpiredContracts::handle()` | Tested  |
| SS5 | Reports & Archives           | covered indirectly by SS4 auto-archive (see 4.2)                          | Tested\*    |
| SS6 | System Administration (Auth) | `User::incrementFailedLogin/autoUnlockIfExpired/isLocked`, `Login::login()` | Tested    |
| SS7 | Communications               | `NotificationLog::actionUrl()`, `RequestViewer::respond()`, `InquiryManager::sendEmail()` | Planned |

---

## SS1 — Public Listings & Inquiries

**Status:** Planned. The likely candidate for branch coverage is
[`RoomListings::submitInquiry()`](../app/Livewire/Public/RoomListings.php#L25-L55)
— mostly a single happy path, but the `foreach($gms as $gm)` loop has an
implicit branch (zero GMs vs one-or-more), and validation provides explicit
true/false branches via the `$this->validate(...)` call.

### 1.1 Suggested branches to cover

| Branch | Decision                                       | Outcome to test                          |
| ------ | ---------------------------------------------- | ---------------------------------------- |
| B1     | `validate()` — any rule fails                  | Validation aborts, no row inserted       |
| B1F    | `validate()` — all rules pass                  | Inquiry inserted                         |
| B2     | `foreach($gms)` — 0 active GMs in DB           | Loop body never runs; inquiry still saved |
| B2F    | `foreach($gms)` — N active GMs                 | N notification rows created              |

### 1.2 Suggested test IDs

`WBT_SS1_INQ_001` through `WBT_SS1_INQ_004` (one per branch outcome).

---

## SS2 — Tenant Management

**Status:** Planned. Pick the heaviest decision-bearing methods in
[`TenantManager.php`](../app/Livewire/Admin/Tenants/TenantManager.php) — typically
`save()` (create vs edit), `archive()`, and the search query
(`when($search, ...)` chain).

### 2.1 Suggested branches to cover

| Branch | Decision                                | Outcome to test                          |
| ------ | --------------------------------------- | ---------------------------------------- |
| B1     | `$this->editingId` is null              | Path = create new tenant                 |
| B1F    | `$this->editingId` is set               | Path = update existing tenant            |
| B2     | `when($search, …)` — search empty       | No `where` clause appended               |
| B2F    | `when($search, …)` — search set         | `where('full_name', 'like', …)` appended |
| B3     | `archive()` — tenant has active contract | Block / show error                      |
| B3F    | `archive()` — tenant has no active     | Status flips, audit logged               |

---

## SS3 — Billing Management

### 3.1 Billing Penalty Cascade — `ApplyBillingPenalties::handle()`

#### Source under test

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

**Configured constants** ([config/citiescapes.php](../config/citiescapes.php)):
`grace_days = 3`, `delinquent_day = 14`, `eviction_day = 30`,
`default_daily_rate = 100.00`.

#### Branches identified

| Branch | Decision                     | Outcome covered                 |
| ------ | ---------------------------- | ------------------------------- |
| B1     | `daysOverdue <= 3`           | Grace status set                |
| B1a    | `daysOverdue === 1 OR === 3` | Grace reminder sent             |
| B2     | `4 ≤ daysOverdue < 14`       | Overdue status, penalty applied |
| B3     | `14 ≤ daysOverdue < 30`      | Delinquent status               |
| B3a    | `daysOverdue === 14`         | Delinquent notice sent          |
| B4     | `daysOverdue ≥ 30`           | Eviction status                 |
| B4a    | `daysOverdue === 30`         | Eviction notice sent            |

#### Test cases

| Test Case ID      | Feature/Module          | Testing Technique                       | Preconditions                                  | Test Input (`daysOverdue`) | Expected Output                                                              | Actual Output                                       | Status |
| ----------------- | ----------------------- | --------------------------------------- | ---------------------------------------------- | -------------------------- | ---------------------------------------------------------------------------- | --------------------------------------------------- | ------ |
| WBT_SS3_BILL_001  | Billing penalty cascade | Branch Coverage (B1)                    | Bill `unpaid`, base_rent=10000, utilities=2000 | 1                          | status=`grace`, penalty=0, total=12000, **grace_reminder logged**            | as expected                                         | Pass   |
| WBT_SS3_BILL_002  | Billing penalty cascade | Branch Coverage (B1)                    | Same                                           | 2                          | status=`grace`, penalty=0, no reminder                                       | as expected                                         | Pass   |
| WBT_SS3_BILL_003  | Billing penalty cascade | Branch Coverage (B1, B1a — boundary)    | Same                                           | 3                          | status=`grace`, penalty=0, **grace_reminder logged** (last day)              | as expected                                         | Pass   |
| WBT_SS3_BILL_004  | Billing penalty cascade | Branch Coverage (B2 — entry boundary)   | Same, penalty_rate=100                         | 4                          | status=`overdue`, penalty=100·(4−3)=**100.00**, total=12100                  | as expected                                         | Pass   |
| WBT_SS3_BILL_005  | Billing penalty cascade | Branch Coverage (B2 — middle)           | Same                                           | 10                         | status=`overdue`, penalty=100·7=**700.00**, total=12700                      | as expected                                         | Pass   |
| WBT_SS3_BILL_006  | Billing penalty cascade | Branch Coverage (B2 — exit boundary)    | Same                                           | 13                         | status=`overdue`, penalty=100·10=**1000.00**, total=13000                    | as expected                                         | Pass   |
| WBT_SS3_BILL_007  | Billing penalty cascade | Branch Coverage (B3, B3a — boundary)    | Same                                           | 14                         | status=`delinquent`, penalty=**1100.00**, **delinquent_notice logged**       | as expected                                         | Pass   |
| WBT_SS3_BILL_008  | Billing penalty cascade | Branch Coverage (B3 — middle)           | Same                                           | 20                         | status=`delinquent`, penalty=**1700.00**, no extra notice                    | as expected                                         | Pass   |
| WBT_SS3_BILL_009  | Billing penalty cascade | Branch Coverage (B3 — exit boundary)    | Same                                           | 29                         | status=`delinquent`, penalty=**2600.00**                                     | as expected                                         | Pass   |
| WBT_SS3_BILL_010  | Billing penalty cascade | Branch Coverage (B4, B4a — boundary)    | Same                                           | 30                         | status=`eviction`, penalty=**2700.00**, **eviction_notice logged**           | as expected                                         | Pass   |
| WBT_SS3_BILL_011  | Billing penalty cascade | Branch Coverage (B4 — beyond)           | Same                                           | 45                         | status=`eviction`, penalty=**4200.00**, no duplicate notice                  | as expected                                         | Pass   |
| WBT_SS3_BILL_012  | Billing penalty cascade | Branch Coverage (loop skip)             | Bill already `paid`                            | n/a                        | bill is **excluded** from query, untouched                                   | excluded                                            | Pass   |
| WBT_SS3_BILL_013  | Billing penalty cascade | Branch Coverage (custom grace)          | contract.penalty_grace_days=5                  | 5                          | status=`grace`, penalty=0 (override path)                                    | grace, 0                                            | Pass   |
| WBT_SS3_BILL_014  | Billing penalty cascade | Branch Coverage (null contract values)  | contract.penalty_rate=null                     | 7                          | falls back to default 100, penalty=400                                       | 400                                                 | Pass   |

---

## SS4 — Contract Management

### 4.1 Contract Timer Badge — `Contract::getTimerBadgeAttribute()`

#### Source under test

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

#### Test cases

| Test Case ID      | Feature/Module       | Testing Technique                     | Preconditions | Test Input (status, end_date)         | Expected Output           | Actual Output | Status |
| ----------------- | -------------------- | ------------------------------------- | ------------- | ------------------------------------- | ------------------------- | ------------- | ------ |
| WBT_SS4_CONT_001  | Contract timer badge | Branch Coverage (B1)                  | —             | status=`draft`, end_date=now+60d      | `gray`                    | gray          | Pass   |
| WBT_SS4_CONT_002  | Contract timer badge | Branch Coverage (B1)                  | —             | status=`terminated`, end_date=now+60d | `gray`                    | gray          | Pass   |
| WBT_SS4_CONT_003  | Contract timer badge | Branch Coverage (B2 — boundary)       | —             | status=`active`, end_date=today       | days_remaining=0 → `gray` | gray          | Pass   |
| WBT_SS4_CONT_004  | Contract timer badge | Branch Coverage (B2 — past)           | —             | status=`active`, end_date=now−5d      | days_remaining=0 → `gray` | gray          | Pass   |
| WBT_SS4_CONT_005  | Contract timer badge | Branch Coverage (B3 — entry)          | —             | status=`active`, end_date=now+1d      | `red`                     | red           | Pass   |
| WBT_SS4_CONT_006  | Contract timer badge | Branch Coverage (B3 — exit boundary)  | —             | status=`active`, end_date=now+7d      | `red`                     | red           | Pass   |
| WBT_SS4_CONT_007  | Contract timer badge | Branch Coverage (B4 — entry boundary) | —             | status=`active`, end_date=now+8d      | `amber`                   | amber         | Pass   |
| WBT_SS4_CONT_008  | Contract timer badge | Branch Coverage (B4 — exit boundary)  | —             | status=`active`, end_date=now+30d     | `amber`                   | amber         | Pass   |
| WBT_SS4_CONT_009  | Contract timer badge | Branch Coverage (B5 — entry boundary) | —             | status=`active`, end_date=now+31d     | `green`                   | green         | Pass   |
| WBT_SS4_CONT_010  | Contract timer badge | Branch Coverage (B5 — far)            | —             | status=`active`, end_date=now+365d    | `green`                   | green         | Pass   |

### 4.2 Auto-Archive Expired Contracts — `AutoArchiveExpiredContracts::handle()`

> This module spans **SS4 → SS5**: it archives expired contracts (SS4) into
> the `archives` table that SS5 (Reports & Archives) reads from. It is
> catalogued under SS4 because the *trigger* is a contract event, with a
> cross-reference from SS5.

#### Source under test

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

#### Test cases

| Test Case ID      | Feature/Module                 | Testing Technique                                | Preconditions                                                     | Test Input  | Expected Output                                                           | Actual Output | Status |
| ----------------- | ------------------------------ | ------------------------------------------------ | ----------------------------------------------------------------- | ----------- | ------------------------------------------------------------------------- | ------------- | ------ |
| WBT_SS4_ARCH_001  | Auto-archive expired contracts | Branch Coverage (loop skip)                      | contract.status=`active`, end_date=tomorrow                       | n/a         | contract **not** archived                                                 | not archived  | Pass   |
| WBT_SS4_ARCH_002  | Auto-archive expired contracts | Branch Coverage (B1=T, B2=T)                     | end_date=yesterday; tenant active; room.current_tenant_id matches | run command | contract→`expired`, tenant→`archived`, room→`available`, two Archive rows | as expected   | Pass   |
| WBT_SS4_ARCH_003  | Auto-archive expired contracts | Branch Coverage (B1=F: tenant null)              | tenant_id orphaned                                                | run command | contract archived, **tenant block skipped**, no second Archive            | as expected   | Pass   |
| WBT_SS4_ARCH_004  | Auto-archive expired contracts | Branch Coverage (B1=F: tenant already archived)  | tenant.status=`archived`                                          | run command | contract archived, tenant block skipped                                   | as expected   | Pass   |
| WBT_SS4_ARCH_005  | Auto-archive expired contracts | Branch Coverage (B2=F: room null)                | contract.room_id stale                                            | run command | contract archived, **room block skipped**                                 | as expected   | Pass   |
| WBT_SS4_ARCH_006  | Auto-archive expired contracts | Branch Coverage (B2=F: tenant mismatch)          | room.current_tenant_id ≠ contract.tenant_id                       | run command | room left untouched                                                       | as expected   | Pass   |
| WBT_SS4_ARCH_007  | Auto-archive expired contracts | Branch Coverage (multi-loop)                     | 3 expired contracts queued                                        | run command | command output `Auto-archived 3 expired contracts.`                       | as expected   | Pass   |

---

## SS5 — Reports & Archives

**Status:** Indirectly covered by SS4 §4.2 above (auto-archive writes the
`archives` rows that SS5 reads). Direct coverage of SS5 is **planned**.

### 5.1 Suggested module to cover

[`ReportManager.php`](../app/Livewire/Admin/Reports/ReportManager.php) — the
report query is built with `when($filter, ...)` chains, each of which is a
two-outcome branch. Pattern:

```php
->when($this->dateFrom, fn($q) => $q->where('archived_at', '>=', $this->dateFrom))   // B1
->when($this->dateTo,   fn($q) => $q->where('archived_at', '<=', $this->dateTo))     // B2
->when($this->recordType, fn($q) => $q->where('record_type', $this->recordType))     // B3
```

Every `when` clause needs **two** test cases: one with the filter set, one
without.

### 5.2 Suggested test IDs

`WBT_SS5_REP_001` through `WBT_SS5_REP_006` (3 filters × 2 outcomes).

---

## SS6 — System Administration (Auth)

### 6.1 Account Lockout / Auto-Unlock — `User`

#### Source under test

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
    if ($this->status === 'locked' && $this->locked_until?->isPast()) {  // B2
        $this->update(['status' => 'active', 'locked_until' => null,
            'failed_login_attempts' => 0]);
        return true;
    }
    return false;
}

public function isLocked(): bool {
    return $this->status === 'locked' && $this->locked_until?->isFuture();   // B3
}
```

#### Test cases

| Test Case ID      | Feature/Module         | Testing Technique                          | Preconditions                      | Test Input | Expected Output                                         | Actual Output             | Status |
| ----------------- | ---------------------- | ------------------------------------------ | ---------------------------------- | ---------- | ------------------------------------------------------- | ------------------------- | ------ |
| WBT_SS6_AUTH_001  | `incrementFailedLogin` | Branch Coverage (B1=false)                 | user.failed_attempts=0             | call once  | attempts=1, status unchanged                            | attempts=1, status=active | Pass   |
| WBT_SS6_AUTH_002  | `incrementFailedLogin` | Branch Coverage (B1=false, near threshold) | attempts=3                         | call once  | attempts=4, status=active                               | as expected               | Pass   |
| WBT_SS6_AUTH_003  | `incrementFailedLogin` | Branch Coverage (B1=true — boundary)       | attempts=4                         | call once  | attempts=5, **status=locked**, locked_until ≈ now+15m   | locked, +15m              | Pass   |
| WBT_SS6_AUTH_004  | `incrementFailedLogin` | Branch Coverage (B1=true — beyond)         | attempts=5 (already locked)        | call once  | attempts=6, status=locked, locked_until refreshed       | as expected               | Pass   |
| WBT_SS6_AUTH_005  | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧T)                  | status=locked, locked_until=now−1m | call       | returns **true**, status=active, attempts=0             | true, active, 0           | Pass   |
| WBT_SS6_AUTH_006  | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧F)                  | status=locked, locked_until=now+5m | call       | returns **false**, no change                            | false                     | Pass   |
| WBT_SS6_AUTH_007  | `autoUnlockIfExpired`  | Branch Coverage (B2: F∧·)                  | status=active                      | call       | returns **false**, no change                            | false                     | Pass   |
| WBT_SS6_AUTH_008  | `autoUnlockIfExpired`  | Branch Coverage (B2: T∧null)               | status=locked, locked_until=null   | call       | null-safe → returns **false**                           | false                     | Pass   |
| WBT_SS6_AUTH_009  | `isLocked`             | Branch Coverage (B3: T∧T)                  | status=locked, locked_until=now+5m | call       | returns **true**                                        | true                      | Pass   |
| WBT_SS6_AUTH_010  | `isLocked`             | Branch Coverage (B3: T∧F)                  | status=locked, locked_until=now−5m | call       | returns **false** (eligible for auto-unlock)            | false                     | Pass   |
| WBT_SS6_AUTH_011  | `isLocked`             | Branch Coverage (B3: F∧·)                  | status=active                      | call       | returns **false**                                       | false                     | Pass   |

### 6.2 Login Flow — `Login::login()`

#### Source under test

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

#### Test cases

| Test Case ID      | Feature/Module | Testing Technique                     | Preconditions                             | Test Input (email, password)      | Expected Output                                                                       | Actual Output     | Status |
| ----------------- | -------------- | ------------------------------------- | ----------------------------------------- | --------------------------------- | ------------------------------------------------------------------------------------- | ----------------- | ------ |
| WBT_SS6_LOGIN_001 | Login flow     | Branch Coverage (B1)                  | DB has no user `ghost@x.com`              | `ghost@x.com`, `password`         | error "Invalid credentials"; AuditLog `failed_login` (no user_id)                     | as expected       | Pass   |
| WBT_SS6_LOGIN_002 | Login flow     | Branch Coverage (B2)                  | user locked, locked_until=now+10m         | correct creds                     | error "Account locked. Try again in 10 minute(s)"                                     | as expected       | Pass   |
| WBT_SS6_LOGIN_003 | Login flow     | Branch Coverage (B2 — auto-unlock)    | user locked, locked_until=now−1m          | correct creds                     | auto-unlock fires (B2=false), proceeds to success                                     | redirected        | Pass   |
| WBT_SS6_LOGIN_004 | Login flow     | Branch Coverage (B3)                  | status=`archived`                         | correct creds                     | error "Account archived…"                                                             | as expected       | Pass   |
| WBT_SS6_LOGIN_005 | Login flow     | Branch Coverage (B4 — wrong password) | active user, attempts=0                   | correct email, **wrong** password | failed_login++, attempts=1, error "Invalid credentials"                               | attempts=1, error | Pass   |
| WBT_SS6_LOGIN_006 | Login flow     | Branch Coverage (B4a)                 | active user, attempts=4                   | wrong password                    | attempts=5, status=`locked`, **GMs notified**, error "Account locked after too many…" | as expected       | Pass   |
| WBT_SS6_LOGIN_007 | Login flow     | Branch Coverage (B5)                  | status=`pending_activation`               | correct creds                     | redirect to `otp.verify`, OtpRecord created, OtpMail sent                             | as expected       | Pass   |
| WBT_SS6_LOGIN_008 | Login flow     | Branch Coverage (B6)                  | active user, must_change_password=true    | correct creds                     | redirect to `password.change`                                                         | as expected       | Pass   |
| WBT_SS6_LOGIN_009 | Login flow     | Branch Coverage (B7 — GM)             | role=`gm`, must_change_password=false     | correct creds                     | redirect to `admin.dashboard`                                                         | as expected       | Pass   |
| WBT_SS6_LOGIN_010 | Login flow     | Branch Coverage (B7 — Tenant)         | role=`tenant`, must_change_password=false | correct creds                     | redirect to `tenant.dashboard`                                                        | as expected       | Pass   |
| WBT_SS6_LOGIN_011 | Login flow     | Branch Coverage (validation)          | —                                         | empty email, empty password       | validation errors; no DB query                                                        | as expected       | Pass   |

---

## SS7 — Communications

**Status:** Planned. Three good branch-coverage targets exist in this
subsystem and they have minimal fixtures, so they're a great next module.

### 7.1 `NotificationLog::actionUrl()`

#### Source under test

File: [NotificationLog.php:23-44](../app/Models/NotificationLog.php#L23-L44)

```php
match ($this->type) {
    'new_inquiry'      => route('admin.inquiries.index'),                 // B1
    'tenant_request'   => route('admin.requests.index'),                  // B2
    'request_response' => route('tenant.requests'),                       // B3
    'account_locked'   => route('admin.audit-log'),                       // B4
    'grace_reminder',
    'delinquent_notice',
    'eviction_notice'  => route('tenant.billing'),                        // B5
    'announcement'     => $role === 'gm'                                  // B6 / B6a
        ? route('admin.announcements.index')
        : route('tenant.dashboard'),
    '30_day_warning',
    '7_day_warning'    => $role === 'gm'                                  // B7 / B7a
        ? route('admin.contracts.index')
        : route('tenant.contract'),
    default            => null,                                           // B8
};
```

#### Suggested test IDs

| Test Case ID      | Branch | Test input                               | Expected                                  |
| ----------------- | ------ | ---------------------------------------- | ----------------------------------------- |
| WBT_SS7_NOTIF_001 | B1     | type=`new_inquiry`                       | `route('admin.inquiries.index')`          |
| WBT_SS7_NOTIF_002 | B2     | type=`tenant_request`                    | `route('admin.requests.index')`           |
| WBT_SS7_NOTIF_003 | B3     | type=`request_response`                  | `route('tenant.requests')`                |
| WBT_SS7_NOTIF_004 | B4     | type=`account_locked`                    | `route('admin.audit-log')`                |
| WBT_SS7_NOTIF_005 | B5     | type=`grace_reminder`                    | `route('tenant.billing')`                 |
| WBT_SS7_NOTIF_006 | B6     | type=`announcement`, role=`gm`           | `route('admin.announcements.index')`      |
| WBT_SS7_NOTIF_007 | B6a    | type=`announcement`, role=`tenant`       | `route('tenant.dashboard')`               |
| WBT_SS7_NOTIF_008 | B7     | type=`30_day_warning`, role=`gm`         | `route('admin.contracts.index')`          |
| WBT_SS7_NOTIF_009 | B7a    | type=`7_day_warning`, role=`tenant`      | `route('tenant.contract')`                |
| WBT_SS7_NOTIF_010 | B8     | type=`unknown`                           | `null`                                    |

### 7.2 `RequestViewer::respond()`

```php
$request->update([... 'admin_response' => $this->adminReply ?: null ...]);  // B1 ternary

if ($request->tenant?->email) {                                              // B2
    Mail::to(...)->send(...);
}
```

| Test Case ID    | Branch  | Test input                                  | Expected                                   |
| --------------- | ------- | ------------------------------------------- | ------------------------------------------ |
| WBT_SS7_RESP_001 | B1=T   | adminReply non-empty                        | `admin_response` saved with the text       |
| WBT_SS7_RESP_002 | B1=F   | adminReply empty                            | `admin_response` saved as `null`           |
| WBT_SS7_RESP_003 | B2=T   | tenant has email                            | `Mail::send` invoked once                  |
| WBT_SS7_RESP_004 | B2=F   | tenant.email=null                           | no mail sent, status still updated         |

### 7.3 `InquiryManager::sendEmail()`

```php
$this->validate([...]);                       // B1 validation pass/fail
$inq = Inquiry::findOrFail($this->composingId);
if (! $inq->email) {                           // B2
    session()->flash('error', ...); return;
}
Mail::to($inq->email)->send(...);              // happy path
$inq->update([... 'status' => 'responded' ...]);
```

| Test Case ID         | Branch | Test input                                  | Expected                                              |
| -------------------- | ------ | ------------------------------------------- | ----------------------------------------------------- |
| WBT_SS7_INQEMAIL_001 | B1=F   | emailSubject=``                             | validation aborts, no Mail send                       |
| WBT_SS7_INQEMAIL_002 | B2=T   | inquiry.email=null (manually nulled in DB)  | flash 'no email' error, no Mail send, no status change |
| WBT_SS7_INQEMAIL_003 | happy  | all valid                                   | Mail sent, status=`responded`, gm_notes saved          |

---

## Coverage summary

| Subsystem | Module(s)                                                                                                   | Branches | Covered | Coverage |
| --------- | ----------------------------------------------------------------------------------------------------------- | -------: | ------: | -------: |
| SS1       | Inquiry submit (planned)                                                                                    |       ~4 |       0 |       0% |
| SS2       | Tenant CRUD (planned)                                                                                       |       ~6 |       0 |       0% |
| SS3       | `ApplyBillingPenalties::handle`                                                                             |        7 |       7 |     100% |
| SS4       | `Contract::getTimerBadgeAttribute` + `AutoArchiveExpiredContracts::handle`                                  |        9 |       9 |     100% |
| SS5       | (covered indirectly via SS4 §4.2; direct planned)                                                           |       ~3 |       0 |       0% |
| SS6       | `User::increment/autoUnlock/isLocked` + `Login::login`                                                      |       12 |      12 |     100% |
| SS7       | `NotificationLog::actionUrl` + `RequestViewer::respond` + `InquiryManager::sendEmail` (planned)             |      ~12 |       0 |       0% |
| **Total** |                                                                                                             |  **~53** |  **28** | **~53%** |

> All **executed** test cases follow the **Branch Coverage** technique. Each
> conditional in the modules under test is exercised for both true and false
> outcomes (or every arm of a `match`), yielding 100% branch coverage on the
> three subsystems that have been directly tested. The remaining four
> subsystems have suggested branch lists and test IDs ready to encode.

---

## Findings — defects surfaced by branch coverage

Branch-coverage testing on SS6 §6.1 surfaced a real defect in
[config/citiescapes.php](../config/citiescapes.php) that would have caused
**every account lockout in production to crash** with a `TypeError`.

| ID         | Subsystem / Module                  | Defect                                                                                                                                         | Resolution                                                                       |
| ---------- | ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| WBT-FIND-1 | SS6 §6.1 (`incrementFailedLogin`)   | `now()->addMinutes(config('citiescapes.auth.lockout_minutes', 15))` received a string `'15'` from `env()`; Carbon 3 strictly requires `int\|float`. | Cast all `env()` numerics in `config/citiescapes.php` to `int` at the config layer. |

The same defect pattern threatened `Login::sendOtp()` (OTP expiry) and the
`ApplyBillingPenalties` numeric thresholds. Casting once at the config layer
remediated all three call sites without touching business logic.

This is the kind of defect Black-Box testing would not have surfaced — the
branch (`failed_login_attempts >= threshold`) only fires on the **5th**
failed attempt, which is exactly the boundary case `WBT_SS6_AUTH_003` was
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

Each subsystem with a tested module has a parallel scenario seeder under
`database/seeders/WhiteBox*ScenarioSeeder.php` so the same fixtures the
PHPUnit suite exercises can be inspected in MySQL / the live UI:

```bash
php artisan db:seed --class=WhiteBoxBillingScenarioSeeder
php artisan db:seed --class=WhiteBoxContractTimerScenarioSeeder
# (add a SS7 seeder when SS7 §7.1–7.3 are encoded)
```

> **Note:** This project's `vendor/composer/platform_check.php` requires PHP
> ≥ 8.3. On the dev machine the Laragon-bundled `php-8.3.30` binary is used.
