# Citiescapes — Apartment Rental Management System

**CS12L Major Project** | Laravel 11 + Livewire 3 + Tailwind CSS

> Covers all 7 subsystems: Room Operations (SS1), Tenant Management (SS2),
> Billing & Collections (SS3), Contract & Lease Management (SS4),
> Report and Archive Management (SS5), User Access and Authentication (SS6),
> Communications & Notifications (SS7).

---

## Prerequisites

- **Laragon** (includes PHP 8.2+, MySQL, Apache, Composer, Node.js)
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ & npm

---

## Installation (Laragon)

### 1. Extract to Laragon's www folder

```
C:\laragon\www\citiescapes\
```

### 2. Create the database

Open **Laragon → Menu → MySQL → HeidiSQL** (or phpMyAdmin) and create a database:

```sql
CREATE DATABASE citiescapes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configure environment

```bash
cd C:\laragon\www\citiescapes
copy .env.example .env
```

Edit `.env` if your MySQL password is not blank. The defaults work with a fresh Laragon install:

```
DB_DATABASE=citiescapes
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install PHP dependencies

```bash
composer install
```

### 5. Generate app key

```bash
php artisan key:generate
```

### 6. Run migrations + seed

```bash
php artisan migrate --seed
```

This creates:

- **GM account**: `gm@citiescapes.test` / `password`
- **22 rooms** across 3 floors (6 + 8 + 8)

### 7. Create storage symlink

```bash
php artisan storage:link
```

### 8. Install frontend dependencies + build

```bash
npm install
npm run build
```

For development with hot-reload:

```bash
npm run dev
```

### 9. Access the site

If using Laragon's pretty URLs:

```
http://citiescapes.test
```

Otherwise:

```
http://localhost/citiescapes/public
```

---

## Default Accounts

| Role            | Email                 | Password   |
| --------------- | --------------------- | ---------- |
| General Manager | `gm@citiescapes.test` | `password` |

Tenant accounts are created by the GM through **Tenant Management**. A temporary password is emailed (check `storage/logs/laravel.log` since MAIL_MAILER=log by default).

---

## System Overview

### SS1 — Room Operations

- **Public page** (`/`): Room listings with photos, amenities, rates, status badges. No login required.
- **Inquiry form**: Visitors submit name + contact + preferred room type. GM gets notified.
- **GM panel** (`/admin/rooms`): CRUD rooms, manually update status (Available/Occupied/Under Maintenance), view occupancy dashboard, manage inquiry log, archive/delete room records.

### SS2 — Tenant Management

- **GM panel** (`/admin/tenants`): Create tenant account (sends temp password via email), view all profiles, archive/delete accounts.
- **Tenant portal** (`/tenant/profile`): View and update own contact details.
- Lease expiry warnings from SS4 display on the tenant dashboard (no duplicate SS2 alerts).

### SS3 — Billing & Collections

- **GM panel** (`/admin/billing`): Record initial fees (deposit + first month + key fee), generate monthly itemized bills, confirm payments, override/waive penalties with written reason.
- **Automated** (daily scheduler): Grace period reminders (days 1–3), daily penalty (day 4+), escalation to Delinquent (day 14) and Eviction (day 30).
- **Tenant portal** (`/tenant/billing`): View all bills, payment history, overdue counter.

### SS4 — Contract & Lease Management

- **GM panel** (`/admin/contracts`): Create draft, upload scanned signed contract, renew/terminate.
- **Tenant portal** (`/tenant/contract`): View full contract summary, two-step acknowledgment (Step 1: read & understood; Step 2: accept penalty clause), lease countdown timer with colour badge (Green > 30d, Amber ≤ 30d, Red ≤ 7d, Gray = Expired).
- **Automated**: 30-day and 7-day warnings, auto-archive expired contracts.

### SS5 — Report and Archive Management

- **GM panel** (`/admin/reports`): View all archived records from SS1–SS4, filter by type/source/date, restore or permanently delete records.
- Scanned contracts viewable from archive.

### SS6 — User Access and Authentication

