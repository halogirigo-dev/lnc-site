# WordPress → Direct PHP Migration Checklist
**Project:** Lombok Nature Culture  
**Host:** Hostinger (LiteSpeed shared hosting)  
**Status:** Custom PHP site already live — removing WordPress remnants

---

## BEFORE YOU START — TAKE A BACKUP

Log in to **Hostinger hPanel** → Backups → Create a full backup now.  
This takes 2–3 minutes and gives you a restore point in case anything goes wrong.

---

## STEP 1 — Upload the Clean .htaccess

Your `.htaccess` has been updated. The old WordPress LiteSpeed Cache block has been removed.

1. Open **Hostinger File Manager** (hPanel → Files → File Manager)
2. Navigate to `public_html/`
3. Find `.htaccess` — right-click → Edit (or Upload to overwrite)
4. Upload the new `.htaccess` from your local `LNC V2/public_html/` folder
5. **Verify:** visit your site homepage — it should still load normally

> If the site breaks after uploading, the `.htaccess.bk` (old WP backup) is already on the server. Rename it to `.htaccess` temporarily to restore while you troubleshoot.

---

## STEP 2 — Upload and Run cleanup.php

This script deletes all WordPress files and other junk in one click.

### 2a. Upload the file
1. Upload `cleanup.php` from `LNC V2/public_html/` to `public_html/` on your server

### 2b. Do a dry run first (optional but recommended)
Visit this URL in your browser:
```
https://yourdomain.com/cleanup.php?token=LNC-CLEAN-2026&dry=1
```
You'll see a preview of everything that would be deleted — no actual changes.

### 2c. Run the real cleanup
```
https://yourdomain.com/cleanup.php?token=LNC-CLEAN-2026
```
You'll see a results table. All WordPress files will be gone.

### 2d. DELETE cleanup.php immediately
This is critical — a cleanup script left on the server is a security risk.  
In File Manager: find `cleanup.php` → right-click → Delete.

---

## STEP 3 — Drop the WordPress Database

WordPress's database is still sitting on your server using storage and posing a risk.

1. Go to **hPanel → Databases → MySQL Databases**
2. Find the database named `u631745707_niBPW`
3. Click **Delete** and confirm
4. Also delete the associated MySQL user if prompted

> Note: Your custom PHP site uses **no database** — it runs entirely from `data.php`. Deleting the WP database has zero impact on your live site.

---

## STEP 4 — Verify Everything Works

Visit each of these pages and confirm they load correctly:

| Page | URL |
|------|-----|
| Homepage | `yourdomain.com` |
| Experiences | `yourdomain.com/experiences` |
| Booking form | `yourdomain.com/booking` |
| Hotels | `yourdomain.com/hotels` |
| Team | `yourdomain.com/team` |
| Thank-you (after test submit) | `yourdomain.com/thank-you` |

Also check:
- WhatsApp button is visible and links correctly
- Mobile menu opens and closes
- Submit a test inquiry to confirm emails arrive

---

## STEP 5 — Security Scan (5 Minutes)

After cleanup, run a quick scan to confirm no WP files remain:

1. In File Manager, search for `wp-login.php` — should return nothing
2. Try visiting `yourdomain.com/wp-admin/` in your browser — should return 403 Forbidden
3. Try `yourdomain.com/wp-config.php` — should return 403 Forbidden

If any return a page instead of 403, the `.htaccess` rules are not being applied — check that `mod_rewrite` is enabled in Hostinger.

---

## STEP 6 — Disable WordPress in Hostinger hPanel

1. Go to **hPanel → Websites → Manage**
2. If WordPress is listed as an "installed application," click the three dots → **Delete**
3. This removes Hostinger's WordPress management record (separate from the files)

---

## WHAT WAS DELETED

| File / Folder | Reason |
|---|---|
| `wp-admin/` | WordPress admin panel |
| `wp-includes/` | WordPress core library (2000+ files) |
| `wp-content/` | WP themes, plugins, old uploads |
| `wp-login.php` and all `wp-*.php` files | WordPress core scripts |
| `readme.html` | Exposed WordPress version to attackers |
| `license.txt` | WordPress license file |
| `create_autologin_ldqtx6y1zvlniijufvtyvvg68229jhqh.php` | Suspicious backdoor file |
| `fetch-hotel-images.php` | Open file upload endpoint (security risk) |
| `default.php` | Hostinger placeholder page |
| `single-lnc_package.php.bak` | Publicly accessible backup file |
| `.htaccess.bk` | Old WordPress .htaccess backup |
| `lnc_tmp/` | Temporary folder |

---

## WHAT STAYS (Your Site)

```
public_html/
├── .htaccess          ← Updated (clean, WP-free)
├── index.php          ← Homepage
├── booking.php        ← Booking form
├── experiences.php    ← Experiences page
├── hotels.php         ← Hotels page
├── team.php           ← Team page
├── thank-you.php      ← Post-booking confirmation
├── process-booking.php← Form handler
├── invoice.php        ← Invoice portal
├── legal.php          ← Terms & policies
├── config.php         ← Site constants
├── data.php           ← All content data
├── includes/          ← PHP components (nav, hero, footer, etc.)
├── assets/            ← CSS + JS
├── uploads/           ← Images (logo, packages, hotels, gallery)
└── fonts/             ← Local Museo fonts
```

---

## AFTER MIGRATION — RECOMMENDED NEXT STEPS

These are from the architecture audit — prioritise after WP is fully removed:

1. **Add Open Graph meta tags** to `head.php` (social sharing previews)
2. **Create `sitemap.xml`** — submit to Google Search Console
3. **Create `robots.txt`** — already drafted in the audit report
4. **Add JSON-LD schema** — helps Google identify you as a TravelAgency
5. **Compress gallery images to WebP** — currently 18MB, should be under 3MB
6. **Add CSRF protection** to booking and inquiry forms

---

*Migration guide for LNC V2 — June 2026*
