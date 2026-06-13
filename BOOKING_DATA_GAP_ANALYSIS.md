# Booking Data Gap Analysis
**Lombok Nature Culture — Field-by-Field Audit**
*Generated: 2026-06-14*

---

## 1. Current Fields — Assessment

### Journey Fields

| Field | DB Column | Type | Form Source | Assessment |
|-------|-----------|------|------------|------------|
| Package code | `package_id` | VARCHAR(20) | Step 1 radio | ✓ Adequate |
| Package title | `package_title` | VARCHAR | Lookup | ✓ Adequate |
| Duration | `package_duration` | VARCHAR(100) | Lookup / form | ✓ Adequate |
| Travel dates | `dates` | VARCHAR | Step 2 text | ⚠ Free-text only — not structured |
| Guests | `guests` | INT | Step 2 select | ✓ Adequate |
| Date flexibility | `flexibility` | VARCHAR(100) | Step 2 select | ✓ Adequate |
| Accommodation tier | `accommodation` | VARCHAR | Step 2 radio | ⚠ Only tier — no specific hotel preference |

### Pricing Fields

| Field | DB Column | Type | Source | Assessment |
|-------|-----------|------|--------|------------|
| Price per person | `package_price_per_pax` | BIGINT | Package lookup | ✓ Adequate |
| Total | `total_amount` | BIGINT | Calculated | ✓ Adequate |
| Deposit | `deposit_amount` | BIGINT | Calculated 30% | ✓ Adequate |
| Balance | `balance_amount` | BIGINT | Calculated 70% | ✓ Adequate |
| Budget range | `budget` | VARCHAR(100) | Step 4 select | ✓ Adequate (indicative) |

### Guest Information Fields

| Field | DB Column | Type | Form Source | Assessment |
|-------|-----------|------|------------|------------|
| Full name | `name` | VARCHAR | Step 3 | ✓ Required |
| Email | `email` | VARCHAR | Step 3 | ✓ Required |
| Phone / WhatsApp | `phone` | VARCHAR(50) | Step 3 | ✓ Adequate |
| Country of residence | `country` | VARCHAR(100) | Step 3 | ✓ Adequate |
| Nationality | `nationality` | VARCHAR(100) | Step 3 | ✓ Adequate |
| Age range | `age_range` | VARCHAR(20) | Step 3 | ✓ Adequate |
| How they heard | `source` | VARCHAR(100) | Step 3 | ✓ Adequate |

### Vision / Requirements Fields

| Field | DB Column | Type | Form Source | Assessment |
|-------|-----------|------|------------|------------|
| Journey description | `message` | TEXT | Step 4 | ✓ Good |
| Special requirements | `special` | TEXT | Step 4 | ⚠ Catch-all — mixes dietary, accessibility, celebrations |
| Budget | `budget` | VARCHAR | Step 4 select | ✓ Adequate |

### Admin / Operational Fields

| Field | DB Column | Type | Source | Assessment |
|-------|-----------|------|--------|------------|
| Admin notes | `admin_notes` | TEXT | Filament | ✓ Adequate |
| Cancellation reason | `cancellation_reason` | TEXT | Filament | ✓ Adequate |
| Assigned guide | `assigned_guide_id` | FK | Filament | ✓ Adequate |

### Lifecycle Timestamps

| Field | DB Column | Type | Assessment |
|-------|-----------|------|------------|
| Contacted at | `contacted_at` | TIMESTAMP | ✓ Adequate |
| Quoted at | `quoted_at` | TIMESTAMP | ✓ Adequate |
| Confirmed at | `confirmed_at` | TIMESTAMP | ✓ Adequate |
| Cancelled at | `cancelled_at` | TIMESTAMP | ✓ Adequate |
| Completed at | `completed_at` | TIMESTAMP | ✓ Adequate |

---

## 2. Missing Fields — Gap Register

### GAP-01: Structured Arrival Date
**Severity: Critical**

| | |
|-|-|
| Current state | `dates` is a free-text VARCHAR: "Aug 10–20, 2026 or flexible" |
| Problem | Cannot query "arrivals today", "arrivals this week", or "no-shows". Cannot sort by travel date. Cannot send automated reminders based on arrival proximity. |
| Required field | `arrival_date DATE` |
| Also needed | `departure_date DATE` |
| Notes | Both nullable — many enquiries are flexible at booking time. Admin fills them in when confirmed. |

---

