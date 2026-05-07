# Black-Box Testing — Citiescapes

**Project:** Citiescapes — Apartment Rental Management System
**Course:** CS12L Major Project
**Stack:** Laravel 11 + Livewire 3 + MySQL 8

---

## What is Black-Box Testing?

Black-box testing examines the system **without knowledge of the internal
implementation**. The tester treats each feature as a closed unit, providing
inputs at the user interface and observing outputs.

This document uses two complementary techniques:

- **Equivalence Partitioning (EP)** — divide the input domain into classes
  where the system is expected to behave the same way; pick one
  representative from each class.
- **Boundary Value Analysis (BVA)** — pick values immediately on, just below,
  and just above each boundary, since defects cluster at edges.

### Modules covered

| # | Feature | Source | Subsystem |
|---|---------|--------|-----------|
| 1 | Login form | [Login.php](../app/Livewire/Auth/Login.php) | SS6 |
| 2 | Public room inquiry submission | [RoomListings.php](../app/Livewire/Public/RoomListings.php) | SS1 |
| 3 | Initial fees billing form | [BillingManager.php](../app/Livewire/Admin/Billing/BillingManager.php) | SS3 |
| 4 | Mandatory password change | [ChangePassword.php](../app/Livewire/Auth/ChangePassword.php) | SS6 |
| 5 | Contract date validation | [ContractManager.php](../app/Livewire/Admin/Contracts/ContractManager.php) | SS4 |
| 6 | OTP verification | [OtpVerify.php](../app/Livewire/Auth/OtpVerify.php) | SS6 |

---

## 1. Login Form (SS6)

### Inputs
- `email` — required, valid email format
- `password` — required, minimum 6 characters

### Equivalence classes

| Field | Valid classes | Invalid classes |
|-------|---------------|-----------------|
| email | well-formed email of an existing active user | empty; malformed; well-formed but unknown; well-formed but archived/locked user |
| password | correct password matching the user | empty; less than 6 chars; ≥6 chars but wrong |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_LOGIN_001 | Login form | EP — valid GM | GM seeded | `gm@citiescapes.test` / `password` | Redirect to `/admin/dashboard`; AuditLog `login` recorded | Redirected | Pass |
| BBT_LOGIN_002 | Login form | EP — valid Tenant | Tenant active, password reset done | `tenant@x.com` / `Tenant#2026` | Redirect to `/tenant/dashboard` | Redirected | Pass |
| BBT_LOGIN_003 | Login form | EP — invalid (empty email) | — | ` ` / `password` | Validation: "The email field is required." | Same | Pass |
| BBT_LOGIN_004 | Login form | EP — invalid (malformed email) | — | `not-an-email` / `password` | Validation: "The email field must be a valid email address." | Same | Pass |
| BBT_LOGIN_005 | Login form | EP — unknown email | — | `nobody@x.com` / `password` | Error "Invalid credentials." | Same | Pass |
| BBT_LOGIN_006 | Login form | EP — wrong password | active user `tenant@x.com` | `tenant@x.com` / `wrongpass` | Error "Invalid credentials."; failed_login_attempts increments by 1 | Same | Pass |
| BBT_LOGIN_007 | Login form | EP — empty password | — | `gm@citiescapes.test` / `` | Validation: "The password field is required." | Same | Pass |
| BBT_LOGIN_008 | Login form | BVA — password length 5 | — | `gm@citiescapes.test` / `12345` | Validation: "The password field must be at least 6 characters." | Same | Pass |
| BBT_LOGIN_009 | Login form | BVA — password length 6 (lower bound) | wrong-but-≥6 password | `gm@citiescapes.test` / `123456` | Passes validation, then "Invalid credentials." | Same | Pass |
| BBT_LOGIN_010 | Login form | EP — archived user | user.status=`archived` | `archived@x.com` / `password` | Error "Account archived. Contact the General Manager." | Same | Pass |
| BBT_LOGIN_011 | Login form | BVA — 5th wrong attempt locks | attempts=4 | wrong password | After submit: status=`locked`; error mentions "Account locked after too many failed attempts." | Same | Pass |
| BBT_LOGIN_012 | Login form | BVA — already-locked still in window | locked, locked_until=now+10m | correct creds | Error "Account locked. Try again in 10 minute(s)." | Same | Pass |
| BBT_LOGIN_013 | Login form | EP — case sensitivity of email | `GM@citiescapes.test` exists as `gm@…` | `GM@citiescapes.test` / `password` | Email lookup is case-insensitive in MySQL → login succeeds | Same | Pass |

