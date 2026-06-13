# PERFORMANCE REPORT — Lombok Nature Culture
**Date:** 2026-06-13  
**Scope:** PHP frontend + PostgreSQL backend performance

---

## Current Performance Baseline

### PHP Frontend Bottlenecks
| Issue | Impact | Solution |
|-------|--------|---------|
| `data.php` parsed on every request | Medium | DB loading with APCu/OPcache cache |
| No HTTP caching headers on PHP pages | Medium | Add `Cache-Control` for static-like pages |
| Images served without dimensions | Medium | Add `width` and `height` attributes |
| No CDN | Medium | Cloudflare free tier |
| Synchronous email on booking submit | High | Use queued mail (Laravel Mail) |
| No DB query optimization | Low | Indexes added in migration |

---

## Database Performance

### Indexes Applied (in migrations)
```sql
-- bookings
CREATE INDEX idx_bookings_ref        ON bookings(ref);
CREATE INDEX idx_bookings_email      ON bookings(email);
CREATE INDEX idx_bookings_status     ON bookings(status);
CREATE INDEX idx_bookings_created_at ON bookings(created_at DESC);

-- payments
CREATE INDEX idx_payments_booking_ref  ON payments(booking_ref);
CREATE INDEX idx_payments_order_id     ON payments(midtrans_order_id);

-- tour_packages
CREATE INDEX idx_tour_packages_category ON tour_packages(category);
CREATE INDEX idx_tour_packages_active   ON tour_packages(is_active);
CREATE INDEX idx_tour_packages_gin      ON tour_packages USING GIN(includes);

-- hotels/properties
CREATE INDEX idx_hotel_properties_hotel_id ON hotel_properties(hotel_id);
```

### PostgreSQL vs MySQL
PostgreSQL's `JSONB` type with GIN indexes provides significantly faster searches on package `includes`, `excludes`, and `itinerary` fields vs MySQL's JSON type.

---

## PHP OPcache Configuration

Configured in `deployment/php-fpm.conf`:
```ini
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 0         ; 0 = never revalidate (production)
opcache.validate_timestamps = 0     ; Disable file timestamp checks
```

**With OPcache**, PHP parsing overhead for `data.php` (315 lines) is eliminated after first request.

---

## Image Performance

### Current State
- `.jpg` + `.webp` pairs exist in `/uploads/`
- Templates use `<picture>` with WebP `<source>` — correct approach ✓
- Images missing `width` and `height` attributes (causes Cumulative Layout Shift)

### Recommendations

**Add explicit dimensions to template images:**
```html
<!-- packages-grid.php — add width/height -->
<img src="..." alt="..." loading="lazy" width="800" height="500"
     style="width:100%;height:100%;object-fit:cover;display:block;">
```

**Add `fetchpriority="high"` to hero image:**
```html
<img src="/uploads/hero-background.webp" fetchpriority="high" loading="eager">
```

---

## Caching Strategy

### Phase 1 (current): OPcache
- PHP files cached in memory via OPcache
- No application-level cache needed for this traffic volume

### Phase 2 (recommended): DB Result Caching
Add APCu caching for package/hotel data (changes rarely):

```php
// In data.php — cache DB results for 5 minutes
function lnc_cached_packages(): ?array {
  if (function_exists('apcu_fetch')) {
    $cached = apcu_fetch('lnc_packages', $ok);
    if ($ok) return $cached;
  }
  $data = lnc_get_packages_from_db();
  if ($data && function_exists('apcu_store')) {
    apcu_store('lnc_packages', $data, 300);
  }
  return $data;
}
```

### Phase 3 (future): Redis/Valkey
For high traffic, use Redis as Laravel cache driver:
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## CDN Recommendations

### Cloudflare (Free Tier)
1. Add site to Cloudflare
2. Enable "Auto Minify" for HTML, CSS, JS
3. Enable "Polish" for image optimization
4. Set Page Rules:
   - `/uploads/*` → Cache Everything, Edge Cache TTL: 1 month
   - `/assets/*` → Cache Everything, Edge Cache TTL: 1 week
   - `/*.php` → Bypass Cache

### Expected improvements
- Static assets served from edge (0ms to CDN, ~5-20ms to visitors)
- WebP conversion handled automatically
- DDoS protection at no cost

---

## Core Web Vitals Targets

| Metric | Current (estimate) | Target |
|--------|--------------------|--------|
| LCP (Largest Contentful Paint) | 2.5–4s | < 2.5s |
| FID / INP (Interaction) | 50–100ms | < 200ms |
| CLS (Cumulative Layout Shift) | ~0.1 | < 0.1 |

**Biggest LCP improvement:** Add `fetchpriority="high"` to hero image + ensure hero image is preloaded.

**Biggest CLS fix:** Add explicit width/height to all `<img>` tags.

---

## Nginx Gzip (Configured)

From `deployment/nginx.conf`:
```nginx
gzip on;
gzip_comp_level 6;
gzip_types text/plain text/css application/json application/javascript image/svg+xml;
```

Expected: 60-80% compression ratio on CSS/JS/HTML responses.

---

## PHP Performance Quick Wins

| Action | Estimated Gain |
|--------|---------------|
| OPcache enabled | 40-60% faster PHP execution |
| PostgreSQL connection pooling (PgBouncer) | 20-30% for high concurrency |
| Increase `pm.max_children` (PHP-FPM) | Better concurrency |
| Minimize `require_once` chain | Minor — already optimized |
