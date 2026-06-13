# Lombok Nature Culture — Full Production Audit Report
**Date:** 2026-06-13 (post-migration)
**Auditor:** Senior Full Stack Architect
**Scope:** `public_html/` PHP frontend + `backend/` Laravel 12 application + `deployment/` config

---

## Architecture Overview (Post-Migration)

```
/Volumes/Toha/LNC V2/
├── public_html/               ← PHP frontend (public-facing, unchanged URLs)
│   ├── index.php              ← Homepage
│   ├── config.php             ← Site-wide constants
│   ├── data.php               ← Content arrays with DB-first override
│   ├── env.php                ← .env parser (pgsql support added)
│   ├── db.php                 ← PostgreSQL PDO helper + DB loaders
│   ├── booking.php            ← Multi-step booking form
│   ├── process-booking.php    ← POST handler → PostgreSQL + Midtrans
│   ├── payment.php            ← Deposit Snap payment page
│   ├── payment-balance.php    ← Balance Snap payment page
│   ├── payment-callback.php   ← Midtrans webhook receiver
│   ├── payment-finish.php     ← Post-payment redirect page
│   ├── invoice.php            ← 3-stage invoice portal
│   ├── thank-you.php          ← Post-booking confirmation
│   ├── experiences.php        ← Package catalogue
│   ├── hotels.php             ← Hotel directory
│   ├── team.php               ← Team profiles
│   ├── legal.php              ← Terms / Privacy / Cancellation
│   ├── includes/              ← PHP component partials (10 files)
│   ├── assets/css/style.css   ← Single stylesheet (~1,956 lines)
│   ├── assets/js/main.js      ← Vanilla JS (~512 lines)
│   ├── uploads/               ← Images (.jpg + .webp pairs)
│   ├── fonts/                 ← Local MuseoModerno + Museo TTF
│   ├── .htaccess              ← Apache security rules + rewrite
│   ├── .env                   ← PostgreSQL + Midtrans credentials
│   ├── lib/midtrans.php       ← Midtrans Snap curl wrapper
│   └── includes/email-functions.php ← HTML email senders
│
├── backend/                   ← Laravel 12 + Filament 3 backend
│   ├── app/Models/            ← 13 Eloquent models
│   ├── app/Http/Controllers/  ← 6 API controllers
│   ├── app/Filament/          ← 9 admin resources + 1 widget
│   ├── database/migrations/   ← 13 PostgreSQL migrations
│   ├── database/seeders/      ← 8 seeders
│   ├── routes/api.php         ← 9 REST endpoints under /api/v1/
│   └── routes/web.php         ← /admin redirect
│
├── deployment/                ← Server configuration files
│   ├── nginx.conf             ← Full Nginx config
│   ├── php-fpm.conf           ← PHP-FPM pool + OPcache
│   ├── supervisor.conf        ← Queue worker + scheduler
│   ├── cron.conf              ← Laravel cron + DB backup
│   └── README.md              ← Deploy guide
│
└── *.md                       ← Audit, schema, security, performance reports
```

---

## Tech Stack

### PHP Frontend (existing, unchanged)
| Layer | Technology |
|---|---|
| Language | PHP 8.2 (procedural, no framework) |
| Database driver | PDO — PostgreSQL (migrated from MySQL) |
| Routing | Direct `.php` file routing + `.htaccess` rewrites |
| Templating | Native PHP `require_once` partials |
| Styling | Single CSS file, CSS custom properties |
| JavaScript | Vanilla JS (zero dependencies) |
| Email | PHP `mail()` via Hostinger SMTP relay |
| Payment | Midtrans Snap (deposit 30% + balance 70%) |
| Hosting | Hostinger VPS (LiteSpeed / Apache) |

### Laravel Backend (new)
| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Admin panel | Filament 3.2 |
| Database | PostgreSQL 15 |
| Queue | Database driver (→ Redis for high traffic) |
| Scheduler | Laravel cron via Supervisor |
| Authentication | Filament built-in (email-restricted to admin domain) |
| API | JSON REST under `/api/v1/` |

---

## Dependency Analysis