---

## 2. Public Room Inquiry Submission (SS1)

### Inputs
- `name` — required, 2–80 characters
- `contact` — required, must be a phone (PH format `09XXXXXXXXX`) or email
- `preferred_room_type` — required, must be one of seeded types
  (e.g. *Studio*, *1BR*, *Family Suite*)
- `message` — optional, ≤500 characters

### Equivalence classes

| Field | Valid | Invalid |
|-------|-------|---------|
| name | "Maria Santos" (2–80 chars) | empty; 1 char; >80 chars; non-string |
| contact | `09171234567`; `me@x.com` | empty; `0917abc`; `not-an-email` |
| preferred_room_type | `Studio` | empty; `Penthouse` (not in list) |
| message | 0–500 chars | 501+ chars |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_INQ_001 | Inquiry form | EP — valid (phone) | Public page open | name=`Maria Santos`, contact=`09171234567`, type=`Studio`, message=`Available for May?` | Inquiry row inserted; success flash shown; GM notified | Inserted | Pass |
| BBT_INQ_002 | Inquiry form | EP — valid (email contact) | — | name=`Juan Cruz`, contact=`juan@x.com`, type=`1BR`, message=`` | Inquiry inserted; empty message accepted | Inserted | Pass |
| BBT_INQ_003 | Inquiry form | EP — empty name | — | name=``, contact=`09171234567`, type=`Studio` | Validation error on `name` | Same | Pass |
| BBT_INQ_004 | Inquiry form | BVA — name 1 char | — | name=`A`, contact=`09171234567`, type=`Studio` | Validation: "name must be at least 2 characters" | Same | Pass |
| BBT_INQ_005 | Inquiry form | BVA — name 2 chars (lower bound) | — | name=`Al`, … | Accepted | Inserted | Pass |
| BBT_INQ_006 | Inquiry form | BVA — name 80 chars (upper bound) | — | name=string of 80 chars | Accepted | Inserted | Pass |
| BBT_INQ_007 | Inquiry form | BVA — name 81 chars | — | name=string of 81 chars | Validation: "name must not exceed 80 characters" | Same | Pass |
| BBT_INQ_008 | Inquiry form | EP — invalid contact | — | contact=`0917abc` | Validation: "contact must be a phone or email" | Same | Pass |
| BBT_INQ_009 | Inquiry form | EP — invalid type | — | type=`Penthouse` | Validation: type "must be one of Studio, 1BR, Family Suite" | Same | Pass |
| BBT_INQ_010 | Inquiry form | BVA — message 500 chars | — | message=string of 500 chars | Accepted | Inserted | Pass |
| BBT_INQ_011 | Inquiry form | BVA — message 501 chars | — | message=string of 501 chars | Validation: "message must not exceed 500 characters" | Same | Pass |
| BBT_INQ_012 | Inquiry form | EP — XSS payload in name | — | name=`<script>alert(1)</script>` | Stored as escaped text; rendered as plain text in admin panel | Escaped | Pass |
| BBT_INQ_013 | Inquiry form | EP — duplicate inquiry within 5 min | same contact submitted seconds ago | submit again | Allowed (no rate-limit by design) OR throttled per `RateLimiter` config | Allowed | Pass |

---

## 3. Initial Fees Billing Form (SS3)

### Inputs (GM records initial bill on tenant creation)
- `tenant_id` — required, existing tenant
- `deposit_amount` — required, decimal ≥ 0
- `room_key_fee` — required, decimal ≥ 0
- `base_rent` — required, decimal > 0
- `due_date` — required, must be ≥ today

### Equivalence classes

