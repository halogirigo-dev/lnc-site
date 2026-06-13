# Booking Workflow Report
**Lombok Nature Culture — Quote-Based Operations Platform**
*Generated: 2026-06-14*

---

## 1. Platform Overview

Lombok Nature Culture operates as a **quote-based travel operation**. Guests submit journey requests via the PHP frontend; the admin team then contacts them, prepares a bespoke quote, confirms the booking, and delivers the journey. No online payment gateway is active at this stage.

The operations platform consists of two layers:

| Layer | Technology | Role |
|-------|-----------|------|
| Guest-facing frontend | PHP 8.2 + PDO/PostgreSQL | Booking request form, thank-you page, invoice view |
| Admin operations panel | Laravel 12 + Filament 3 | Booking CRM, status management, customer profiles, invoicing |

---

## 2. Booking Lifecycle — State Machine

```
[Guest submits form]
        │
        ▼
     ┌─────┐
     │ NEW │  ◄─── Initial state (set by process-booking.php)
     └──┬──┘
        │ Admin: Mark Contacted
        ▼
  ┌───────────┐
  │ CONTACTED │  ◄─── Team has reached out via WhatsApp / email
  └─────┬─────┘
        │ Admin: Send Quote (+ creates Invoice record)
        ▼
  ┌────────┐
  │ QUOTED │  ◄─── Proposal/quote sent, awaiting guest acceptance
  └────┬───┘
       │ Admin: Confirm Booking
       ▼
  ┌───────────┐
  │ CONFIRMED │  ◄─── Guest accepted; journey is locked in
  └─────┬─────┘
        │ Admin: Mark Complete
        ▼
  ┌───────────┐
  │ COMPLETED │  (terminal)
  └───────────┘

  From any active status:
        │ Admin: Cancel
        ▼
  ┌───────────┐
  │ CANCELLED │  (terminal)
  └───────────┘
```

### Status Definitions

| Status | Meaning | Badge Color |
|--------|---------|-------------|
| `new` | Form submitted; not yet contacted | Orange (warning) |
| `contacted` | Admin has reached out to guest | Blue (info) |
| `quoted` | Quote/proposal sent to guest | Indigo (primary) |
| `confirmed` | Guest accepted — journey confirmed | Green (success) |
| `completed` | Journey delivered | Gray |
| `cancelled` | Booking cancelled at any stage | Red (danger) |

### Valid Transitions

```php
new       → contacted | cancelled
contacted → quoted    | cancelled
quoted    → confirmed | cancelled
confirmed → completed | cancelled
cancelled → (terminal)
completed → (terminal)
```

---

## 3. Database Schema

### `customers`

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGSERIAL PK | |
| `name` | VARCHAR(200) | |
| `email` | VARCHAR(200) | UNIQUE — used as identity key |
| `phone` | VARCHAR(50) | |
| `country` | VARCHAR(100) | |
| `nationality` | VARCHAR(100) | |
| `age_range` | VARCHAR(20) | 18-25, 26-35, 36-45, 46-55, 55+ |
| `source` | VARCHAR(100) | google, instagram, referral, etc. |
| `admin_notes` | TEXT | Internal only; not shown to guest |
| `last_booking_at` | TIMESTAMPTZ | Updated on each booking upsert |
| `created_at` | TIMESTAMPTZ | |
| `updated_at` | TIMESTAMPTZ | |

**Upsert logic:** When a booking is submitted (via PHP frontend or admin panel), the customer record is created or updated by email. Fields are only overwritten if the new value is non-null, preserving existing data.

---

