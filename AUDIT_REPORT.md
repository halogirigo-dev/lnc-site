# Lombok Nature Culture — Full Production Audit Report
**Date:** June 2026  
**Auditor:** Senior Staff Software Engineer / Technical Architect  
**Scope:** All files in `public_html/`

---

## Architecture Overview

```
public_html/
├── index.php              ← Homepage (entry point)
├── config.php             ← Site-wide constants (SITE_NAME, BASE_URL, etc.)
├── data.php               ← All content data (packages, hotels, team, testimonials)
├── booking.php            ← Multi-step booking form
├── process-booking.php    ← Form POST handler → session + email
├── thank-you.php          ← Post-submission confirmation
├── experiences.php        ← Package catalogue
├── hotels.php             ← Hotel partner directory
├── team.php               ← Team profiles
├── legal.php              ← Terms, Privacy, Cancellation
├── invoice.php            ← Invoice/proposal portal
├── includes/              ← PHP component partials
│   ├── head.php           ← <head> (meta, OG, fonts, CSS)
│   ├── nav.php            ← Sticky navigation
│   ├── hero.php           ← Homepage hero section
│   ├── footer.php         ← Footer + floating WA button
│   ├── packages-grid.php  ← Package card grid
│   ├── hotels.php         ← Hotel zone tabs
│   ├── inquiry-cta.php    ← Quick inquiry form
│   └── ... (8 more partials)
├── assets/
│   ├── css/style.css      ← Single stylesheet (1,956+ lines)
│   └── js/main.js         ← Vanilla JS (512 lines)
├── uploads/               ← Images (hero, packages, logos)
└── fonts/                 ← Local MuseoModerno & Museo TTF files
```

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.x (procedural, no framework) |
| Routing | Direct `.php` file routing + `.htaccess` pretty URLs |
| Templating | Native PHP includes (`includes/*.php`) |
| Styling | Single CSS file with CSS custom properties (design tokens) |
| JavaScript | Vanilla JS (zero dependencies, ~512 lines) |
| Fonts | Local TTF (MuseoModerno, Museo) + Google Fonts (Cormorant Garamond, DM Sans) |
| State | PHP sessions (`$_SESSION`) for booking data |
| Email | PHP `mail()` (native — no SMTP library) |
| CMS | None — data driven by `data.php` arrays |
| Hosting | Hostinger (LiteSpeed server) |
| Deployment | FTP upload (manual) |

## Dependency Analysis

- **Zero npm dependencies** — pure PHP/HTML/CSS/JS
- **Zero composer dependencies** — all native PHP
- **Google Fonts** — 2 families loaded via CDN (external network dependency)
- **WordPress remnants** — full WP installation still present in `public_html/` (not used, security risk)

## Risk Analysis

| Risk | Severity | Status |
|---|---|---|
| wp-config.php exposed with DB credentials | CRITICAL | Blocked via .htaccess |
| WordPress admin panel publicly accessible | CRITICAL | Blocked via .htaccess |
| Autologin PHP file (Hostinger tool) | HIGH | Blocked via .htaccess |
| No CSRF protection on forms | HIGH | FIXED |
| Missing server-side form validation | HIGH | FIXED |
| Duplicate WhatsApp float button | MEDIUM | FIXED |
| Missing OG/Twitter meta tags | MEDIUM | FIXED |
| Missing robots.txt / sitemap.xml | MEDIUM | FIXED |
| Missing favicon | LOW | FIXED |
| 78 inline styles in booking.php | MEDIUM | FIXED |
| Gallery images 18MB unoptimised | MEDIUM | Requires manual conversion |
| PHP mail() used (unreliable) | LOW | Documented for future |

## Improvement Opportunities

1. **Database migration** — `data.php` arrays → MySQL/SQLite for scalability
2. **PHPMailer** — Replace `mail()` with authenticated SMTP
3. **Image optimization** — WebP/AVIF conversion for gallery images
4. **Hero WebP** — CSS background-image can't use `<picture>`; `.htaccess` content negotiation needed
5. **Structured data per page** — TouristAttraction schema for individual experiences
