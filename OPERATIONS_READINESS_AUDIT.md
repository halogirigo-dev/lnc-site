# Operations Readiness Audit
**Lombok Nature Culture — Booking System**
*Generated: 2026-06-14*

---

## Executive Summary

The current booking system is **operationally functional** for a quote-based travel agency at small scale. Core workflow (receive → contact → quote → confirm → complete) is implemented end-to-end with database persistence, audit trail, and admin panel visibility. However, several gaps must be resolved before the system can support real daily operations without admin workarounds.

**Readiness verdict: 68% — Not production-ready without targeted fixes.**

---

## 1. Customer Management

### What Works
| Capability | Status | Notes |
|-----------|--------|-------|
| Customer creation from booking submission | ✓ | Upsert by email in PHP + Eloquent |
| Deduplication by email | ✓ | `ON CONFLICT (email) DO UPDATE` |
| Customer profile in admin | ✓ | CustomerResource with all contact fields |
| Booking history per customer | ✓ | `BookingsRelationManager` on customer view |
| Repeat guest detection | ✓ | `bookings_count` badge on customer list |
| `last_booking_at` tracking | ✓ | Updated on every upsert |
| Internal admin notes | ✓ | `admin_notes` field, not visible to guest |

### What's Missing
| Gap | Severity | Impact |
|----|---------|--------|
| No `total_lifetime_value` aggregated field | Medium | Can't quickly identify high-value repeat clients |
| No VIP / flag tagging on customer | Medium | No way to mark customers as premium/problematic |
| Customers created only from bookings — no manual creation in admin | Low | Minor; workaround is to create a booking and discard |
| No merge duplicate customers tool | Low | Manual email typos create orphan customer records |

---

## 2. Booking Lifecycle

### Status Flow Coverage
```
new → contacted → quoted → confirmed → completed    ← implemented ✓
        ↓            ↓         ↓           ↓
     cancelled    cancelled cancelled   cancelled    ← implemented ✓
```

### Lifecycle Actions Available
| Action | Trigger | Where Available | Status |
|--------|---------|----------------|--------|
| Mark Contacted | new → contacted | Table row + View page | ✓ |
| Send Quote | contacted → quoted | Table row + View page | ✓ |
| Confirm Booking | quoted → confirmed | Table row + View page | ✓ |
| Mark Complete | confirmed → completed | Table row + View page | ✓ |
| Cancel | any active → cancelled | Table row + View page | ✓ |
| Create Invoice | any active | View page only | ✓ |
| Edit Details | any | View page | ✓ |

### Lifecycle Gaps
| Gap | Severity | Impact |
|----|---------|--------|
| State machine not enforced in Filament actions — `canTransitionTo()` is defined but never called; admin can theoretically double-transition | High | Data integrity risk; a booking could be set to `contacted` twice |
| No re-open mechanism for cancelled bookings | Medium | If cancelled in error, must manually edit DB or recreate booking |
| No `re-quote` path (quoted → contacted) for negotiation rounds | Medium | Forces cancel + new booking if guest wants revised pricing |
| Guide assignment is not required before confirming booking | High | Confirmed bookings can have no guide assigned — a logistics failure waiting to happen |
| No `arrival_date` / `departure_date` as structured date fields | Critical | Operations team cannot run "arrivals today" or "upcoming journeys" queries |

---

## 3. Invoice Management

### Capabilities
| Capability | Status |
|-----------|--------|
| Quote invoice creation (auto 30/70 split) | ✓ |
| Multiple invoice types (proposal/quote/deposit/receipt) | ✓ |
| Status tracking (draft/sent/viewed/accepted/cancelled) | ✓ |
| sent_at, viewed_at, accepted_at timestamps | ✓ |
| Mark Sent / Mark Accepted actions in Filament | ✓ |
| Status tabs in InvoiceResource list | ✓ |