### `bookings`

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGSERIAL PK | |
| `ref` | VARCHAR(30) | UNIQUE — format `LNC-YYYY-XXXXX` |
| `customer_id` | FK → customers | Nullable (set at upsert time) |
| `assigned_guide_id` | FK → team_members | Nullable |
| `status` | VARCHAR(50) | Default `new` |
| `package_id` | VARCHAR(100) | |
| `package_title` | VARCHAR(200) | |
| `package_duration` | VARCHAR(100) | |
| `package_price_per_pax` | INTEGER | IDR |
| `total_amount` | INTEGER | IDR |
| `deposit_amount` | INTEGER | 30% of total |
| `balance_amount` | INTEGER | 70% of total |
| `guests` | INTEGER | |
| `dates` | VARCHAR(200) | Free-text preferred dates |
| `flexibility` | VARCHAR(100) | |
| `accommodation` | VARCHAR(200) | |
| `name` | VARCHAR(200) | Denormalized from guest |
| `email` | VARCHAR(200) | |
| `phone` | VARCHAR(50) | |
| `country` | VARCHAR(100) | |
| `nationality` | VARCHAR(100) | |
| `age_range` | VARCHAR(20) | |
| `source` | VARCHAR(100) | |
| `message` | TEXT | |
| `special` | TEXT | Special requirements |
| `budget` | VARCHAR(100) | |
| `admin_notes` | TEXT | Internal |
| `cancellation_reason` | TEXT | Set on cancel |
| `contacted_at` | TIMESTAMPTZ | Set when → contacted |
| `quoted_at` | TIMESTAMPTZ | Set when → quoted |
| `confirmed_at` | TIMESTAMPTZ | Set when → confirmed |
| `cancelled_at` | TIMESTAMPTZ | Set when → cancelled |
| `completed_at` | TIMESTAMPTZ | Set when → completed |
| `created_at` | TIMESTAMPTZ | |
| `updated_at` | TIMESTAMPTZ | |

**Note:** Guest fields are denormalized into bookings for historical accuracy — if a customer updates their profile later, the booking still reflects what was true when submitted.

---

### `booking_status_logs`

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGSERIAL PK | |
| `booking_ref` | VARCHAR(30) | FK → bookings.ref |
| `from_status` | VARCHAR(50) | NULL for initial entry |
| `to_status` | VARCHAR(50) | |
| `changed_by` | VARCHAR(200) | Admin email or `system` |
| `notes` | TEXT | Optional context |
| `created_at` | TIMESTAMPTZ | |
| `updated_at` | TIMESTAMPTZ | |

Every status transition writes one row. The `from_status = NULL` row is the creation entry. The full log is surfaced in the Filament booking view as a chronological timeline.

---

### `invoices`

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGSERIAL PK | |
| `booking_ref` | VARCHAR(30) | FK → bookings.ref |
| `invoice_number` | VARCHAR(100) | UNIQUE |
| `type` | VARCHAR(50) | proposal \| quote \| deposit_invoice \| final_receipt |
| `status` | VARCHAR(50) | draft \| sent \| viewed \| accepted \| cancelled |
| `total_amount` | INTEGER | IDR |
| `deposit_amount` | INTEGER | |
| `balance_amount` | INTEGER | |
| `deposit_pct` | SMALLINT | Default 30 |
| `issued_at` | DATE | |
| `valid_until` | DATE | |
| `due_deposit_at` | DATE | |
| `due_balance_at` | DATE | |
| `sent_at` | TIMESTAMPTZ | When admin marks sent |
| `viewed_at` | TIMESTAMPTZ | Future: set by link tracking |
| `accepted_at` | TIMESTAMPTZ | When admin marks accepted |
| `notes` | TEXT | |
| `created_at` | TIMESTAMPTZ | |
| `updated_at` | TIMESTAMPTZ | |

**Invoice numbering:**
- Proposal: `PRO-{ref}`
- Quote: `QT-{ref}-{YYYYMMDD}`
- Deposit Invoice: `INV-D-{ref}-{YYYYMMDD}`
- Final Receipt: `RCP-{ref}-{YYYYMMDD}`

---

## 4. Admin Workflow — Step by Step

### Step 1 — Receive New Request
- Guest submits booking form on `booking.php`
- `process-booking.php` creates booking with `status = new`
- Booking status log entry inserted: `NULL → new` by `system`
- Admin and guest receive email notifications
- Admin sees new entry appear in **Bookings → New tab** (orange badge)

### Step 2 — Contact Guest
- Admin opens booking, clicks **Mark Contacted**
- Modal prompts for optional contact notes (e.g. "Sent WhatsApp intro + asked about dates")
- `transitionTo('contacted')` runs:
  - Sets `status = contacted`, `contacted_at = now()`
  - Inserts log: `new → contacted` by `admin@email`
- Booking moves to **Contacted tab**

