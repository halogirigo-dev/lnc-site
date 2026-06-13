# Lombok Nature Culture — Architectural Audit Report
**Audited:** June 2026  
**Auditor Role:** Senior Frontend Architect & Web Performance Specialist  
**Files Reviewed:** `index.php`, `booking.php`, `process-booking.php`, `config.php`, `data.php`, `assets/css/style.css` (1,956 lines), `assets/js/main.js` (512 lines), all 11 `includes/` partials

---

## ⬡ Overall Health Score: 68 / 100 — "Solid Foundation, Critical Security Gaps"

| Dimension | Score | Verdict |
|---|---|---|
| Structure & DRY | 80/100 | ✅ Good component system |
| Performance | 65/100 | ⚠️ Images & fonts unoptimised |
| Security | 45/100 | 🔴 Critical — WP files exposed |
| SEO / Discoverability | 55/100 | ⚠️ Missing OG, sitemap, schema |
| Scalability | 75/100 | ✅ Clean pattern, DB-ready |
| Code Quality | 70/100 | ⚠️ Inline style debt in booking.php |

The site's PHP-includes architecture is genuinely well-thought-out. `data.php` as a single source of truth, `config.php` for constants, and the `includes/` component system show real structural intent. The critical problems are in security and discoverability — two areas that directly threaten revenue.

---

## 🔴 HIGH PRIORITY — Fix Before Next Deployment

### 1. WordPress Files Exposed in `public_html/` — CRITICAL SECURITY RISK

This is the most urgent issue in the entire codebase. A full WordPress installation (or its remnants) is sitting in the same public directory as your live PHP site:

```
wp-admin/          ← Admin panel publicly accessible
wp-login.php       ← Login endpoint exposed (brute-force target)
wp-config.php      ← May contain database credentials
wp-config-sample.php
wp-activate.php
wp-includes/       ← PHP library files, publicly reachable
wp-content/
readme.html        ← Exposes WordPress version number to attackers
```

**Risk:** `wp-login.php` alone will receive thousands of automated brute-force attempts per day once indexed. `wp-config.php` may expose your database host, username, and password in plaintext. `readme.html` tells attackers exactly which WordPress version you're running, enabling targeted exploits.

**Fix:** Delete or move the entire WordPress installation. If you still need it, it must live outside `public_html/` or be protected via `.htaccess` IP restrictions.

---

### 2. Suspicious Autologin File

```
create_autologin_ldqtx6y1zvlniijufvtyvvg68229jhqh.php
```

This filename pattern (random hash suffix) is characteristic of malware backdoors or compromised hosting tools. **Do not assume this is safe.** Inspect its contents immediately and delete it if it was not intentionally placed there.

---

### 3. Public Backup & Temp Files

```
single-lnc_package.php.bak   ← Backup file served publicly
lnc_tmp/                      ← Temp folder accessible via browser
```

Backup files expose your source code without PHP execution — an attacker can read your raw PHP. Add to `.htaccess`:

```apache
<FilesMatch "\.(bak|tmp|log|sql|swp)$">
  Require all denied
</FilesMatch>
```

---

### 4. Missing CSRF Protection on Booking & Inquiry Forms

`process-booking.php` processes `$_POST` with no CSRF token check. Any external site can silently submit forged requests on a user's behalf. This is a standard vulnerability.

**Fix — add to `process-booking.php` top:**

```php
session_start();
// Generate token on form load (in booking.php):
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Validate on POST:
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
  http_response_code(403);
  exit('Invalid request.');
}
```

Add `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">` to both forms.

---

### 5. Missing Fonts — Raleway & Lato Are Never Loaded

`head.php` imports only two font families from Google Fonts:
- **Cormorant Garamond** (used for body italic/editorial)
- **DM Sans** (used in CSS classes)

But `booking.php` (78 inline style attributes) and `inquiry-cta.php` reference `font-family:'Raleway'` and `font-family:'Lato'` — **neither of which is imported anywhere**. These fall back to system fonts, creating visual inconsistency on the most important conversion pages.

Your local `/fonts/` directory contains Museo and MuseoModerno (`.ttf`) files which are also not declared via `@font-face` in `style.css`.

**Fix:** Either add Raleway + Lato to the Google Fonts import in `head.php`, or replace all `Raleway`/`Lato` references in `booking.php` with your declared font variables (see Better Practice section below).

---

### 6. Gallery Images: 18MB Unoptimised

```
/uploads/gallery/   →  18 MB total (5 JPEG/JPG files)
```

