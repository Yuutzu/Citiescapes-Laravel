# Citiescapes — Demo / Manual Test Guide

A practical walkthrough for demoing every status branch in the system to a grader, advisor, or stakeholder. **Read top to bottom; each section builds on the previous setup.**

---

## 0. One-time setup (run once before any demo)

```bash
# Drop everything and start clean
php artisan migrate:fresh --force

# Seed GM + demo tenant + 22 rooms
php artisan db:seed --class=AdminOnlySeeder
```

You now have:

| Role         | Email                              | Password   |
|--------------|------------------------------------|------------|
| General Manager | `citiescapes2017@gmail.com`     | `password` |
| Demo Tenant     | `demo.tenant@citiescapes.test`  | `password` |

The 22 rooms (Floor 1: 6 compact, Floor 2: 8 compact, Floor 3: 8 spacious) exist and are all `available`.

**Two browser windows recommended:** GM logged in on one, demo tenant on the other. Use Chrome + Firefox (or one incognito) so they don't share session cookies.

---

## 1. Demo scenarios — pick what you want to show

The system ships with 57 pre-built scenarios. Each one sets up a specific status branch so you don't have to manually click through a 10-step setup.

**Run any single scenario:**
```bash
php artisan testbed:seed --scenario=<slug>
```

**Run all scenarios for one subsystem:**
```bash
php artisan testbed:seed --subsystem=SS3
```

**See the full catalog:**
```bash
php artisan testbed:seed --list
```

### Convenience demo commands

For client/grader demos, eight `demo:*` commands wrap the testbed scenarios into focused per-subsystem seeds. Each prints a checklist of what to click after seeding:

| Command | Demos |
|---|---|
| `php artisan demo:rooms` | Room statuses + inquiries |
| `php artisan demo:tenant-lifecycle` | Pending / OTP / active / archived tenants |
| `php artisan demo:contract-lifecycle` | Draft → ack → active → expired → terminated |
| `php artisan demo:billing-statuses` | All 7 bill statuses + initial payment + override |
| `php artisan demo:comms` | Requests, complaints, announcements, bell |
| `php artisan demo:auth` | Lockout, auto-unlock, forced password change |
| `php artisan demo:archive` | All 4 archive types + restore guard |
| `php artisan demo:everything` | Wipes DB then runs all 7 above in one shot |

**Reset the testbed without touching the GM/22-room base:**
```bash
php artisan db:wipe-test --no-confirm
```

---

## 2. Subsystem-by-subsystem demo paths

Each subsection is a script you can read out loud. **Bold = thing to demonstrate, indented = what to click.**

### SS1 — Room Operations

**Public landing page (open in a fresh browser, no login)**
- Show the hero + room-type cards (Compact / Spacious)
- Submit a public inquiry through the form
- Verify the GM gets a bell notification (login as GM, check bell icon)

**Admin Room Management**
```bash
php artisan testbed:seed --scenario=room.occupied
php artisan testbed:seed --scenario=room.under_maintenance
```
- Show the Room Management table with the 3 status colors (green / blue / amber)
- Try to change an *occupied* room's status — the system blocks it with "tenant must be moved out first"
- Click **Edit Public Room Cards** — show the SS1 card editor

### SS2 — Tenant Management

**Create a tenant** (the standard demo flow)
- GM → Tenant Management → **+ Create Tenant**
- Enter name + email → submit
- Email sent containing temp password (check `storage/logs/laravel.log` if `MAIL_MAILER=log`, or the inbox if SMTP is live)
- New tenant logs in with temp password → forced to OTP screen → enters OTP from email → forced to change password

**Archive a tenant**
```bash
php artisan testbed:seed --scenario=tenant.archived_manual
```
- GM → Tenant Management → filter by **Archived** → show the row with the gray badge
- Click **Permanently Delete** to demonstrate hard delete (only works on archived rows)

### SS3 — Billing & Collections

**Initial payment (move-in)**
- GM → Billing → **Record Initial Fees** → pick contract → enter deposit + first month + key fee
- Receipt row appears with breakdown

**Generate a monthly bill**
- GM → Billing → **Generate Monthly Bill** → pick contract → enter electricity / water / wifi
- Bill appears in `unpaid` status

**Show every penalty/arrears branch — the 9 status colors**
```bash
php artisan testbed:seed --scenario=bill.unpaid_future       # blue — upcoming
php artisan testbed:seed --scenario=bill.grace_day1          # amber — grace period
php artisan testbed:seed --scenario=bill.grace_day3          # amber — grace ending tomorrow
php artisan testbed:seed --scenario=bill.overdue_day4        # orange — overdue, day 4 = first penalty day
php artisan testbed:seed --scenario=bill.overdue_day13       # orange — about to flip to delinquent
php artisan testbed:seed --scenario=bill.delinquent_day14    # red — delinquent (14+ days)
php artisan testbed:seed --scenario=bill.delinquent_day29    # red — about to flip to eviction
php artisan testbed:seed --scenario=bill.eviction_day30      # dark red — eviction
php artisan testbed:seed --scenario=bill.eviction_day45      # dark red — deep arrears
php artisan testbed:seed --scenario=bill.paid                # green — settled
```

**Apply Deposit to Arrears** (GM eviction tool)
- Seed `bill.delinquent_day29` first
- GM → Billing → find the delinquent bill → **Apply Deposit** → confirm
- Shows the deposit applied as a Payment row; bill status flips to `paid` if deposit covers it

**Mark for Termination** (GM eviction shortcut)
- Seed `bill.eviction_day30` first
- GM → Billing → **Mark for Termination** on the eviction-status bill → confirm
- Contract flips to `terminated`, tenant archived, room freed, **tenant receives bell + email notification**

