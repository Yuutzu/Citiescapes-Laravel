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

---

## How to plan black-box tests per subsystem

For every subsystem in this project, repeat the same five steps:

1. **List the user-facing features** in that subsystem (every form, every
   action button, every flow that takes input).
2. **For each feature, list the inputs** (form fields, dropdowns, URL params).
   Record each rule: required? min/max? format? allowed values?
3. **Build EP classes per field** — one *valid* class plus one *invalid* class
   per way the input can be wrong. Pick a representative value for each.
4. **Build BVA cases per numeric/length boundary** — three values per limit
   (just-below, exactly on, just-above).
5. **Document each case as a row** with these columns:
   `Test ID | Feature | Technique | Preconditions | Test Input | Expected | Actual | Pass/Fail`,
   then run them manually in the browser and fill in the *Actual* column.

The test-case ID format used throughout this doc is
`BBT_<SUBSYSTEM>_<FEATURE>_<NNN>` so cases stay sortable and traceable.

---

## Subsystems covered

| #   | Subsystem                    | Features under test                                                                                                                       | Status      |
| --- | ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| SS1 | Public Listings & Inquiries  | Public room inquiry submission                                                                                                            | Tested      |
| SS2 | Tenant Management            | Tenant CRUD, archive/restore, search                                                                                                      | Planned     |
| SS3 | Billing Management           | Initial fees billing form                                                                                                                 | Tested      |
| SS4 | Contract Management          | Contract create / date validation                                                                                                         | Tested      |
| SS5 | Reports & Archives           | Report filters, archive viewer                                                                                                            | Planned     |
| SS6 | System Administration (Auth) | Login form, mandatory password change, OTP verification                                                                                   | Tested      |
| SS7 | Communications               | Tenant request/complaint, admin response, inquiry reply email                                                                             | Planned     |

---

## SS1 — Public Listings & Inquiries

### 1.1 Public Room Inquiry Submission

#### Source under test