These BUCHSTEINERPHOTOGRAPHY images have uppercase `.JPG` extensions and appear to be uncompressed originals. On mobile, this creates multi-second load delays and high data costs for your international audience.

**Fix:**
1. Convert to WebP with quality 80: `cwebp input.jpg -q 80 -o output.webp`
2. Serve with `<picture>` fallback (see Better Practice below)
3. All gallery images already have `loading="lazy"` — good. Keep it.
4. Package card images (292KB each) should also be compressed to under 80KB at their displayed size.

---

### 7. No Open Graph, No Structured Data, No Sitemap

`head.php` has zero social sharing metadata and no SEO schema. This means:
- WhatsApp/Facebook previews show a blank card when guests share your link
- Google can't identify you as a TravelAgency with ratings and location
- No `robots.txt` means crawlers get no guidance; no `sitemap.xml` means pages may not be indexed

**Add to `head.php`:**

```php
<!-- Open Graph -->
<meta property="og:type"        content="website">
<meta property="og:title"       content="<?= htmlspecialchars($page_title) ?> — <?= SITE_NAME ?>">
<meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta property="og:image"       content="<?= BASE_URL ?>/uploads/hero-background.jpg">
<meta property="og:url"         content="https://lomboknatureculture.com<?= $_SERVER['REQUEST_URI'] ?>">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= htmlspecialchars($page_title) ?> — <?= SITE_NAME ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta name="twitter:image"       content="<?= BASE_URL ?>/uploads/hero-background.jpg">

<!-- Canonical -->
<link rel="canonical" href="https://lomboknatureculture.com<?= strtok($_SERVER['REQUEST_URI'],'?') ?>">
```

**Add `robots.txt` to `public_html/`:**

```
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /.private/
Disallow: /lnc_tmp/
Sitemap: https://lomboknatureculture.com/sitemap.xml
```

**Add JSON-LD to `head.php`** (tourism schema):

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "TravelAgency",
  "name": "PT Lombok Nature Culture",
  "url": "https://lomboknatureculture.com",
  "logo": "https://lomboknatureculture.com/uploads/logo-1777215811265.png",
  "description": "Your Ethical Partner for Authentic Private Journeys in Lombok",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Lombok",
    "addressRegion": "West Nusa Tenggara",
    "addressCountry": "ID"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "500"
  }
}
</script>
```

---

### 8. Duplicate WhatsApp Float Buttons

`footer.php` renders **two separate WhatsApp floating buttons** — `.wa-float` and `.whatsapp-float` — both fixed-position on every page. One is dead weight and one may be invisible behind the other.

**Fix:** Delete the first `.wa-float` block in `footer.php`, keep only `.whatsapp-float` (or vice versa). Consolidate into a single consistent element.

---

### 9. Booking Form Has No Server-Side Validation

`process-booking.php` uses only `htmlspecialchars(strip_tags(trim()))`. The email field has `FILTER_VALIDATE_EMAIL` only for the confirmation send — not to reject the submission. A guest can submit with an invalid email and get no error message.

**Fix:** Add validation before storing/emailing:

```php
if (!filter_var($booking['email'], FILTER_VALIDATE_EMAIL)) {
  header('Location: booking.php?error=invalid_email');
  exit;
}
if (empty($booking['name']) || strlen($booking['name']) < 2) {
  header('Location: booking.php?error=invalid_name');
  exit;
}
```

Also: `@mail()` silences errors. Replace with PHPMailer or similar for reliable delivery tracking.

---

## ⚠️ MEDIUM PRIORITY — Refactor When Possible

### 10. `booking.php` Has 78 Inline Style Attributes

This is the most significant code quality issue. Inline styles:
- Cannot be cached by the browser
- Cannot be overridden at breakpoints (no `!important` in media queries works on inline)
- Cannot be themed or updated centrally
- Bloat HTML payload by ~8–12KB

The entire booking page's visual design is locked in inline `style=""` attributes. This makes mobile responsiveness adjustments painful. See the Better Practice section for the refactor path.

### 11. Hero Background Has No `<picture>` / WebP Fallback

```php
// hero.php — current:
background-image: url('/uploads/hero-background.jpg')
```

This is a 136KB JPEG in a CSS `background-image` — it cannot use the `<picture>` element or `srcset`. No WebP version is served.

**Fix:** Convert hero to an `<img>` or use CSS custom property that swaps based on a `@supports` query, or generate a WebP and serve it via `.htaccess` content negotiation.

### 12. No `favicon` Declared

`head.php` has no `<link rel="icon">`. Browsers will make an extra HTTP request to `/favicon.ico` that returns a 404 on every page load.

### 13. `loading="lazy"` Missing on Package Card Images

Gallery and hotel images correctly use `loading="lazy"`. Package card images in `packages-grid.php` do not. Add `loading="lazy"` to the `<img>` tags inside `.pkg-card`.

---

## ✅ WHAT'S WORKING WELL — Don't Change

- **`config.php` + `data.php` pattern** is excellent. Single source of truth for all pricing, itineraries, and team data. Any future CMS migration has a clean extraction point.
- **Cache-busting via `filemtime()`** on CSS and JS — professional approach, no stale asset issues.
- **PHP includes component system** — DRY, logical, scalable. Adding a blog or dashboard page is just `include 'includes/head.php'`.
- **Mobile hamburger menu** — proper `aria-expanded`, ESC key support, overlay close — accessible.
- **IntersectionObserver scroll reveal** — performant, no library dependency.
- **`passive: true`** on scroll event listeners — correct performance practice.
- **Multi-step booking form** — strong UX pattern. The sidebar summary updating in real time is a genuine CRO asset.
- **`process-booking.php`** generating a booking reference and sending both team + guest confirmation emails is the right flow.

---

## ⬡ BETTER PRACTICE IMPLEMENTATION — Refactoring `booking.php`

The booking form is your most important conversion asset. Right now, its styles are locked in 78 inline attributes. Here is the recommended refactor approach:

### Step 1 — Add CSS Custom Properties to `style.css`

```css
/* ─── DESIGN TOKENS ─────────────────────────── */
:root {
  /* Typography */
  --font-heading:  'Cormorant Garamond', Georgia, serif;
  --font-ui:       'DM Sans', system-ui, sans-serif;
  --font-mono:     'MuseoModerno', monospace;

  /* Colors */
  --color-ink:     #1a2118;
  --color-muted:   #8a7d6e;
  --color-teal:    #2cb896;
  --color-sand:    #e0d8ce;
  --color-cream:   #ede9e1;
  --color-surface: #ffffff;

  /* Spacing */
  --space-sm:  8px;
  --space-md:  16px;
  --space-lg:  32px;
  --space-xl:  48px;
}