### GAP-02: Structured Departure Date
**Severity: Critical**

| | |
|-|-|
| Current state | Embedded in free-text `dates` field |
| Problem | Cannot calculate trip length programmatically. Cannot query "guests departing this weekend". |
| Required field | `departure_date DATE` |
| Relationship | Must be ≥ `arrival_date` if both are set |

---

### GAP-03: Arrival Flight Details
**Severity: High**

| | |
|-|-|
| Current state | Not captured |
| Problem | LNC provides airport pickup at Lombok International Airport (LOP). Guide needs flight number, arrival time, and terminal to meet the guest. Without this, guide coordination is manual (admin emails guest separately). |
| Required field | `arrival_flight VARCHAR(30)` — e.g. "GA 400" |
| Also useful | `arrival_time TIME` or store as VARCHAR if format varies |
| Notes | Not available at booking time — collected after confirmation. Admin fills in. |

---

### GAP-04: Departure Flight Details
**Severity: Medium**

| | |
|-|-|
| Current state | Not captured |
| Problem | Drop-off logistics require knowing departure time. Guide needs to plan the final day. |
| Required field | `departure_flight VARCHAR(30)` |

---

### GAP-05: Dietary Requirements
**Severity: High**

| | |
|-|-|
| Current state | Buried in `special` textarea alongside mobility, celebrations, photography requests |
| Problem | A single `special` field means dietary information cannot be extracted, filtered, or passed to restaurants and hotels systematically. Guides cannot get a dietary-only briefing. Allergy information mixed with anniversary requests is a safety risk. |
| Required field | `dietary_requirements TEXT` |
| Examples | "Strict vegan", "gluten-free", "halal only", "peanut allergy — severe" |
| Notes | Separate field makes it impossible to miss on the operational briefing sheet. |

---

### GAP-06: Group Type / Trip Purpose
**Severity: High**

| | |
|-|-|
| Current state | Not captured as structured data (may appear in `message` free text) |
| Problem | Honeymoon couples need different experiences than family groups. Corporate retreats require different logistics. Solo travellers have different safety considerations (e.g. Rinjani). |
| Required field | `group_type VARCHAR(30)` |
| Values | solo / couple / family / friends / corporate / other |
| Also useful | `trip_purpose VARCHAR(50)` — honeymoon / anniversary / birthday / leisure / corporate / bucket-list |

---

### GAP-07: Emergency Contact
**Severity: High — Critical for trekking packages**

| | |
|-|-|
| Current state | Not captured |
| Problem | Rinjani treks and multi-day remote journeys require an emergency contact. This is an operational and liability requirement. If a guest is incapacitated on the mountain, staff need a next-of-kin contact immediately. |
| Required fields | `emergency_contact_name VARCHAR(200)`, `emergency_contact_phone VARCHAR(50)` |
| Notes | Should be mandatory before a confirmed booking on any trekking package. Currently cannot be enforced. |

---

### GAP-08: Pickup / Drop-off Location
**Severity: Medium**

| | |
|-|-|
| Current state | Not captured (accommodation tier is captured but not address) |
| Problem | If guest is staying at a hotel before the tour starts (not arriving from airport), guide needs a pickup address. Currently this is managed via WhatsApp only. |
| Required field | `pickup_location TEXT` |
| Examples | "Aruna Resort, Senggigi", "LOP Airport Terminal 1", "Gili Trawangan pier" |

---

### GAP-09: Passport / Visa Details
**Severity: Low-Medium**

| | |
|-|-|
| Current state | `nationality` captured but no passport details |
| Problem | Certain nationals require visa-on-arrival assistance. Some permits (Rinjani trekking permits) require passport numbers. |
| Required fields | `passport_number VARCHAR(50)`, `passport_expiry DATE` |
| Notes | Optional — collected only for confirmed bookings that require it. |

---

### GAP-10: Transport Requirements
**Severity: Medium**

| | |
|-|-|
| Current state | Not captured explicitly |
| Problem | Some guests need inter-island transfers (Lombok → Gili Islands), private vs. shared transport, or wheelchair-accessible vehicles. Currently mixed into `special` textarea. |
| Required field | `transport_requirements TEXT` |
| Notes | Separate field allows guide to pull this directly onto briefing sheet. |

---

### GAP-11: Accommodation Property Name
**Severity: Medium**