[`RoomListings::submitInquiry()`](../app/Livewire/Public/RoomListings.php#L25-L55)

```php
$this->validate([
    'sender_name'    => 'required|max:100',
    'contact_number' => 'required|max:20',
    'inquiryEmail'   => 'nullable|email|max:100',
    'message'        => 'required|max:500',
]);
```

#### Inputs (form fields on the public landing page)

- `sender_name` — required, max 100 chars
- `contact_number` — required, max 20 chars (free-form text — no phone format check)
- `inquiryEmail` — optional; if provided, must be a valid email and max 100 chars
- `preferred_room_type` — dropdown limited by UI to `any` | `small` | `big`
- `message` — required, max 500 chars

#### Equivalence classes

| Field               | Valid classes                                      | Invalid classes                       |
| ------------------- | -------------------------------------------------- | ------------------------------------- |
| sender_name         | non-empty string ≤100 chars                        | empty; >100 chars                     |
| contact_number      | non-empty string ≤20 chars                         | empty; >20 chars                      |
| inquiryEmail        | empty (nullable) **or** well-formed email ≤100     | malformed email; >100 chars           |
| preferred_room_type | `any`, `small`, `big`                              | — (UI-restricted)                     |
| message             | non-empty string ≤500 chars                        | empty; >500 chars                     |

#### Test cases

| Test Case ID    | Feature/Module | Testing Technique                        | Preconditions   | Test Input                                                                                                          | Expected Output                                                                       | Actual Output | Status |
| --------------- | -------------- | ---------------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- | ------------- | ------ |
| BBT_SS1_INQ_001 | Inquiry form   | EP — valid (with email)                  | Public page open | sender_name=`Maria Santos`, contact_number=`09171234567`, inquiryEmail=`maria@x.com`, type=`small`, message=`Available?` | Row inserted in `inquiries`; success flash; GM(s) get a `new_inquiry` notification | Inserted      | Pass   |
| BBT_SS1_INQ_002 | Inquiry form   | EP — valid (no email)                    | —               | sender_name=`Juan Cruz`, contact_number=`09221234567`, inquiryEmail=``, type=`big`, message=`Asking about big`        | Inserted; empty email accepted (nullable)                                             | Inserted      | Pass   |
| BBT_SS1_INQ_003 | Inquiry form   | EP — empty sender_name                   | —               | sender_name=``, rest valid                                                                                          | Validation: "The sender name field is required."                                      | Same          | Pass   |
| BBT_SS1_INQ_004 | Inquiry form   | BVA — sender_name 100 chars              | —               | sender_name = 100-char string                                                                                       | Accepted                                                                              | Inserted      | Pass   |
| BBT_SS1_INQ_005 | Inquiry form   | BVA — sender_name 101 chars              | —               | sender_name = 101-char string                                                                                       | Validation: "The sender name field must not be greater than 100 characters."          | Same          | Pass   |
| BBT_SS1_INQ_006 | Inquiry form   | EP — empty contact_number                | —               | contact_number=``, rest valid                                                                                       | Validation: "The contact number field is required."                                   | Same          | Pass   |
| BBT_SS1_INQ_007 | Inquiry form   | BVA — contact_number 20 chars            | —               | contact_number = 20-char string                                                                                     | Accepted                                                                              | Inserted      | Pass   |
| BBT_SS1_INQ_008 | Inquiry form   | BVA — contact_number 21 chars            | —               | contact_number = 21-char string                                                                                     | Validation: "The contact number field must not be greater than 20 characters."        | Same          | Pass   |
| BBT_SS1_INQ_009 | Inquiry form   | EP — malformed phone string              | —               | contact_number=`0917abc`, rest valid                                                                                | **Accepted** — no server-side phone format rule                                       | Inserted      | Pass\* |
| BBT_SS1_INQ_010 | Inquiry form   | EP — malformed email                     | —               | inquiryEmail=`not-an-email`, rest valid                                                                             | Validation: "The inquiry email field must be a valid email address."                  | Same          | Pass   |
| BBT_SS1_INQ_011 | Inquiry form   | BVA — inquiryEmail 100 chars             | —               | inquiryEmail = 100-char valid email                                                                                 | Accepted                                                                              | Inserted      | Pass   |
| BBT_SS1_INQ_012 | Inquiry form   | BVA — inquiryEmail 101 chars             | —               | inquiryEmail = 101-char email                                                                                       | Validation: "must not be greater than 100 characters."                                | Same          | Pass   |
| BBT_SS1_INQ_013 | Inquiry form   | EP — preferred_room_type each option     | —               | type ∈ {`any`,`small`,`big`}                                                                                        | All three accepted; saved verbatim                                                    | Inserted      | Pass   |
| BBT_SS1_INQ_014 | Inquiry form   | EP — empty message                       | —               | message=``, rest valid                                                                                              | Validation: "The message field is required."                                          | Same          | Pass   |
| BBT_SS1_INQ_015 | Inquiry form   | BVA — message 500 chars                  | —               | message = 500-char string                                                                                           | Accepted                                                                              | Inserted      | Pass   |
| BBT_SS1_INQ_016 | Inquiry form   | BVA — message 501 chars                  | —               | message = 501-char string                                                                                           | Validation: "The message field must not be greater than 500 characters."              | Same          | Pass   |
| BBT_SS1_INQ_017 | Inquiry form   | EP — XSS payload in sender_name          | —               | sender_name=`<script>alert(1)</script>`, rest valid                                                                 | Stored as raw text; rendered escaped in `/admin/inquiries`                            | Escaped       | Pass   |
| BBT_SS1_INQ_018 | Inquiry form   | EP — duplicate submission                | submit twice    | second submit                                                                                                       | Both rows insert (no rate-limit by design)                                            | 2 rows        | Pass   |

> **Note on BBT_SS1_INQ_009 (malformed phone):** the current implementation
> only validates `contact_number` for *length*, not format. To reject bad
> formats, add `'contact_number' => 'required|regex:/^09\d{9}$/|max:20'`.

---

## SS2 — Tenant Management

**Status:** Planned. The features below have not yet been black-box tested.
This section documents the test plan so the same EP/BVA recipe can be applied.

### 2.1 Planned features under test

- **Tenant create form** ([TenantManager.php](../app/Livewire/Admin/Tenants/TenantManager.php))
  — fields: `full_name`, `email`, `contact_number`, `emergency_contact`, `role`.
- **Tenant edit form** — same fields, plus status toggle.
- **Archive / restore** — destructive action with confirmation.
- **Search & filter** — text search over `full_name` / `email`, status filter.

### 2.2 Test plan template

For each form field, record:

| Field             | Rule (read from `$this->validate(...)`) | EP class examples                | BVA cases                     |
| ----------------- | --------------------------------------- | -------------------------------- | ----------------------------- |
| full_name         | required, max N                         | empty / valid / over-max         | N−1, N, N+1                   |
| email             | required, email, unique:users           | empty / malformed / duplicate    | length boundary if `max:` set |
| contact_number    | required, max N                         | empty / valid / over-max         | N−1, N, N+1                   |
| role              | required, in:gm,tenant                  | one valid + one out-of-list      | —                             |

### 2.3 Specific scenarios to add

- `BBT_SS2_TEN_001` — create tenant with all valid fields.
- `BBT_SS2_TEN_002` — duplicate email → "email has already been taken".
- `BBT_SS2_TEN_003` — empty required field → required message.
- `BBT_SS2_TEN_004..007` — BVA on `full_name` and `contact_number` length.
- `BBT_SS2_TEN_008` — invalid role → "selected role is invalid".
- `BBT_SS2_TEN_009` — archive an active tenant → status flips to `archived`,
  flash message, row hidden from active list.
- `BBT_SS2_TEN_010` — restore archived tenant → status back to `active`.
- `BBT_SS2_TEN_011..013` — search with empty / 1-char / matching / no-match queries.

> Open `TenantManager.php` and copy each `$this->validate([...])` rule into
> the table above to drive the actual case list before running the suite.

---

## SS3 — Billing Management

### 3.1 Initial Fees Billing Form

#### Inputs (GM records initial bill on tenant creation)

- `tenant_id` — required, existing tenant
- `deposit_amount` — required, decimal ≥ 0
- `room_key_fee` — required, decimal ≥ 0
- `base_rent` — required, decimal > 0
- `due_date` — required, must be ≥ today

#### Equivalence classes

| Field          | Valid                  | Invalid                     |
| -------------- | ---------------------- | --------------------------- |
| deposit_amount | `0.00`; `15000.00`     | negative; non-numeric; > max |
| room_key_fee   | `0.00`; `500.00`       | negative; non-numeric       |
| base_rent      | `8000.00`; `25000.00`  | `0`; negative; non-numeric  |
| due_date       | today; today+30d       | yesterday; malformed        |

#### Test cases

| Test Case ID     | Feature/Module    | Testing Technique                  | Preconditions                  | Test Input                                | Expected Output                                                | Actual Output | Status |
| ---------------- | ----------------- | ---------------------------------- | ------------------------------ | ----------------------------------------- | -------------------------------------------------------------- | ------------- | ------ |
| BBT_SS3_BILL_001 | Initial fees form | EP — valid                         | tenant exists, contract drafted | deposit=15000, key=500, rent=10000, due=today+30 | Bill created, total=25500; status=`unpaid`            | Created       | Pass   |
| BBT_SS3_BILL_002 | Initial fees form | BVA — deposit lower (0)            | —                              | deposit=0                                 | Accepted (waived deposit); total = key+rent                    | Created       | Pass   |
| BBT_SS3_BILL_003 | Initial fees form | BVA — deposit just below 0         | —                              | deposit=`-0.01`                           | Validation: "deposit must be at least 0"                       | Same          | Pass   |
| BBT_SS3_BILL_004 | Initial fees form | BVA — deposit upper                | —                              | deposit=999999.99                         | Accepted                                                       | Created       | Pass   |
| BBT_SS3_BILL_005 | Initial fees form | BVA — deposit just above upper     | —                              | deposit=1000000.00                        | Validation: exceeds max                                        | Same          | Pass   |
| BBT_SS3_BILL_006 | Initial fees form | EP — non-numeric deposit           | —                              | deposit=`abc`                             | Validation: "must be a number"                                 | Same          | Pass   |
| BBT_SS3_BILL_007 | Initial fees form | BVA — base_rent zero               | —                              | rent=0                                    | Validation: "base_rent must be greater than 0"                 | Same          | Pass   |
| BBT_SS3_BILL_008 | Initial fees form | BVA — base_rent 0.01               | —                              | rent=0.01                                 | Accepted                                                       | Created       | Pass   |
| BBT_SS3_BILL_009 | Initial fees form | EP — past due_date                 | —                              | due_date=yesterday                        | Validation: "due_date must be a date after or equal to today" | Same          | Pass   |
| BBT_SS3_BILL_010 | Initial fees form | BVA — due_date today               | —                              | due_date=today                            | Accepted                                                       | Created       | Pass   |
| BBT_SS3_BILL_011 | Initial fees form | EP — malformed date                | —                              | due_date=`32/13/2026`                     | Validation: "due_date must be a valid date"                    | Same          | Pass   |
| BBT_SS3_BILL_012 | Initial fees form | EP — non-existent tenant           | —                              | tenant_id=99999                           | Validation: "selected tenant is invalid"                       | Same          | Pass   |
| BBT_SS3_BILL_013 | Initial fees form | EP — duplicate first-month bill    | bill exists for period         | submit                                    | Error: "An initial bill already exists for this contract"      | Same          | Pass   |

### 3.2 Planned (next iteration)

- Monthly bill generation form — fields: `billing_period`, `base_rent`,
  `utilities`, `extras`, `due_date`. Same EP/BVA recipe.
- Mark-paid action — date paid, payment method dropdown, amount paid (BVA on
  partial payment, exact, overpayment).

---

## SS4 — Contract Management

### 4.1 Contract Date Validation

#### Inputs (GM creates contract draft)

- `tenant_id` — required, existing tenant with no active contract
- `room_id` — required, room.status must be `available`
- `start_date` — required, ≥ today
- `end_date` — required, > start_date
- `base_rent_rate`, `deposit`, `room_key_fee` — decimal ≥ 0

#### Equivalence classes

| Field      | Valid                      | Invalid                               |
| ---------- | -------------------------- | ------------------------------------- |
| start_date | today; today+30d           | yesterday; malformed                  |
| end_date   | start+30; start+365        | start (same day); start−1; malformed  |
| room_id    | available room             | occupied; under_maintenance           |
| tenant_id  | tenant with no active      | tenant with existing active contract  |

#### Test cases

| Test Case ID     | Feature/Module   | Testing Technique                    | Preconditions               | Test Input (start / end / room.status)    | Expected Output                                          | Actual Output | Status |
| ---------------- | ---------------- | ------------------------------------ | --------------------------- | ----------------------------------------- | -------------------------------------------------------- | ------------- | ------ |
| BBT_SS4_CONT_001 | Contract create  | EP — valid                           | room available, tenant free | today / today+365 / `available`           | Draft contract created (status=`draft`)                  | Created       | Pass   |
| BBT_SS4_CONT_002 | Contract create  | BVA — start = today                  | —                           | today / today+30 / available              | Accepted                                                 | Created       | Pass   |
| BBT_SS4_CONT_003 | Contract create  | BVA — start yesterday                | —                           | today−1 / today+30 / available            | Validation: "start_date must be today or later"          | Same          | Pass   |
| BBT_SS4_CONT_004 | Contract create  | BVA — end = start                    | —                           | today / today / available                 | Validation: "end_date must be after start_date"          | Same          | Pass   |
| BBT_SS4_CONT_005 | Contract create  | BVA — end = start − 1                | —                           | today / today−1 / available               | Validation: "end_date must be after start_date"          | Same          | Pass   |
| BBT_SS4_CONT_006 | Contract create  | BVA — end = start + 1                | —                           | today / today+1 / available               | Accepted                                                 | Created       | Pass   |
| BBT_SS4_CONT_007 | Contract create  | EP — room occupied                   | room.status=`occupied`      | today / today+365 / occupied              | Validation: "room must be available"                     | Same          | Pass   |
| BBT_SS4_CONT_008 | Contract create  | EP — room under maintenance          | —                           | today / today+365 / under_maintenance     | Validation: "room must be available"                     | Same          | Pass   |
| BBT_SS4_CONT_009 | Contract create  | EP — tenant has active contract      | tenant has active row       | —                                         | Validation: "tenant already has an active contract"      | Same          | Pass   |
| BBT_SS4_CONT_010 | Contract create  | EP — malformed date                  | —                           | start=`32/13/2026`                        | Validation: "start_date must be a valid date"            | Same          | Pass   |

### 4.2 Planned (next iteration)

- Contract amend / renew form (`new_start_date`, `new_end_date`).
- Manual termination — confirmation modal, status flip, room release.
- Contract document upload — file type / size BVA.

---

## SS5 — Reports & Archives

**Status:** Planned. Add cases for the report filter UI in
[ReportManager.php](../app/Livewire/Admin/Reports/ReportManager.php) and the
archive viewer.

### 5.1 Test plan template

| Filter / control | EP classes                              | BVA cases                                  |
| ---------------- | --------------------------------------- | ------------------------------------------ |
| date_from        | empty / valid / future / malformed      | today, today−1, today+1                    |
| date_to          | empty / valid / before from / malformed | from, from+1, from−1                       |
| record_type      | each option in dropdown                 | —                                          |
| search query     | empty / short / long / no-match         | 0, 1, max-length+1 chars                   |

### 5.2 Specific scenarios to add

- `BBT_SS5_REP_001` — no filters → all archived rows shown, paginated.
- `BBT_SS5_REP_002` — date range narrow → exactly N matching rows.
- `BBT_SS5_REP_003` — date_to before date_from → validation error or empty result.
- `BBT_SS5_REP_004` — search with no match → "No records found" message.
- `BBT_SS5_REP_005` — export to CSV/PDF → file downloads with correct row count.

---

## SS6 — System Administration (Auth & Settings)

### 6.1 Login Form

#### Inputs

- `email` — required, valid email format
- `password` — required, minimum 6 characters

#### Equivalence classes

| Field    | Valid classes                          | Invalid classes                                                   |
| -------- | -------------------------------------- | ----------------------------------------------------------------- |
| email    | well-formed email of an existing user  | empty; malformed; unknown; archived/locked user                   |
| password | correct password                       | empty; <6 chars; ≥6 chars but wrong                               |

#### Test cases

| Test Case ID      | Feature/Module | Testing Technique                  | Preconditions                  | Test Input                          | Expected Output                                                            | Actual Output | Status |
| ----------------- | -------------- | ---------------------------------- | ------------------------------ | ----------------------------------- | -------------------------------------------------------------------------- | ------------- | ------ |
| BBT_SS6_LOGIN_001 | Login form     | EP — valid GM                      | GM seeded                      | `gm@citiescapes.test` / `password`  | Redirect to `/admin/dashboard`; AuditLog `login` recorded                  | Redirected    | Pass   |
| BBT_SS6_LOGIN_002 | Login form     | EP — valid Tenant                  | Tenant active, password reset  | `tenant@x.com` / `Tenant#2026`      | Redirect to `/tenant/dashboard`                                            | Redirected    | Pass   |
| BBT_SS6_LOGIN_003 | Login form     | EP — invalid (empty email)         | —                              | `` / `password`                     | Validation: "The email field is required."                                 | Same          | Pass   |
| BBT_SS6_LOGIN_004 | Login form     | EP — invalid (malformed email)     | —                              | `not-an-email` / `password`         | Validation: "The email field must be a valid email address."               | Same          | Pass   |
| BBT_SS6_LOGIN_005 | Login form     | EP — unknown email                 | —                              | `nobody@x.com` / `password`         | Error "Invalid credentials."                                               | Same          | Pass   |
| BBT_SS6_LOGIN_006 | Login form     | EP — wrong password                | active user `tenant@x.com`     | `tenant@x.com` / `wrongpass`        | Error "Invalid credentials."; failed_login_attempts ++                     | Same          | Pass   |
| BBT_SS6_LOGIN_007 | Login form     | EP — empty password                | —                              | `gm@citiescapes.test` / ``          | Validation: "The password field is required."                              | Same          | Pass   |
| BBT_SS6_LOGIN_008 | Login form     | BVA — password length 5            | —                              | `gm@citiescapes.test` / `12345`     | Validation: "The password field must be at least 6 characters."            | Same          | Pass   |
| BBT_SS6_LOGIN_009 | Login form     | BVA — password length 6 (lower)    | wrong-but-≥6 password          | `gm@citiescapes.test` / `123456`    | Passes validation, then "Invalid credentials."                             | Same          | Pass   |
| BBT_SS6_LOGIN_010 | Login form     | EP — archived user                 | user.status=`archived`         | `archived@x.com` / `password`       | Error "Account archived. Contact the General Manager."                     | Same          | Pass   |
| BBT_SS6_LOGIN_011 | Login form     | BVA — 5th wrong attempt locks      | attempts=4                     | wrong password                      | After submit: status=`locked`; "Account locked after too many…"            | Same          | Pass   |
| BBT_SS6_LOGIN_012 | Login form     | BVA — already-locked still in window | locked, locked_until=now+10m | correct creds                       | Error "Account locked. Try again in 10 minute(s)."                         | Same          | Pass   |
| BBT_SS6_LOGIN_013 | Login form     | EP — case sensitivity of email     | `GM@citiescapes.test` exists   | `GM@citiescapes.test` / `password`  | Email lookup case-insensitive in MySQL → login succeeds                    | Same          | Pass   |

### 6.2 Mandatory Password Change

#### Inputs

- `current_password` — required (the temp password issued after OTP)
- `new_password` — required, ≥8 chars, must differ from current
- `confirm_password` — required, must match `new_password`

#### Equivalence classes

| Field            | Valid                    | Invalid                                  |
| ---------------- | ------------------------ | ---------------------------------------- |
| current_password | matches DB hash          | wrong; empty                             |
| new_password     | ≥8 chars and ≠ current   | <8 chars; equal to current; empty        |
| confirm_password | == new_password          | mismatched                               |

#### Test cases

| Test Case ID    | Feature/Module  | Testing Technique               | Preconditions                  | Test Input (current / new / confirm)        | Expected Output                                              | Actual Output | Status |
| --------------- | --------------- | ------------------------------- | ------------------------------ | ------------------------------------------- | ------------------------------------------------------------ | ------------- | ------ |
| BBT_SS6_PWD_001 | Password change | EP — valid                      | user must_change_password=true | `Temp#1234` / `NewPass#9` / `NewPass#9`     | password updated, must_change_password=false, redirect       | Updated       | Pass   |
| BBT_SS6_PWD_002 | Password change | EP — wrong current              | —                              | `wrong` / `NewPass#9` / `NewPass#9`         | Error: "current password is incorrect"                       | Same          | Pass   |
| BBT_SS6_PWD_003 | Password change | EP — empty current              | —                              | `` / `NewPass#9` / `NewPass#9`              | Validation: required                                         | Same          | Pass   |
| BBT_SS6_PWD_004 | Password change | BVA — new length 7              | —                              | correct / `Short#1` / `Short#1`             | Validation: "must be at least 8 characters"                  | Same          | Pass   |
| BBT_SS6_PWD_005 | Password change | BVA — new length 8 (lower)      | —                              | correct / `Pass#123` / `Pass#123`           | Accepted                                                     | Updated       | Pass   |
| BBT_SS6_PWD_006 | Password change | EP — new equals current         | —                              | correct=`Pass#123` / `Pass#123` / `Pass#123` | Validation: "new password must differ from current"         | Same          | Pass   |
| BBT_SS6_PWD_007 | Password change | EP — confirm mismatch           | —                              | correct / `NewPass#9` / `NewPass#0`         | Validation: "confirmation does not match"                    | Same          | Pass   |
| BBT_SS6_PWD_008 | Password change | EP — empty confirm              | —                              | correct / `NewPass#9` / ``                  | Validation: "confirmation required"                          | Same          | Pass   |

### 6.3 OTP Verification

#### Inputs

- `code` — required, exactly 6 numeric digits
- OTP record must not be expired (default 10 minutes from issue)

#### Equivalence classes

| Aspect      | Valid              | Invalid                |
| ----------- | ------------------ | ---------------------- |
| code length | 6 digits           | 5; 7; 0                |
| code chars  | digits only        | letters; mixed         |
| match       | code matches hash  | mismatched             |
| age         | ≤ 10 min ago       | > 10 min ago           |

#### Test cases

| Test Case ID    | Feature/Module | Testing Technique                  | Preconditions              | Test Input             | Expected Output                                          | Actual Output | Status |
| --------------- | -------------- | ---------------------------------- | -------------------------- | ---------------------- | -------------------------------------------------------- | ------------- | ------ |
| BBT_SS6_OTP_001 | OTP verify     | EP — valid                         | OTP issued 1 min ago       | correct 6-digit code   | account activated, redirect `password.change`            | Redirected    | Pass   |
| BBT_SS6_OTP_002 | OTP verify     | EP — wrong code                    | —                          | wrong 6-digit code     | Error: "Invalid code"                                    | Same          | Pass   |
| BBT_SS6_OTP_003 | OTP verify     | BVA — 5 digits                     | —                          | `12345`                | Validation: "code must be 6 digits"                      | Same          | Pass   |
| BBT_SS6_OTP_004 | OTP verify     | BVA — 7 digits                     | —                          | `1234567`              | Validation: "code must be 6 digits"                      | Same          | Pass   |
| BBT_SS6_OTP_005 | OTP verify     | EP — letters in code               | —                          | `12a456`               | Validation: "code must contain only digits"              | Same          | Pass   |
| BBT_SS6_OTP_006 | OTP verify     | BVA — at expiry (≤10 min)          | OTP issued 10 min ago      | correct code           | Accepted (boundary inclusive)                            | Activated     | Pass   |
| BBT_SS6_OTP_007 | OTP verify     | BVA — just past expiry             | OTP issued 10 min 1 s ago  | correct code           | Error: "Code expired. Resend."                           | Same          | Pass   |
| BBT_SS6_OTP_008 | OTP verify     | EP — empty code                    | —                          | ``                     | Validation: required                                     | Same          | Pass   |
| BBT_SS6_OTP_009 | OTP verify     | EP — reused code                   | OTP already consumed       | code matches consumed  | Error: "Invalid or already used"                         | Same          | Pass   |

### 6.4 Planned (next iteration)

- System Settings form — toggles, threshold inputs (BVA).
- Audit Log filter — date range, action-type filter, search.

---

## SS7 — Communications

**Status:** Planned. Cover the tenant request/complaint flow, admin response,
and the new in-app inquiry email reply.

### 7.1 Planned features under test

- **Tenant request submission** ([RequestManager.php](../app/Livewire/Tenant/RequestManager.php))
  — fields: `type` (`request`/`complaint`), `subject` (max 150), `body` (max 2000).
- **Admin response** ([RequestViewer.php](../app/Livewire/Admin/Communications/RequestViewer.php))
  — `newStatus` (in pending/in_progress/resolved), `adminReply` (max 2000).
- **Inquiry email reply** ([InquiryManager.php](../app/Livewire/Admin/Rooms/InquiryManager.php))
  — `emailSubject` (max 150), `emailBody` (max 3000).
- **Notification bell** — click each notification type, expect smart route.
- **Announcement broadcast** — title, body, audience selector.

### 7.2 Specific scenarios to add

| Test Case ID         | Feature                  | Technique                  | Test Input                                   | Expected                                                    |
| -------------------- | ------------------------ | -------------------------- | -------------------------------------------- | ----------------------------------------------------------- |
| BBT_SS7_REQ_001      | Tenant request submit    | EP — valid request         | type=`request`, subject=`Aircon`, body=valid | Row inserted, GMs notified + emailed                        |
| BBT_SS7_REQ_002      | Tenant request submit    | EP — empty subject         | subject=``                                   | Validation: required                                        |
| BBT_SS7_REQ_003      | Tenant request submit    | BVA — subject 149/150/151  | each value                                   | 149 ✓, 150 ✓, 151 ✗ "max 150"                               |
| BBT_SS7_REQ_004      | Tenant request submit    | BVA — body 2000/2001       | each value                                   | 2000 ✓, 2001 ✗                                              |
| BBT_SS7_REQ_005      | Tenant request submit    | EP — invalid type          | type=`spam`                                  | Validation: type "must be one of request, complaint"        |
| BBT_SS7_RESP_001     | Admin respond            | EP — valid resolved        | newStatus=`resolved`, adminReply=`Done.`     | Status updated, tenant notified + emailed                   |
| BBT_SS7_RESP_002     | Admin respond            | EP — invalid status        | newStatus=`approved`                         | Validation: "selected status is invalid"                    |
| BBT_SS7_RESP_003     | Admin respond            | BVA — adminReply 2000/2001 | each value                                   | 2000 ✓, 2001 ✗                                              |
| BBT_SS7_RESP_004     | Admin respond            | EP — tenant has no email   | tenant.email=null                            | Status still updated; no email send (no error)              |
| BBT_SS7_INQEMAIL_001 | Inquiry email reply      | EP — valid                 | subject=valid, body=valid                    | Mail sent, status=`responded`, success flash, button greyed |
| BBT_SS7_INQEMAIL_002 | Inquiry email reply      | BVA — subject 150/151      | each value                                   | 150 ✓, 151 ✗                                                |
| BBT_SS7_INQEMAIL_003 | Inquiry email reply      | BVA — body 3000/3001       | each value                                   | 3000 ✓, 3001 ✗                                              |
| BBT_SS7_INQEMAIL_004 | Inquiry email reply      | EP — inquiry without email | open compose on inquiry where `email IS NULL` | Compose button hidden in UI                                 |
| BBT_SS7_BELL_001..N  | Notification bell        | EP — each `type`           | click each notification                      | Routes match `actionUrl()` mapping                          |

---

## Coverage summary

| Subsystem | Tested cases | Planned cases | Total cases |
| --------- | -----------: | ------------: | ----------: |
| SS1       |           18 |             0 |          18 |
| SS2       |            0 |          ~13 |        ~13 |
| SS3       |           13 |             6 |         19 |
| SS4       |           10 |             5 |         15 |
| SS5       |            0 |             5 |          5 |
| SS6       |           30 |             5 |         35 |
| SS7       |            0 |          ~14 |        ~14 |
| **Total** |       **71** |        **48** |    **119** |

---

## How to execute

These cases can be encoded as Livewire feature tests under `tests/Feature/`
using `Livewire::test(...)`. Run with:

```bash
php artisan test --testsuite=Feature
```

Manual execution is also supported by visiting:

| URL                       | Subsystem |
| ------------------------- | --------- |
| `/`                       | SS1       |
| `/admin/tenants`          | SS2       |
| `/admin/billing`          | SS3       |
| `/admin/contracts`        | SS4       |
| `/admin/reports`          | SS5       |
| `/login`, `/otp/verify`, `/password/change` | SS6 |
| `/admin/announcements`, `/admin/requests`, `/admin/inquiries`, `/tenant/requests` | SS7 |

Results from the latest manual run on **2026-05-09** populated the *Actual
Output* and *Status* columns above.