/* ─── BOOKING LAYOUT ────────────────────────── */
.booking-header {
  background: var(--color-surface);
  border-bottom: 1px solid rgba(0,0,0,.08);
  padding: 0 48px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.booking-step__title {
  font-family: var(--font-heading);
  font-weight: 900;
  font-size: clamp(24px, 4vw, 34px);
  color: var(--color-ink);
  margin-bottom: 8px;
}

.booking-step__subtitle {
  font-family: var(--font-ui);
  font-size: 15px;
  color: var(--color-muted);
  margin-bottom: 32px;
  line-height: 1.7;
}

.form-label {
  display: block;
  font-family: var(--font-ui);
  font-weight: 600;
  font-size: 10px;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-muted);
  margin-bottom: 8px;
}

.pkg-option {
  background: var(--color-surface);
  padding: 18px 20px;
  margin-bottom: 8px;
  display: grid;
  grid-template-columns: 48px 1fr auto;
  gap: 14px;
  align-items: center;
  border: 2px solid var(--color-sand);
  cursor: pointer;
  transition: border-color .2s, box-shadow .2s;
}

.pkg-option:hover,
.pkg-option.selected {
  border-color: var(--color-teal);
  box-shadow: 0 0 0 1px var(--color-teal);
}

.pkg-option__icon {
  width: 44px;
  height: 44px;
  background: var(--color-cream);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.pkg-option__id {
  font-family: var(--font-ui);
  font-weight: 700;
  font-size: 9px;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-teal);
  margin-bottom: 3px;
}

.pkg-option__title {
  font-family: var(--font-heading);
  font-weight: 800;
  font-size: 18px;
  color: var(--color-ink);
  margin-bottom: 2px;
}

.pkg-option__sub {
  font-family: var(--font-ui);
  font-size: 12px;
  color: var(--color-muted);
}

