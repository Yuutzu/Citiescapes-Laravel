# Citiescapes — Documentation

System documentation for the Citiescapes (CS12L) apartment rental management system. After cleanup, this folder contains only the authoritative, current documents.

## Files

| File | Purpose |
| --- | --- |
| [`system_flows.html`](system_flows.html) | **Plain-English walkthrough of every flow in the system** — actors, step-by-step business actions, end-to-end stories (move-in, monthly billing, lease renewal, arrears & eviction, scan-access approval), automation schedule, and a changelog of recent additions. Start here if you want to understand "what happens when I click this button." |
| [`use_cases_all_subsystems.html`](use_cases_all_subsystems.html) | **Canonical reference.** Use case diagrams, business activity narratives, and event content for all seven subsystems (SS1–SS7). All other documents are aligned to this file. Open in a browser. |
| [`black_box_testing_all_subsystems.html`](black_box_testing_all_subsystems.html) | Black-box test plan organized by subsystem and business activity. Each test case has an ID (`BBT_SS{n}_{module}_{nnn}`), a technique tag (Use-Case / EP / BVA / Decision Tbl. / State Trans. / Security), steps, and expected result. |
| [`white_box_testing_all_subsystems.html`](white_box_testing_all_subsystems.html) | White-box test plan — branch coverage cases tied to actual class and method names. Test IDs follow `WBT_SS{n}_{module}_{nnn}`. |
| [`EVENT_TABLES_REVISED.txt`](EVENT_TABLES_REVISED.txt) | Plain-text Entities / Processes / Event Tables / Data Flows / Data Stores / Process Descriptions per subsystem. |
| [`WHITE_BOX_TESTING_COMPREHENSIVE.txt`](WHITE_BOX_TESTING_COMPREHENSIVE.txt) | Plain-text companion to the WBT HTML — methodology notes, per-subsystem branch lists, execution procedures. |

## Subsystem index (canonical names)

| # | Subsystem |
| --- | --- |
| SS1 | Room Operations |
| SS2 | Tenant Management |
| SS3 | Billing & Collections |
| SS4 | Contract & Lease Management |
| SS5 | Report and Archive Management |
| SS6 | User Access and Authentication |
| SS7 | Communications & Notifications |

## Running the tests

Test commands live in the project root:

- `php artisan test` — run the full PHPUnit suite.
- `php artisan test:subsystem SS1` (… `SS7`, or `all`) — run black-box + white-box tests for one subsystem (see [`app/Console/Commands/RunTestsForSubsystem.php`](../app/Console/Commands/RunTestsForSubsystem.php)). Flags: `--black-box`, `--white-box`, `--coverage`.
- `run-tests.bat` / `run-tests.ps1` — wrappers for the local Laragon environment.

## Reading order

1. **First-time reader / picking up after a break:** open **system_flows.html** — it's a narrative walkthrough of every button and what it does, organised by subsystem and by end-to-end story.
2. Read **use_cases_all_subsystems.html** for the formal use-case diagrams and actor lists.
3. Read **EVENT_TABLES_REVISED.txt** for the data model and process descriptions.
4. Use **black_box_testing_all_subsystems.html** as the behavioural QA checklist.
5. Use **white_box_testing_all_subsystems.html** + **WHITE_BOX_TESTING_COMPREHENSIVE.txt** when writing or maintaining the PHPUnit suite.

## Conventions

- The single admin role is "General Manager" (GM).
- "System" denotes an automated, scheduler-driven actor — used for daily penalties, expiry warnings, OTP dispatch, auto-archive, and public listing re-render after a Room Type Card image update.
- All inter-subsystem signals flow through the system bus; "System" is shown as the actor when no human user originates the action.

## Recent additions — Eviction & Arrears flow (2026-05-19)

Four related changes extend SS3 (Billing) and its hand-off to SS4 (Contracts) and SS7 (Communications), to cover the case where a tenant cannot pay an outstanding bill and the account drifts into `delinquent` or `eviction`.

### 1. Partial-payment aware confirmation

[`App\Livewire\Admin\Billing\BillingManager::confirmPayment()`](../app/Livewire/Admin/Billing/BillingManager.php) previously flipped a bill to `paid` regardless of how much was paid. It now:

- Records the payment row as before.
- Computes the bill's outstanding balance from the sum of all confirmed payments (`Bill::$paid_amount` and `$balance` accessors).
- Only marks the bill `paid` when the balance reaches zero. Otherwise the existing status (`unpaid`, `grace`, `overdue`, `delinquent`, `eviction`) is preserved.
- Audit-logs the payment as either `payment_confirmed` (full) or `payment_partial` (partial, with remaining balance).

