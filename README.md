# LNC PHP Site — Hostinger Upload Guide

## Files in this package
```
php-site/
├── index.php          ← Homepage
├── experiences.php    ← All tour packages (tabbed)
├── booking.php        ← 5-step inquiry form
├── invoice.php        ← 3-stage proposal/invoice/receipt
├── hotels.php         ← Hotel database (4 zones, 14 properties)
├── team.php           ← Team profiles
├── legal.php          ← Terms, Privacy, Cancellation, Cookies
├── thank-you.php      ← Post-booking confirmation
├── config.php         ← Site settings (edit this first!)
├── data.php           ← All real tour & hotel data
├── .htaccess          ← Apache config for Hostinger
├── includes/          ← Reusable PHP sections
│   ├── head.php
│   ├── nav.php
│   ├── hero.php
│   ├── experience-bar.php
│   ├── packages-grid.php
│   ├── hotels.php
│   ├── philosophy.php
│   ├── how-it-works.php
│   ├── trust.php
│   ├── team-preview.php
│   ├── testimonials.php
│   ├── gallery.php
│   ├── inquiry-cta.php
│   └── footer.php
├── assets/
│   ├── css/style.css  ← All styles
│   └── js/main.js     ← All interactions
└── uploads/
    └── logo-*.png     ← Your logo
```

## Step-by-step: Upload to Hostinger

1. Log in to **hPanel** → **File Manager**
2. Navigate to `public_html/`
3. Create a folder e.g. `lnc/` (or upload directly to root)
4. Upload and extract this ZIP into that folder
5. Visit `yourdomain.com/lnc/` — site is live!

## Before going live — edit config.php

Open `config.php` and update:
- `SITE_PHONE` → your real WhatsApp number
- `SITE_WA` → your WhatsApp number (digits only, no +)
- `SITE_EMAIL` → your real email
- `BASE_URL` → set to `/lnc` if installed in a subfolder, or leave blank for root

## Adding real photos

Replace `<div class="ph">` placeholders in any include file with:
```html
<img src="<?= UPLOADS_URL ?>/your-photo.jpg" alt="description" style="width:100%;height:300px;object-fit:cover;">
```

Upload photos to the `uploads/` folder.

## WordPress Integration

If you prefer to run inside WordPress:
- Install the **Insert PHP Code Snippet** plugin
- Or use **Elementor** and manually recreate sections using the CSS variables in style.css
- Color palette: --teal: #2cb896 · --gold: #c4964a · --dark: #1a2118 · --bg: #f7f4ee

## Email / Form Setup

The booking form posts to `thank-you.php`. To actually send emails:
1. Install **PHPMailer** or use Hostinger's built-in PHP mail()
2. Add to `thank-you.php`:
```php
mail($email_to, 'New Booking Request', $message, 'From: ' . SITE_EMAIL);
```
Or use a free service like **Formspree** by changing the form action.

## Need help?
Contact: hello@lnc-travel.com
