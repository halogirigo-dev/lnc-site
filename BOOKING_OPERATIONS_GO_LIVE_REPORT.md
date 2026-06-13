# Booking Operations Go-Live Report
**Lombok Nature Culture — Readiness Assessment (No Payment Integration)**
*Generated: 2026-06-14*

---

## Executive Decision

**VERDICT: NOT READY FOR GO-LIVE**

The system has a solid foundation but contains 4 Critical blockers and 7 High issues that must be resolved before real customer bookings can be managed through this platform without significant operational risk.

Estimated remediation time for Critical + High items: **2–3 days of focused development.**

---

## Critical Issues

These will cause real operational failures on day one.

---

### C-01: No Structured Arrival / Departure Dates
**Category: Database Schema + Admin UX**

**Problem:** Travel dates are stored as free-text in `bookings.dates` (e.g. "Aug 10–20, 2026 or flexible"). There is no `arrival_date DATE` or `departure_date DATE` column.

**Operational impact:**
- Guide coordinator cannot run "arrivals today" or "arrivals this week"
- Cannot sort bookings by travel date
- Cannot calculate days until journey for urgency indicators
- Cannot filter "Confirmed bookings arriving in August" to prepare briefing packages
- Cannot calculate trip length (nights) for pricing verification

**Required action:**
```sql
ALTER TABLE bookings
    ADD COLUMN arrival_date DATE NULL,
    ADD COLUMN departure_date DATE NULL;
CREATE INDEX ON bookings (arrival_date);
CREATE INDEX ON bookings (departure_date);
```
Add both fields to BookingResource form (Operations section, editable post-confirmation) and infolist (Journey section).

---

### C-02: Guide Assignment Not Required on Confirmation
**Category: Business Logic**

**Problem:** A booking can transition to `confirmed` status with `assigned_guide_id = NULL`. There is no validation check, no warning, and no required field enforcement.

**Operational impact:**
- Confirmed bookings without a guide are invisible — there is no dashboard showing "confirmed but no guide"
- A guest arrives expecting a private guide; the team has no one assigned
- The only way to find unassigned confirmed bookings is to use the "No Guide Assigned" filter AND the "Confirmed" tab simultaneously — two manual steps that require staff to remember to check

**Required action:**
Add a validation check in the `confirm_booking` action:
```php
->action(function (Booking $record, array $data) {
    if (!$record->assigned_guide_id && empty($data['assigned_guide_id'])) {
        Notification::make()
            ->title('Guide required before confirming')
            ->body('Please assign a guide before confirming this booking.')
            ->danger()->send();
        return;
    }
    // ... proceed with transition
})
```
Add a `Select::make('assigned_guide_id')` to the `confirm_booking` action form so guide can be assigned inline at confirmation time.

---

### C-03: State Machine Not Enforced
**Category: Data Integrity**

**Problem:** `Booking::canTransitionTo()` is defined but never called. `transitionTo()` applies any transition without validation. If an admin has two browser tabs open and clicks "Contact" on both, the booking will be logged as transitioning `new → contacted` twice.

**Operational impact:**
- Duplicate status log entries create an inconsistent audit trail
- Invalid transitions (e.g. `completed → new`) are technically possible via the edit page
- A guest's booking could show "Contacted" twice in the status timeline with no indication of error

**Required action:**
```php
// In Booking::transitionTo()
public function transitionTo(string $newStatus, ...): void
{
    if (!$this->canTransitionTo($newStatus)) {
        return; // or throw exception
    }
    // ... rest of method
}
```

---

### C-04: No Arrival View for Operations
**Category: Admin UX**

**Problem:** There is no view, filter, or widget showing "journeys starting today" or "journeys this week." Staff must manually browse all confirmed bookings and mentally track which ones are imminent.

**Operational impact:**
- Guide coordinator cannot prepare daily briefings
- Airport pickup coordination relies entirely on WhatsApp and memory
- A booking that slips through with no guide assignment and an imminent arrival date will not be noticed until the guest calls from the airport

**Required action:**
Add two filters to BookingResource table:
```php
Tables\Filters\Filter::make('arriving_today')
    ->label('Arriving Today')
    ->query(fn ($q) => $q->whereDate('arrival_date', today())),

Tables\Filters\Filter::make('arriving_this_week')
    ->label('Arriving This Week')
    ->query(fn ($q) => $q->whereBetween('arrival_date', [today(), today()->addDays(7)])),
```
Requires C-01 (structured arrival_date) to be implemented first.

---

## High Issues

These will cause recurring friction but not immediate failure.

---

### H-01: No Export Capability
**Category: Operations**

Guides need briefing sheets. Finance needs booking summaries. Neither is achievable without export. Currently the only way to extract data is via direct database access.

**Action required:** Add `ExportBulkAction` or a custom CSV export to BookingResource.

---

### H-02: Dietary Requirements Not Structured
**Category: Safety / Operations**

Dietary requirements, allergy information, and mobility needs are all mixed into a single `special` TEXT field. This is a safety risk: a severe allergy buried in a paragraph of text alongside an anniversary request can be missed when preparing a restaurant itinerary.

**Action required:**
```sql
ALTER TABLE bookings ADD COLUMN dietary_requirements TEXT NULL;
```
Add separate field to booking form (Step 4) and to the admin edit form.

---

### H-03: Emergency Contact Not Captured
**Category: Safety — Critical for trekking**

Rinjani treks and multi-day remote journeys require emergency contact information. Currently this is not captured at any point in the booking process.

