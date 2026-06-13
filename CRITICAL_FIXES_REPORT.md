# Critical Fixes Report — Booking Operations Hardening

**Date:** 2026-06-14
**Phase:** P0 — Critical Blockers Resolution

---

## Summary

All P0 critical blockers identified in the Operations Readiness Audit have been resolved. The booking system now has structured date fields, enforced state transitions, guide assignment requirements, and a re-negotiation workflow.

---

## Fix 1: Structured Arrival and Departure Dates

**Problem:** The `dates` field was a free-text string (e.g., "August 2026"). This made it impossible to query arrivals by date, sort bookings chronologically, or power an operations dashboard.

**Resolution:**
- Migration `2026_01_01_000015_add_arrival_departure_dates_to_bookings.php` adds:
  - `arrival_date DATE` — confirmed arrival date (nullable)
  - `departure_date DATE` — confirmed departure date (nullable)
  - Indexes on both columns for query performance
- Booking model casts both as `'date'`
- BookingResource form includes date pickers for both fields in the "Journey Dates" section
- The original `dates` field is retained as the guest-stated preference during initial enquiry

**Impact:** Operations staff can now filter/sort by arrival date. Dashboard widgets for "Today's Arrivals" and "Active Tours" are now queryable.

---

## Fix 2: Guide Assignment Enforced Before Confirmation

**Problem:** Bookings could be confirmed without a guide assigned, creating an operational risk (guests arriving with no guide).

**Resolution:**
- `Booking::transitionTo()` throws `\RuntimeException` if `newStatus === STATUS_CONFIRMED` and `assigned_guide_id` is null
- `ViewBooking.php` confirm action modal now includes a required `assigned_guide_id` Select field
- If guide is not already set, it is saved via `saveQuietly()` before calling `transitionTo()`
- `BookingResource` confirm action (table row action) also requires guide selection with same enforcement

**Code path:**
```
Admin clicks "Confirm Booking"
  → Modal: required guide select + required notes
  → Action: record->assigned_guide_id = data['assigned_guide_id']
  → record->saveQuietly()  (triggers observer, logs field change)
  → record->transitionTo(STATUS_CONFIRMED, ...)
    → canTransitionTo() check
    → guide null check (throws if missing)
    → BookingStatusLog::record(...)
    → confirmed_at = now()
    → save()
```

---

## Fix 3: State Machine Enforcement in transitionTo()

**Problem:** `transitionTo()` previously did not validate the transition. Any code could call `transitionTo('confirmed')` from `'new'` without going through the proper stages, bypassing the entire workflow.

**Resolution:**
- `transitionTo()` now calls `canTransitionTo($newStatus)` first and throws `\InvalidArgumentException` if the transition is not in `allowedTransitions()`
- `allowedTransitions()` defines the complete state graph:
  - `new → contacted`
  - `contacted → quoted, new, cancelled`
  - `quoted → confirmed, contacted, cancelled`  ← re-quote path added
  - `confirmed → completed, cancelled`
- Any code attempting an invalid transition will get an exception, making silent corruption impossible

---

## Fix 4: Re-Negotiate (Re-Quote) Workflow

**Problem:** Once a quote was sent, there was no path back for renegotiation. If a guest wanted changes, the booking was stuck in `quoted` status with no way to revert to `contacted` for a revised quote.

**Resolution:**
- `allowedTransitions()` includes `STATUS_QUOTED → [STATUS_CONFIRMED, STATUS_CONTACTED, STATUS_CANCELLED]`
- `ViewBooking.php` adds a "Re-negotiate" header action (visible when status=quoted) with:
  - Required notes field explaining reason for reversion
  - Calls `transitionTo(STATUS_CONTACTED, ...)`
  - Logs to `booking_status_logs` with full audit trail
- `BookingResource.php` table-level `re_quote` row action mirrors the same logic

---

## Verification Checklist

| Check | Status |
|-------|--------|
| `arrival_date` and `departure_date` columns exist in DB | ✓ Migration created |
| Date pickers appear in Booking edit form | ✓ BookingResource form section |
| Guide required on confirm in ViewBooking | ✓ Enforced in form + transitionTo() |
| Guide required on confirm in BookingResource row action | ✓ Same modal form |
| `transitionTo('confirmed')` from `'new'` throws exception | ✓ canTransitionTo() enforced |
| Re-quote action visible on quoted bookings | ✓ ViewBooking + BookingResource |
| Re-quote logs to booking_status_logs | ✓ Via transitionTo() |
| All transitions write audit log entry | ✓ BookingStatusLog::record() |
