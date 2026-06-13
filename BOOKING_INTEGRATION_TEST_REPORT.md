# Booking Integration Test Report
**Lombok Nature Culture — End-to-End Booking Flow**
*Generated: 2026-06-14*

---

## Test Scope

Validates the complete booking journey from public form submission through database storage to Filament admin visibility and thank-you page display. No payment integration tested.

---

## Test Environment

| Component | Technology |
|-----------|-----------|
| Frontend | PHP 8.2, PDO/PostgreSQL |
| Admin Panel | Laravel 12, Filament 3.2 |
| Database | PostgreSQL 15+ |
| Session | PHP native sessions |

---

## Test Case 1 — Happy Path (DB Connected)

### Preconditions
- PostgreSQL running and accessible
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` configured in `.env`
- Migrations run: `customers`, `bookings`, `booking_status_logs` tables exist
- No existing bookings in DB (fresh state)

### Steps

| # | Action | Expected Result |
|---|--------|----------------|
| 1 | Navigate to `booking.php` | Form loads; CSRF token in session; no errors |
| 2 | Step 1: Select `LNC-3D` (3-Day Lombok Experience) | Package radio selected; sidebar updates with title and price |
| 3 | Step 2: Dates = "Sep 15–18, 2026"; Guests = 2; Flexibility = "Fixed dates" | Form fields populated |
| 4 | Step 3: Name = "Sarah Chen"; Email = "sarah@test.com"; Phone = "+44 7700 123456"; Country = "UK"; Nationality = "British"; Age = "26–35"; Source = "Instagram" | All fields filled |
| 5 | Step 4: Message = "Looking for an authentic culture experience"; Budget = "Rp 6.000.000 – 10.000.000" | Vision fields filled |
| 6 | Step 5: Review step shows all values correctly | Summary matches input |
| 7 | Click Submit | JS overrides action to `process-booking.php`; POST submitted |

### Expected Outcomes After Submit

#### process-booking.php
- [ ] CSRF check passes
- [ ] Honeypot field `website` is empty → passes
- [ ] Rate limit not exceeded → passes
- [ ] Name validation passes (≥2 chars)
- [ ] Email validation passes (valid format)
- [ ] Phone validation passes (matches regex)
- [ ] `lnc_db()` returns PDO connection
- [ ] `lnc_generate_ref()` queries DB: `SELECT COUNT(*) FROM bookings WHERE ref LIKE 'LNC-2026-%'` → returns 0
- [ ] Ref generated: `LNC-2026-00001`
- [ ] Package lookup finds `LNC-3D` in `$packages_short`
- [ ] Pricing calculated: `total = price × 2 guests; deposit = 30%`
- [ ] `$_SESSION['lnc_booking']` set with all fields
- [ ] Customer upsert: `INSERT INTO customers ... ON CONFLICT (email) DO UPDATE` → `id` returned
- [ ] Booking insert: `INSERT INTO bookings (ref='LNC-2026-00001', customer_id=1, status='new', ...)` → success
- [ ] Status log insert: `INSERT INTO booking_status_logs (booking_ref='LNC-2026-00001', from_status=NULL, to_status='new', changed_by='system', notes='Booking submitted via website.')` → success
- [ ] Admin email sent to `SITE_EMAIL`
- [ ] Guest confirmation email sent to `sarah@test.com`
- [ ] Redirect: `Location: thank-you.php?ref=LNC-2026-00001&type=quote`

#### Database State After Submit

**customers table:**
```
id | name       | email           | phone            | country | nationality | age_range | source    | last_booking_at
1  | Sarah Chen | sarah@test.com  | +44 7700 123456  | UK      | British     | 26-35     | Instagram | 2026-06-14 ...
```

**bookings table:**
```
ref            | customer_id | status | package_id | package_title           | guests | dates           | total_amount | name       | email
LNC-2026-00001 | 1           | new    | LNC-3D     | 3-Day Lombok Experience | 2      | Sep 15–18, 2026 | [calculated] | Sarah Chen | sarah@test.com
```

**booking_status_logs table:**
```
id | booking_ref    | from_status | to_status | changed_by | notes
1  | LNC-2026-00001 | NULL        | new       | system     | Booking submitted via website.
```

#### thank-you.php
- [ ] `lnc_get_booking('LNC-2026-00001')` returns booking row from DB
- [ ] `$is_quote = true` (type=quote parameter)
- [ ] `$variant = 'quote'`
- [ ] Page title: "Request Received — LNC-2026-00001"
- [ ] Reference card shows:
  - Reference: **LNC-2026-00001**
  - Package: **LNC-3D — 3-Day Lombok Experience**
  - Name: **Sarah Chen**
  - Travel Dates: **Sep 15–18, 2026**
  - Guests: **2 guests**
- [ ] "What Happens Next" steps rendered
- [ ] WhatsApp button links to LNC number with ref in message
- [ ] Footer shows: "Reference: LNC-2026-00001 · Confirmation sent to sarah@test.com"

#### Filament Admin
- [ ] Navigate to `/admin/bookings`
- [ ] Booking `LNC-2026-00001` appears in table
- [ ] **New** tab badge shows `1`
- [ ] Navigation badge (sidebar) shows `1` (new + contacted count)
- [ ] Row shows: Ref, "Sarah Chen", "3-Day Lombok Experience", "New" badge (orange), "2", "Sep 15–18, 2026"
- [ ] Click View → opens booking detail page
- [ ] Infolist overview section: ref, status=New, package, created_at
- [ ] Guest section: name, email, phone, country, nationality
- [ ] Status Timeline section: shows 1 row — "system: new" with note "Booking submitted via website."
- [ ] Customer relation: `customer_id` links to Sarah Chen's customer record
- [ ] Navigate to `/admin/customers` → Sarah Chen appears with `bookings_count = 1`
- [ ] Click Sarah Chen → View shows profile; Bookings relation manager shows LNC-2026-00001

---

## Test Case 2 — Second Booking Same Customer

### Preconditions
- Test Case 1 completed; `sarah@test.com` exists in `customers` table

### Steps
Submit another booking with same email `sarah@test.com`, different package and dates.

### Expected Outcomes
- [ ] `lnc_generate_ref()` queries DB: COUNT = 1 → generates `LNC-2026-00002`
- [ ] Customer upsert: `ON CONFLICT (email) DO UPDATE` → same `customer_id = 1`, `last_booking_at` updated
- [ ] NEW booking inserted with `ref = LNC-2026-00002`, `customer_id = 1`
- [ ] New status log row inserted for `LNC-2026-00002`
- [ ] Filament `/admin/customers` → Sarah Chen shows `bookings_count = 2`
- [ ] Sarah Chen view → Bookings relation manager shows both bookings

---

## Test Case 3 — DB Unavailable (Session Fallback)

### Preconditions
- PostgreSQL intentionally unreachable (wrong credentials in `.env`)

### Steps
Submit a booking normally through `booking.php`.

### Expected Outcomes
- [ ] `lnc_db()` returns `null`
- [ ] Ref falls back to random: `LNC-2026-A3F7B` (5-char random hex)
- [ ] `$_SESSION['lnc_booking']` still populated with all form data
- [ ] DB block skipped without fatal error
- [ ] Emails still sent (email does not depend on DB)
- [ ] Redirect to `thank-you.php?ref=LNC-2026-A3F7B&type=quote`
- [ ] `lnc_get_booking()` returns null (no DB)
- [ ] `$_SESSION['lnc_booking']` used as fallback → booking array built from session
- [ ] thank-you.php renders `quote` variant with session data
- [ ] Reference card shows ref, package, name, dates, guests (from session)
- [ ] No fatal error shown to user

---

## Test Case 4 — Bot / Honeypot Rejection

### Preconditions
- Valid session with CSRF token

### Steps
Submit POST with `website=hello@spam.com` (honeypot field filled).

### Expected Outcomes
- [ ] `!empty($_POST['website'])` → true
- [ ] Redirect to `thank-you.php?ref=LNC-BOT-BLOCKED&type=quote` silently
- [ ] No DB writes performed
- [ ] No emails sent
- [ ] `lnc_get_booking('LNC-BOT-BLOCKED')` returns null
- [ ] Session fallback: `$_SESSION['lnc_booking']` not set yet → `$booking = null`
- [ ] Redirect to `booking.php` (the `if (!$booking)` guard)

---

## Test Case 5 — Rate Limiting

### Preconditions
- Submit 5 valid bookings from same IP in session window

### Steps
Submit a 6th booking from same IP.

### Expected Outcomes
- [ ] `$_rate['count']` = 6 > 5
- [ ] Redirect to `booking.php?error=invalid_request`
- [ ] Error banner: "Your session has expired. Please try again."
- [ ] No DB writes for the 6th attempt

---

## Test Case 6 — Admin Lifecycle Transition

### Preconditions
- Test Case 1 completed; `LNC-2026-00001` in `new` status in DB

### Steps in Filament

| # | Action | Expected |
|---|--------|---------|
| 1 | Open booking `LNC-2026-00001` → click "Mark Contacted" | Modal appears with Notes field |
| 2 | Enter notes: "Sent WhatsApp intro." → click action | Status changes to `contacted` |
| 3 | Verify status timeline | Shows 2 rows: NULL→new (system), new→contacted (admin email) |
| 4 | Click "Send Quote" | Modal appears with Total Amount and Notes fields |
| 5 | Enter total = 14,000,000 → click action | Invoice created; status = `quoted` |
| 6 | Verify pricing on booking | deposit = 4,200,000 (30%), balance = 9,800,000 (70%) |
| 7 | Navigate to Invoices | QT-LNC-2026-00001-{date} appears, status = draft |
| 8 | Click "Confirm Booking" | Status = `confirmed` |
| 9 | Status timeline shows 4 entries | NULL→new, new→contacted, contacted→quoted, quoted→confirmed |

---

## Test Case 7 — CSRF Validation

### Steps
Submit form with tampered/missing `csrf_token`.

### Expected Outcomes
- [ ] `!hash_equals()` → false → redirect `booking.php?error=invalid_request`
- [ ] No DB writes
- [ ] No emails

---

## Results Summary

| Test Case | Description | Status |
|-----------|-------------|--------|
| TC-1 | Happy path — DB connected, full flow | Implemented ✓ |
| TC-2 | Second booking same customer — upsert | Implemented ✓ |
| TC-3 | DB unavailable — session fallback | Implemented ✓ |
| TC-4 | Bot rejection — honeypot | Implemented ✓ |
| TC-5 | Rate limiting | Implemented ✓ |
| TC-6 | Admin lifecycle transitions | Implemented ✓ |
| TC-7 | CSRF validation | Implemented ✓ |

**Note:** Test cases are based on code analysis and logic tracing. Live end-to-end execution requires a running PostgreSQL instance with migrations applied (`php artisan migrate`), a configured `.env`, and a reachable web server.

---

## Integration Checklist

### ✓ Phase 1 — Booking Flow Audit
- All form fields documented
- Submission flow traced end-to-end
- Session usage mapped
- Redirect map complete
- Generated: `BOOKING_FLOW_AUDIT.md`

### ✓ Phase 2 — Field Mapping
- All form → DB column mappings documented
- Upsert logic explained
- Session ↔ DB equivalency table
- Generated: `BOOKING_FIELD_MAPPING.md`

### ✓ Phase 3 — Database Integration
- Customer upsert: `ON CONFLICT (email) DO UPDATE` with `COALESCE`
- Booking insert: `status = 'new'`, `customer_id` FK linked
- Status log insert: `NULL → new` by `system`

### ✓ Phase 4 — Booking Reference Format
- Format: `LNC-YYYY-NNNNN` (e.g. `LNC-2026-00001`)
- `lnc_generate_ref(PDO $db)` added to `db.php`
- `process-booking.php` uses sequential ref when DB connected
- Random fallback for DB-less mode preserved
- `Booking::generateRef()` in Laravel also updated to sequential format

### ✓ Phase 5 — Status History
- `booking_status_logs` row inserted on every booking creation
- `from_status = NULL`, `to_status = 'new'`, `changed_by = 'system'`
- All subsequent transitions logged by `Booking::transitionTo()`

### ✓ Phase 6 — Admin Visibility
- BookingResource: `ref`, customer name, package, status, guests, dates, total
- Status tabs: New, Contacted, Quoted, Confirmed, Completed, Cancelled
- Navigation badge: count of new + contacted
- Booking View: status timeline via `statusLogs` RepeatableEntry
- Booking View: customer name linked via `customer()` relation
- CustomerResource: booking history via `BookingsRelationManager`

### ✓ Phase 7 — Thank You Page
- Session fallback status updated from `pending_payment` → `new`
- `$is_quote` condition updated to include `new`, `contacted`, `quoted` statuses
- Quote variant card now displays: Ref, Package, Name, **Travel Dates**, **Guests**
- "Travel Dates" shows "To be confirmed" if field is empty
- Guests displays as "1 guest" / "N guests" (correct pluralization)

### ✓ Phase 8 — Testing
- All test cases documented with expected outcomes
- Generated: `BOOKING_INTEGRATION_TEST_REPORT.md`

---

## Files Modified

| File | Change Summary |
|------|---------------|
| `public_html/db.php` | Added `lnc_generate_ref(PDO $db): string` |
| `public_html/process-booking.php` | Uses `lnc_generate_ref()` when DB available; random fallback preserved |
| `public_html/thank-you.php` | Session fallback status `new`; `$is_quote` covers lifecycle statuses; quote card shows Travel Dates + Guests |
| `backend/app/Models/Booking.php` | `generateRef()` updated to sequential `LNC-YYYY-NNNNN` format |

## Files Created

| File | Purpose |
|------|---------|
| `BOOKING_FLOW_AUDIT.md` | Complete audit of booking.php, process-booking.php, thank-you.php |
| `BOOKING_FIELD_MAPPING.md` | Form field → DB column mapping with transform notes |
| `BOOKING_INTEGRATION_TEST_REPORT.md` | This document — test cases and integration checklist |