**Penalty override**
- Seed `bill.penalty_override` to show an already-overridden bill
- Or click **Override / Waive Penalty** on any overdue bill to demo live

### SS4 — Contract & Lease Management

**Full move-in flow (most impressive demo)**
1. GM → Tenant Management → create a new tenant
2. GM → Contract Management → **+ Create Draft** → pick the new tenant + an available room → upload a scan (PDF/image) → save as draft
3. Open second browser window → tenant logs in → sees the **draft contract banner** on dashboard → clicks **Review & Sign**
4. Tenant clicks Step 1 → "I have read and understood"
5. Tenant clicks Step 2 → "I accept the penalty clause" → **contract activates, room flips to occupied**
6. Switch back to GM → Dashboard → see the room turn **blue** in the occupancy overview
7. GM → Room Management → see the tenant name appear in the room row

**Lease timer + warnings**
```bash
php artisan testbed:seed --scenario=contract.active_amber_30   # 30-day warning
php artisan testbed:seed --scenario=contract.active_red_7      # 7-day urgent warning
php artisan testbed:seed --scenario=contract.expired_unarchived  # ready for auto-archive cron
```

Then run the warning crons live:
```bash
php artisan contracts:send-warnings
php artisan contracts:auto-archive
```

**Terminate a contract**
- Seed `contract.active_green` first
- GM → Contract Management → click **Terminate** on an active contract → enter reason → confirm
- Tenant gets bell + email notification; contract archived; room freed

**Scan access flow**
- Seed `contract.active_green` first (contract has a scan attached)
- Tenant logs in → My Contract → sees locked scan with **Request Access** button
- GM → Contract Management → sees the request badge → clicks **Approve**
- Tenant refreshes My Contract → sees scan inline + an "Access expires in 7 days" badge
- After 7 days (or manipulate the DB to fake it), the system auto-revokes — tenant sees "Access window expired" panel

### SS5 — Reports & Archive

**Browse archive**
```bash
php artisan testbed:seed --scenario=archive.tenant
php artisan testbed:seed --scenario=archive.contract
php artisan testbed:seed --scenario=archive.bill
```
- GM → Archive → show the filter dropdown (by record type / source subsystem)
- Show date-range filter
- Click **CSV Export** on the filtered list
- Click **PDF Export** on a contract archive row

**Restore**
- Seed `archive.tenant` → click **Restore** on the tenant row → tenant re-activated
- Try to restore an `archive.contract` row → blocked with the "contracts are immutable" message

### SS6 — User Access & Authentication

**Login lockout demo**
1. Logout
2. Enter `citiescapes2017@gmail.com` + wrong password 5 times in a row
3. 5th attempt locks the account → "Try again in 15 minutes" message
4. GM gets a bell notification ("Account for X was locked")

```bash
php artisan testbed:seed --scenario=user.locked_active      # account already locked
php artisan testbed:seed --scenario=user.locked_expired     # lockout window expired, ready for auto-unlock
```

**Forced password change**
```bash
php artisan testbed:seed --scenario=user.must_change_password
```
- Login as that user → forced to `/password/change` before they can use any other page

**Audit log**
- GM → System → Audit Log → show the filterable log of every action

### SS7 — Communications

**Announcement to all tenants**
- GM → Announcements → **+ New Announcement** → recipient = "All Active Tenants" → enter title + body → send
- Every active tenant gets: bell notification + email (check log) + their dashboard's announcements card updates

**Direct announcement**
- Same flow but recipient = "Specific Tenant" → pick one

**Tenant request flow**
- Tenant → My Requests → **+ New Request** → fill subject + body → submit
- GM gets bell + email
- GM → Requests → respond → tenant gets bell + email + dashboard updates

**Bell notifications**
- Show the bell icon in the top nav → click → dropdown of recent notifications
- **Mark all as read** action
- Only the current user's notifications affected — verify by having both windows open

---

## 3. Background / scheduled jobs

These would run on a cron in production. To demo manually:

```bash
# Apply penalties to overdue bills (computes per-day fees, flips statuses)
php artisan billing:apply-penalties

# Send 30-day + 7-day expiry warnings
php artisan contracts:send-warnings

# Auto-archive contracts past their end_date
php artisan contracts:auto-archive
```

Or run all three at once after seeding scenarios:
```bash
php artisan testbed:seed --apply-crons
```

---

## 4. Quick troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| "No email sent" | `MAIL_MAILER=log` in `.env` | Check `storage/logs/laravel.log` — emails are written there in local dev |
| Bell doesn't update | Livewire navigation cached the old state | Hard refresh (Ctrl+F5) |
| "Account locked" on demo GM | Too many wrong-password attempts | `php artisan tinker --execute="App\Models\User::where('email','citiescapes2017@gmail.com')->update(['failed_login_attempts'=>0,'locked_until'=>null,'status'=>'active']);"` |
| Room shows tenant but status is green | Stale data from before the Step-2 fix | `php artisan tinker` then `App\Models\Room::where('current_tenant_id','!=',null)->update(['status'=>'occupied']);` |
| Want to start completely fresh | Need clean slate | `php artisan db:wipe-test --no-confirm && php artisan db:seed --class=AdminOnlySeeder` |

---

## 5. What to say if asked about test coverage

The PHPUnit suite has **270 tests passing, 0 failing, 1 documented skip** (a CSRF-token test that Laravel's framework explicitly disables in unit-test mode). Run with:

```bash
php artisan test
```

The test suite covers every business activity in BBT + WBT. See `docs/black_box_testing_all_subsystems.html` and `docs/white_box_testing_all_subsystems.html` for the per-subsystem checklist.