| | |
|-|-|
| Current state | `accommodation` captures tier only (Eco 2-3★ / Comfort 3-4★ / Luxury 5★) |
| Problem | For confirmed bookings, admin needs to know the specific hotel/villa, not just tier. Currently this is tracked separately (if at all). |
| Required field | `accommodation_name VARCHAR(200)` — specific property name |
| Workflow | Tier selected at booking time; admin fills property name after quote/confirmation. |

---

### GAP-12: Number of Nights (Calculated)
**Severity: Low**

| | |
|-|-|
| Current state | `package_duration` is VARCHAR ("3 Days / 2 Nights") |
| Problem | Cannot calculate `nights = departure_date - arrival_date` without structured dates (see GAP-01, GAP-02). |
| Dependency | Requires GAP-01 and GAP-02 to be resolved first. |

---

## 3. Field Gap Summary Table

| Gap ID | Field(s) | Severity | Category |
|--------|---------|---------|---------|
| GAP-01 | `arrival_date DATE` | Critical | Journey logistics |
| GAP-02 | `departure_date DATE` | Critical | Journey logistics |
| GAP-03 | `arrival_flight VARCHAR(30)` | High | Guide coordination |
| GAP-04 | `departure_flight VARCHAR(30)` | Medium | Guide coordination |
| GAP-05 | `dietary_requirements TEXT` | High | Safety / operations |
| GAP-06 | `group_type VARCHAR(30)`, `trip_purpose VARCHAR(50)` | High | Product matching |
| GAP-07 | `emergency_contact_name`, `emergency_contact_phone` | High | Safety / liability |
| GAP-08 | `pickup_location TEXT` | Medium | Guide coordination |
| GAP-09 | `passport_number`, `passport_expiry` | Low-Medium | Compliance |
| GAP-10 | `transport_requirements TEXT` | Medium | Logistics |
| GAP-11 | `accommodation_name VARCHAR(200)` | Medium | Logistics |
| GAP-12 | Nights (calculated) | Low | Reporting |

---

## 4. Migration Required

The following migration resolves Critical and High severity gaps:

```php
Schema::table('bookings', function (Blueprint $table) {
    // Journey dates (structured)
    $table->date('arrival_date')->nullable()->after('dates');
    $table->date('departure_date')->nullable()->after('arrival_date');

    // Flight coordination
    $table->string('arrival_flight', 30)->nullable()->after('departure_date');
    $table->string('departure_flight', 30)->nullable()->after('arrival_flight');

    // Specific accommodation
    $table->string('accommodation_name', 200)->nullable()->after('accommodation');

    // Pickup logistics
    $table->text('pickup_location')->nullable()->after('accommodation_name');

    // Structured requirements (separated from 'special' catch-all)
    $table->text('dietary_requirements')->nullable()->after('special');
    $table->text('transport_requirements')->nullable()->after('dietary_requirements');

    // Trip purpose and group type
    $table->string('group_type', 30)->nullable()->after('guests');
    $table->string('trip_purpose', 50)->nullable()->after('group_type');

    // Emergency contact (critical for trekking)
    $table->string('emergency_contact_name', 200)->nullable();
    $table->string('emergency_contact_phone', 50)->nullable();

    // Indexes
    $table->index('arrival_date');
    $table->index('departure_date');
});
```

---

## 5. Form Fields to Add (booking.php)

For the public booking form, only **group_type** and **dietary_requirements** should be added immediately (Step 3 and Step 4 respectively). Flight details and emergency contacts are post-confirmation admin fields.

| Form Addition | Step | Reason |
|--------------|------|--------|
| Group type (radio: solo/couple/family/friends/corporate) | Step 2 | Affects product matching and pricing |
| Dietary requirements (textarea) | Step 4 | Safety — must not be buried in "special" |
| Preferred dates structured (date picker OR keep free-text) | Step 2 | Optional — free-text is more flexible for browse-mode enquiries |

---

## 6. Admin-Only Fields (post-confirmation)

These fields should appear in BookingResource Edit form only, not the public form:

| Field | When Filled |
|-------|-----------|
| `arrival_date` | After confirmation when dates are locked |
| `departure_date` | After confirmation |
| `arrival_flight` | Guest provides after booking is confirmed |
| `departure_flight` | Guest provides after booking is confirmed |
| `accommodation_name` | After hotel is booked |
| `pickup_location` | Confirmed at guide briefing |
| `emergency_contact_name` | Required before trek departure |
| `emergency_contact_phone` | Required before trek departure |
| `transport_requirements` | Clarified post-quote |