### 2. Tenant-side eviction banner + payment-plan request

[`App\Livewire\Tenant\BillingView`](../app/Livewire/Tenant/BillingView.php) renders a red banner at the top of the tenant's billing page when any of their bills is in `delinquent` or `eviction` status. Each critical bill shows a **Request payment plan** button that calls `requestPaymentPlan($billId)`, which:

- Validates the bill belongs to the authenticated tenant and is in the right status.
- Guards against duplicates — one open arrears request per bill.
- Creates a `tenant_requests` row with auto-filled subject `"Payment difficulty — Bill #N (period)"` and a body summarising the bill state.
- Notifies all active GMs via in-app bell (`NotificationLog`) and email (`TenantRequestMail`).
- Audit-logs as `payment_plan_requested` under SS3.

### 3. GM "Apply Deposit to Arrears" action

[`BillingManager::applyDepositToArrears($billId)`](../app/Livewire/Admin/Billing/BillingManager.php) lets the GM credit the contract's remaining security deposit against an unpaid bill:

- Loads the bill's contract and computes deposit remaining (`deposit` − `deposit_applied_amount`).
- Applies the smaller of remaining deposit / outstanding balance.
- Inserts a `Payment` row with `payment_method = 'deposit_applied'` so the audit trail is intact.
- Increments `contracts.deposit_applied_amount` and stamps `deposit_applied_at` to prevent double-application.
- Marks the bill `paid` if the deposit fully cleared the balance.
- Audit-logs as `deposit_applied_to_bill` under SS3.

Migration: [`2026_05_19_000020_add_deposit_applied_to_contracts.php`](../database/migrations/2026_05_19_000020_add_deposit_applied_to_contracts.php) adds `deposit_applied_amount` and `deposit_applied_at` columns to `contracts`.

### 4. GM "Mark for Termination" shortcut

[`BillingManager::terminateForArrears($billId)`](../app/Livewire/Admin/Billing/BillingManager.php) one-clicks contract termination from an eviction-status bill:

- Refuses to operate if the contract is already terminated.
- Flips contract to `terminated`, archives it to SS5 (`Archive`) with auto-generated reason citing the bill, sets the tenant to `archived`, and frees the room (status `available`, `current_tenant_id = null`).
- Re-uses the same logic as `ContractManager::terminate()` but bypasses the modal — the bill row provides enough context for an auto-filled reason.
- Audit-logs as `contract_terminated_arrears` under SS3.

### Where the buttons live in the UI

- **Tenant** → My Bills page → red banner at top whenever any bill is in `delinquent` or `eviction` → **Request payment plan** per bill.
- **GM** → Billing Management → bills table row actions → **Apply Deposit** and **Mark for Termination** appear when the bill's status is `delinquent` or `eviction`.

### Test coverage to add — known gap (not yet implemented)

The arrears / payment-plan production code listed above ships in `BillingManager` and `BillingView` and is exercised manually, but the following automated tests have **not** been written yet. They are listed here so future maintainers see the gap; the suite is otherwise green (270 passed, 0 failed, 1 skipped as of 2026-05-24).

- `WBT_SS3_PAY_PARTIAL` — `confirmPayment()` with amount less than `total_amount` keeps existing status, logs `payment_partial`, leaves `paid_at = null`.
- `WBT_SS3_PAY_FULL_VIA_PARTIALS` — two partials summing to `total_amount` flip the bill to `paid` on the second.
- `WBT_SS3_OVERPAYMENT_GUARD` — `confirmPayment()` rejects an amount greater than the remaining balance.
- `WBT_SS3_DEPOSIT_APPLY` — `applyDepositToArrears()` applies deposit credit, increments `deposit_applied_amount`, refuses re-application past deposit cap.
- `WBT_SS3_TERMINATE_ARREARS` — `terminateForArrears()` archives contract, archives tenant, frees room, no-ops on already-terminated contract.
- `BBT_SS3_TENANT_REQUEST_PLAN` — tenant clicks **Request payment plan** → pending tenant_request created, GM receives notification + email, duplicate request blocked.
- `WBT_SS4_SCAN_DECISION_DENY` / `_APPROVE` — `ContractManager::submitScanDecision()` branches (deny requires reason, approve flips status).