**Action required:**
```sql
ALTER TABLE bookings
    ADD COLUMN emergency_contact_name VARCHAR(200) NULL,
    ADD COLUMN emergency_contact_phone VARCHAR(50) NULL;
```
Add to BookingResource Operations section (post-confirmation admin field, not public form).

---

### H-04: Assigned Guide Not Visible in Booking Table
**Category: Admin UX**

The bookings table shows ref, guest, package, guests, dates, total, status, received. It does not show which guide is assigned. Staff cannot scan the list to see which confirmed bookings have guides vs. which do not.

**Action required:**
```php
Tables\Columns\TextColumn::make('assignedGuide.name')
    ->label('Guide')
    ->placeholder('Unassigned')
    ->badge()
    ->color(fn ($state) => $state ? 'success' : 'danger')
    ->toggleable(),
```

---

### H-05: No Bulk "Mark Contacted" Action
**Category: Admin UX**

When 10 new bookings arrive after a weekend, the team must click "Contact" on each individually. This is 10 modal confirmations. Bulk intake processing is a standard travel agency operation.

**Action required:** Add `BulkAction::make('mark_contacted')` to BookingResource table.

---

### H-06: Booking Field Changes Not Audited
**Category: Data Integrity**

When an admin edits pricing, guest details, or accommodation via the Edit page, no record is kept of what changed, who changed it, or when. If a pricing dispute arises with a guest, there is no way to prove what the original quote was.

**Action required:** Implement model observer or activity log for `Booking` model covering at minimum: `total_amount`, `deposit_amount`, `balance_amount`, `name`, `email`, `status`.

---

### H-07: No Notes Required on Confirmation
**Category: Audit Quality**

The confirmation action allows empty notes. For the most consequential transition in the workflow (the guest has agreed to pay), there should be a mandatory record of how they confirmed (email, WhatsApp, in-person) and on what date.

**Action required:** Make `notes` required in the `confirm_booking` action form.

---

## Medium Issues

Should be resolved within first month of operation.

---

### M-01: No Date Range Filter on Bookings List
**Severity: Medium**

Cannot filter "all bookings received this month" or "all bookings from January".

---

### M-02: Invoice Due Dates Not Visible on Booking View
**Severity: Medium**

Confirmed booking infolist shows invoice status but not `due_deposit_at`. Admin must navigate to the Invoice resource to find when the deposit is due.

---

### M-03: Group Type Not Captured
**Severity: Medium**

Solo / couple / family / friends / corporate is not captured. Affects guide assignment, accommodation recommendations, and activity customisation.

---

### M-04: Re-quote Path Not Available
**Severity: Medium**

If a guest wants to negotiate pricing (fewer guests, different dates, different accommodation), there is no workflow path. Admin must cancel and recreate or manually edit pricing fields with no audit trail.

---

### M-05: Package Title Not Visible in Table Row
**Severity: Medium**

Table shows `package_id` code badge (e.g. "LNC-3D") but not the title. Staff must memorise package codes.

---

### M-06: No Source/Country Filter on Bookings
**Severity: Medium**

Marketing cannot filter "all Instagram bookings from UK guests" from the admin panel.

---

## Low Issues

Track for backlog; not blockers.

---

### L-01: Arrival Flight Details Not Captured
Needed for airport pickup coordination. Not urgent until first booking is confirmed.

### L-02: Pickup Location Not Captured
Where to collect the guest if not from LOP airport.

### L-03: `updated_at` on booking_status_logs
Log entries should be immutable; `updated_at` implies they can change.

### L-04: No Repeat Guest Identification on Booking View
The booking view shows customer name but not whether this is their 1st or 5th journey with LNC.

### L-05: No Performance Dashboard
Conversion rates, response times, source breakdown — all calculable from existing data but not surfaced in admin.

---

## Go-Live Checklist

### Must resolve before accepting first real booking:
- [ ] C-01: Add `arrival_date` and `departure_date` fields
- [ ] C-02: Require guide assignment on confirmation
- [ ] C-03: Enforce state machine in `transitionTo()`
- [ ] C-04: Add "arriving today/this week" filter

### Must resolve within first week:
- [ ] H-01: Add CSV export
- [ ] H-02: Add `dietary_requirements` field
- [ ] H-03: Add emergency contact fields
- [ ] H-04: Show assigned guide in table
- [ ] H-05: Add bulk "Mark Contacted" action
- [ ] H-06: Add field change audit log
- [ ] H-07: Require notes on confirmation

### Must resolve within first month:
- [ ] M-01 through M-06 (see above)

---

## What Is Ready

The following can be used in production today without modification:

| Component | Readiness |
|-----------|---------|
| Booking intake (website → DB) | ✓ Ready |
| Customer deduplication | ✓ Ready |
| Booking status lifecycle (6 states) | ✓ Ready (unvalidated) |
| Status audit trail | ✓ Ready (gaps noted) |
| Invoice creation (quote type) | ✓ Ready |
| Invoice status tracking | ✓ Ready |
| Admin panel access control (auth) | ✓ Ready (Filament auth) |
| Email notifications on submission | ✓ Ready |
| Session fallback (DB-less mode) | ✓ Ready |
| Thank-you page | ✓ Ready |
| Booking reference format | ✓ Ready (LNC-YYYY-NNNNN) |

---

## Summary Table

| Severity | Count | Status |
|---------|-------|--------|
| Critical | 4 | Must fix before go-live |
| High | 7 | Must fix within first week |
| Medium | 6 | Fix within first month |
| Low | 5 | Backlog |
| **Total** | **22** | |

**Estimated remediation effort:**
- Critical issues: ~1 day
- High issues: ~2 days
- **Total to production-ready: 3 working days**