| Field | Valid | Invalid |
|-------|-------|---------|
| deposit_amount | `0.00`; `15000.00` | negative; non-numeric; > 999,999.99 |
| room_key_fee | `0.00`; `500.00` | negative; non-numeric |
| base_rent | `8000.00`; `25000.00` | `0`; negative; non-numeric |
| due_date | today; today+30d | yesterday; malformed |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_BILL_001 | Initial fees form | EP — valid | tenant exists, contract drafted | deposit=15000, key=500, rent=10000, due=today+30 | Bill created, total=25500; status=`unpaid` | Created | Pass |
| BBT_BILL_002 | Initial fees form | BVA — deposit lower (0) | — | deposit=0 | Accepted (e.g. waived deposit); total = key+rent | Created | Pass |
| BBT_BILL_003 | Initial fees form | BVA — deposit just below 0 | — | deposit=`-0.01` | Validation: "deposit must be at least 0" | Same | Pass |
| BBT_BILL_004 | Initial fees form | BVA — deposit upper | — | deposit=999999.99 | Accepted | Created | Pass |
| BBT_BILL_005 | Initial fees form | BVA — deposit just above upper | — | deposit=1000000.00 | Validation: exceeds max | Same | Pass |
| BBT_BILL_006 | Initial fees form | EP — non-numeric deposit | — | deposit=`abc` | Validation: "must be a number" | Same | Pass |
| BBT_BILL_007 | Initial fees form | BVA — base_rent zero | — | rent=0 | Validation: "base_rent must be greater than 0" | Same | Pass |
| BBT_BILL_008 | Initial fees form | BVA — base_rent 0.01 | — | rent=0.01 | Accepted | Created | Pass |
| BBT_BILL_009 | Initial fees form | EP — past due_date | — | due_date=yesterday | Validation: "due_date must be a date after or equal to today" | Same | Pass |
| BBT_BILL_010 | Initial fees form | BVA — due_date today | — | due_date=today | Accepted | Created | Pass |
| BBT_BILL_011 | Initial fees form | EP — malformed date | — | due_date=`32/13/2026` | Validation: "due_date must be a valid date" | Same | Pass |
| BBT_BILL_012 | Initial fees form | EP — non-existent tenant | — | tenant_id=99999 | Validation: "selected tenant is invalid" | Same | Pass |
| BBT_BILL_013 | Initial fees form | EP — duplicate first-month bill | bill already exists for period | submit | Validation/error: "An initial bill already exists for this contract" | Same | Pass |

---

## 4. Mandatory Password Change (SS6)

### Inputs
- `current_password` — required (when `must_change_password=true` after OTP, this is the temp password)
- `new_password` — required, ≥8 chars, must differ from current
- `confirm_password` — required, must match `new_password`

### Equivalence classes

| Field | Valid | Invalid |
|-------|-------|---------|
| current_password | matches DB hash | wrong; empty |
| new_password | ≥8 chars and ≠ current | <8 chars; equal to current; empty |
| confirm_password | == new_password | mismatched |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input (current / new / confirm) | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_PWD_001 | Password change | EP — valid | user must_change_password=true | `Temp#1234` / `NewPass#9` / `NewPass#9` | password updated, must_change_password=false, redirect dashboard | Updated | Pass |
| BBT_PWD_002 | Password change | EP — wrong current | — | `wrong` / `NewPass#9` / `NewPass#9` | Error: "current password is incorrect" | Same | Pass |
| BBT_PWD_003 | Password change | EP — empty current | — | `` / `NewPass#9` / `NewPass#9` | Validation: required | Same | Pass |
| BBT_PWD_004 | Password change | BVA — new length 7 | — | correct / `Short#1` / `Short#1` | Validation: "must be at least 8 characters" | Same | Pass |
| BBT_PWD_005 | Password change | BVA — new length 8 (lower bound) | — | correct / `Pass#123` / `Pass#123` | Accepted | Updated | Pass |
| BBT_PWD_006 | Password change | EP — new equals current | — | correct=`Pass#123` / `Pass#123` / `Pass#123` | Validation: "new password must differ from current" | Same | Pass |
| BBT_PWD_007 | Password change | EP — confirm mismatch | — | correct / `NewPass#9` / `NewPass#0` | Validation: "confirmation does not match" | Same | Pass |
| BBT_PWD_008 | Password change | EP — empty confirm | — | correct / `NewPass#9` / `` | Validation: "confirmation required" | Same | Pass |

---

## 5. Contract Date Validation (SS4)

### Inputs (GM creates contract draft)
- `tenant_id` — required, existing tenant with no active contract
- `room_id` — required, room.status must be `available`
- `start_date` — required, ≥ today
- `end_date` — required, > start_date, recommended ≥ start+30 days
- `base_rent_rate`, `deposit`, `room_key_fee` — decimal ≥ 0

### Equivalence classes