### Step 3 — Send Quote
- Admin clicks **Send Quote**
- Modal requires: **Total Amount (IDR)** and optional notes
- System:
  - Creates `Invoice` record (type: `quote`, status: `draft`)
  - Auto-calculates deposit (30% rounded to nearest 1,000 IDR) and balance
  - Updates `booking.total_amount`, `deposit_amount`, `balance_amount`
  - Transitions booking to `quoted`
  - Inserts log: `contacted → quoted`
- Admin can also manually create additional invoice types via **Create Invoice** button

### Step 4 — Confirm Booking
- Guest accepts the quote (off-system, via WhatsApp/email)
- Admin clicks **Confirm Booking**
- Modal: optional confirmation notes
- `transitionTo('confirmed')` → sets `confirmed_at`, inserts log
- Booking moves to **Confirmed tab** (green)

### Step 5 — Mark Invoice Sent / Accepted
- From Invoice list or invoice view page, admin marks invoice as:
  - **Sent** — records `sent_at`
  - **Accepted** — records `accepted_at`

### Step 6 — Complete Journey
- After guest's journey ends, admin clicks **Mark Complete**
- `transitionTo('completed')` → sets `completed_at`, inserts log
- Booking moves to **Completed tab** (gray)

### Cancel at Any Stage
- Admin clicks **Cancel**, required reason field
- `cancellation_reason` saved to booking
- `transitionTo('cancelled')` → sets `cancelled_at`, inserts log
- Booking moves to **Cancelled tab** (red)

---

## 5. Filament Admin Panel

### Booking Resource (`/admin/bookings`)

**List view — tabs:**
| Tab | Filter | Badge color |
|-----|--------|-------------|
| All | — | count total |
| New | status = new | orange |
| Contacted | status = contacted | blue |
| Quoted | status = quoted | indigo |
| Confirmed | status = confirmed | green |
| Completed | status = completed | gray |
| Cancelled | status = cancelled | red |

**Table columns:** Ref, Guest name, Package, Status (badge), Guests, Dates, Total, Submitted date

**Inline row actions:** Contact, Send Quote, Confirm, Mark Complete, Cancel (each shows only when transition is valid)

**Navigation badge:** Shows count of `new + contacted` bookings in orange — draws attention to bookings requiring action.

**View page — sections:**
- Overview: ref, status, package, guide assigned, submitted date
- Journey details: package title, duration, dates, guests, flexibility, accommodation
- Guest details: name, email, phone, country, nationality, source
- Pricing: total, deposit, balance
- Guest Vision (collapsed): message, special requirements, budget
- Admin Notes (editable inline)
- Status Timeline: RepeatableEntry showing all `booking_status_logs` rows in order
- Invoices: RepeatableEntry showing all linked invoices with type/status/amount

**Header actions:** Contact, Send Quote, Confirm Booking, Mark Complete, Create Invoice, Cancel, Edit Details

---

### Customer Resource (`/admin/customers`)

**List view:** Name, Email, Phone, Country (badge), Booking Count (badge), Last Booking date, Source

**Filters:** Country, Source

**View page:** Profile details + Admin Notes section

**Relation Manager:** Inline booking history table showing all bookings for the customer with status badges and links to booking view page

**No create action** — customers are created automatically via booking submissions (upserted by email).

---

### Invoice Resource (`/admin/invoices`)

**List view — tabs:** All, Draft, Sent, Viewed, Accepted, Cancelled

**Table columns:** Invoice #, Booking Ref (linked), Type (badge), Status (badge), Total, Issued, Valid Until

**Row actions:**
- Mark Sent (visible when `draft`)
- Mark Accepted (visible when `sent` or `viewed`)

**View page sections:**
- Invoice header: number, type, status, linked booking, guest name/email
- Amounts: total, deposit (30%), balance (70%)
- Dates: issued, valid until, deposit due, balance due, sent_at, viewed_at, accepted_at
- Notes

**Header actions:** Mark Sent, Mark Accepted, Cancel Invoice

---

## 6. PHP Frontend Integration

### `process-booking.php` — Booking Submission