### PHP Frontend
| Dependency | Type | Risk |
|---|---|---|
| PHP 8.2 PDO + pgsql extension | Core | Low — standard |
| `php mail()` | Core | Medium — deliverability |
| Midtrans Snap JS (CDN) | External | Low — payment gateway |
| Google Fonts (CDN) | External | Low — fonts only |
| Local TTF fonts | Local | None |
| WordPress remnants in `public_html/` | Legacy | HIGH — remove from server |

### Laravel Backend
| Dependency | Type | Risk |
|---|---|---|
| `laravel/framework ^12.0` | Composer | Low — LTS support |
| `filament/filament ^3.2` | Composer | Low — active project |
| `ext-pgsql` / `ext-pdo_pgsql` | PHP ext | Low — install once |
| `guzzlehttp/guzzle` | Composer | Low — via Laravel |
| `ramsey/uuid` | Composer | Low — via Laravel |
| No other external packages | — | — |

---

## File-by-File Risk Assessment

### High-Risk (review/action required)

| File | Risk | Status |
|---|---|---|
| `public_html/wp-config.php` | CRITICAL — DB credentials exposed if PHP fails | Blocked by .htaccess; delete from server |
| `public_html/wp-admin/` | CRITICAL — brute-force target | Blocked by .htaccess; delete entire WP install |
| `public_html/xmlrpc.php` | HIGH — DDoS amplification vector | Blocked by .htaccess; delete file |
| `public_html/create-tables.php` | HIGH — exposes schema, enables DDL execution | Blocked by .htaccess; delete after DB setup |
| `public_html/single-lnc_package.php.bak` | MEDIUM — raw PHP source readable | Blocked by .htaccess; delete file |

### Modified (post-migration state)

| File | Change | DB Before | DB After |
|---|---|---|---|
| `env.php` | Added `DB_CONNECTION`, `DB_PORT` constants | MySQL only | pgsql default |
| `db.php` | Full rewrite — DSN, positional params, 4 new helpers | MySQL PDO | PostgreSQL PDO |
| `data.php` | DB-first override block appended | Static only | DB-driven + fallback |
| `create-tables.php` | Full rewrite — PostgreSQL DDL for 13 tables | MySQL 2 tables | PostgreSQL 13 tables |
| `process-booking.php` | Positional params, honeypot, rate limit | `:named` | `$1/$2` + ON CONFLICT |
| `payment.php` | ON CONFLICT syntax | ON DUPLICATE KEY | ON CONFLICT DO UPDATE |
| `payment-balance.php` | ON CONFLICT syntax | ON DUPLICATE KEY | ON CONFLICT DO UPDATE |
| `payment-callback.php` | Positional params (demo + real webhook) | `:named` | `$1/$2` |
| `.env` | Added pgsql config, DEPLOY_SECRET | MySQL vars | PostgreSQL vars |
| `.github/workflows/deploy.yml` | Full SSH-based CI/CD | Webhook only | SSH + artisan |

---

## Database Schema Summary

**13 tables, PostgreSQL 15+, all with BIGSERIAL PKs and TIMESTAMPTZ audit columns**

| Table | Rows (estimated) | JSONB? | Notes |
|---|---|---|---|
| `users` | ~3 | No | Admin panel access only |
| `destinations` | 4 | No | Geographic zones |
| `tour_packages` | 8 | Yes (includes, excludes, itinerary, highlights) | GIN indexes on JSONB |
| `hotels` | 4 | No | Zone/category groupings |
| `hotel_properties` | 11 | No | Individual property listings |
| `customers` | Grows | No | Unique by email, guest profiles |
| `testimonials` | 3+ | No | publish toggle, sort_order |
| `team_members` | 4+ | No | sort_order, years_experience |
| `gallery` | 5+ | No | category, sort_order |
| `faq` | 6+ | No | category, sort_order |
| `bookings` | Grows | No | status machine, ref=unique |
| `payments` | Grows | No | midtrans_status, payment_type |
| `invoices` | Grows | No | invoice_number, type |

**All foreign keys** use `ON DELETE CASCADE` / `ON DELETE RESTRICT` where appropriate.