- First-time activation: temporary password → OTP via email → account activated → mandatory password change.
- Login lockout: 5 failed attempts → 15-minute lock → GM notified → auto-unlock.
- Session timeout on inactivity.
- **Audit log** (`/admin/audit-log`): Full system-wide event log (logins, logouts, failed attempts, CRUD actions, setting changes).
- **Settings** (`/admin/settings`): Session timeout, lockout threshold, OTP expiry, penalty defaults.

### SS7 — Communications & Notifications

- **GM panel** (`/admin/announcements`, `/admin/requests`): Compose broadcast or direct announcements, review tenant requests / complaints, respond with a status workflow (open / in_progress / resolved) and Resolved lock.
- **Tenant portal** (`/tenant/requests`): Submit maintenance requests and complaints, track own request status, view GM replies.
- **Notification bell** (header on every authenticated page): In-app notification dropdown plus email fan-out for every comms event.

---

## Scheduled Tasks

Four commands run daily via Laravel's scheduler (configure your OS cron or Laragon's scheduler):

```bash
php artisan schedule:run
```

Or run individually:

```bash
php artisan billing:apply-penalties     # SS3: Apply daily penalties + escalate status
php artisan contracts:send-warnings     # SS4: Send 30-day and 7-day warnings
php artisan contracts:auto-archive      # SS4: Archive expired contracts + tenant accounts
php artisan room:sync-status            # SS1/SS4: Reconcile rooms.current_tenant_id with active contracts
```

**Laragon tip**: Use Windows Task Scheduler to run `php artisan schedule:run` every minute, or run commands manually during testing.

---

## Email Setup

By default, emails go to `storage/logs/laravel.log` (MAIL_MAILER=log).

To test with real emails, use [Mailtrap](https://mailtrap.io) and update `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

---

## File Structure

```
citiescapes/
├── app/
│   ├── Console/Commands/         # Scheduler commands (SS3, SS4) + room:sync-status (SS1/SS4) + test:subsystem
│   ├── Http/Middleware/           # EnsureUserHasRole, EnsureAccountActivated
│   ├── Livewire/
│   │   ├── Auth/                  # Login, OtpVerify, ChangePassword (SS6)
│   │   ├── Public/                # RoomListings (SS1 public)
│   │   ├── Admin/
│   │   │   ├── Dashboard.php      # GM overview
│   │   │   ├── Rooms/             # RoomManager, InquiryManager (SS1)
│   │   │   ├── Tenants/           # TenantManager (SS2)
│   │   │   ├── Billing/           # BillingManager (SS3)
│   │   │   ├── Contracts/         # ContractManager (SS4)
│   │   │   ├── Reports/           # ReportManager (SS5)
│   │   │   ├── Settings/          # AuditLogViewer, SystemSettings (SS6)
│   │   │   └── Communications/    # AnnouncementManager, RequestViewer (SS7)
│   │   ├── Tenant/                # Dashboard, Profile, ContractView, BillingView, RequestManager (SS7)
│   │   └── NotificationBell.php   # Header notification dropdown (SS7)
│   ├── Mail/                      # TempPasswordMail, OtpMail
│   └── Models/                    # 11 Eloquent models
├── config/
│   └── citiescapes.php            # Business rules (penalty schedule, auth, building)
├── database/
│   ├── migrations/                # Migration files (rooms, contracts, billing, system, announcements, tenant_requests, initial_payments, amenities)
│   └── seeders/                   # GM account + 22 rooms
├── resources/views/
│   ├── layouts/                   # app.blade.php, guest.blade.php, public.blade.php
│   ├── livewire/                  # All component views
│   └── emails/                    # temp-password, otp templates
└── routes/web.php                 # All routes with middleware groups
```

---

## Tech Stack

| Layer        | Technology                                             |
| ------------ | ------------------------------------------------------ |
| Backend      | Laravel 11 (PHP 8.2+)                                  |
| Frontend     | Livewire 3, Tailwind CSS 3.4, Alpine.js (via Livewire) |
| Database     | MySQL 8 (via Laragon)                                  |
| PDF Export   | barryvdh/laravel-dompdf                                |
| Excel Export | phpoffice/phpspreadsheet                               |
| Build        | Vite 5                                                 |

---

## Team

- Liu, John Marlo
- Suansing, John Carlo
- Tagud, Ben Alexandre
- Polinar, Justine Keith Q.

University of Mindanao — CS12L Major Project