.pkg-option__price {
  font-family: var(--font-heading);
  font-weight: 900;
  font-size: 15px;
  color: var(--color-teal);
  text-align: right;
}
```

### Step 2 — Refactor `booking.php` package option HTML

**Before (current — inline everything):**
```php
<div class="pkg-option"
  style="background:#fff;padding:18px 20px;display:grid;grid-template-columns:48px 1fr auto;
         gap:14px;border:2px solid #e0d8ce;cursor:pointer;transition:border-color .2s;">
  <div style="width:44px;height:44px;background:#ede9e1;display:flex;align-items:center;
              justify-content:center;font-size:18px;">◈</div>
  <div>
    <p style="font-family:'Raleway',sans-serif;font-weight:700;font-size:9px;
              letter-spacing:.18em;text-transform:uppercase;color:#2cb896;">LNC-01</p>
    <p style="font-family:'Raleway',sans-serif;font-weight:800;font-size:16px;
              color:#1a2118;">Lombok Signature</p>
  </div>
</div>
```

**After (semantic, CSS-driven):**
```php
<div class="pkg-option<?= $pkg['id'] === $prefill_exp ? ' selected' : '' ?>"
  data-title="<?= htmlspecialchars($pkg['title']) ?>"
  data-price="<?= fmt_idr($pkg['price']) ?>"
  data-duration="<?= htmlspecialchars($pkg['duration']) ?>"
  data-id="<?= $pkg['id'] ?>">

  <div class="pkg-option__icon" aria-hidden="true"><?= $icons[$pkg['category']] ?? '◉' ?></div>

  <div class="pkg-option__body">
    <p class="pkg-option__id"><?= $pkg['id'] ?> · <?= htmlspecialchars($pkg['duration']) ?></p>
    <p class="pkg-option__title"><?= htmlspecialchars($pkg['title']) ?></p>
    <p class="pkg-option__sub"><?= htmlspecialchars($pkg['subtitle']) ?></p>
  </div>

  <div class="pkg-option__pricing">
    <p class="pkg-option__price"><?= fmt_idr($pkg['price']) ?></p>
    <p class="pkg-option__per">/pax · excl. hotel</p>
    <div class="pkg-radio-dot<?= $pkg['id'] === $prefill_exp ? ' pkg-radio-dot--selected' : '' ?>"></div>
  </div>

  <input type="radio" name="package" value="<?= $pkg['id'] ?>"
    class="sr-only" <?= $pkg['id'] === $prefill_exp ? 'checked' : '' ?>>
</div>
```

This change alone reduces `booking.php` HTML payload by approximately 6–8KB and makes every style change a one-line CSS edit instead of a grep-and-replace across 78 occurrences.

---

## SCALABILITY — Can This Architecture Support a Blog or Dashboard?

**Yes, with one addition.** The current pattern:

```
page.php → require config.php + data.php → include includes/*.php
```

...scales cleanly. To add a blog:

1. Create `blog.php` and `includes/blog-*.php` partials (same pattern)
2. Add a `/posts/` folder with individual `.php` files or a flat-file format
3. Long term: migrate `data.php` arrays to SQLite or MySQL — the array structure maps directly to relational tables

For a customer dashboard (invoice tracking), the session-based approach in `process-booking.php` is already the right foundation. Add a simple `invoices/` table and a `dashboard.php` page behind a password check.

The architecture does **not** need to be rebuilt to scale. It needs database-backed persistence and a thin routing layer (a single `index.php` with URL parsing, or Apache `mod_rewrite` rules).

---

## QUICK-WIN CHECKLIST

| Priority | Item | Effort | Impact |
|---|---|---|---|
| 🔴 Immediate | Delete WordPress files & WP remnants | 5 min | 🔴 Critical security |
| 🔴 Immediate | Inspect & delete autologin PHP file | 5 min | 🔴 Possible backdoor |
| 🔴 Immediate | Add CSRF token to both forms | 1 hr | High security |
| 🔴 Immediate | Remove/block .bak, lnc_tmp, readme.html | 10 min | Security |
| 🔴 Immediate | Fix Raleway/Lato font imports | 30 min | Visual fidelity |
| 🔴 Immediate | Remove duplicate WhatsApp float | 5 min | UX |
| ⚠️ This week | Add OG / Twitter card meta to head.php | 1 hr | Social sharing |
| ⚠️ This week | Add JSON-LD TravelAgency schema | 1 hr | SEO ranking |
| ⚠️ This week | Create robots.txt + sitemap.xml | 1 hr | SEO indexing |
| ⚠️ This week | Compress gallery images → WebP | 2 hrs | Page speed |
| ⚠️ This week | Add server-side form validation | 2 hrs | Data integrity |
| 📅 Next sprint | Refactor booking.php inline styles → CSS | 4 hrs | Maintainability |
| 📅 Next sprint | Add lazy loading to package card images | 30 min | Performance |
| 📅 Next sprint | Add favicon | 15 min | Polish |

---

*End of Audit — Lombok Nature Culture Architecture Review*
