# DATABASE SCHEMA — Lombok Nature Culture
**Target:** PostgreSQL 15+  
**Date:** 2026-06-13  
**Backend:** Laravel 12 + Filament

---

## Entity Relationship Overview

```
destinations ─────────────────┐
                               │ (zone reference)
tour_packages ─────────────────┤
hotels ──────┬─────────────────┤ (content tables)
             │                 │
hotel_properties               │
                               │
customers ──────────────────────┤
    │                           │
    └── bookings ───────────────┤
             │                  │
             ├── payments        │
             └── invoices        │
                                 │
testimonials ───────────────────┘
team_members
gallery
faq
users (admin)
```

---

## Table Definitions

### `destinations`
Geographic zones for tour routing and hotel assignment.

```sql
CREATE TABLE destinations (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    area        VARCHAR(100),
    color       VARCHAR(20),
    description TEXT,
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_destinations_active ON destinations(is_active);
```

**Seed Data:**
| name | area |
|------|------|
| Kuta Mandalika | South Lombok |
| Senggigi & Barat | West Lombok |
| Gili Islands | North Lombok |
| Highlands | East & North Lombok |

---

### `tour_packages`
All tour packages (short stay, long stay, Bali).

```sql
CREATE TABLE tour_packages (
    id              BIGSERIAL PRIMARY KEY,
    package_code    VARCHAR(20) UNIQUE NOT NULL,
    title           VARCHAR(255) NOT NULL,
    subtitle        TEXT,
    duration        VARCHAR(100),
    category        VARCHAR(50),
    image_path      VARCHAR(500),
    price_per_pax   BIGINT DEFAULT 0,
    price_label     VARCHAR(50),
    min_pax         INT DEFAULT 1,
    includes        JSONB DEFAULT '[]',
    excludes        JSONB DEFAULT '[]',
    itinerary       JSONB DEFAULT '[]',
    is_active       BOOLEAN DEFAULT TRUE,
    is_long_stay    BOOLEAN DEFAULT FALSE,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_tour_packages_category  ON tour_packages(category);
CREATE INDEX idx_tour_packages_active    ON tour_packages(is_active);
CREATE INDEX idx_tour_packages_code      ON tour_packages(package_code);
CREATE INDEX idx_tour_packages_gin_incl  ON tour_packages USING GIN(includes);
```

**Column Notes:**
- `package_code` — e.g. `LNC-01`, `BALI-01`
- `price_per_pax` — IDR, 0 means "Request Quote"
- `includes/excludes` — JSON arrays of strings
- `itinerary` — JSON array: `[{day, title, items:[]}]`
- `is_long_stay` — true for 7–14 day packages

---

### `hotels`
Hotel zones/destinations grouping.

```sql
CREATE TABLE hotels (
    id          BIGSERIAL PRIMARY KEY,
    zone        VARCHAR(100) NOT NULL,
    area        VARCHAR(100),
    zone_color  VARCHAR(20),
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);
```

---

### `hotel_properties`
Individual hotel/property listings within a zone.

```sql
CREATE TABLE hotel_properties (
    id          BIGSERIAL PRIMARY KEY,
    hotel_id    BIGINT NOT NULL REFERENCES hotels(id) ON DELETE CASCADE,
    name        VARCHAR(255) NOT NULL,
    type        VARCHAR(100),
    room_type   VARCHAR(255),
    features    TEXT,
    price_low   VARCHAR(50),
    price_high  VARCHAR(50),
    breakfast   VARCHAR(100),
    rating      VARCHAR(100),
    review_text TEXT,
    contact     VARCHAR(100),
    image_path  VARCHAR(500),
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hotel_properties_hotel_id ON hotel_properties(hotel_id);
CREATE INDEX idx_hotel_properties_active   ON hotel_properties(is_active);
```

---

### `customers`
Normalized customer profiles (extracted from bookings).

```sql
CREATE TABLE customers (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255),
    phone       VARCHAR(50),
    country     VARCHAR(100),
    nationality VARCHAR(100),
    age_range   VARCHAR(20),
    source      VARCHAR(100),
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE UNIQUE INDEX idx_customers_email ON customers(email) WHERE email IS NOT NULL;
CREATE INDEX idx_customers_name ON customers(name);
```

---

