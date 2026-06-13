# Booking Field Mapping
**Lombok Nature Culture — Form → Database**
*Generated: 2026-06-14*

---

## Legend

- **Source:** Where the value originates (form field, calculated, generated, system)
- **Transform:** Any transformation applied before storage
- **Required:** Whether a NULL value causes a DB error (vs. silently stored as NULL)

---

## customers table

| DB Column | Source | Form Field | Transform | Required in DB |
|-----------|--------|-----------|-----------|----------------|
| `id` | system | — | BIGSERIAL auto-increment | PK |
| `name` | form | `name` | `clean()` (strip_tags + trim + htmlspecialchars) | No |
| `email` | form | `email` | `clean()` + `FILTER_VALIDATE_EMAIL` | UNIQUE key |
| `phone` | form | `phone` | `clean()` + regex validate | No |
| `country` | form | `country` | `clean()` | No |
| `nationality` | form | `nationality` | `clean()` | No |
| `age_range` | form | `age_range` | `clean()` | No |
| `source` | form | `source` | `clean()` | No |
| `admin_notes` | admin | — | Filament admin panel only | No |
| `last_booking_at` | system | — | `NOW()` on each upsert | No |
| `created_at` | system | — | `NOW()` on first insert | No |
| `updated_at` | system | — | `NOW()` on insert/update | No |

### Upsert Logic
```sql
INSERT INTO customers (name, email, phone, country, nationality, age_range, source, last_booking_at, ...)
VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), ...)
ON CONFLICT (email) DO UPDATE SET
  name            = COALESCE(EXCLUDED.name, customers.name),
  phone           = COALESCE(EXCLUDED.phone, customers.phone),
  country         = COALESCE(EXCLUDED.country, customers.country),
  nationality     = COALESCE(EXCLUDED.nationality, customers.nationality),
  age_range       = COALESCE(EXCLUDED.age_range, customers.age_range),
  source          = COALESCE(EXCLUDED.source, customers.source),
  last_booking_at = NOW(),
  updated_at      = NOW()
```
**Note:** `COALESCE(EXCLUDED.value, customers.value)` means new values only overwrite if non-null — existing customer data is preserved if the new submission leaves a field blank.

---

## bookings table

| DB Column | Source | Form Field | Transform | Required in DB |
|-----------|--------|-----------|-----------|----------------|
| `id` | system | — | BIGSERIAL | PK |
| `ref` | generated | — | `LNC-{YEAR}-{NNNNN}` sequential | UNIQUE |
| `customer_id` | system | — | FK from customer upsert result | No (FK) |
| `assigned_guide_id` | admin | — | Filament admin only | No (FK) |
| `status` | system | — | Hardcoded `'new'` | No (default) |
| `package_id` | form | `package` | `clean()` → looked up in packages array | No |
| `package_title` | lookup | `package` | From package array `['title']` | No |
| `package_duration` | lookup / form | `package` / `duration` | Package array `['duration']`, else form `duration` field | No |
| `package_price_per_pax` | lookup | `package` | Package array `['price']` (int IDR) | No |
| `total_amount` | calculated | — | `package_price × max(1, (int)guests)` | No |
| `deposit_amount` | calculated | — | `round(total × 0.30 / 1000) × 1000` | No |
| `balance_amount` | calculated | — | `total − deposit` | No |
| `guests` | form | `guests` | `(int)`, min 1 | No |
| `dates` | form | `dates` | `clean()` — free-text | No |
| `flexibility` | form | `flexibility` | `clean()` | No |
| `accommodation` | form | `accommodation` | `clean()` | No |
| `name` | form | `name` | `clean()` — denormalized from customer | No |
| `email` | form | `email` | `clean()` — denormalized from customer | No |
| `phone` | form | `phone` | `clean()` — denormalized from customer | No |
| `country` | form | `country` | `clean()` — denormalized | No |
| `nationality` | form | `nationality` | `clean()` — denormalized | No |
| `age_range` | form | `age_range` | `clean()` — denormalized | No |
| `source` | form | `source` | `clean()` — denormalized | No |
| `message` | form | `message` | `clean()` | No |
| `special` | form | `special` | `clean()` | No |
| `budget` | form | `budget` | `clean()` | No |
| `admin_notes` | admin | — | Filament only | No |
| `cancellation_reason` | admin | — | Set on cancel action | No |
| `contacted_at` | lifecycle | — | Set by `transitionTo('contacted')` | No |
| `quoted_at` | lifecycle | — | Set by `transitionTo('quoted')` | No |
| `confirmed_at` | lifecycle | — | Set by `transitionTo('confirmed')` | No |
| `cancelled_at` | lifecycle | — | Set by `transitionTo('cancelled')` | No |
| `completed_at` | lifecycle | — | Set by `transitionTo('completed')` | No |
| `created_at` | system | — | `NOW()` | No |
| `updated_at` | system | — | `NOW()` | No |