1. Validates CSRF token (rotated per submission)
2. Checks honeypot field (bots silently redirected)
3. Rate limits: max 5 submissions per IP per session hour
4. Sanitizes and validates all inputs
5. Generates unique `ref` (format: `LNC-YYYY-XXXXX`)
6. Looks up package details from `data.php` arrays
7. Calculates pricing (total = price_per_pax × guests; deposit = 30% rounded)
8. Stores booking in session (always — fallback for DB-less mode)
9. If DB available:
   - Upserts customer by email (`ON CONFLICT (email) DO UPDATE`)
   - Inserts booking with `status = 'new'`
   - Inserts `booking_status_logs` entry: `NULL → new` by `system`
10. Sends admin email (full details) + guest confirmation email
11. Redirects to `thank-you.php?ref=...&type=quote`

---

## 7. Data Integrity Guarantees

| Guarantee | Mechanism |
|-----------|-----------|
| Every booking has a unique ref | `UNIQUE` constraint + `generateRef()` loop |
| Every status change is recorded | `transitionTo()` always calls `BookingStatusLog::record()` |
| Customer records deduplicated | Upsert by email in both PHP and Eloquent layers |
| Guest data preserved at booking time | Denormalized name/email/phone into `bookings` table |
| Pricing always consistent | `Invoice::createQuote()` atomically updates both invoice and booking |
| Lifecycle timestamps accurate | Set by `transitionTo()`, not by the caller |
| Audit trail actor recorded | `changed_by` = authenticated admin email or `system` |

---

## 8. Operational Metrics

The following data is available in the admin panel for operations review:

- Bookings by status (tab badges, updated live)
- Pending action count (navigation badge: new + contacted)
- Customer booking frequency (bookings_count on customer list)
- Repeat guests (customers with bookings_count > 1)
- Response time (created_at → contacted_at from status logs)
- Quote conversion (contacted → quoted → confirmed rates via status log)
- Seasonal demand (booking creation dates by package)
- Cancellation reasons (cancellation_reason field on booking)

---

## 9. File Manifest

### PHP Frontend (modified)

| File | Change |
|------|--------|
| `public_html/process-booking.php` | Status `new` (was `pending_payment`); customer upsert; status log insert; Midtrans redirect removed |
| `public_html/booking.php` | Honeypot field added |

### Laravel Backend (new/modified)

| File | Role |
|------|------|
| `app/Models/Booking.php` | Status constants, `transitionTo()`, lifecycle timestamps, relationships |
| `app/Models/BookingStatusLog.php` | `record()` factory method, `booking` relationship |
| `app/Models/Customer.php` | `upsertFromBooking()`, `bookings()`, `activeBookings()` |
| `app/Models/Invoice.php` | `createQuote()`, `markSent()`, `markAccepted()`, type/status helpers |
| `app/Filament/Resources/BookingResource.php` | Full CRUD + lifecycle actions + infolist with timeline |
| `app/Filament/Resources/BookingResource/Pages/ListBookings.php` | Status tabs with live counts |
| `app/Filament/Resources/BookingResource/Pages/ViewBooking.php` | 7 header action buttons |
| `app/Filament/Resources/BookingResource/Pages/CreateBooking.php` | Auto-ref, customer upsert, initial log |
| `app/Filament/Resources/BookingResource/Pages/EditBooking.php` | Redirect to view after save |
| `app/Filament/Resources/CustomerResource.php` | List, view, edit + booking history relation manager |
| `app/Filament/Resources/CustomerResource/Pages/ListCustomers.php` | Customer list |
| `app/Filament/Resources/CustomerResource/Pages/ViewCustomer.php` | Customer profile view |
| `app/Filament/Resources/CustomerResource/Pages/EditCustomer.php` | Customer edit |
| `app/Filament/Resources/CustomerResource/RelationManagers/BookingsRelationManager.php` | Inline booking history on customer view |
| `app/Filament/Resources/InvoiceResource.php` | Invoice list, view, mark sent/accepted |
| `app/Filament/Resources/InvoiceResource/Pages/ListInvoices.php` | Status tabs |
| `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php` | Invoice detail + actions |
| `database/migrations/..._create_customers_table.php` | PostgreSQL schema |
| `database/migrations/..._create_bookings_table.php` | With lifecycle timestamps |
| `database/migrations/..._create_booking_status_logs_table.php` | Audit trail table |
| `database/migrations/..._create_invoices_table.php` | With sent/viewed/accepted timestamps |

---

*End of Booking Workflow Report*