### Invoice Gaps
| Gap | Severity | Impact |
|----|---------|--------|
| No way to email invoice to guest from admin panel | High | Admin must copy-paste invoice details manually into email client |
| No invoice PDF generation | High | Guests receive no shareable document |
| Invoice due dates visible in list but not displayed in booking infolist | Medium | Must navigate to Invoice tab to see when deposit is due |
| `due_deposit_at` not shown on confirmed booking's summary | Medium | Admin has to remember deposit deadlines separately |
| No overdue invoice detection or alert | Medium | Draft invoices that are past `valid_until` date are not flagged |

---

## 4. Filament Admin Panel

### Navigation
| Panel Feature | Status |
|--------------|--------|
| Navigation badge (new + contacted count) | ✓ |
| Status tabs: All/New/Contacted/Quoted/Confirmed/Completed/Cancelled | ✓ |
| Searchable by ref, name, email | ✓ |
| Status filter dropdown | ✓ |
| "Has Pricing" filter | ✓ |
| "No Guide Assigned" filter | ✓ |
| Default sort: newest first | ✓ |

### Filament Gaps
| Gap | Severity | Impact |
|----|---------|--------|
| No date range filter (can't filter "received this week" or "arriving in August") | High | Critical for daily operations and handoff preparation |
| Assigned guide not shown in bookings table | High | Must open each booking to see guide assignment |
| No country/nationality/source filter on bookings | Medium | Can't filter "all UK guests" or "all Instagram leads" |
| Bulk actions: only Delete — no bulk "Mark Contacted", no bulk export | High | Daily intake processing requires bulk actions |
| No CSV/Excel export | High | Guides need briefing sheets; finance needs booking summaries |
| No dashboard widget showing pipeline (funnel: new→contact→quote→confirm) | Medium | Management has no high-level visibility |
| Can't assign guide from list view | Medium | Requires opening edit page for each booking |
| No "copy booking ref" button in table (copyable is only on view page) | Low | Minor convenience gap |

---

## 5. Data Integrity

### Schema Constraints
| Constraint | Status |
|-----------|--------|
| Booking ref UNIQUE | ✓ |
| Customer email UNIQUE | ✓ |
| Invoice number UNIQUE | ✓ |
| `booking_status_logs` FK to `bookings.ref` CASCADE DELETE | ✓ |
| `invoices` FK to `bookings.ref` CASCADE DELETE | ✓ |
| `customer_id` FK nullable (supports bookings without customer link) | ✓ |
| `assigned_guide_id` FK nullable | ✓ |

### Integrity Gaps
| Gap | Severity | Impact |
|----|---------|--------|
| No DB-level CHECK constraint on `status` values | Medium | A raw SQL insert with invalid status would succeed |
| No DB-level CHECK on `total_amount >= 0` | Low | Negative pricing theoretically possible |
| Lifecycle timestamps (`confirmed_at` etc.) not enforced at DB level — set by application only | Medium | If `transitionTo()` is bypassed via direct edit, timestamps won't be set |
| `package_id` references `tour_packages.package_code` logically but no FK constraint | Medium | Orphan package IDs possible if packages are renamed/deleted |

---

## 6. Overall Readiness Score

| Domain | Score | Verdict |
|--------|-------|---------|
| Customer management | 80% | Good |
| Booking lifecycle | 70% | Functional, needs enforcement |
| Invoice management | 55% | Works but manual-heavy |
| Filament admin UX | 60% | Needs daily-ops features |
| Data integrity | 75% | Solid schema, app-level gaps |
| **Overall** | **68%** | **Not production-ready** |

---

## 7. Priority Action List

| Priority | Item |
|---------|------|
| Critical | Add `arrival_date`, `departure_date` structured date fields to `bookings` |
| Critical | Require guide assignment before confirming a booking |
| High | Enforce state machine in Filament (`canTransitionTo()` check before action fires) |
| High | Add date range filter to booking list |
| High | Add assigned guide column to booking table |
| High | Add bulk "Mark Contacted" action for intake processing |
| High | Add CSV export |
| High | Add invoice due date visibility on confirmed booking view |
| Medium | Add dietary requirements and emergency contact fields |
| Medium | Add re-quote / negotiation path to lifecycle |
| Medium | Add overdue invoice detection |
| Low | Dashboard pipeline widget |
| Low | Customer VIP/flag tagging |