---

## API Surface (Laravel REST)

| Method | Endpoint | Auth | Purpose |
|---|---|---|---|
| GET | `/api/v1/packages` | None | All active packages + optional `?category=` |
| GET | `/api/v1/packages/{code}` | None | Single package by code |
| GET | `/api/v1/hotels` | None | All zones with properties |
| GET | `/api/v1/testimonials` | None | Active testimonials |
| GET | `/api/v1/team` | None | Active team members |
| POST | `/api/v1/bookings` | None | Create booking (upserts customer) |
| GET | `/api/v1/bookings/{ref}` | None | Booking status + payment state |
| POST | `/api/v1/payments/webhook` | HMAC | Midtrans payment notification |
| GET | `/api/v1/health` | None | Uptime / health check |

All responses use consistent `{ data: ..., meta: ... }` JSON envelope.

---

## Admin Panel (Filament 3)

**URL:** `https://lomboknatureculture.com/admin`
**Access:** Restricted to `@lomboknatureculture.com` email or `ADMIN_EMAIL` env var

| Resource | CRUD | Custom Actions |
|---|---|---|
| Bookings | Read + Update | Change status, assign guide, add notes |
| Payments | Read only | — |
| Tour Packages | Full CRUD | Repeater itinerary editor |
| Hotels | Full CRUD | Inline property management |
| Testimonials | Full CRUD | Publish toggle, drag reorder |
| Team Members | Full CRUD | Drag reorder |
| Gallery | Full CRUD | Drag reorder, category filter |
| FAQ | Full CRUD | Category management, drag reorder |
| Destinations | Full CRUD | Zone/color management |

Dashboard widget: 4 stat cards (Total Bookings, Pending Payment, Confirmed, Revenue Collected).

---

## Booking Flow (Full System)

```
booking.php (5-step form)
  ↓ CSRF token + honeypot field + rate limit (5/hr per IP)
  ↓
process-booking.php
  ↓ Validate CSRF hash_equals()
  ↓ Check honeypot field empty
  ↓ Check rate limit
  ↓ filter_var() email + name + phone validation
  ↓ Sanitize all inputs (htmlspecialchars + strip_tags)
  ↓ Lookup package price from data.php / DB
  ↓
  ├─ Has price? → INSERT bookings (PostgreSQL $1..$22) ON CONFLICT DO NOTHING
  │               → lnc_format_snap_params('deposit')
  │               → lnc_get_snap_token() → Midtrans API
  │               → INSERT payments ON CONFLICT DO UPDATE snap_token
  │               → Redirect to payment.php
  │
  └─ Quote only → INSERT bookings
                  → lnc_send_admin_alert()
                  → Redirect to thank-you.php

payment.php → Midtrans Snap JS → snap.pay(token)
  ↓
payment-callback.php (POST from Midtrans)
  ↓ Verify HMAC-SHA512 signature
  ↓ Parse order_id: LNC-2026-XXXXX-DEP
  ↓ UPDATE payments SET midtrans_status = 'settlement'
  ↓ UPDATE bookings SET status = 'deposit_paid'
  ↓ lnc_send_deposit_confirmed()

thank-you.php → payment-balance.php (when status=deposit_paid)
  ↓ INSERT payments (balance) ON CONFLICT DO UPDATE snap_token
  ↓ Midtrans Snap balance payment
  ↓
payment-callback.php (balance variant)
  ↓ UPDATE bookings SET status = 'confirmed'
  ↓ lnc_send_balance_confirmed()
```

---

## Security Posture