### Denormalization Note
Guest contact fields (`name`, `email`, `phone`, `country`, `nationality`, `age_range`, `source`) are stored in **both** `customers` and `bookings`. This is intentional:
- `customers` = current profile (updatable)
- `bookings` = snapshot at time of booking (immutable historical record)

If a customer changes their name/email/country later, past booking records still reflect what was true when they booked.

---

## booking_status_logs table

| DB Column | Source | Value (on create) |
|-----------|--------|------------------|
| `id` | system | BIGSERIAL |
| `booking_ref` | generated | `$ref` (e.g. `LNC-2026-00001`) |
| `from_status` | system | `NULL` (initial entry has no prior status) |
| `to_status` | system | `'new'` |
| `changed_by` | system | `'system'` (website submission) |
| `notes` | system | `'Booking submitted via website.'` |
| `created_at` | system | `NOW()` |
| `updated_at` | system | `NOW()` |

### Subsequent Entries (via Filament Admin)
All subsequent transitions are logged by `Booking::transitionTo()` in Laravel:

| Transition | `from_status` | `to_status` | `changed_by` |
|-----------|-------------|-----------|------------|
| Contact guest | `new` | `contacted` | admin@email.com |
| Send quote | `contacted` | `quoted` | admin@email.com |
| Confirm booking | `quoted` | `confirmed` | admin@email.com |
| Mark complete | `confirmed` | `completed` | admin@email.com |
| Cancel | any | `cancelled` | admin@email.com |

---

## Session Data (`$_SESSION['lnc_booking']`)

Stored as a PHP array alongside DB writes. Used as fallback when DB is unavailable.

| Session Key | DB Equivalent | Notes |
|------------|--------------|-------|
| `ref` | `bookings.ref` | |
| `package_id` | `bookings.package_id` | |
| `package_title` | `bookings.package_title` | |
| `package_duration` | `bookings.package_duration` | |
| `package_price` | `bookings.package_price_per_pax` | |
| `dates` | `bookings.dates` | |
| `guests` | `bookings.guests` | |
| `flexibility` | `bookings.flexibility` | |
| `accommodation` | `bookings.accommodation` | |
| `name` | `bookings.name` | |
| `email` | `bookings.email` | |
| `phone` | `bookings.phone` | |
| `country` | `bookings.country` | |
| `nationality` | `bookings.nationality` | |
| `age_range` | `bookings.age_range` | |
| `source` | `bookings.source` | |
| `message` | `bookings.message` | |
| `special` | `bookings.special` | |
| `budget` | `bookings.budget` | |
| `total_amount` | `bookings.total_amount` | Also stored as `subtotal` |
| `deposit_amount` | `bookings.deposit_amount` | Also stored as `deposit` |
| `balance_amount` | `bookings.balance_amount` | Also stored as `balance` |
| `deposit_pct` | — | Always 30 |
| `due_deposit` | — | Human-readable date string |
| `due_balance` | — | Human-readable date string |
| `submitted_at` | `bookings.created_at` | Human-readable timestamp |
| `issued` | — | Invoice display only |
| `expiry` | — | Invoice display only |

---

## Reference Format

### Current (random, pre-Phase-4)
```
LNC-2026-A3F7B   ← 5-char random hex uppercase
```

### Required (sequential, post-Phase-4)
```
LNC-2026-00001   ← 5-digit zero-padded sequential per year
LNC-2026-00002
LNC-2026-00123
```

**Generation logic:**
1. Query `COUNT(*) FROM bookings WHERE ref LIKE 'LNC-{YEAR}-%'`
2. Increment by 1
3. Pad to 5 digits: `str_pad($n, 5, '0', STR_PAD_LEFT)`
4. Loop until unique (handles edge case of concurrent inserts)
5. If DB unavailable: fall back to random 5-char hex (preserves session-only mode)