### `bookings`
Central booking record. Links customer + package + payments + invoices.

```sql
CREATE TABLE bookings (
    id                      BIGSERIAL PRIMARY KEY,
    ref                     VARCHAR(20) UNIQUE NOT NULL,
    customer_id             BIGINT REFERENCES customers(id),
    status                  VARCHAR(50) NOT NULL DEFAULT 'pending_payment',
    package_id              VARCHAR(20),
    package_title           VARCHAR(255),
    package_duration        VARCHAR(100),
    package_price_per_pax   BIGINT DEFAULT 0,
    total_amount            BIGINT DEFAULT 0,
    deposit_amount          BIGINT DEFAULT 0,
    balance_amount          BIGINT DEFAULT 0,
    guests                  INT NOT NULL DEFAULT 1,
    dates                   VARCHAR(255),
    flexibility             VARCHAR(100),
    accommodation           VARCHAR(255),
    name                    VARCHAR(255),
    email                   VARCHAR(255),
    phone                   VARCHAR(50),
    country                 VARCHAR(100),
    nationality             VARCHAR(100),
    age_range               VARCHAR(20),
    source                  VARCHAR(100),
    message                 TEXT,
    special                 TEXT,
    budget                  VARCHAR(100),
    assigned_guide          VARCHAR(255),
    admin_notes             TEXT,
    confirmed_at            TIMESTAMPTZ,
    created_at              TIMESTAMPTZ DEFAULT NOW(),
    updated_at              TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_bookings_ref         ON bookings(ref);
CREATE INDEX idx_bookings_email       ON bookings(email);
CREATE INDEX idx_bookings_status      ON bookings(status);
CREATE INDEX idx_bookings_customer_id ON bookings(customer_id);
CREATE INDEX idx_bookings_created_at  ON bookings(created_at DESC);
```

**Status Flow:**
```
pending_payment → deposit_paid → balance_paid/confirmed
                → cancelled (from any state)
confirmed       → completed (after trip)
```

**Valid Statuses:**
- `pending_payment` — Booking submitted, no payment
- `deposit_paid` — 30% deposit received
- `balance_paid` — Full payment received
- `confirmed` — Admin confirmed, guide assigned
- `cancelled` — Booking cancelled
- `completed` — Trip completed

---

### `payments`
Midtrans payment records (deposit + balance).

```sql
CREATE TABLE payments (
    id                      BIGSERIAL PRIMARY KEY,
    booking_ref             VARCHAR(20) NOT NULL REFERENCES bookings(ref),
    payment_type            VARCHAR(20) NOT NULL CHECK (payment_type IN ('deposit', 'balance')),
    amount                  BIGINT NOT NULL DEFAULT 0,
    midtrans_order_id       VARCHAR(100),
    midtrans_transaction_id VARCHAR(100),
    midtrans_status         VARCHAR(50),
    payment_method          VARCHAR(50),
    snap_token              TEXT,
    raw_notification        JSONB,
    paid_at                 TIMESTAMPTZ,
    created_at              TIMESTAMPTZ DEFAULT NOW(),
    updated_at              TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_payments_booking_ref   ON payments(booking_ref);
CREATE INDEX idx_payments_order_id      ON payments(midtrans_order_id);
CREATE INDEX idx_payments_status        ON payments(midtrans_status);
CREATE UNIQUE INDEX idx_payments_unique ON payments(booking_ref, payment_type);
```

---

### `invoices`
Invoice records auto-generated from bookings.

```sql
CREATE TABLE invoices (
    id              BIGSERIAL PRIMARY KEY,
    booking_ref     VARCHAR(20) NOT NULL REFERENCES bookings(ref),
    invoice_number  VARCHAR(50) UNIQUE NOT NULL,
    type            VARCHAR(20) DEFAULT 'proposal' CHECK (type IN ('proposal', 'deposit', 'receipt')),
    status          VARCHAR(50) DEFAULT 'draft',
    total_amount    BIGINT DEFAULT 0,
    deposit_amount  BIGINT DEFAULT 0,
    balance_amount  BIGINT DEFAULT 0,
    deposit_pct     INT DEFAULT 30,
    issued_at       DATE DEFAULT CURRENT_DATE,
    expires_at      DATE,
    due_deposit_at  DATE,
    due_balance_at  DATE,
    paid_at         TIMESTAMPTZ,
    notes           TEXT,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_invoices_booking_ref ON invoices(booking_ref);
CREATE INDEX idx_invoices_status      ON invoices(status);
```