| Control | Status | Notes |
|---|---|---|
| CSRF on all forms | ✅ Implemented | `hash_equals()` + 32-byte random token |
| Honeypot anti-spam | ✅ Implemented | `name="website"` hidden field + server check |
| Session-based rate limiting | ✅ Implemented | Max 5 submissions/hour per IP |
| Server-side input validation | ✅ Implemented | email, name, phone validated before DB write |
| Midtrans HMAC-SHA512 signature | ✅ Implemented | Webhook verified before any DB update |
| PostgreSQL positional params | ✅ All files | No SQL injection via named param bypass |
| WordPress URLs blocked | ✅ .htaccess | All `/wp-*` paths return 403 |
| `.bak`, `.env`, `db.php` blocked | ✅ .htaccess | Raw source never served |
| Content Security Policy | ✅ Applied | Full CSP with Midtrans allowlist |
| Security response headers | ✅ Applied | 8 headers (X-Frame-Options, XCTO, COOP, etc.) |
| noindex on payment pages | ✅ Applied | 5 pages + robots.txt disallow |
| Secure session cookies | ✅ Applied | HttpOnly, SameSite=Lax, Secure |
| HTTPS / HSTS | ⚠️ Pending | HSTS header is commented; enable post-SSL confirm |
| WordPress files on server | ❌ Not done | Must delete from VPS — not possible without server access |

---

## Performance Posture

| Concern | Status | Notes |
|---|---|---|
| OPcache enabled | ✅ Configured | `deployment/php-fpm.conf` — 128MB, validate=0 |
| Static asset caching | ✅ .htaccess | 1yr for images/fonts, 7d for CSS/JS |
| Gzip compression | ✅ Configured | Nginx level 6, covers HTML/CSS/JS |
| WebP image pairs | ✅ Existing | `.jpg` + `.webp` pairs + `<picture>` in templates |
| Database indexes | ✅ Created | 10 indexes in migrations (bookings, payments, packages) |
| GIN index on JSONB | ✅ Created | `tour_packages.includes` — fast `?` queries |
| Hero image `fetchpriority` | ⚠️ Pending | Add `fetchpriority="high"` to LCP image |
| `<img>` width/height attributes | ⚠️ Pending | CLS risk — ~20 images need explicit dimensions |
| CDN | ⚠️ Not configured | Cloudflare free tier recommended |
| APCu DB result caching | ⚠️ Not configured | Optional — 5-min package cache |
| Synchronous booking email | ⚠️ Present | `mail()` blocks response; consider queue |

---

## Technical Debt Register

| Item | Severity | Impact |
|---|---|---|
| WordPress installation in `public_html/` | HIGH | Security attack surface |
| `create-tables.php` in webroot | MEDIUM | Remove after initial DB setup |
| `single-lnc_package.php.bak` in webroot | MEDIUM | Raw PHP source exposure |
| `php mail()` for transactional email | MEDIUM | Delivery rate and DKIM/SPF issues |
| Hero `<img>` missing `fetchpriority="high"` | MEDIUM | Core Web Vitals LCP |
| All `<img>` missing `width`/`height` | MEDIUM | Cumulative Layout Shift |
| No Redis/APCu cache layer | LOW | Only needed at 50+ concurrent users |
| No PgBouncer connection pooling | LOW | Only needed at 50+ concurrent users |
| HSTS header commented out | LOW | Enable once HTTPS confirmed stable |
| `backend/config/services.php` missing | LOW | Midtrans service config — env vars work |

---

## Remaining Action Items (Prioritised)

### Immediate (before go-live)
1. Set real Midtrans production API keys in `public_html/.env`
2. Set `APP_KEY` in `backend/.env` (`php artisan key:generate`)
3. Change `ADMIN_PASSWORD` after first `php artisan db:seed`
4. Set `DEPLOY_SECRET` to a real random value in GitHub Secrets + `.env`
5. Run `php artisan migrate && php artisan db:seed` on production VPS

### Before Going Public
6. Enable HSTS in `.htaccess` (`Strict-Transport-Security`)
7. Delete WordPress installation from VPS server
8. Delete `create-tables.php` from webroot after DB setup
9. Run `php artisan make:filament-user` or seed admin user
10. Add Midtrans webhook URL in Midtrans dashboard: `https://lomboknatureculture.com/payment-callback.php`

### Recommended (post-launch)
11. Configure SMTP relay in `backend/.env` for reliable Laravel mail
12. Add Cloudflare as CDN (free tier sufficient)
13. Add `width`/`height` to all `<img>` tags for CLS fix
14. Add `fetchpriority="high"` to hero image for LCP improvement
15. Enable APCu or Redis for DB result caching when traffic grows
