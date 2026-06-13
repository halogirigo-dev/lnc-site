# Audit Trail Report
**Lombok Nature Culture — booking_status_logs Review**
*Generated: 2026-06-14*

---

## 1. Current Schema

```sql
CREATE TABLE booking_status_logs (
    id              BIGSERIAL PRIMARY KEY,
    booking_ref     VARCHAR(30) NOT NULL,
    from_status     VARCHAR(50) NULL,          -- NULL on initial creation
    to_status       VARCHAR(50) NOT NULL,
    changed_by      VARCHAR(200) DEFAULT 'system',  -- email string, not FK
    notes           TEXT NULL,
    created_at      TIMESTAMPTZ,
    updated_at      TIMESTAMPTZ,

    FOREIGN KEY (booking_ref) REFERENCES bookings(ref) ON DELETE CASCADE,
    INDEX (booking_ref),
    INDEX (created_at)
);
```

---

## 2. What the Audit Trail Captures

### Per Entry: 6 Data Points

| Field | Captures | Example |
|-------|---------|---------|
| `booking_ref` | Which booking | `LNC-2026-00001` |
| `from_status` | Previous state (NULL = creation) | `new` / NULL |
| `to_status` | New state | `contacted` |
| `changed_by` | Who changed it | `admin@lomboknatureculture.com` / `system` |
| `notes` | Reason / context | "Sent WhatsApp intro, guest confirmed interest" |
| `created_at` | When exactly | `2026-06-14 09:34:17 UTC` |

### Standard Log Entries Over a Booking Lifecycle

| Event | `from_status` | `to_status` | `changed_by` |
|-------|------------|-----------|------------|
| Website submission | NULL | `new` | `system` |
| Admin contacts guest | `new` | `contacted` | `admin@lnc.com` |
| Admin sends quote | `contacted` | `quoted` | `admin@lnc.com` |
| Guest accepts | `quoted` | `confirmed` | `admin@lnc.com` |
| Journey complete | `confirmed` | `completed` | `admin@lnc.com` |

### Cancellation at any stage
| Event | `from_status` | `to_status` |
|-------|------------|-----------|
| Cancel (new) | `new` | `cancelled` |
| Cancel (quoted) | `quoted` | `cancelled` |

---

## 3. What IS Working

| Requirement | Status | Notes |
|-----------|--------|-------|
| Every status change logged | ✓ | `transitionTo()` always calls `BookingStatusLog::record()` |
| Creation event logged | ✓ | `from_status = NULL` on first entry |
| Timestamp per entry | ✓ | `created_at` |
| Actor recorded | ✓ | `changed_by` = admin email or `system` |
| Context notes | ✓ | Optional `notes` field |
| Previous state preserved | ✓ | `from_status` |
| New state preserved | ✓ | `to_status` |
| FK cascade on booking delete | ✓ | Logs deleted with booking |
| Displayed in admin | ✓ | RepeatableEntry in booking infolist |
| Ordered chronologically | ✓ | Ordered by `created_at ASC` in `statusLogs()` relationship |

---

## 4. Audit Trail Gaps

### AUDIT-01: No User ID Foreign Key
**Severity: High**

`changed_by` stores the email address as a plain string. This has several problems:

1. If an admin changes their email, historical logs remain showing the old address — no link to the user record
2. No JOIN possible to the `users` table — cannot query "all changes made by this admin"
3. An attacker who gains DB access could falsify `changed_by` without detection (no FK constraint)
4. If `auth()->user()` returns null (unauthenticated context), it silently falls back to `'admin'` string — no way to distinguish between a real user and the fallback

**Recommended fix:**
```php
$table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
// Keep changed_by VARCHAR as a display cache (email at time of change)
```

---

### AUDIT-02: Booking Field Changes Not Logged
**Severity: High**

When an admin edits a booking's fields — pricing, guest name, email, accommodation, admin notes — **none of these changes are logged**. The `booking_status_logs` table only records status transitions, not field mutations.

**Scenario:** Admin edits `total_amount` from 14,000,000 to 10,000,000 after a phone negotiation. There is no record that this happened, who did it, or when.

**Impact:** No pricing audit trail. No way to answer "why is this booking's total different from the original quote?" No accountability for field edits.

**Recommended fix:** Add an `activity_logs` table (or use Laravel Activity Log package) to track model attribute changes:
```
booking LNC-2026-00001: total_amount changed from 14000000 to 10000000 by admin@lnc.com at 2026-06-14 11:22
booking LNC-2026-00001: admin_notes updated by admin@lnc.com at 2026-06-14 11:23
```

---

### AUDIT-03: State Machine Not Enforced Before Logging
**Severity: High**

`transitionTo()` logs the change regardless of whether it is a valid transition. Because `canTransitionTo()` is never called by the action handlers, it is theoretically possible to log:

```
confirmed → contacted  (invalid, but not prevented)
new → completed        (invalid, but not prevented)
```

If this occurs (via race condition or two open tabs), the log would faithfully record an invalid transition with no indication it was invalid.

**Recommended fix:**
```php
public function transitionTo(string $newStatus, ?string $changedBy = null, ?string $notes = null): void
{
    if (!$this->canTransitionTo($newStatus)) {
        throw new \InvalidArgumentException(
            "Cannot transition from {$this->status} to {$newStatus}"
        );
    }
    // ... rest of method
}
```