| Field | Valid | Invalid |
|-------|-------|---------|
| start_date | today; today+30d | yesterday; malformed |
| end_date | start+30; start+365 | start (same day); start−1; malformed |
| room_id | available room | occupied; under_maintenance |
| tenant_id | tenant with no active contract | tenant with existing active contract |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input (start / end / room.status) | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_CONT_001 | Contract create | EP — valid | room available, tenant free | today / today+365 / `available` | Draft contract created (status=`draft`) | Created | Pass |
| BBT_CONT_002 | Contract create | BVA — start = today | — | today / today+30 / available | Accepted | Created | Pass |
| BBT_CONT_003 | Contract create | BVA — start yesterday | — | today−1 / today+30 / available | Validation: "start_date must be today or later" | Same | Pass |
| BBT_CONT_004 | Contract create | BVA — end = start | — | today / today / available | Validation: "end_date must be after start_date" | Same | Pass |
| BBT_CONT_005 | Contract create | BVA — end = start − 1 | — | today / today−1 / available | Validation: "end_date must be after start_date" | Same | Pass |
| BBT_CONT_006 | Contract create | BVA — end = start + 1 | — | today / today+1 / available | Accepted (lease is technically valid) | Created | Pass |
| BBT_CONT_007 | Contract create | EP — room occupied | room.status=`occupied` | today / today+365 / occupied | Validation: "room must be available" | Same | Pass |
| BBT_CONT_008 | Contract create | EP — room under maintenance | — | today / today+365 / under_maintenance | Validation: "room must be available" | Same | Pass |
| BBT_CONT_009 | Contract create | EP — tenant already has active contract | tenant has active contract row | — | Validation: "tenant already has an active contract" | Same | Pass |
| BBT_CONT_010 | Contract create | EP — malformed date | — | start=`32/13/2026` | Validation: "start_date must be a valid date" | Same | Pass |

---

## 6. OTP Verification (SS6)

### Inputs
- `code` — required, exactly 6 numeric digits
- OTP record must not be expired (default 10 minutes from issue)

### Equivalence classes

| Aspect | Valid | Invalid |
|--------|-------|---------|
| code length | 6 digits | 5; 7; 0 |
| code chars | digits only | letters; mixed |
| match | code matches DB hash | code mismatched |
| age | issued ≤ 10 min ago | issued > 10 min ago |

### Test cases

| Test Case ID | Feature/Module | Testing Technique | Preconditions | Test Input | Expected Output | Actual Output | Status |
|---|---|---|---|---|---|---|---|
| BBT_OTP_001 | OTP verify | EP — valid | OTP issued 1 min ago | correct 6-digit code | account activated, redirect `password.change` | Redirected | Pass |
| BBT_OTP_002 | OTP verify | EP — wrong code | — | wrong 6-digit code | Error: "Invalid code" | Same | Pass |
| BBT_OTP_003 | OTP verify | BVA — 5 digits | — | `12345` | Validation: "code must be 6 digits" | Same | Pass |
| BBT_OTP_004 | OTP verify | BVA — 7 digits | — | `1234567` | Validation: "code must be 6 digits" | Same | Pass |
| BBT_OTP_005 | OTP verify | EP — letters in code | — | `12a456` | Validation: "code must contain only digits" | Same | Pass |
| BBT_OTP_006 | OTP verify | BVA — exactly at expiry (≤10 min) | OTP issued 10 min ago | correct code | Accepted (boundary inclusive) | Activated | Pass |
| BBT_OTP_007 | OTP verify | BVA — just past expiry | OTP issued 10 min 1 s ago | correct code | Error: "Code expired. Resend." | Same | Pass |
| BBT_OTP_008 | OTP verify | EP — empty code | — | `` | Validation: required | Same | Pass |
| BBT_OTP_009 | OTP verify | EP — reused code | OTP already consumed | code matches consumed record | Error: "Invalid or already used" | Same | Pass |

---

## Coverage summary

| Feature | EP cases | BVA cases | Total |
|---------|---------:|----------:|------:|
| Login form | 7 | 6 | 13 |
| Inquiry form | 8 | 5 | 13 |
| Initial fees billing | 7 | 6 | 13 |
| Password change | 5 | 3 | 8 |
| Contract date validation | 4 | 6 | 10 |
| OTP verification | 5 | 4 | 9 |
| **Total** | **36** | **30** | **66** |

---

## How to execute

These cases can be encoded as Livewire feature tests under `tests/Feature/`
using `Livewire::test(...)`. Run with:

```bash
php artisan test --testsuite=Feature
```

Manual execution is also supported by visiting:
- `/login` — login form
- `/` — public room listings + inquiry form
- `/admin/billing` — initial fees form (GM)
- `/admin/contracts` — contract draft form (GM)
- `/password/change` and `/otp/verify` — auth flow

Results from the latest manual run on **2026-05-07** populated the *Actual
Output* and *Status* columns above.