**Invoice Numbering:**
- Proposal: `PRO-LNC-2026-XXXXX`
- Deposit Invoice: `INV-LNC-2026-XXXXX-D`
- Final Receipt: `RCP-LNC-2026-XXXXX`

---

### `testimonials`
Guest review quotes for homepage display.

```sql
CREATE TABLE testimonials (
    id          BIGSERIAL PRIMARY KEY,
    quote       TEXT NOT NULL,
    guest_name  VARCHAR(255),
    guest_origin VARCHAR(255),
    experience  VARCHAR(255),
    rating      SMALLINT DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    is_active   BOOLEAN DEFAULT TRUE,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_testimonials_active ON testimonials(is_active);
```

---

### `gallery`
Photo gallery images.

```sql
CREATE TABLE gallery (
    id          BIGSERIAL PRIMARY KEY,
    image_path  VARCHAR(500) NOT NULL,
    caption     VARCHAR(500),
    alt_text    VARCHAR(255),
    category    VARCHAR(50),
    is_active   BOOLEAN DEFAULT TRUE,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_gallery_active   ON gallery(is_active);
CREATE INDEX idx_gallery_category ON gallery(category);
```

---

### `faq`
Frequently asked questions (for FAQ schema + site FAQ section).

```sql
CREATE TABLE faq (
    id          BIGSERIAL PRIMARY KEY,
    question    TEXT NOT NULL,
    answer      TEXT NOT NULL,
    category    VARCHAR(50),
    is_active   BOOLEAN DEFAULT TRUE,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_faq_active   ON faq(is_active);
CREATE INDEX idx_faq_category ON faq(category);
```

---

### `team_members`
Guide and staff profiles.

```sql
CREATE TABLE team_members (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    role            VARCHAR(255),
    specialization  VARCHAR(255),
    years_experience INT,
    origin          VARCHAR(255),
    languages       VARCHAR(255),
    certifications  TEXT,
    bio             TEXT,
    image_path      VARCHAR(500),
    is_active       BOOLEAN DEFAULT TRUE,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_team_members_active ON team_members(is_active);
```

---

### `users`
Admin panel users (Laravel Auth / Filament).

```sql
CREATE TABLE users (
    id                 BIGSERIAL PRIMARY KEY,
    name               VARCHAR(255) NOT NULL,
    email              VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at  TIMESTAMPTZ,
    password           VARCHAR(255) NOT NULL,
    remember_token     VARCHAR(100),
    created_at         TIMESTAMPTZ DEFAULT NOW(),
    updated_at         TIMESTAMPTZ DEFAULT NOW()
);

CREATE UNIQUE INDEX idx_users_email ON users(email);
```

---

## Relationships Summary

```
tour_packages (1) ────── (N) bookings      [via package_id]
customers     (1) ────── (N) bookings      [customer_id FK]
bookings      (1) ────── (N) payments      [booking_ref FK]
bookings      (1) ────── (N) invoices      [booking_ref FK]
hotels        (1) ────── (N) hotel_properties [hotel_id FK]
```

---

## Migration Order

Run Laravel migrations in this order to respect foreign key constraints:
1. `create_users_table`
2. `create_destinations_table`
3. `create_tour_packages_table`
4. `create_hotels_table`
5. `create_hotel_properties_table`
6. `create_customers_table`
7. `create_testimonials_table`
8. `create_team_members_table`
9. `create_gallery_table`
10. `create_faq_table`
11. `create_bookings_table`
12. `create_payments_table`
13. `create_invoices_table`

---

## PostgreSQL-Specific Features Used

| Feature | Usage |
|---------|-------|
| `BIGSERIAL` | Auto-increment PKs |
| `TIMESTAMPTZ` | Timezone-aware timestamps |
| `JSONB` | Package includes/excludes/itinerary, payment raw notification |
| `GIN index` | JSONB query optimization |
| Partial unique index | `customers.email` nullable unique |
| `CHECK` constraints | Payment type, invoice type, rating range |
| `REFERENCES ... ON DELETE CASCADE` | hotel_properties → hotels |