---

### AUDIT-04: No IP Address or Session ID
**Severity: Medium**

There is no `ip_address` or `session_id` captured in the status log. For a system that will be accessed by a small admin team, this is acceptable initially, but becomes important if:
- A status is changed that shouldn't have been (who logged in from where?)
- Suspicious activity occurs (unusual status changes outside business hours)

**Recommended field:** `ip_address VARCHAR(45)` (supports IPv6)

---

### AUDIT-05: `updated_at` is Meaningless in a Log Table
**Severity: Low**

`updated_at` exists on `booking_status_logs` but log entries should never be updated — they are immutable records. The `updated_at` column adds nothing and could mislead if it were ever set to a different value than `created_at`.

**Recommended fix:** Remove `updated_at` from this table, or add a DB-level trigger that prevents UPDATE on `booking_status_logs`.

---

### AUDIT-06: Notes Field Not Required on Critical Transitions
**Severity: Medium**

The `notes` field is optional for all transitions. For the `cancelled` status, the `cancellation_reason` is captured on the booking record itself, but the status log entry can still have empty `notes`.

For `confirmed` status in particular — the most consequential transition — there should be a policy that requires notes explaining how the guest confirmed (email, WhatsApp, phone call, which date).

**Current state:** Filament actions make notes optional for all transitions.
**Recommended:** Make notes required for `quoted → confirmed` and all cancellations.

---

### AUDIT-07: No Duration-in-Status Tracking
**Severity: Low**

From the logs alone, it is possible to calculate how long a booking spent in each status:
```sql
SELECT
    from_status,
    to_status,
    created_at - LAG(created_at) OVER (PARTITION BY booking_ref ORDER BY created_at) AS duration
FROM booking_status_logs
WHERE booking_ref = 'LNC-2026-00001';
```

But this requires complex querying. There is no pre-calculated `duration_in_status` field, and the lifecycle timestamps on the `bookings` table (e.g. `contacted_at`, `quoted_at`) provide this only for the standard transitions, not for edge cases like cancelled→new (re-open) or multiple quote rounds.

**Recommendation:** Document this as a reporting query rather than a schema change. The data is there; it just requires a view or report query.

---

### AUDIT-08: `changed_by` Fallback Is Ambiguous
**Severity: Medium**

In `transitionTo()`:
```php
$changedBy ??= Auth::user()?->email ?? 'system';
```

The value `'system'` is used for:
1. Website submissions (legitimate system action)
2. Any case where `Auth::user()` is null (could indicate an unauthenticated admin session)
3. Any future background job or CLI command

These three cases are indistinguishable from the `changed_by` value. This means that if a background job incorrectly fires a status transition, it appears in the log identically to a legitimate website submission.

**Recommended:** Use distinct identifiers:
- Website: `'website_submission'`
- CLI/background: `'cli:scheduler'` or `'queue:job'`
- Authenticated admin: `admin@email.com` (already done)

---

## 5. Audit Log Query Examples

These queries work with the current schema:

### All status changes for a booking
```sql
SELECT from_status, to_status, changed_by, notes, created_at
FROM booking_status_logs
WHERE booking_ref = 'LNC-2026-00001'
ORDER BY created_at ASC;
```

### Time to first contact (response time KPI)
```sql
SELECT
    b.ref,
    b.created_at AS submitted_at,
    l.created_at AS contacted_at,
    EXTRACT(EPOCH FROM (l.created_at - b.created_at)) / 3600 AS hours_to_contact
FROM bookings b
JOIN booking_status_logs l ON l.booking_ref = b.ref AND l.to_status = 'contacted'
ORDER BY hours_to_contact DESC;
```

### All changes made by a specific admin
```sql
SELECT booking_ref, from_status, to_status, notes, created_at
FROM booking_status_logs
WHERE changed_by = 'admin@lomboknatureculture.com'
ORDER BY created_at DESC;
```

### Conversion rate (new → confirmed)
```sql
SELECT
    COUNT(DISTINCT CASE WHEN to_status = 'new' THEN booking_ref END) AS total_enquiries,
    COUNT(DISTINCT CASE WHEN to_status = 'confirmed' THEN booking_ref END) AS confirmed,
    ROUND(
        100.0 * COUNT(DISTINCT CASE WHEN to_status = 'confirmed' THEN booking_ref END) /
        NULLIF(COUNT(DISTINCT CASE WHEN to_status = 'new' THEN booking_ref END), 0),
        1
    ) AS conversion_pct
FROM booking_status_logs
WHERE created_at >= NOW() - INTERVAL '90 days';
```

---

## 6. Audit Trail Compliance Summary

| Requirement | Implemented | Gap |
|-----------|------------|-----|
| Who changed the status | Partial | Email string only; no FK to users |
| When | ✓ | `created_at` timestamp |
| Why | Partial | Optional `notes` field |
| From what state | ✓ | `from_status` |
| To what state | ✓ | `to_status` |
| Which booking | ✓ | `booking_ref` FK |
| Field change history | ✗ | Not implemented — only status tracked |
| IP address | ✗ | Not captured |
| State machine enforcement | ✗ | Logs happen even for invalid transitions |
| Immutability | Partial | No DB constraint preventing UPDATE |
