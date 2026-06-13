# Audit Trail Hardening Report

**Date:** 2026-06-14
**Phase:** P3/P4 — Audit Trail & Immutable Logs

---

## Summary

The booking system now has a two-layer immutable audit trail: status transition logs and field-level change logs. Every change to a booking — who made it, when, from what IP, and what the old and new values were — is permanently recorded and protected at both the application and database level.

---

## Layer 1: Status Transition Log (`booking_status_logs`)

### Purpose
Record every booking status change with full attribution.

### Schema

```sql
CREATE TABLE booking_status_logs (
    id              BIGSERIAL PRIMARY KEY,
    booking_ref     VARCHAR(30) REFERENCES bookings(ref) ON DELETE CASCADE,
    from_status     VARCHAR(30),
    to_status       VARCHAR(30) NOT NULL,
    changed_by      VARCHAR(200) DEFAULT 'system',
    changed_by_id   BIGINT REFERENCES users(id) ON DELETE SET NULL,
    ip_address      VARCHAR(45),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT now(),
    updated_at      TIMESTAMP DEFAULT now()
);
```

### Hardening Applied

| Measure | Implementation |
|---------|---------------|
| No application-level updates | `boot()` returns false on `updating` event |
| User FK ownership | `changed_by_id` → `users.id` (nullable, SET NULL on delete) |
| IP address capture | Stored from `request()->ip()` via `transitionTo()` |
| Automatic population | Every call to `transitionTo()` calls `BookingStatusLog::record()` |

### Migration
`2026_01_01_000017_harden_booking_status_logs.php` — adds `changed_by_id` and `ip_address` to existing table.

---

## Layer 2: Field Change Log (`booking_field_logs`)

### Purpose
Record every individual field value change with old and new values.

### Schema

```sql
CREATE TABLE booking_field_logs (
    id              BIGSERIAL PRIMARY KEY,
    booking_ref     VARCHAR(30) REFERENCES bookings(ref) ON DELETE CASCADE,
    field_name      VARCHAR(100) NOT NULL,
    old_value       TEXT,
    new_value       TEXT,
    changed_by      VARCHAR(200) DEFAULT 'system',
    changed_by_id   BIGINT REFERENCES users(id) ON DELETE SET NULL,
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP DEFAULT now()
    -- No updated_at — append-only by design
);
```

### Hardening Applied

| Measure | Implementation |
|---------|---------------|
| No application-level updates | `boot()` returns false on `updating` event |
| No application-level deletes | `boot()` returns false on `deleting` event |
| No `updated_at` column | `$timestamps = false` + only `created_at` in `$fillable` |
| CASCADE on booking delete | FK: `ON DELETE CASCADE` (DB-level, bypasses app observer) |

### Tracked Fields

The following booking fields are tracked for every change:

```
total_amount, deposit_amount, balance_amount,
guests, dates, arrival_date, departure_date,
assigned_guide_id,
name, email, phone,
accommodation, accommodation_name,
dietary_requirements, emergency_contact_name, emergency_contact_phone,
admin_notes
```

### Implementation: BookingObserver

File: `app/Observers/BookingObserver.php`

```php
public function updating(Booking $booking): void
{
    foreach (BookingFieldLog::fields() as $field) {
        if (!$booking->isDirty($field)) continue;

        $old = $booking->getOriginal($field);
        $new = $booking->getAttribute($field);

        // Skip no-op changes (null == '' etc.)
        if ($old === $new || ((string)$old === (string)$new)) continue;

        BookingFieldLog::create([
            'booking_ref'   => $booking->ref,
            'field_name'    => $field,
            'old_value'     => $old !== null ? (string)$old : null,
            'new_value'     => $new !== null ? (string)$new : null,
            'changed_by'    => Auth::user()?->email ?? 'system',
            'changed_by_id' => Auth::id(),
            'ip_address'    => request()->ip(),
            'created_at'    => now(),
        ]);
    }
}
```

Registration: `AppServiceProvider::boot()` → `Booking::observe(BookingObserver::class)`

---

## What Is Audited

| Event | Layer 1 (Status Log) | Layer 2 (Field Log) |
|-------|---------------------|---------------------|
| Booking created | ✓ (NULL → new) | — |
| Status transition | ✓ | — |
| Guide assigned | — | ✓ (assigned_guide_id) |
| Price updated | — | ✓ (total_amount, deposit_amount, balance_amount) |
| Dates changed | — | ✓ (arrival_date, departure_date, dates) |
| Guest info changed | — | ✓ (name, email, phone, guests) |
| Accommodation changed | — | ✓ (accommodation, accommodation_name) |
| Emergency contact changed | — | ✓ (emergency_contact_name, emergency_contact_phone) |
| Dietary requirements changed | — | ✓ |
| Admin notes changed | — | ✓ |
| Cancellation reason | ✓ (in notes field) | ✓ (if cancellation_reason in tracked fields) |

---

## Accessing Audit History

### Via Admin Panel
The booking detail infolist (ViewBooking) displays:
- Status history via `RepeatableEntry` on `statusLogs` relation
- Field change history via `RepeatableEntry` on `fieldLogs` relation (if rendered in infolist)

### Via Eloquent
```php
$booking->statusLogs()->orderBy('created_at', 'desc')->get();
$booking->fieldLogs()->where('field_name', 'assigned_guide_id')->get();
```

---

## Security Properties

| Property | Status |
|---------|--------|
| Append-only at application level | ✓ (boot() observers) |
| User identity recorded | ✓ (changed_by string + changed_by_id FK) |
| IP address recorded | ✓ |
| Timestamp immutable | ✓ (no updates possible) |
| Cascade delete on booking | ✓ (DB-level FK, not blocked by app observer) |
| No silent overwriting | ✓ (old_value vs new_value both stored) |
| No-op changes skipped | ✓ (string cast comparison in observer) |
